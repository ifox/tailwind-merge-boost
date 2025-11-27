<?php

declare(strict_types=1);

use App\Services\TailwindMergeBoost;
use TailwindMerge\Laravel\Facades\TailwindMerge;

/*
|--------------------------------------------------------------------------
| TailwindMergeBoost Feature Parity Tests
|--------------------------------------------------------------------------
|
| These tests are copied from the official tailwind-merge-php test suite
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
| ArbitraryPropertiesTest.php
|--------------------------------------------------------------------------
*/

describe('arbitrary properties - conflicts', function () {
    it('handles arbitrary property conflicts correctly', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['[paint-order:markers] [paint-order:normal]', '[paint-order:normal]'],
        ['[paint-order:markers] [--my-var:2rem] [paint-order:normal] [--my-var:4px]', '[paint-order:normal] [--my-var:4px]'],
        ['[--first-var:1rem] [--second-var:2rem]', '[--first-var:1rem] [--second-var:2rem]'],
    ]);

    it('handles arbitrary property conflicts with modifiers correctly', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['[paint-order:markers] hover:[paint-order:normal]', '[paint-order:markers] hover:[paint-order:normal]'],
        ['hover:[paint-order:markers] hover:[paint-order:normal]', 'hover:[paint-order:normal]'],
        ['hover:focus:[paint-order:markers] focus:hover:[paint-order:normal]', 'focus:hover:[paint-order:normal]'],
        ['[paint-order:markers] [paint-order:normal] [--my-var:2rem] lg:[--my-var:4px]', '[paint-order:normal] [--my-var:2rem] lg:[--my-var:4px]'],
    ]);

    test('handles complex arbitrary property conflicts correctly', function () {
        $input = '[-unknown-prop:::123:::] [-unknown-prop:url(https://hi.com)]';
        $output = '[-unknown-prop:url(https://hi.com)]';
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    });

    it('handles important modifier correctly', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['![some:prop] [some:other]', '![some:prop] [some:other]'],
        ['![some:prop] [some:other] [some:one] ![some:another]', '[some:one] ![some:another]'],
    ]);
});

/*
|--------------------------------------------------------------------------
| ArbitraryValuesTest.php
|--------------------------------------------------------------------------
*/

