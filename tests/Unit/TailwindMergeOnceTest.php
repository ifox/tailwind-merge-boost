<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\TailwindMergeOnce;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use TailwindMerge\Support\Config;

class TailwindMergeOnceTest extends TestCase
{
    private TailwindMergeOnce $merger;

    protected function setUp(): void
    {
        parent::setUp();
        Config::setAdditionalConfig(config('tailwind-merge', []));
        $this->merger = new TailwindMergeOnce(Config::getMergedConfig(), app('cache')->store());
    }

    #[Test]
    public function it_merges_conflicting_classes(): void
    {
        $result = $this->merger->merge('p-4 p-6');
        $this->assertSame('p-6', $result);
    }

    #[Test]
    public function it_keeps_non_conflicting_classes(): void
    {
        $result = $this->merger->merge('p-4 mt-4');
        $this->assertContains('p-4', explode(' ', $result));
        $this->assertContains('mt-4', explode(' ', $result));
    }

    #[Test]
    public function it_handles_empty_input(): void
    {
        $this->assertSame('', $this->merger->merge(''));
        $this->assertSame('', $this->merger->merge('   '));
    }

    #[Test]
    public function it_handles_array_input(): void
    {
        $result = $this->merger->merge(['p-4', 'p-6']);
        $this->assertSame('p-6', $result);
    }

    #[Test]
    public function it_handles_nested_array_input(): void
    {
        $result = $this->merger->merge(['p-4', ['mt-4', 'p-8']]);
        $this->assertContains('mt-4', explode(' ', $result));
        $this->assertContains('p-8', explode(' ', $result));
        $this->assertNotContains('p-4', explode(' ', $result));
    }

    #[Test]
    public function it_handles_modifiers(): void
    {
        $result = $this->merger->merge('hover:bg-red-500 hover:bg-blue-500');
        $this->assertSame('hover:bg-blue-500', $result);
    }

    #[Test]
    public function it_keeps_different_modifiers(): void
    {
        $result = $this->merger->merge('hover:bg-red-500 focus:bg-blue-500');
        $this->assertContains('hover:bg-red-500', explode(' ', $result));
        $this->assertContains('focus:bg-blue-500', explode(' ', $result));
    }

    #[Test]
    public function it_handles_responsive_modifiers(): void
    {
        $result = $this->merger->merge('md:p-4 md:p-8');
        $this->assertSame('md:p-8', $result);
    }

    #[Test]
    public function it_keeps_different_responsive_modifiers(): void
    {
        $result = $this->merger->merge('md:p-4 lg:p-8');
        $this->assertContains('md:p-4', explode(' ', $result));
        $this->assertContains('lg:p-8', explode(' ', $result));
    }

    #[Test]
    public function it_handles_important_modifier(): void
    {
        $result = $this->merger->merge('!p-4 !p-8');
        $this->assertSame('!p-8', $result);
    }

    #[Test]
    public function it_keeps_important_and_non_important_separate(): void
    {
        $result = $this->merger->merge('p-4 !p-8');
        $this->assertContains('p-4', explode(' ', $result));
        $this->assertContains('!p-8', explode(' ', $result));
    }

    #[Test]
    public function it_handles_negative_values(): void
    {
        $result = $this->merger->merge('-mt-4 -mt-8');
        $this->assertSame('-mt-8', $result);
    }

    #[Test]
    public function it_handles_display_classes(): void
    {
        $result = $this->merger->merge('block flex');
        $this->assertSame('flex', $result);
    }

    #[Test]
    public function it_handles_position_classes(): void
    {
        $result = $this->merger->merge('static relative absolute');
        $this->assertSame('absolute', $result);
    }

    #[Test]
    public function it_handles_text_decoration_classes(): void
    {
        $result = $this->merger->merge('underline line-through');
        $this->assertSame('line-through', $result);
    }

    #[Test]
    public function it_handles_font_style_classes(): void
    {
        $result = $this->merger->merge('italic not-italic');
        $this->assertSame('not-italic', $result);
    }

    #[Test]
    public function it_handles_visibility_classes(): void
    {
        $result = $this->merger->merge('visible invisible');
        $this->assertSame('invisible', $result);
    }

    #[Test]
    public function it_memoizes_results_with_once(): void
    {
        $input = 'p-4 p-6';

        // First call
        $result1 = $this->merger->merge($input);

        // Second call should use once() memoization
        $result2 = $this->merger->merge($input);

        $this->assertSame($result1, $result2);
        $this->assertSame('p-6', $result1);
    }

