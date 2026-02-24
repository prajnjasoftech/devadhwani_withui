<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test the API health endpoint.
     *
     * @return void
     */
    public function test_api_returns_successful_response()
    {
        $response = $this->getJson('/api/panchang/list');

        $response->assertStatus(200);
    }
}
