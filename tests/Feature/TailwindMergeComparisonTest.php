<?php

declare(strict_types=1);

use App\Services\TailwindMergeBoost;
use TailwindMerge\Laravel\Facades\TailwindMerge;

/*
|--------------------------------------------------------------------------
| Tailwind Merge Comparison Tests
|--------------------------------------------------------------------------
|
| These tests compare the behavior of both TailwindMerge (original package)
| and TailwindMergeBoost (efficient implementation) to ensure consistent
| output across various Tailwind CSS class merging scenarios.
|
*/

beforeEach(function () {
    $this->boost = new TailwindMergeBoost();
});

/*
|--------------------------------------------------------------------------
| Basic Class Conflict Tests
|--------------------------------------------------------------------------
*/

describe('basic class conflicts', function () {
    it('merges conflicting padding classes', function () {
        $input = 'p-4 p-6';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('p-6');
        expect($boostResult)->toBe('p-6');
        expect($boostResult)->toBe($twmResult);
    });

    it('merges conflicting margin classes', function () {
        $input = 'mt-2 mt-4';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('mt-4');
        expect($boostResult)->toBe('mt-4');
        expect($boostResult)->toBe($twmResult);
    });

    it('keeps non-conflicting margin classes', function () {
        $input = 'mt-2 mb-4';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toContain('mt-2');
        expect($twmResult)->toContain('mb-4');
        expect($boostResult)->toContain('mt-2');
        expect($boostResult)->toContain('mb-4');
    });

    it('merges conflicting background color classes', function () {
        $input = 'bg-red-500 bg-blue-500';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('bg-blue-500');
        expect($boostResult)->toBe('bg-blue-500');
    });

    it('merges conflicting text color classes', function () {
        $input = 'text-red-500 text-blue-500';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('text-blue-500');
        expect($boostResult)->toBe('text-blue-500');
    });

    it('merges conflicting width classes', function () {
        $input = 'w-4 w-full';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('w-full');
        expect($boostResult)->toBe('w-full');
    });

    it('merges conflicting height classes', function () {
        $input = 'h-4 h-screen';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('h-screen');
        expect($boostResult)->toBe('h-screen');
    });
});

/*
|--------------------------------------------------------------------------
| Display and Position Tests
|--------------------------------------------------------------------------
*/

describe('display and position classes', function () {
    it('merges conflicting display classes', function () {
        $input = 'block flex';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('flex');
        expect($boostResult)->toBe('flex');
    });

    it('merges multiple display classes', function () {
        $input = 'block inline-block flex grid';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('grid');
        expect($boostResult)->toBe('grid');
    });

    it('merges conflicting position classes', function () {
        $input = 'static relative absolute';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('absolute');
        expect($boostResult)->toBe('absolute');
    });

    it('merges conflicting visibility classes', function () {
        $input = 'visible invisible';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('invisible');
        expect($boostResult)->toBe('invisible');
    });
});

/*
|--------------------------------------------------------------------------
| Modifier Tests (hover, focus, etc.)
|--------------------------------------------------------------------------
*/

describe('modifier handling', function () {
    it('merges conflicting hover modifiers', function () {
        $input = 'hover:bg-red-500 hover:bg-blue-500';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('hover:bg-blue-500');
        expect($boostResult)->toBe('hover:bg-blue-500');
    });

    it('keeps different state modifiers', function () {
        $input = 'hover:bg-red-500 focus:bg-blue-500';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toContain('hover:bg-red-500');
        expect($twmResult)->toContain('focus:bg-blue-500');
        expect($boostResult)->toContain('hover:bg-red-500');
        expect($boostResult)->toContain('focus:bg-blue-500');
    });

    it('merges conflicting responsive modifiers', function () {
        $input = 'md:p-4 md:p-8';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('md:p-8');
        expect($boostResult)->toBe('md:p-8');
    });

    it('keeps different responsive modifiers', function () {
        $input = 'md:p-4 lg:p-8';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toContain('md:p-4');
        expect($twmResult)->toContain('lg:p-8');
        expect($boostResult)->toContain('md:p-4');
        expect($boostResult)->toContain('lg:p-8');
    });

    it('handles dark mode modifier', function () {
        $input = 'dark:text-white dark:text-gray-100';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('dark:text-gray-100');
        expect($boostResult)->toBe('dark:text-gray-100');
    });

    it('handles combined modifiers', function () {
        $input = 'hover:md:bg-red-500 hover:md:bg-blue-500';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('hover:md:bg-blue-500');
        expect($boostResult)->toBe('hover:md:bg-blue-500');
    });
});

