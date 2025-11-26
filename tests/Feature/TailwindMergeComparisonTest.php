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

/*
|--------------------------------------------------------------------------
| Arbitrary Value Tests
|--------------------------------------------------------------------------
*/

describe('arbitrary values - spacing', function () {
    it('merges conflicting arbitrary padding values', function () {
        $input = 'p-[10px] p-[20px]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('p-[20px]');
        expect($boostResult)->toBe('p-[20px]');
    });

    it('merges arbitrary padding with standard padding', function () {
        $input = 'p-4 p-[15px]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('p-[15px]');
        expect($boostResult)->toBe('p-[15px]');
    });

    it('merges arbitrary margin with standard margin', function () {
        $input = 'mt-4 mt-[2rem]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('mt-[2rem]');
        expect($boostResult)->toBe('mt-[2rem]');
    });

    it('keeps different direction arbitrary spacing', function () {
        $input = 'pt-[10px] pb-[20px]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toContain('pt-[10px]');
        expect($twmResult)->toContain('pb-[20px]');
        expect($boostResult)->toContain('pt-[10px]');
        expect($boostResult)->toContain('pb-[20px]');
    });

    it('handles negative arbitrary margin', function () {
        $input = '-mt-4 -mt-[10px]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('-mt-[10px]');
        expect($boostResult)->toBe('-mt-[10px]');
    });
});

describe('arbitrary values - sizing', function () {
    it('merges arbitrary width values', function () {
        $input = 'w-[100px] w-[200px]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('w-[200px]');
        expect($boostResult)->toBe('w-[200px]');
    });

    it('merges arbitrary height values', function () {
        $input = 'h-10 h-[50vh]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('h-[50vh]');
        expect($boostResult)->toBe('h-[50vh]');
    });

    it('merges min-width with arbitrary values', function () {
        $input = 'min-w-0 min-w-[300px]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('min-w-[300px]');
        expect($boostResult)->toBe('min-w-[300px]');
    });

    it('merges max-height with arbitrary values', function () {
        $input = 'max-h-screen max-h-[80vh]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('max-h-[80vh]');
        expect($boostResult)->toBe('max-h-[80vh]');
    });

    it('merges min-height with arbitrary values', function () {
        $input = 'min-h-[100px] min-h-[200px]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('min-h-[200px]');
        expect($boostResult)->toBe('min-h-[200px]');
    });
});