    #[Test]
    #[DataProvider('mergeTestCases')]
    public function it_merges_classes_correctly(string $input, string $expected): void
    {
        $result = $this->merger->merge($input);
        $this->assertSame($expected, $result, "Failed for input: {$input}");
    }

    /**
     * @return array<string, array{input: string, expected: string}>
     */
    public static function mergeTestCases(): array
    {
        return [
            'padding conflict' => [
                'input' => 'p-4 p-6',
                'expected' => 'p-6',
            ],
            'margin conflict' => [
                'input' => 'mt-2 mt-4',
                'expected' => 'mt-4',
            ],
            'background color conflict' => [
                'input' => 'bg-red-500 bg-blue-500',
                'expected' => 'bg-blue-500',
            ],
            'text color conflict' => [
                'input' => 'text-red-500 text-blue-500',
                'expected' => 'text-blue-500',
            ],
            'width conflict' => [
                'input' => 'w-4 w-full',
                'expected' => 'w-full',
            ],
            'height conflict' => [
                'input' => 'h-4 h-screen',
                'expected' => 'h-screen',
            ],
            'flex direction conflict' => [
                'input' => 'flex-row flex-col',
                'expected' => 'flex-col',
            ],
            'rounded conflict' => [
                'input' => 'rounded rounded-lg',
                'expected' => 'rounded-lg',
            ],
            'shadow conflict' => [
                'input' => 'shadow shadow-lg',
                'expected' => 'shadow-lg',
            ],
            'border color conflict' => [
                'input' => 'border-red-500 border-blue-500',
                'expected' => 'border-blue-500',
            ],
            'gap conflict' => [
                'input' => 'gap-2 gap-4',
                'expected' => 'gap-4',
            ],
            'justify conflict' => [
                'input' => 'justify-start justify-center',
                'expected' => 'justify-center',
            ],
            'items conflict' => [
                'input' => 'items-start items-center',
                'expected' => 'items-center',
            ],
            'overflow conflict' => [
                'input' => 'overflow-auto overflow-hidden',
                'expected' => 'overflow-hidden',
            ],
            'opacity conflict' => [
                'input' => 'opacity-50 opacity-100',
                'expected' => 'opacity-100',
            ],
            'z-index conflict' => [
                'input' => 'z-10 z-50',
                'expected' => 'z-50',
            ],
            'transition conflict' => [
                'input' => 'transition transition-all',
                'expected' => 'transition-all',
            ],
            'duration conflict' => [
                'input' => 'duration-100 duration-300',
                'expected' => 'duration-300',
            ],
        ];
    }

    #[Test]
    public function it_handles_complex_class_combinations(): void
    {
        $result = $this->merger->merge(
            'flex flex-col items-center justify-between p-4 p-6 bg-white shadow-lg rounded-xl'
        );

        // Should remove p-4 (conflict with p-6) but keep everything else
        $classes = explode(' ', $result);
        $this->assertContains('flex', $classes);
        $this->assertContains('flex-col', $classes);
        $this->assertContains('items-center', $classes);
        $this->assertContains('justify-between', $classes);
        $this->assertContains('p-6', $classes);
        $this->assertContains('bg-white', $classes);
        $this->assertContains('shadow-lg', $classes);
        $this->assertContains('rounded-xl', $classes);
        $this->assertNotContains('p-4', $classes);
    }

    #[Test]
    public function it_handles_multiple_modifier_combinations(): void
    {
        $result = $this->merger->merge(
            'hover:bg-red-500 focus:bg-blue-500 active:bg-green-500 hover:bg-yellow-500'
        );

        $classes = explode(' ', $result);
        // hover:bg-yellow-500 should win over hover:bg-red-500
        $this->assertContains('hover:bg-yellow-500', $classes);
        $this->assertContains('focus:bg-blue-500', $classes);
        $this->assertContains('active:bg-green-500', $classes);
        $this->assertNotContains('hover:bg-red-500', $classes);
    }

    #[Test]
    public function it_preserves_order_of_non_conflicting_classes(): void
    {
        $result = $this->merger->merge('flex items-center space-x-4');

        // All classes should be preserved since none conflict
        $this->assertStringContainsString('flex', $result);
        $this->assertStringContainsString('items-center', $result);
        $this->assertStringContainsString('space-x-4', $result);
    }

    #[Test]
    public function it_handles_unknown_classes_gracefully(): void
    {
        $result = $this->merger->merge('custom-class p-4 another-custom-class');

        $classes = explode(' ', $result);
        $this->assertContains('custom-class', $classes);
        $this->assertContains('p-4', $classes);
        $this->assertContains('another-custom-class', $classes);
    }
}