/*
|--------------------------------------------------------------------------
| Important Modifier Tests
|--------------------------------------------------------------------------
*/

describe('important modifier handling', function () {
    it('merges conflicting important modifiers', function () {
        $input = '!p-4 !p-8';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('!p-8');
        expect($boostResult)->toBe('!p-8');
    });

    it('keeps important and non-important separate', function () {
        $input = 'p-4 !p-8';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toContain('p-4');
        expect($twmResult)->toContain('!p-8');
        expect($boostResult)->toContain('p-4');
        expect($boostResult)->toContain('!p-8');
    });
});

/*
|--------------------------------------------------------------------------
| Negative Value Tests
|--------------------------------------------------------------------------
*/

describe('negative value handling', function () {
    it('merges conflicting negative margin classes', function () {
        $input = '-mt-4 -mt-8';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('-mt-8');
        expect($boostResult)->toBe('-mt-8');
    });

    it('keeps positive and negative margins separate when different directions', function () {
        $input = 'mt-4 -mb-4';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toContain('mt-4');
        expect($twmResult)->toContain('-mb-4');
        expect($boostResult)->toContain('mt-4');
        expect($boostResult)->toContain('-mb-4');
    });
});

/*
|--------------------------------------------------------------------------
| Complex Scenarios
|--------------------------------------------------------------------------
*/

describe('complex scenarios', function () {
    it('handles complex class combinations', function () {
        $input = 'flex flex-col items-center justify-between p-4 p-6 bg-white shadow-lg rounded-xl';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        // Both should remove p-4 and keep p-6
        expect($twmResult)->toContain('flex');
        expect($twmResult)->toContain('flex-col');
        expect($twmResult)->toContain('items-center');
        expect($twmResult)->toContain('justify-between');
        expect($twmResult)->toContain('p-6');
        expect($twmResult)->not->toContain('p-4');

        expect($boostResult)->toContain('flex');
        expect($boostResult)->toContain('flex-col');
        expect($boostResult)->toContain('items-center');
        expect($boostResult)->toContain('justify-between');
        expect($boostResult)->toContain('p-6');
        expect($boostResult)->not->toContain('p-4');
    });

    it('handles multiple conflicting groups', function () {
        $input = 'mt-4 mb-2 px-6 py-4 mt-8 px-8';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        // Both should resolve conflicts
        expect($twmResult)->toContain('mt-8');
        expect($twmResult)->not->toContain('mt-4');
        expect($twmResult)->toContain('px-8');
        expect($twmResult)->not->toContain('px-6');
        expect($twmResult)->toContain('mb-2');
        expect($twmResult)->toContain('py-4');

        expect($boostResult)->toContain('mt-8');
        expect($boostResult)->not->toContain('mt-4');
        expect($boostResult)->toContain('px-8');
        expect($boostResult)->not->toContain('px-6');
        expect($boostResult)->toContain('mb-2');
        expect($boostResult)->toContain('py-4');
    });

    it('handles real-world component classes', function () {
        $input = 'flex flex-col items-center justify-between p-4 bg-white shadow-lg rounded-xl hover:shadow-xl transition-shadow duration-300 mx-auto max-w-md w-full space-y-4 p-8 bg-gray-100';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        // Both should resolve p-4/p-8 and bg-white/bg-gray-100 conflicts
        expect($twmResult)->toContain('p-8');
        expect($twmResult)->not->toContain('p-4');
        expect($twmResult)->toContain('bg-gray-100');
        expect($twmResult)->not->toContain('bg-white');

        expect($boostResult)->toContain('p-8');
        expect($boostResult)->not->toContain('p-4');
        expect($boostResult)->toContain('bg-gray-100');
        expect($boostResult)->not->toContain('bg-white');
    });
});

/*
|--------------------------------------------------------------------------
| Typography Tests
|--------------------------------------------------------------------------
*/

