<?php

namespace Tests\Feature\Solr;

use Carbon\Carbon;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class OptimizeTest extends TestCase
{
    /** @test */
    public function it_can_dispatch_optimize_command()
    {
        // ARRANGE
        Bus::fake();

        $this->setToken(env('SERVICE_SECURE_TOKEN'));

        // ARRANGE
        $item = [
            'id' => 1,
            'source' => 'ahgz',
            'type' => 'article',
            'content' => $this->faker()->sentence(),
        ];

    	// ACT
        $response = $this->postJson('/secure/v1/items', $this->createRequestModel('items', $item), $this->headers());

    	// ASSERT
        Bus::assertDispatched(\Ipunkt\LaravelIndexer\Jobs\Items\CreateItem::class);
        Bus::assertDispatched(\Ipunkt\LaravelIndexer\Jobs\Solr\Optimize::class);
    }
}
