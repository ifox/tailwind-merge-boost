<?php

namespace App\Services;

use TailwindMerge\TailwindMerge;

class TailwindMergeOnce extends TailwindMerge
{
    /**
     * Count of total merge calls.
     */
    private int $mergeCalls = 0;

    /**
     * Count of actual parent::merge() calls (cache misses).
     */
    private int $actualMergeCalls = 0;

    /**
     * Override the merge method to wrap it in the once() helper.
     *
     * @param  string|array<array-key, string|array<array-key, string>>  ...$args
     */
    public function merge(...$args): string
    {
        $this->mergeCalls++;

        // usage of once() memoizes the result for the duration of the request
        // based on the instance and the arguments passed.
        return once(function () use ($args) {
            $this->actualMergeCalls++;

            return parent::merge(...$args);
        });
    }

    /**
     * Get the total number of merge calls.
     */
    public function getMergeCalls(): int
    {
        return $this->mergeCalls;
    }

    /**
     * Get the number of actual merge calls (cache misses).
     */
    public function getActualMergeCalls(): int
    {
        return $this->actualMergeCalls;
    }

    /**
     * Get the number of cache hits (merge calls - actual merge calls).
     */
    public function getCacheHits(): int
    {
        return $this->mergeCalls - $this->actualMergeCalls;
    }

    /**
     * Reset the call counters.
     */
    public function resetStats(): void
    {
        $this->mergeCalls = 0;
        $this->actualMergeCalls = 0;
    }
}
