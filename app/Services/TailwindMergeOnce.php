<?php

namespace App\Services;

use TailwindMerge\TailwindMerge;

class TailwindMergeOnce extends TailwindMerge
{
    /**
     * Override the merge method to wrap it in the once() helper.
     *
     * @param  string|array<array-key, string|array<array-key, string>>  ...$args
     */
    public function merge(...$args): string
    {
        // usage of once() memoizes the result for the duration of the request
        // based on the instance and the arguments passed.
        return once(fn () => parent::merge(...$args));
    }
}
