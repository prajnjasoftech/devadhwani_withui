<?php

namespace Tests\Unit;

use App\Traits\ApiResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class ApiResponseTraitTest extends TestCase
{
    use ApiResponse;

    /** @test */
    public function it_returns_success_response()
    {
        $response = $this->success(['key' => 'value'], 'Test message', 200);

        $this->assertEquals(200, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);

        $this->assertTrue($content['status']);
        $this->assertEquals('Test message', $content['message']);
        $this->assertEquals(['key' => 'value'], $content['data']);
    }

    /** @test */
    public function it_returns_success_with_pagination()
    {
        $items = collect([
            ['id' => 1, 'name' => 'Item 1'],
            ['id' => 2, 'name' => 'Item 2'],
        ]);

        $paginator = new LengthAwarePaginator(
            $items,
            10, // total
            2,  // per page
            1   // current page
        );

        $response = $this->successWithPagination($paginator, 'Items fetched');

        $content = json_decode($response->getContent(), true);

        $this->assertTrue($content['status']);
        $this->assertEquals('Items fetched', $content['message']);
        $this->assertCount(2, $content['data']);
        $this->assertEquals(1, $content['meta']['current_page']);
        $this->assertEquals(2, $content['meta']['per_page']);
        $this->assertEquals(10, $content['meta']['total']);
        $this->assertEquals(5, $content['meta']['last_page']);
    }

    /** @test */
    public function it_returns_error_response()
    {
        $response = $this->error('Something went wrong', 400);

        $this->assertEquals(400, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);

        $this->assertFalse($content['status']);
        $this->assertEquals('Something went wrong', $content['error']);
    }

    /** @test */
    public function it_returns_error_with_errors_array()
    {
        $errors = ['field' => ['Field is required']];
        $response = $this->error('Validation failed', 422, $errors);

        $content = json_decode($response->getContent(), true);

        $this->assertEquals($errors, $content['errors']);
    }

    /** @test */
    public function it_returns_not_found_response()
    {
        $response = $this->notFound('Resource not found');

        $this->assertEquals(404, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);

        $this->assertFalse($content['status']);
        $this->assertEquals('Resource not found', $content['error']);
    }
}
