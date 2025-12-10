<?php

namespace Modules\Shared\tests\Unit\Shared;

use tests\Unit\TestCase;
use Modules\Shared\Http\resources\ApiResponse;

class ApiResponseTest extends TestCase
{
    public function test_success_response_structure()
    {
        $response = ApiResponse::success(['id' => 1], 'Ok');
        
        $this->assertTrue($response['success']);
        $this->assertEquals('Ok', $response['message']);
        $this->assertEquals(['id' => 1], $response['data']);
    }

    public function test_error_response_structure()
    {
        $response = ApiResponse::error('Failed', 400);
        
        $this->assertFalse($response['success']);
        $this->assertEquals('Failed', $response['message']);
    }
}