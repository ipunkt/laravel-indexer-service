<?php

namespace Tests\Feature\Items;

use Illuminate\Support\Facades\Queue;
use Ipunkt\LaravelIndexer\Jobs\Items\DeleteItem;
use Ipunkt\LaravelIndexer\Jobs\Solr\Optimize;
use Solarium\Client;
use Solarium\Exception\HttpException;
use Solarium\QueryType\Update\Query\Query;
use Tests\TestCase;

class ItemDeletionTest extends TestCase
{
    /** @test */
    public function it_is_not_a_public_api()
    {
        // ARRANGE
        $this->logout();

        // ACT
        $response = $this->deleteJson('/public/v1/items/123', [], $this->headers());

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
    public function it_deletes_an_item_by_calling_api()
    {
        $this->setToken(env('SERVICE_SECURE_TOKEN'));

        // ARRANGE
        $this->expectsJobs(DeleteItem::class, Optimize::class);

    	// ACT
        $response = $this->deleteJson('/secure/v1/items/1', [], $this->headers());

    	// ASSERT
    	$response->assertStatus(204);
    }

    /** @test */
    public function it_queues_the_item_by_dispatching_job()
    {
        // ARRANGE
        Queue::fake();

    	// ACT
        dispatch(new DeleteItem(1234));

    	// ASSERT

        // Queue is \Illuminate\Support\Testing\Fakes\QueueFake
    	Queue::assertPushed(DeleteItem::class);
    }

    /** @test */
    public function the_job_will_request_the_solr_via_client()
    {
        // ARRANGE
        $solr = \Mockery::mock(Client::class);
        $solr->shouldReceive('createUpdate')->andReturn(new Query());
        $solr->shouldReceive('update')->withAnyArgs()->andReturn();

    	// ACT
        $job = new DeleteItem(1234);

        $throwsException = false;
        try {
            $job->handle($solr);
        } catch (\Exception $e) {
            $throwsException = true;
        }

    	// ASSERT
        $this->assertFalse($throwsException);
    }

    /** @test */
    public function the_job_will_request_the_solr_via_client_and_throws_an_exception_on_error()
    {
        // ARRANGE
        $solr = \Mockery::mock(Client::class);
        $solr->shouldReceive('createUpdate')->andReturn(new Query());
        $solr->shouldReceive('update')->withAnyArgs()->andThrow(new HttpException('Test failed'));

    	// ACT
        $job = new DeleteItem(1234);

        $throwsException = false;
        try {
            $job->handle($solr);
        } catch (\Exception $e) {
            $throwsException = true;
        }

    	// ASSERT
        $this->assertTrue($throwsException);
    }

    /** @test */
    public function it_fails_when_validation_rules_failed()
    {
        // ARRANGE
        $this->doesntExpectJobs(DeleteItem::class);

        // ACT
        $response = $this->deleteJson('/public/v1/items/1234-abcd', [], $this->headers());

        // ASSERT
        $response->assertStatus(400);
        $response->assertExactJson([
            'errors' => [
                [
                    'status' => '400',
                    'title' => 'The given data failed to pass validation.',
                    'source' => [
                        [
                            'pointer' => 'id',
                            'message' => 'The id must be a number.'
                        ],
                    ],
                ],
            ],
        ]);
    }
}