describe('arbitrary values - simple conflicts', function () {
    it('handles simple conflicts with arbitrary values correctly', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['m-[2px] m-[10px]', 'm-[10px]'],
        ['m-[2px] m-[11svmin] m-[12in] m-[13lvi] m-[14vb] m-[15vmax] m-[16mm] m-[17%] m-[18em] m-[19px] m-[10dvh]', 'm-[10dvh]'],
        ['h-[10px] h-[11cqw] h-[12cqh] h-[13cqi] h-[14cqb] h-[15cqmin] h-[16cqmax]', 'h-[16cqmax]'],
        ['z-20 z-[99]', 'z-[99]'],
        ['my-[2px] m-[10rem]', 'm-[10rem]'],
        ['cursor-pointer cursor-[grab]', 'cursor-[grab]'],
        ['m-[2px] m-[calc(100%-var(--arbitrary))]', 'm-[calc(100%-var(--arbitrary))]'],
        ['m-[2px] m-[length:var(--mystery-var)]', 'm-[length:var(--mystery-var)]'],
        ['opacity-10 opacity-[0.025]', 'opacity-[0.025]'],
        ['scale-75 scale-[1.7]', 'scale-[1.7]'],
        ['brightness-90 brightness-[1.75]', 'brightness-[1.75]'],
        ['min-h-[0.5px] min-h-[0]', 'min-h-[0]'],
        ['text-[0.5px] text-[color:0]', 'text-[0.5px] text-[color:0]'],
        ['text-[0.5px] text-[--my-0]', 'text-[0.5px] text-[--my-0]'],
    ]);

    it('handles arbitrary length conflicts with labels and modifiers correctly', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['hover:m-[2px] hover:m-[length:var(--c)]', 'hover:m-[length:var(--c)]'],
        ['hover:focus:m-[2px] focus:hover:m-[length:var(--c)]', 'focus:hover:m-[length:var(--c)]'],
        ['border-b border-[color:rgb(var(--color-gray-500-rgb)/50%))]', 'border-b border-[color:rgb(var(--color-gray-500-rgb)/50%))]'],
        ['border-[color:rgb(var(--color-gray-500-rgb)/50%))] border-b', 'border-[color:rgb(var(--color-gray-500-rgb)/50%))] border-b'],
        ['border-b border-[color:rgb(var(--color-gray-500-rgb)/50%))] border-some-coloooor', 'border-b border-some-coloooor'],
    ]);

    it('handles complex arbitrary value conflicts correctly', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['grid-rows-[1fr,auto] grid-rows-2', 'grid-rows-2'],
        ['grid-rows-[repeat(20,minmax(0,1fr))] grid-rows-3', 'grid-rows-3'],
    ]);

    it('handles ambiguous arbitrary values correctly', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['mt-2 mt-[calc(theme(fontSize.4xl)/1.125)]', 'mt-[calc(theme(fontSize.4xl)/1.125)]'],
        ['p-2 p-[calc(theme(fontSize.4xl)/1.125)_10px]', 'p-[calc(theme(fontSize.4xl)/1.125)_10px]'],
        ['mt-2 mt-[length:theme(someScale.someValue)]', 'mt-[length:theme(someScale.someValue)]'],
        ['mt-2 mt-[theme(someScale.someValue)]', 'mt-[theme(someScale.someValue)]'],
        ['text-2xl text-[length:theme(someScale.someValue)]', 'text-[length:theme(someScale.someValue)]'],
        ['text-2xl text-[calc(theme(fontSize.4xl)/1.125)]', 'text-[calc(theme(fontSize.4xl)/1.125)]'],
        ['bg-cover bg-[percentage:30%] bg-[length:200px_100px]', 'bg-[length:200px_100px]'],
        ['bg-none bg-[url(.)] bg-[image:.] bg-[url:.] bg-[linear-gradient(.)] bg-gradient-to-r', 'bg-gradient-to-r'],
    ]);

    it('handles ambiguous non conflicting arbitrary values correctly', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['border-[2px] border-[0.85px] border-[#ff0000] border-[#0000ff]', 'border-[0.85px] border-[#0000ff]'],
    ]);
});

/*
|--------------------------------------------------------------------------
| ArbitraryVariantsTest.php
|--------------------------------------------------------------------------
*/

