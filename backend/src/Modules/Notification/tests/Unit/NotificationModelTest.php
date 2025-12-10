<?php

namespace Modules\Notification\tests\Unit;

use Tests\TestCase;
use Modules\Notification\Domain\Models\Notification;

class NotificationModelTest extends TestCase
{
    public function test_mark_as_read_updates_timestamp()
    {
        $notification = new Notification(['read_at' => null]);
        
        $notification->markAsRead(); 
        
        $this->assertNotNull($notification->read_at);
        $this->assertEqualsWithDelta(now()->timestamp, $notification->read_at->timestamp, 1);
    }

    public function test_mark_as_read_does_nothing_if_already_read()
    {
        $oldTime = now()->subDays(1);
        $notification = new Notification(['read_at' => $oldTime]);
        
        $notification->markAsRead();
        
        $this->assertEquals($oldTime->timestamp, $notification->read_at->timestamp);
    }
}