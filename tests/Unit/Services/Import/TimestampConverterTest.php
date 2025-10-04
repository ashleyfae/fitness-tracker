<?php

namespace Tests\Unit\Services\Import;

use App\Services\Import\TimestampConverter;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class TimestampConverterTest extends TestCase
{
    private TimestampConverter $converter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->converter = new TimestampConverter;
    }

    /** @test */
    public function it_converts_unix_timestamp_to_carbon(): void
    {
        $timestamp = 1456181231; // 2016-02-22 19:47:11 UTC

        $result = $this->converter->fromUnix($timestamp);

        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals(1456181231, $result->timestamp);
    }

    /** @test */
    public function it_converts_date_string_to_carbon(): void
    {
        $dateString = '2024-01-15 14:30:00';

        $result = $this->converter->fromDateString($dateString);

        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals('2024-01-15 14:30:00', $result->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_handles_various_date_formats(): void
    {
        $formats = [
            '2024-01-15' => '2024-01-15 00:00:00',
            '2024-01-15 14:30' => '2024-01-15 14:30:00',
            '2024-01-15 14:30:45' => '2024-01-15 14:30:45',
        ];

        foreach ($formats as $input => $expected) {
            $result = $this->converter->fromDateString($input);
            $this->assertEquals($expected, $result->format('Y-m-d H:i:s'));
        }
    }

    /** @test */
    public function it_converts_zero_timestamp(): void
    {
        $result = $this->converter->fromUnix(0);

        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals('1970-01-01 00:00:00', $result->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_converts_recent_timestamp(): void
    {
        $timestamp = 1738059610; // 2025-01-28 10:20:10 UTC

        $result = $this->converter->fromUnix($timestamp);

        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertEquals(1738059610, $result->timestamp);
    }
}