describe('arbitrary values - colors', function () {
    it('merges arbitrary background colors', function () {
        $input = 'bg-red-500 bg-[#ff0000]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('bg-[#ff0000]');
        expect($boostResult)->toBe('bg-[#ff0000]');
    });

    it('merges arbitrary text colors', function () {
        $input = 'text-blue-500 text-[rgb(255,0,0)]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('text-[rgb(255,0,0)]');
        expect($boostResult)->toBe('text-[rgb(255,0,0)]');
    });

    it('merges arbitrary border colors', function () {
        $input = 'border-gray-300 border-[#ccc]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('border-[#ccc]');
        expect($boostResult)->toBe('border-[#ccc]');
    });

    it('merges arbitrary ring colors', function () {
        // Note: TailwindMerge v1.1.2 doesn't recognize HSL in arbitrary values as colors
        // So both classes are kept. Our implementation matches this behavior.
        $input = 'ring-blue-500 ring-[hsl(200,100%,50%)]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        // Both keep both classes since HSL isn't recognized as a color
        expect($boostResult)->toBe($twmResult);
    });

    it('merges arbitrary ring hex colors', function () {
        $input = 'ring-blue-500 ring-[#ff0000]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('ring-[#ff0000]');
        expect($boostResult)->toBe('ring-[#ff0000]');
    });

    it('merges arbitrary gradient colors', function () {
        $input = 'from-red-500 from-[#ff6b6b]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('from-[#ff6b6b]');
        expect($boostResult)->toBe('from-[#ff6b6b]');
    });
});

describe('arbitrary values - borders', function () {
    it('merges arbitrary border width', function () {
        $input = 'border-2 border-[3px]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('border-[3px]');
        expect($boostResult)->toBe('border-[3px]');
    });

    it('merges arbitrary border radius', function () {
        $input = 'rounded-lg rounded-[20px]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('rounded-[20px]');
        expect($boostResult)->toBe('rounded-[20px]');
    });

    it('keeps border width and color arbitrary values separate', function () {
        $input = 'border-[3px] border-[#ccc]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toContain('border-[3px]');
        expect($twmResult)->toContain('border-[#ccc]');
        expect($boostResult)->toContain('border-[3px]');
        expect($boostResult)->toContain('border-[#ccc]');
    });
});

describe('arbitrary values - typography', function () {
    it('merges arbitrary font sizes', function () {
        $input = 'text-lg text-[18px]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('text-[18px]');
        expect($boostResult)->toBe('text-[18px]');
    });

    it('merges arbitrary line heights', function () {
        $input = 'leading-6 leading-[1.75]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('leading-[1.75]');
        expect($boostResult)->toBe('leading-[1.75]');
    });

    it('merges arbitrary letter spacing', function () {
        $input = 'tracking-wide tracking-[0.1em]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('tracking-[0.1em]');
        expect($boostResult)->toBe('tracking-[0.1em]');
    });

    it('merges arbitrary font weight', function () {
        $input = 'font-bold font-[600]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('font-[600]');
        expect($boostResult)->toBe('font-[600]');
    });
});

describe('arbitrary values - layout', function () {
    it('merges arbitrary z-index values', function () {
        $input = 'z-10 z-[100]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('z-[100]');
        expect($boostResult)->toBe('z-[100]');
    });

    it('merges arbitrary inset values', function () {
        $input = 'top-4 top-[10%]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('top-[10%]');
        expect($boostResult)->toBe('top-[10%]');
    });

    it('merges arbitrary gap values', function () {
        $input = 'gap-4 gap-[1.5rem]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('gap-[1.5rem]');
        expect($boostResult)->toBe('gap-[1.5rem]');
    });

    it('merges arbitrary aspect ratio', function () {
        $input = 'aspect-video aspect-[4/3]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('aspect-[4/3]');
        expect($boostResult)->toBe('aspect-[4/3]');
    });

    it('merges arbitrary columns', function () {
        // Note: TailwindMerge v1.1.2 doesn't merge columns with different value types
        $input = 'columns-3 columns-[200px]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        // Both implementations should match TailwindMerge's behavior
        expect($boostResult)->toBe($twmResult);
    });
});

describe('arbitrary values - transforms', function () {
    it('merges arbitrary rotate values', function () {
        $input = 'rotate-45 rotate-[30deg]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('rotate-[30deg]');
        expect($boostResult)->toBe('rotate-[30deg]');
    });

    it('merges arbitrary scale values', function () {
        $input = 'scale-150 scale-[1.25]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('scale-[1.25]');
        expect($boostResult)->toBe('scale-[1.25]');
    });

    it('merges negative arbitrary rotate', function () {
        $input = '-rotate-45 -rotate-[15deg]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('-rotate-[15deg]');
        expect($boostResult)->toBe('-rotate-[15deg]');
    });
});

describe('arbitrary values - effects', function () {
    it('merges arbitrary opacity values', function () {
        $input = 'opacity-50 opacity-[0.75]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('opacity-[0.75]');
        expect($boostResult)->toBe('opacity-[0.75]');
    });

    it('merges arbitrary shadow values', function () {
        $input = 'shadow-lg shadow-[0_4px_6px_rgba(0,0,0,0.1)]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('shadow-[0_4px_6px_rgba(0,0,0,0.1)]');
        expect($boostResult)->toBe('shadow-[0_4px_6px_rgba(0,0,0,0.1)]');
    });

    it('merges arbitrary blur values', function () {
        $input = 'blur-lg blur-[10px]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('blur-[10px]');
        expect($boostResult)->toBe('blur-[10px]');
    });
});

describe('arbitrary values - transitions', function () {
    it('merges arbitrary duration values', function () {
        $input = 'duration-300 duration-[400ms]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('duration-[400ms]');
        expect($boostResult)->toBe('duration-[400ms]');
    });

    it('merges arbitrary delay values', function () {
        $input = 'delay-150 delay-[200ms]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('delay-[200ms]');
        expect($boostResult)->toBe('delay-[200ms]');
    });
});

describe('arbitrary values - with modifiers', function () {
    it('merges arbitrary values with hover modifier', function () {
        $input = 'hover:p-4 hover:p-[20px]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('hover:p-[20px]');
        expect($boostResult)->toBe('hover:p-[20px]');
    });

    it('merges arbitrary values with responsive modifier', function () {
        $input = 'md:w-full md:w-[500px]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('md:w-[500px]');
        expect($boostResult)->toBe('md:w-[500px]');
    });

    it('merges arbitrary values with combined modifiers', function () {
        $input = 'lg:hover:bg-blue-500 lg:hover:bg-[#3b82f6]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('lg:hover:bg-[#3b82f6]');
        expect($boostResult)->toBe('lg:hover:bg-[#3b82f6]');
    });

    it('merges arbitrary values with dark mode', function () {
        $input = 'dark:text-white dark:text-[#f0f0f0]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('dark:text-[#f0f0f0]');
        expect($boostResult)->toBe('dark:text-[#f0f0f0]');
    });

    it('merges arbitrary values with important modifier', function () {
        $input = '!mt-4 !mt-[30px]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('!mt-[30px]');
        expect($boostResult)->toBe('!mt-[30px]');
    });
});

describe('arbitrary properties', function () {
    it('merges conflicting arbitrary CSS properties', function () {
        $input = '[color:red] [color:blue]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('[color:blue]');
        expect($boostResult)->toBe('[color:blue]');
    });

    it('keeps different arbitrary CSS properties', function () {
        $input = '[color:red] [background:blue]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toContain('[color:red]');
        expect($twmResult)->toContain('[background:blue]');
        expect($boostResult)->toContain('[color:red]');
        expect($boostResult)->toContain('[background:blue]');
    });

    it('merges arbitrary mask-type properties', function () {
        $input = '[mask-type:alpha] [mask-type:luminance]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('[mask-type:luminance]');
        expect($boostResult)->toBe('[mask-type:luminance]');
    });
});

describe('arbitrary values - grid', function () {
    it('merges arbitrary grid columns', function () {
        $input = 'grid-cols-3 grid-cols-[repeat(auto-fit,minmax(200px,1fr))]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('grid-cols-[repeat(auto-fit,minmax(200px,1fr))]');
        expect($boostResult)->toBe('grid-cols-[repeat(auto-fit,minmax(200px,1fr))]');
    });

    it('merges arbitrary grid rows', function () {
        $input = 'grid-rows-4 grid-rows-[200px_1fr_auto]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('grid-rows-[200px_1fr_auto]');
        expect($boostResult)->toBe('grid-rows-[200px_1fr_auto]');
    });
});

describe('arbitrary values - flex', function () {
    it('merges arbitrary flex basis', function () {
        $input = 'basis-1/2 basis-[200px]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('basis-[200px]');
        expect($boostResult)->toBe('basis-[200px]');
    });

    it('merges arbitrary order', function () {
        $input = 'order-2 order-[99]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('order-[99]');
        expect($boostResult)->toBe('order-[99]');
    });
});

describe('arbitrary values - complex scenarios', function () {
    it('handles real-world component with multiple arbitrary values', function () {
        $input = 'p-4 w-[300px] h-[200px] bg-[#f5f5f5] rounded-[10px] shadow-[0_2px_4px_rgba(0,0,0,0.1)] p-[20px] bg-white';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        // Both should resolve conflicts
        expect($twmResult)->toContain('p-[20px]');
        expect($twmResult)->not->toContain('p-4');
        expect($twmResult)->toContain('bg-white');
        expect($twmResult)->not->toContain('bg-[#f5f5f5]');

        expect($boostResult)->toContain('p-[20px]');
        expect($boostResult)->not->toContain('p-4');
        expect($boostResult)->toContain('bg-white');
        expect($boostResult)->not->toContain('bg-[#f5f5f5]');
    });

    it('handles calc() in arbitrary values', function () {
        $input = 'w-full w-[calc(100%-20px)]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('w-[calc(100%-20px)]');
        expect($boostResult)->toBe('w-[calc(100%-20px)]');
    });

    it('handles url() in arbitrary values', function () {
        $input = 'bg-none bg-[url("/image.png")]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('bg-[url("/image.png")]');
        expect($boostResult)->toBe('bg-[url("/image.png")]');
    });

    it('handles CSS variables in arbitrary values', function () {
        $input = 'text-gray-500 text-[var(--custom-color)]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('text-[var(--custom-color)]');
        expect($boostResult)->toBe('text-[var(--custom-color)]');
    });

    it('handles clamp() in arbitrary values', function () {
        $input = 'text-lg text-[clamp(1rem,2vw,1.5rem)]';

        $twmResult = TailwindMerge::merge($input);
        $boostResult = $this->boost->merge($input);

        expect($twmResult)->toBe('text-[clamp(1rem,2vw,1.5rem)]');
        expect($boostResult)->toBe('text-[clamp(1rem,2vw,1.5rem)]');
    });
});
