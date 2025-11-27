<?php

declare(strict_types=1);

use App\Services\TailwindMergeBoost;
use TailwindMerge\Laravel\Facades\TailwindMerge;

/*
|--------------------------------------------------------------------------
| TailwindMergeBoost Feature Parity Tests
|--------------------------------------------------------------------------
|
| These tests are adapted from the official tailwind-merge-php test suite
| (https://github.com/gehrisandro/tailwind-merge-php/tree/main/tests/Feature)
| to highlight cases where TailwindMergeBoost differs from TailwindMerge.
|
| Tests that pass show feature parity.
| Tests that fail show areas where Boost implementation differs.
|
*/

beforeEach(function () {
    $this->boost = new TailwindMergeBoost();
});

/*
|--------------------------------------------------------------------------
| Conflict Resolution Tests (from ConflictsTest.php)
|--------------------------------------------------------------------------
*/

describe('conflicts - padding and margin', function () {
    it('merges padding classes correctly', function () {
        $input = 'p-4 p-8';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('overrides px/py with p', function () {
        // TailwindMerge: p overrides px and py
        $input = 'px-4 py-2 p-6';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('overrides individual paddings with p', function () {
        // TailwindMerge: p overrides pt, pr, pb, pl
        $input = 'pt-4 pr-4 pb-4 pl-4 p-8';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles margin axis classes', function () {
        $input = 'mx-4 my-2 m-6';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });
});

describe('conflicts - borders', function () {
    it('handles border width vs border color', function () {
        // border-2 is width, border-red-500 is color - should keep both
        $input = 'border-2 border-red-500';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles border side widths', function () {
        $input = 'border-t border-t-2';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles border side colors', function () {
        $input = 'border-t-red-500 border-t-blue-500';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles mixed border conflicts', function () {
        $input = 'border border-2 border-4';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });
});

describe('conflicts - ring', function () {
    it('handles ring width vs ring color', function () {
        $input = 'ring ring-2 ring-blue-500';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles ring offset', function () {
        $input = 'ring-offset-2 ring-offset-4';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });
});

describe('conflicts - text', function () {
    it('handles text size vs text color', function () {
        // text-lg is size, text-red-500 is color - should keep both
        $input = 'text-lg text-red-500';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('merges text sizes correctly', function () {
        $input = 'text-sm text-lg text-xl';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });
});

describe('conflicts - background', function () {
    it('handles bg-none vs bg color', function () {
        $input = 'bg-red-500 bg-none';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles bg-gradient vs bg color', function () {
        // bg-gradient is a different utility from bg color
        $input = 'bg-red-500 bg-gradient-to-r';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });
});

/*
|--------------------------------------------------------------------------
| Modifier Tests (from ModifiersTest.php)
|--------------------------------------------------------------------------
*/

describe('modifiers - basic', function () {
    it('handles hover modifier', function () {
        $input = 'hover:p-4 hover:p-8';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles focus modifier', function () {
        $input = 'focus:bg-red-500 focus:bg-blue-500';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('keeps different state modifiers separate', function () {
        $input = 'hover:p-4 focus:p-8';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });
});

describe('modifiers - responsive', function () {
    it('handles sm breakpoint', function () {
        $input = 'sm:p-4 sm:p-8';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles md breakpoint', function () {
        $input = 'md:flex md:block';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles lg breakpoint', function () {
        $input = 'lg:w-full lg:w-1/2';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles xl breakpoint', function () {
        $input = 'xl:text-sm xl:text-lg';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles 2xl breakpoint', function () {
        $input = '2xl:grid 2xl:flex';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });
});

describe('modifiers - combined', function () {
    it('handles multiple modifiers', function () {
        $input = 'sm:hover:p-4 sm:hover:p-8';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles different modifier orders', function () {
        // hover:sm vs sm:hover - should be normalized
        $input = 'hover:sm:p-4 sm:hover:p-8';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles dark mode with hover', function () {
        $input = 'dark:hover:bg-red-500 dark:hover:bg-blue-500';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });
});

describe('modifiers - group and peer', function () {
    it('handles group-hover', function () {
        $input = 'group-hover:opacity-50 group-hover:opacity-100';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles peer-focus', function () {
        $input = 'peer-focus:text-red-500 peer-focus:text-blue-500';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });
});

describe('modifiers - important', function () {
    it('handles important modifier', function () {
        $input = '!p-4 !p-8';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('separates important from non-important', function () {
        $input = 'p-4 !p-8';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });
});

/*
|--------------------------------------------------------------------------
| Arbitrary Value Tests (from ArbitraryValuesTest.php)
|--------------------------------------------------------------------------
*/

describe('arbitrary values - basics', function () {
    it('handles arbitrary padding', function () {
        $input = 'p-[10px] p-[20px]';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles arbitrary with standard', function () {
        $input = 'p-4 p-[15px]';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });
});

describe('arbitrary values - colors', function () {
    it('handles hex colors', function () {
        $input = 'bg-red-500 bg-[#ff0000]';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles rgb colors', function () {
        $input = 'text-blue-500 text-[rgb(255,0,0)]';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles rgba colors', function () {
        $input = 'bg-red-500 bg-[rgba(255,0,0,0.5)]';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles css variables', function () {
        $input = 'text-gray-500 text-[var(--custom-color)]';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });
});

describe('arbitrary values - sizing', function () {
    it('handles calc()', function () {
        $input = 'w-full w-[calc(100%-20px)]';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles min()', function () {
        $input = 'w-96 w-[min(100%,400px)]';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles max()', function () {
        $input = 'h-screen h-[max(100vh,500px)]';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles clamp()', function () {
        $input = 'text-lg text-[clamp(1rem,2vw,1.5rem)]';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });
});

describe('arbitrary values - urls', function () {
    it('handles url() in bg', function () {
        $input = 'bg-none bg-[url("/image.png")]';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });
});

/*
|--------------------------------------------------------------------------
| Arbitrary Properties Tests (from ArbitraryPropertiesTest.php)
|--------------------------------------------------------------------------
*/

describe('arbitrary properties', function () {
    it('handles basic arbitrary property', function () {
        $input = '[color:red] [color:blue]';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('keeps different properties separate', function () {
        $input = '[color:red] [background:blue]';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles arbitrary property with modifier', function () {
        $input = 'hover:[color:red] hover:[color:blue]';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles mask-type property', function () {
        $input = '[mask-type:alpha] [mask-type:luminance]';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });
});

/*
|--------------------------------------------------------------------------
| Content Utilities Tests (from ContentUtilitiesTest.php)
|--------------------------------------------------------------------------
*/

describe('content utilities', function () {
    it('handles content-none', function () {
        $input = 'content-none content-["hello"]';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });
});

/*
|--------------------------------------------------------------------------
| Non-Conflicting Tests (from NonConflictingTest.php)  
|--------------------------------------------------------------------------
*/

describe('non-conflicting classes', function () {
    it('keeps all non-conflicting classes', function () {
        $input = 'flex items-center justify-between p-4 bg-white shadow-lg rounded-xl';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('preserves order of non-conflicting classes', function () {
        $input = 'border-2 border-solid border-red-500';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });
});

/*
|--------------------------------------------------------------------------
| Order Sensitivity Tests
|--------------------------------------------------------------------------
*/

describe('order sensitivity', function () {
    it('last class wins', function () {
        $input = 'bg-red-500 bg-blue-500 bg-green-500';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('respects order with modifiers', function () {
        $input = 'hover:bg-red-500 bg-blue-500 hover:bg-green-500';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });
});

/*
|--------------------------------------------------------------------------
| Edge Cases Tests
|--------------------------------------------------------------------------
*/

describe('edge cases', function () {
    it('handles empty string', function () {
        expect($this->boost->merge(''))->toBe(TailwindMerge::merge(''));
    });

    it('handles whitespace only', function () {
        expect($this->boost->merge('   '))->toBe(TailwindMerge::merge('   '));
    });

    it('handles extra whitespace between classes', function () {
        $input = 'p-4    p-8';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles newlines in input', function () {
        $input = "p-4\np-8";
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles tabs in input', function () {
        $input = "p-4\tp-8";
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });
});

/*
|--------------------------------------------------------------------------
| Complex Real-World Tests
|--------------------------------------------------------------------------
*/

describe('real-world components', function () {
    it('handles button component', function () {
        $input = 'inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 bg-red-500 hover:bg-red-600';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles card component', function () {
        $input = 'rounded-lg border bg-card text-card-foreground shadow-sm p-6 bg-white border-gray-200';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles input component', function () {
        $input = 'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 border-red-500 focus:border-red-500';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });
});

/*
|--------------------------------------------------------------------------
| Tailwind v3 Specific Features
|--------------------------------------------------------------------------
*/

describe('tailwind v3 features', function () {
    it('handles arbitrary variants', function () {
        $input = '[&>*]:p-4 [&>*]:p-8';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles data attributes', function () {
        $input = 'data-[state=open]:bg-red-500 data-[state=open]:bg-blue-500';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles has selector', function () {
        $input = 'has-[input]:p-4 has-[input]:p-8';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });
});

/*
|--------------------------------------------------------------------------
| Space and Divide Utilities
|--------------------------------------------------------------------------
*/

describe('space utilities', function () {
    it('handles space-x', function () {
        $input = 'space-x-2 space-x-4';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles space-y', function () {
        $input = 'space-y-2 space-y-4';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles negative space', function () {
        $input = '-space-x-2 -space-x-4';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });
});

describe('divide utilities', function () {
    it('handles divide-x', function () {
        $input = 'divide-x divide-x-2';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles divide-y', function () {
        $input = 'divide-y divide-y-2';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles divide color', function () {
        $input = 'divide-gray-200 divide-gray-400';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });
});

/*
|--------------------------------------------------------------------------
| Size Utility Tests
|--------------------------------------------------------------------------
*/

describe('size utilities', function () {
    it('handles size utility', function () {
        $input = 'size-4 size-8';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('size overrides width and height', function () {
        // In TailwindMerge, size should override w and h
        $input = 'w-4 h-4 size-8';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });
});

/*
|--------------------------------------------------------------------------
| Inset Utilities Tests  
|--------------------------------------------------------------------------
*/

describe('inset utilities', function () {
    it('handles inset', function () {
        $input = 'inset-0 inset-4';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('inset overrides individual positions', function () {
        $input = 'top-4 right-4 bottom-4 left-4 inset-0';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });

    it('handles inset-x and inset-y', function () {
        $input = 'inset-x-4 inset-y-4 inset-0';
        expect($this->boost->merge($input))->toBe(TailwindMerge::merge($input));
    });
});
