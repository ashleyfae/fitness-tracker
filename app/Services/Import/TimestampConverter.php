<?php

namespace App\Services\Import;

use Illuminate\Support\Carbon;

class TimestampConverter
{
    /**
     * Convert Unix timestamp to Carbon instance
     */
    public function fromUnix(int $timestamp): Carbon
    {
        return Carbon::createFromTimestamp($timestamp);
    }

    /**
     * Convert date string to Carbon instance
     */
    public function fromDateString(string $date): Carbon
    {
        return Carbon::parse($date);
    }
}
