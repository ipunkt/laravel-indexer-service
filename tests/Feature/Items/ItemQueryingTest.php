<?php

namespace Tests\Feature\Items;

use Tests\TestCase;

class ItemQueryingTest extends TestCase
{
    /** @test */
    public function it_is_not_a_public_api()
    {
        // ARRANGE
        $this->logout();

        // ACT
        $response = $this->getJson('/public/v1/items', $this->headers());

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
    public function it_can_query_items_from_solr_with_creating_and_deletion()
    {
        // ARRANGE
        $this->setToken(env('SERVICE_SECURE_TOKEN'));

        $data = collect();
        $data->push([
            'id' => 1,
            'title' => $this->faker()->name,
            'content' => $this->faker()->sentence(),
            'tags' => [
                $this->faker()->randomElement(['red', 'green', 'blue']),
                $this->faker()->randomElement(['bold', 'tall', 'tiny', 'full'])
            ],
            'color' => '#' . $this->faker()->hexColor,
        ])->push([
            'id' => 2,
            'title' => $this->faker()->name,
            'content' => $this->faker()->sentence(),
            'tags' => [
                $this->faker()->randomElement(['red', 'green', 'blue']),
                $this->faker()->randomElement(['bold', 'tall', 'tiny', 'full'])
            ],
            'color' => '#' . $this->faker()->hexColor,
        ])->push([
            'id' => 3,
            'title' => $this->faker()->name,
            'content' => $this->faker()->sentence(),
            'tags' => [
                $this->faker()->randomElement(['red', 'green', 'blue']),
                $this->faker()->randomElement(['bold', 'tall', 'tiny', 'full'])
            ],
            'color' => '#' . $this->faker()->hexColor,
        ])->push([
            'id' => 4,
            'title' => $this->faker()->name,
            'content' => $this->faker()->sentence(),
            'tags' => [
                $this->faker()->randomElement(['red', 'green', 'blue']),
                $this->faker()->randomElement(['bold', 'tall', 'tiny', 'full'])
            ],
            'color' => '#' . $this->faker()->hexColor,
        ])->push([
            'id' => 5,
            'title' => $this->faker()->name,
            'content' => $this->faker()->sentence(),
            'tags' => [
                $this->faker()->randomElement(['red', 'green', 'blue']),
                $this->faker()->randomElement(['bold', 'tall', 'tiny', 'full'])
            ],
            'color' => $this->faker()->hexColor,
        ]);

        $data->each(function (array $item) {
            $this->postJson('/secure/v1/items', $this->createRequestModel('items', $item), $this->headers());
        });

        // ACT
        $response = $this->getJson('/secure/v1/select?query=*:*&start=0&rows=3&sort[id]=desc', $this->headers());

        // ASSERT
        $response->assertStatus(200);
        $data = $response->json();
        $i = 5;
        foreach ($data['data'] as $item) {
            $this->assertEquals((string)$i, $item['id']);
            $i--;
        }
        $this->assertEquals($i, 2);

        foreach ($data['data'] as $item) {
            $response = $this->deleteJson('/secure/v1/items/' . $item['id'], [], $this->headers());
            $response->assertStatus(204);
        }

        $response = $this->getJson('/secure/v1/select?query=*:*&start=0&rows=3', $this->headers());

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertEquals(0, $data['meta']['pagination']['start']);
        $this->assertEquals(2, $data['meta']['pagination']['rows']);
        $this->assertEquals(2, $data['meta']['pagination']['total']);
        $this->assertEquals(1, $data['meta']['pagination']['page']);

        $response = $this->deleteJson('/secure/v1/items/1', [], $this->headers());
        $response->assertStatus(204);
        $response = $this->deleteJson('/secure/v1/items/2', [], $this->headers());
        $response->assertStatus(204);
    }

}
