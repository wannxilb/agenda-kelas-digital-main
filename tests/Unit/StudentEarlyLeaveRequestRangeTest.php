<?php

namespace Tests\Unit;

use App\Models\StudentEarlyLeaveRequest;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class StudentEarlyLeaveRequestRangeTest extends TestCase
{
    private function makeRequest(array $attributes): StudentEarlyLeaveRequest
    {
        $request = new StudentEarlyLeaveRequest();
        $request->setRawAttributes($attributes);

        return $request;
    }

    public function test_single_day_request_only_covers_its_own_date(): void
    {
        $request = $this->makeRequest(['date' => '2026-08-03', 'date_end' => null]);

        $this->assertTrue($request->coversDate('2026-08-03'));
        $this->assertFalse($request->coversDate('2026-08-04'));
        $this->assertFalse($request->isMultiDay());
    }

    public function test_multi_day_request_covers_whole_range(): void
    {
        $request = $this->makeRequest(['date' => '2026-08-04', 'date_end' => '2026-08-06']);

        $this->assertTrue($request->coversDate('2026-08-04'));
        $this->assertTrue($request->coversDate('2026-08-05'));
        $this->assertTrue($request->coversDate('2026-08-06'));
        $this->assertFalse($request->coversDate('2026-08-03'));
        $this->assertFalse($request->coversDate('2026-08-07'));
        $this->assertTrue($request->isMultiDay());
        $this->assertEquals('2026-08-06', $request->effectiveEndDate()->toDateString());
    }

    public function test_effective_end_date_defaults_to_start_date(): void
    {
        $request = $this->makeRequest(['date' => '2026-08-03', 'date_end' => null]);

        $this->assertEquals('2026-08-03', $request->effectiveEndDate()->toDateString());
    }
}