describe('typography classes', function () {
    it('merges conflicting font weight classes', function () {
        $input = 'font-normal font-bold';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('font-bold');
        expect($boostResult)->toBe('font-bold');
    });

    it('merges conflicting text decoration classes', function () {
        $input = 'underline line-through';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('line-through');
        expect($boostResult)->toBe('line-through');
    });

    it('merges conflicting font style classes', function () {
        $input = 'italic not-italic';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('not-italic');
        expect($boostResult)->toBe('not-italic');
    });

    it('merges conflicting text transform classes', function () {
        $input = 'uppercase lowercase';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('lowercase');
        expect($boostResult)->toBe('lowercase');
    });
});

/*
|--------------------------------------------------------------------------
| Flexbox and Grid Tests
|--------------------------------------------------------------------------
*/

describe('flexbox and grid classes', function () {
    it('merges conflicting flex direction classes', function () {
        $input = 'flex-row flex-col';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('flex-col');
        expect($boostResult)->toBe('flex-col');
    });

    it('merges conflicting justify content classes', function () {
        $input = 'justify-start justify-center';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('justify-center');
        expect($boostResult)->toBe('justify-center');
    });

    it('merges conflicting align items classes', function () {
        $input = 'items-start items-center';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('items-center');
        expect($boostResult)->toBe('items-center');
    });

    it('merges conflicting gap classes', function () {
        $input = 'gap-2 gap-4';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('gap-4');
        expect($boostResult)->toBe('gap-4');
    });
});

/*
|--------------------------------------------------------------------------
| Border and Effects Tests
|--------------------------------------------------------------------------
*/

describe('border and effects classes', function () {
    it('merges conflicting rounded classes', function () {
        $input = 'rounded rounded-lg';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('rounded-lg');
        expect($boostResult)->toBe('rounded-lg');
    });

    it('merges conflicting shadow classes', function () {
        $input = 'shadow shadow-lg';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('shadow-lg');
        expect($boostResult)->toBe('shadow-lg');
    });

    it('merges conflicting opacity classes', function () {
        $input = 'opacity-50 opacity-100';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('opacity-100');
        expect($boostResult)->toBe('opacity-100');
    });

    it('merges conflicting border color classes', function () {
        $input = 'border-red-500 border-blue-500';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('border-blue-500');
        expect($boostResult)->toBe('border-blue-500');
    });
});

/*
|--------------------------------------------------------------------------
| Transition and Animation Tests
|--------------------------------------------------------------------------
*/

describe('transition and animation classes', function () {
    it('merges conflicting transition classes', function () {
        $input = 'transition transition-all';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('transition-all');
        expect($boostResult)->toBe('transition-all');
    });

    it('merges conflicting duration classes', function () {
        $input = 'duration-100 duration-300';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('duration-300');
        expect($boostResult)->toBe('duration-300');
    });
});

/*
|--------------------------------------------------------------------------
| Edge Cases
|--------------------------------------------------------------------------
*/

describe('edge cases', function () {
    it('handles empty input', function () {
        $input = '';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('');
        expect($boostResult)->toBe('');
    });

    it('handles whitespace-only input', function () {
        $input = '   ';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('');
        expect($boostResult)->toBe('');
    });

    it('handles unknown custom classes', function () {
        $input = 'custom-class p-4 another-custom';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toContain('custom-class');
        expect($twmResult)->toContain('p-4');
        expect($twmResult)->toContain('another-custom');

        expect($boostResult)->toContain('custom-class');
        expect($boostResult)->toContain('p-4');
        expect($boostResult)->toContain('another-custom');
    });

    it('preserves non-conflicting classes', function () {
        $input = 'flex items-center space-x-4';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toContain('flex');
        expect($twmResult)->toContain('items-center');
        expect($twmResult)->toContain('space-x-4');

        expect($boostResult)->toContain('flex');
        expect($boostResult)->toContain('items-center');
        expect($boostResult)->toContain('space-x-4');
    });
});

/*
|--------------------------------------------------------------------------
| Array Input Tests
|--------------------------------------------------------------------------
*/

describe('array input handling', function () {
    it('handles array input for boost', function () {
        $result = $this->boost->merge(['p-4', 'p-6']);
        expect($result)->toBe('p-6');
    });

    it('handles nested array input for boost', function () {
        $result = $this->boost->merge(['p-4', ['mt-4', 'p-8']]);

        expect($result)->toContain('mt-4');
        expect($result)->toContain('p-8');
        expect($result)->not->toContain('p-4');
    });
});
