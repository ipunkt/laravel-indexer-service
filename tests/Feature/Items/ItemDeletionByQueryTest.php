<?php


namespace Tests\Feature\Items;


use Ipunkt\LaravelIndexer\Jobs\Items\DeleteByQuery;
use Ipunkt\LaravelIndexer\Jobs\Solr\Optimize;
use Tests\TestCase;

class ItemDeletionByQueryTest extends TestCase
{
	/** @test */
	public function it_is_not_a_public_api()
	{
		// ARRANGE
		$this->logout();

		// ACT
		$response = $this->deleteJson('/public/v1/items/-', [], $this->headers());

		// ASSERT
		$response->assertStatus(400);
		$response->assertExactJson([
			'errors' => [
				[
					'status' => '400',
					'title' => 'Token invalid',
				]
			]
		]);
	}

	/** @test */
	public function it_needs_a_query_parameter_so_it_fails_with_validation_when_missing()
	{
		$this->setToken(env('SERVICE_SECURE_TOKEN'));

		// ARRANGE
		$this->doesntExpectJobs(DeleteByQuery::class, Optimize::class);

		// ACT
		$response = $this->deleteJson('/secure/v1/items/-', [
			'query1' => 'source:jobsterne AND type:job AND delete_after_tdt:[2018-07-20T13:07:15Z TO *]',
		], $this->headers());

		// ASSERT
		$response->assertStatus(400);
	}

	/** @test */
	public function it_deletes_items_by_calling_api_with_query()
	{
		$this->setToken(env('SERVICE_SECURE_TOKEN'));

		// ARRANGE
		$this->expectsJobs(DeleteByQuery::class, Optimize::class);

		// ACT
		$response = $this->deleteJson('/secure/v1/items/-', [
			'query' => 'source:jobsterne AND type:job AND delete_after_tdt:[NOW TO *]',
		], $this->headers());

		// ASSERT
		$response->assertStatus(204);
	}
}