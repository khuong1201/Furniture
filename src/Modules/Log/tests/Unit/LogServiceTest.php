<?php

namespace Modules\Log\tests\Unit;

use Tests\TestCase;
use Modules\Log\Domain\Models\Log;
use Modules\Log\Domain\Repositories\LogRepositoryInterface;
use Modules\Log\Infrastructure\Repositories\EloquentLogRepository;
use Modules\Log\Services\LogService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LogServiceTest extends TestCase
{
    use RefreshDatabase;

    protected LogService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Bind repository và tạo instance service
        $this->app->bind(LogRepositoryInterface::class, EloquentLogRepository::class);
        $repository = $this->app->make(LogRepositoryInterface::class);
        $this->service = new LogService($repository);
    }

    public function test_can_get_logs_without_filter()
    {
        Log::factory()->count(5)->create();

        $logs = $this->service->getLogs();
        $this->assertCount(5, $logs);
    }

    public function test_can_get_logs_with_filter()
    {
        Log::factory()->count(3)->create(['type' => 'system']);
        Log::factory()->count(2)->create(['type' => 'model']);

        $systemLogs = $this->service->getLogs(['type' => 'system']);
        $modelLogs  = $this->service->getLogs(['type' => 'model']);

        $this->assertCount(3, $systemLogs);
        $this->assertCount(2, $modelLogs);
    }
}
