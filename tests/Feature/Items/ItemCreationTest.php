<?php

namespace Tests\Feature\Items;

use Illuminate\Support\Facades\Queue;
use Ipunkt\LaravelIndexer\Jobs\Items\CreateItem;
use Ipunkt\LaravelIndexer\Jobs\Solr\Optimize;
use Mockery\MockInterface;
use Solarium\Client;
use Solarium\Exception\HttpException;
use Solarium\QueryType\Update\Query\Query;
use Tests\TestCase;

class ItemCreationTest extends TestCase
{
    /** @test */
    public function it_is_not_a_public_api()
    {
        // ARRANGE
        $item = [
            'id' => 1,
            'source' => 'feed',
            'type' => 'article',
            'content' => $this->faker()->sentence(),
        ];

        // ACT
        $response = $this->postJson('/public/v1/items', $this->createRequestModel('items', $item), $this->headers());

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
    public function it_creates_an_item_by_calling_api()
    {
        $this->setToken(env('SERVICE_SECURE_TOKEN'));

        // ARRANGE
        $item = [
            'id' => 1,
            'source' => 'feed',
            'type' => 'article',
            'content' => $this->faker()->sentence(),
        ];

        $this->expectsJobs(CreateItem::class, Optimize::class);

        // ACT
        $response = $this->postJson('/secure/v1/items', $this->createRequestModel('items', $item), $this->headers());

        // ASSERT
        $response->assertStatus(204);
    }

    /** @test */
    public function it_queues_the_item_by_dispatching_job()
    {
        // ARRANGE
        Queue::fake();

        $item = [
            'source' => 'feed',
            'type' => 'article',
            'content' => $this->faker()->sentence(),
        ];

        // ACT
        dispatch(new CreateItem($item));

        // ASSERT

        // Queue is \Illuminate\Support\Testing\Fakes\QueueFake
        Queue::assertPushed(CreateItem::class);
    }

    /** @test */
    public function the_job_will_request_the_solr_via_client()
    {
        // ARRANGE
        /** @var Client|MockInterface $solr */
        $solr = \Mockery::mock(Client::class);
        $solr->shouldReceive('createUpdate')->andReturn(new Query());
        $solr->shouldReceive('update')->withAnyArgs()->andReturn();

        // ACT
        $job = new CreateItem($item = [
            'source' => 'feed',
            'type' => 'article',
            'content' => $this->faker()->sentence(),
        ]);

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
        $job = new CreateItem($item = [
            'source' => 'feed',
            'type' => 'article',
            'content' => $this->faker()->sentence(),
        ]);

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
        $this->setToken(env('SERVICE_SECURE_TOKEN'));

        // ARRANGE
        $item = [
            'source' => 'a-hgz',
            'type' => 'article',
            'content' => $this->faker()->sentence(),
        ];

        $this->doesntExpectJobs(CreateItem::class);

        // ACT
        $response = $this->postJson('/secure/v1/items', $this->createRequestModel('items', $item), $this->headers());

        // ASSERT
        $response->assertStatus(400);
        $response->assertExactJson([
            'errors' => [
                [
                    'status' => '400',
                    'title' => 'The given data failed to pass validation.',
                    'source' => [
                        [
                            'pointer' => 'source',
                            'message' => 'The selected source is invalid.'
                        ],
                    ],
                ],
            ],
        ]);
    }
}