describe('arbitrary variants', function () {
    it('basic arbitrary variants', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['[&>*]:underline [&>*]:line-through', '[&>*]:line-through'],
        ['[&>*]:underline [&>*]:line-through [&_div]:line-through', '[&>*]:line-through [&_div]:line-through'],
        ['supports-[display:grid]:flex supports-[display:grid]:grid', 'supports-[display:grid]:grid'],
    ]);

    it('arbitrary variants with modifiers', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['dark:lg:hover:[&>*]:underline dark:lg:hover:[&>*]:line-through', 'dark:lg:hover:[&>*]:line-through'],
        ['dark:lg:hover:[&>*]:underline dark:hover:lg:[&>*]:line-through', 'dark:hover:lg:[&>*]:line-through'],
        ['hover:[&>*]:underline [&>*]:hover:line-through', 'hover:[&>*]:underline [&>*]:hover:line-through'],
        ['hover:dark:[&>*]:underline dark:hover:[&>*]:underline dark:[&>*]:hover:line-through', 'dark:hover:[&>*]:underline dark:[&>*]:hover:line-through'],
    ]);

    it('arbitrary variants with complex syntax in them', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['[@media_screen{@media(hover:hover)}]:underline [@media_screen{@media(hover:hover)}]:line-through', '[@media_screen{@media(hover:hover)}]:line-through'],
        ['hover:[@media_screen{@media(hover:hover)}]:underline hover:[@media_screen{@media(hover:hover)}]:line-through', 'hover:[@media_screen{@media(hover:hover)}]:line-through'],
    ]);

    test('arbitrary variants with attribute selectors', function () {
        $input = '[&[data-open]]:underline [&[data-open]]:line-through';
        $output = '[&[data-open]]:line-through';
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    });

    test('arbitrary variants with multiple attribute selectors', function () {
        $input = '[&[data-foo][data-bar]:not([data-baz])]:underline [&[data-foo][data-bar]:not([data-baz])]:line-through';
        $output = '[&[data-foo][data-bar]:not([data-baz])]:line-through';
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    });

    it('multiple arbitrary variants', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['[&>*]:[&_div]:underline [&>*]:[&_div]:line-through', '[&>*]:[&_div]:line-through'],
        ['[&>*]:[&_div]:underline [&_div]:[&>*]:line-through', '[&>*]:[&_div]:underline [&_div]:[&>*]:line-through'],
        ['hover:dark:[&>*]:focus:disabled:[&_div]:underline dark:hover:[&>*]:disabled:focus:[&_div]:line-through', 'dark:hover:[&>*]:disabled:focus:[&_div]:line-through'],
        ['hover:dark:[&>*]:focus:[&_div]:disabled:underline dark:hover:[&>*]:disabled:focus:[&_div]:line-through', 'hover:dark:[&>*]:focus:[&_div]:disabled:underline dark:hover:[&>*]:disabled:focus:[&_div]:line-through'],
    ]);

    it('arbitrary variants with arbitrary properties', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['[&>*]:[color:red] [&>*]:[color:blue]', '[&>*]:[color:blue]'],
        ['[&[data-foo][data-bar]:not([data-baz])]:nod:noa:[color:red] [&[data-foo][data-bar]:not([data-baz])]:noa:nod:[color:blue]', '[&[data-foo][data-bar]:not([data-baz])]:noa:nod:[color:blue]'],
    ]);
});

/*
|--------------------------------------------------------------------------
| ClassGroupConflictsTest.php
|--------------------------------------------------------------------------
*/

describe('class group conflicts', function () {
    it('merges classes from same group correctly', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['overflow-x-auto overflow-x-hidden', 'overflow-x-hidden'],
        ['w-full w-fit', 'w-fit'],
        ['overflow-x-auto overflow-x-hidden overflow-x-scroll', 'overflow-x-scroll'],
        ['overflow-x-auto hover:overflow-x-hidden overflow-x-scroll', 'hover:overflow-x-hidden overflow-x-scroll'],
        ['overflow-x-auto hover:overflow-x-hidden hover:overflow-x-auto overflow-x-scroll', 'hover:overflow-x-auto overflow-x-scroll'],
        ['col-span-1 col-span-full', 'col-span-full'],
    ]);

    it('merges classes from Font Variant Numeric section correctly', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['lining-nums tabular-nums diagonal-fractions', 'lining-nums tabular-nums diagonal-fractions'],
        ['normal-nums tabular-nums diagonal-fractions', 'tabular-nums diagonal-fractions'],
        ['tabular-nums diagonal-fractions normal-nums', 'normal-nums'],
        ['tabular-nums proportional-nums', 'proportional-nums'],
    ]);
});

/*
|--------------------------------------------------------------------------
| ColorsTest.php
|--------------------------------------------------------------------------
*/

describe('colors', function () {
    it('handles color conflicts properly', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['bg-grey-5 bg-hotpink', 'bg-hotpink'],
        ['hover:bg-grey-5 hover:bg-hotpink', 'hover:bg-hotpink'],
    ]);
});

/*
|--------------------------------------------------------------------------
| ConflictsAcrossClassGroupsTest.php
|--------------------------------------------------------------------------
*/

