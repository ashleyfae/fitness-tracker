<?php

namespace Tests\Unit\Services\Import;

use App\Services\Import\LogsParser;
use PHPUnit\Framework\TestCase;

class LogsParserTest extends TestCase
{
    private LogsParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new LogsParser;
    }

    /** @test */
    public function it_parses_standard_logs(): void
    {
        $result = $this->parser->parse('22.5x5,22.5x8,22.5x8');

        $this->assertCount(3, $result);
        $this->assertEquals(['weight_kg' => 22.5, 'number_reps' => 5], $result[0]);
        $this->assertEquals(['weight_kg' => 22.5, 'number_reps' => 8], $result[1]);
        $this->assertEquals(['weight_kg' => 22.5, 'number_reps' => 8], $result[2]);
    }

    /** @test */
    public function it_parses_bodyweight_exercises(): void
    {
        $result = $this->parser->parse('0.5x10,0.5x12');

        $this->assertCount(2, $result);
        $this->assertEquals(['weight_kg' => 0.5, 'number_reps' => 10], $result[0]);
        $this->assertEquals(['weight_kg' => 0.5, 'number_reps' => 12], $result[1]);
    }

    /** @test */
    public function it_parses_single_set(): void
    {
        $result = $this->parser->parse('30x10');

        $this->assertCount(1, $result);
        $this->assertEquals(['weight_kg' => 30.0, 'number_reps' => 10], $result[0]);
    }

    /** @test */
    public function it_handles_empty_logs(): void
    {
        $result = $this->parser->parse('');

        $this->assertEmpty($result);
    }

    /** @test */
    public function it_handles_zero_logs(): void
    {
        $result = $this->parser->parse('0x0');

        $this->assertEmpty($result);
    }

    /** @test */
    public function it_skips_time_based_exercises(): void
    {
        $result = $this->parser->parse('0x5x30');

        $this->assertEmpty($result);
    }

    /** @test */
    public function it_handles_mixed_standard_and_time_based(): void
    {
        $result = $this->parser->parse('22.5x8,0x5x30,22.5x10');

        $this->assertCount(2, $result);
        $this->assertEquals(['weight_kg' => 22.5, 'number_reps' => 8], $result[0]);
        $this->assertEquals(['weight_kg' => 22.5, 'number_reps' => 10], $result[1]);
    }

    /** @test */
    public function it_handles_integer_weights(): void
    {
        $result = $this->parser->parse('100x5,120x3');

        $this->assertCount(2, $result);
        $this->assertEquals(['weight_kg' => 100.0, 'number_reps' => 5], $result[0]);
        $this->assertEquals(['weight_kg' => 120.0, 'number_reps' => 3], $result[1]);
    }

    /** @test */
    public function it_handles_whitespace(): void
    {
        $result = $this->parser->parse(' 22.5x5 , 22.5x8 ');

        $this->assertCount(2, $result);
        $this->assertEquals(['weight_kg' => 22.5, 'number_reps' => 5], $result[0]);
        $this->assertEquals(['weight_kg' => 22.5, 'number_reps' => 8], $result[1]);
    }
}