describe('conflicts across class groups', function () {
    it('handles conflicts across class groups correctly', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['inset-1 inset-x-1', 'inset-1 inset-x-1'],
        ['inset-x-1 inset-1', 'inset-1'],
        ['inset-x-1 left-1 inset-1', 'inset-1'],
        ['inset-x-1 inset-1 left-1', 'inset-1 left-1'],
        ['inset-x-1 right-1 inset-1', 'inset-1'],
        ['inset-x-1 right-1 inset-x-1', 'inset-x-1'],
        ['inset-x-1 right-1 inset-y-1', 'inset-x-1 right-1 inset-y-1'],
        ['right-1 inset-x-1 inset-y-1', 'inset-x-1 inset-y-1'],
        ['inset-x-1 hover:left-1 inset-1', 'hover:left-1 inset-1'],
        ['pl-4 px-6', 'px-6'],
    ]);

    it('ring and shadow classes do not create conflict', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['ring shadow', 'ring shadow'],
        ['ring-2 shadow-md', 'ring-2 shadow-md'],
        ['shadow ring', 'shadow ring'],
        ['shadow-md ring-2', 'shadow-md ring-2'],
    ]);

    it('touch classes do create conflicts correctly', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['touch-pan-x touch-pan-right', 'touch-pan-right'],
        ['touch-none touch-pan-x', 'touch-pan-x'],
        ['touch-pan-x touch-none', 'touch-none'],
        ['touch-pan-x touch-pan-y touch-pinch-zoom', 'touch-pan-x touch-pan-y touch-pinch-zoom'],
        ['touch-manipulation touch-pan-x touch-pan-y touch-pinch-zoom', 'touch-pan-x touch-pan-y touch-pinch-zoom'],
        ['touch-pan-x touch-pan-y touch-pinch-zoom touch-auto', 'touch-auto'],
    ]);

    it('line-clamp classes do create conflicts correctly', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['overflow-auto inline line-clamp-1', 'line-clamp-1'],
        ['line-clamp-1 overflow-auto inline', 'line-clamp-1 overflow-auto inline'],
    ]);
});

/*
|--------------------------------------------------------------------------
| ContentUtilitiesTest.php
|--------------------------------------------------------------------------
*/

describe('content utilities', function () {
    test('merges content utilities correctly', function () {
        $input = "content-['hello'] content-[attr(data-content)]";
        $output = 'content-[attr(data-content)]';
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    });
});

/*
|--------------------------------------------------------------------------
| ImportantModifierTest.php
|--------------------------------------------------------------------------
*/

describe('important modifier', function () {
    it('merges tailwind classes with important modifier correctly', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['!font-medium !font-bold', '!font-bold'],
        ['!font-medium !font-bold font-thin', '!font-bold font-thin'],
        ['!right-2 !-inset-x-px', '!-inset-x-px'],
        ['focus:!inline focus:!block', 'focus:!block'],
    ]);
});

/*
|--------------------------------------------------------------------------
| ModifiersTest.php
|--------------------------------------------------------------------------
*/

describe('modifiers', function () {
    it('conflicts across prefix modifiers', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['hover:block hover:inline', 'hover:inline'],
        ['hover:block hover:focus:inline', 'hover:block hover:focus:inline'],
        ['hover:block hover:focus:inline focus:hover:inline', 'hover:block focus:hover:inline'],
        ['focus-within:inline focus-within:block', 'focus-within:block'],
    ]);

    it('conflicts across postfix modifiers', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['text-lg/7 text-lg/8', 'text-lg/8'],
        ['text-lg/none leading-9', 'text-lg/none leading-9'],
        ['leading-9 text-lg/none', 'text-lg/none'],
        ['w-full w-1/2', 'w-1/2'],
    ]);
});

/*
|--------------------------------------------------------------------------
| NegativeValuesTest.php
|--------------------------------------------------------------------------
*/

describe('negative values', function () {
    it('handles negative value conflicts correctly', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['-m-2 -m-5', '-m-5'],
        ['-top-12 -top-2000', '-top-2000'],
    ]);

    it('handles conflicts between positive and negative values correctly', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['-m-2 m-auto', 'm-auto'],
        ['top-12 -top-69', '-top-69'],
    ]);

    it('handles conflicts across groups with negative values correctly', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['-right-1 inset-x-1', 'inset-x-1'],
        ['hover:focus:-right-1 focus:hover:inset-x-1', 'focus:hover:inset-x-1'],
    ]);
});

/*
|--------------------------------------------------------------------------
| NonConflictingClasses.php
|--------------------------------------------------------------------------
*/

describe('non-conflicting classes', function () {
    it('merges non-conflicting classes correctly', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['border-t border-white/10', 'border-t border-white/10'],
        ['border-t border-white', 'border-t border-white'],
        ['text-3.5xl text-black', 'text-3.5xl text-black'],
    ]);
});

/*
|--------------------------------------------------------------------------
| NonTailwindClasses.php
|--------------------------------------------------------------------------
*/

describe('non-tailwind classes', function () {
    it('does not alter non-tailwind classes', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['non-tailwind-class inline block', 'non-tailwind-class block'],
        ['inline block inline-1', 'block inline-1'],
        ['inline block i-inline', 'block i-inline'],
        ['focus:inline focus:block focus:inline-1', 'focus:block focus:inline-1'],
    ]);
});

/*
|--------------------------------------------------------------------------
| PerSideBorderColorsTest.php
|--------------------------------------------------------------------------
*/

describe('per-side border colors', function () {
    it('merges classes with per-side border colors correctly', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['border-t-some-blue border-t-other-blue', 'border-t-other-blue'],
        ['border-t-some-blue border-some-blue', 'border-some-blue'],
    ]);
});

/*
|--------------------------------------------------------------------------
| PseudoVariants.php
|--------------------------------------------------------------------------
*/

describe('pseudo variants', function () {
    it('handles pseudo variants conflicts properly', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['empty:p-2 empty:p-3', 'empty:p-3'],
        ['hover:empty:p-2 hover:empty:p-3', 'hover:empty:p-3'],
        ['read-only:p-2 read-only:p-3', 'read-only:p-3'],
    ]);

    it('handles pseudo variant group conflicts properly', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['group-empty:p-2 group-empty:p-3', 'group-empty:p-3'],
        ['peer-empty:p-2 peer-empty:p-3', 'peer-empty:p-3'],
        ['group-empty:p-2 peer-empty:p-3', 'group-empty:p-2 peer-empty:p-3'],
        ['hover:group-empty:p-2 hover:group-empty:p-3', 'hover:group-empty:p-3'],
        ['group-read-only:p-2 group-read-only:p-3', 'group-read-only:p-3'],
    ]);
});

/*
|--------------------------------------------------------------------------
| StandaloneClassesTest.php
|--------------------------------------------------------------------------
*/

describe('standalone classes', function () {
    it('merges standalone classes from same group correctly', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['inline block', 'block'],
        ['hover:block hover:inline', 'hover:inline'],
        ['hover:block hover:block', 'hover:block'],
        ['inline hover:inline focus:inline hover:block hover:focus:block', 'inline focus:inline hover:block hover:focus:block'],
        ['underline line-through', 'line-through'],
        ['line-through no-underline', 'no-underline'],
    ]);
});

/*
|--------------------------------------------------------------------------
| TailwindCssVersionsTest.php
|--------------------------------------------------------------------------
*/

describe('Tailwind CSS versions', function () {
    it('supports Tailwind CSS v3.3 features', function (string|array $input, string $output) {
        $mergeInput = is_array($input) ? implode(' ', $input) : $input;
        expect($this->boost->merge($mergeInput))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['text-red text-lg/7 text-lg/8', 'text-red text-lg/8'],
        [[
            'start-0 start-1',
            'end-0 end-1',
            'ps-0 ps-1 pe-0 pe-1',
            'ms-0 ms-1 me-0 me-1',
            'rounded-s-sm rounded-s-md rounded-e-sm rounded-e-md',
            'rounded-ss-sm rounded-ss-md rounded-ee-sm rounded-ee-md',
        ], 'start-1 end-1 ps-1 pe-1 ms-1 me-1 rounded-s-md rounded-e-md rounded-ss-md rounded-ee-md'],
        ['start-0 end-0 inset-0 ps-0 pe-0 p-0 ms-0 me-0 m-0 rounded-ss rounded-es rounded-s', 'inset-0 p-0 m-0 rounded-s'],
        ['hyphens-auto hyphens-manual', 'hyphens-manual'],
        ['from-0% from-10% from-[12.5%] via-0% via-10% via-[12.5%] to-0% to-10% to-[12.5%]', 'from-[12.5%] via-[12.5%] to-[12.5%]'],
        ['from-0% from-red', 'from-0% from-red'],
        ['list-image-none list-image-[url(./my-image.png)] list-image-[var(--value)]', 'list-image-[var(--value)]'],
        ['caption-top caption-bottom', 'caption-bottom'],
        ['line-clamp-2 line-clamp-none line-clamp-[10]', 'line-clamp-[10]'],
        ['delay-150 delay-0 duration-150 duration-0', 'delay-0 duration-0'],
        ['justify-normal justify-center justify-stretch', 'justify-stretch'],
        ['content-normal content-center content-stretch', 'content-stretch'],
        ['whitespace-nowrap whitespace-break-spaces', 'whitespace-break-spaces'],
    ]);

    it('supports Tailwind CSS v3.4 features', function (string|array $input, string $output) {
        $mergeInput = is_array($input) ? implode(' ', $input) : $input;
        expect($this->boost->merge($mergeInput))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['h-svh h-dvh w-svw w-dvw', 'h-dvh w-dvw'],
        ['has-[[data-potato]]:p-1 has-[[data-potato]]:p-2 group-has-[:checked]:grid group-has-[:checked]:flex', 'has-[[data-potato]]:p-2 group-has-[:checked]:flex'],
        ['text-wrap text-pretty', 'text-pretty'],
        ['w-5 h-3 size-10 w-12', 'size-10 w-12'],
        ['grid-cols-2 grid-cols-subgrid grid-rows-5 grid-rows-subgrid', 'grid-cols-subgrid grid-rows-subgrid'],
        ['min-w-0 min-w-50 min-w-px max-w-0 max-w-50 max-w-px', 'min-w-px max-w-px'],
        ['forced-color-adjust-none forced-color-adjust-auto', 'forced-color-adjust-auto'],
        ['appearance-none appearance-auto', 'appearance-auto'],
        ['float-start float-end clear-start clear-end', 'float-end clear-end'],
        ['*:p-10 *:p-20 hover:*:p-10 hover:*:p-20', '*:p-20 hover:*:p-20'],
    ]);
});

/*
|--------------------------------------------------------------------------
| TailwindMergeTest.php
|--------------------------------------------------------------------------
*/

describe('basic merges', function () {
    it('does basic merges', function (string $input, string $output) {
        expect($this->boost->merge($input))->toBe($output);
        expect(TailwindMerge::merge($input))->toBe($output);
    })->with([
        ['h-10 w-10', 'h-10 w-10'],
        ['mix-blend-normal mix-blend-multiply', 'mix-blend-multiply'],
        ['h-10 h-min', 'h-min'],
        ['stroke-black stroke-1', 'stroke-black stroke-1'],
        ['stroke-2 stroke-[3]', 'stroke-[3]'],
        ['outline-black outline-1', 'outline-black outline-1'],
        ['grayscale-0 grayscale-[50%]', 'grayscale-[50%]'],
        ['grow grow-[2]', 'grow-[2]'],
        ['h-10 lg:h-12 lg:h-20', 'h-10 lg:h-20'],
        ['text-black dark:text-white dark:text-gray-700', 'text-black dark:text-gray-700'],
    ]);
});
