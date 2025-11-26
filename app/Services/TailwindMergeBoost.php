<?php

declare(strict_types=1);

namespace App\Services;

/**
 * TailwindMergeBoost - An efficient Tailwind CSS class merger.
 *
 * This implementation is optimized for performance by:
 * - Using lookup tables for common class patterns
 * - Avoiding unnecessary object creation
 * - Using simple string operations and regex
 * - Caching parsed class groups
 */
class TailwindMergeBoost
{
    /**
     * Cache for parsed class groups.
     *
     * @var array<string, string>
     */
    private array $classGroupCache = [];

    /**
     * Maximum cache size.
     */
    private int $cacheSize = 500;

    /**
     * Class group patterns - ordered by specificity (more specific first).
     *
     * @var array<string, string>
     */
    private static array $classGroupPatterns = [
        // Spacing - padding (more specific first)
        '/^ps-/' => 'padding-s',
        '/^pe-/' => 'padding-e',
        '/^pt-/' => 'padding-t',
        '/^pr-/' => 'padding-r',
        '/^pb-/' => 'padding-b',
        '/^pl-/' => 'padding-l',
        '/^px-/' => 'padding-x',
        '/^py-/' => 'padding-y',
        '/^p-/' => 'padding',
        // Spacing - margin (more specific first, handles negative values)
        '/^-?ms-/' => 'margin-s',
        '/^-?me-/' => 'margin-e',
        '/^-?mt-/' => 'margin-t',
        '/^-?mr-/' => 'margin-r',
        '/^-?mb-/' => 'margin-b',
        '/^-?ml-/' => 'margin-l',
        '/^-?mx-/' => 'margin-x',
        '/^-?my-/' => 'margin-y',
        '/^-?m-/' => 'margin',
        // Width/Height/Size
        '/^w-/' => 'width',
        '/^min-w-/' => 'min-width',
        '/^max-w-/' => 'max-width',
        '/^h-/' => 'height',
        '/^min-h-/' => 'min-height',
        '/^max-h-/' => 'max-height',
        '/^size-/' => 'size',
        // Flex
        '/^flex-/' => 'flex',
        '/^basis-/' => 'flex-basis',
        '/^grow/' => 'flex-grow',
        '/^shrink/' => 'flex-shrink',
        '/^order-/' => 'order',
        // Grid
        '/^grid-cols-/' => 'grid-cols',
        '/^grid-rows-/' => 'grid-rows',
        '/^col-/' => 'grid-col',
        '/^row-/' => 'grid-row',
        '/^gap-/' => 'gap',
        '/^gap-x-/' => 'gap-x',
        '/^gap-y-/' => 'gap-y',
        // Text size (must be before text color)
        '/^text-(xs|sm|base|lg|xl|2xl|3xl|4xl|5xl|6xl|7xl|8xl|9xl)$/' => 'text-size',
        // Text color (general pattern for colors)
        '/^text-/' => 'text-color',
        // Border width (must be before border color) - border, border-0, border-2, border-4, border-8
        '/^border-(0|2|4|8)$/' => 'border-width',
        // Border side widths
        '/^border-[xytblr]-(0|2|4|8)$/' => 'border-width',
        '/^border-[xytblr]$/' => 'border-width',
        // Border color
        '/^border-/' => 'border-color',
        // Ring width (must be before ring color) - ring, ring-0, ring-1, ring-2, ring-4, ring-8, ring-inset
        '/^ring-(0|1|2|4|8)$/' => 'ring-width',
        // Ring offset
        '/^ring-offset-/' => 'ring-offset',
        // Ring color
        '/^ring-/' => 'ring-color',
        // Outline width (must be before outline color)
        '/^outline-(0|1|2|4|8)$/' => 'outline-width',
        // Outline offset
        '/^outline-offset-/' => 'outline-offset',
        // Outline color
        '/^outline-/' => 'outline-color',
        // Background gradient (must be before bg color)
        '/^bg-gradient-/' => 'bg-gradient',
        // Background color
        '/^bg-/' => 'bg-color',
        '/^fill-/' => 'fill',
        '/^stroke-/' => 'stroke',
        '/^shadow($|-)/' => 'shadow',
        '/^accent-/' => 'accent',
        '/^caret-/' => 'caret',
        '/^decoration-/' => 'decoration',
        '/^divide-/' => 'divide',
        '/^placeholder-/' => 'placeholder',
        '/^from-/' => 'gradient-from',
        '/^via-/' => 'gradient-via',
        '/^to-/' => 'gradient-to',
        // Typography
        '/^font-/' => 'font',
        '/^leading-/' => 'leading',
        '/^tracking-/' => 'tracking',
        '/^indent-/' => 'indent',
        '/^align-/' => 'align',
        '/^whitespace-/' => 'whitespace',
        '/^break-/' => 'break',
        '/^hyphens-/' => 'hyphens',
        // Border radius
        '/^rounded/' => 'rounded',
        // Transforms
        '/^scale-/' => 'scale',
        '/^rotate-/' => 'rotate',
        '/^translate-/' => 'translate',
        '/^skew-/' => 'skew',
        '/^origin-/' => 'origin',
        // Transitions
        '/^transition/' => 'transition',
        '/^duration-/' => 'duration',
        '/^ease-/' => 'ease',
        '/^delay-/' => 'delay',
        '/^animate-/' => 'animate',
        // Filters
        '/^blur/' => 'blur',
        '/^brightness-/' => 'brightness',
        '/^contrast-/' => 'contrast',
        '/^grayscale/' => 'grayscale',
        '/^hue-rotate-/' => 'hue-rotate',
        '/^invert/' => 'invert',
        '/^saturate-/' => 'saturate',
        '/^sepia/' => 'sepia',
        '/^drop-shadow/' => 'drop-shadow',
        '/^backdrop-/' => 'backdrop',
        // Layout
        '/^aspect-/' => 'aspect',
        '/^columns-/' => 'columns',
        '/^object-/' => 'object',
        '/^overflow-/' => 'overflow',
        '/^overscroll-/' => 'overscroll',
        '/^inset-/' => 'inset',
        '/^top-/' => 'top',
        '/^right-/' => 'right',
        '/^bottom-/' => 'bottom',
        '/^left-/' => 'left',
        '/^start-/' => 'start',
        '/^end-/' => 'end',
        '/^z-/' => 'z-index',
        // Spacing between
        '/^space-[xy]-/' => 'space',
        // Scroll
        '/^scroll-/' => 'scroll',
        '/^snap-/' => 'snap',
        // Other
        '/^opacity-/' => 'opacity',
        '/^cursor-/' => 'cursor',
        '/^select-/' => 'select',
        '/^resize/' => 'resize',
        '/^list-/' => 'list',
        '/^appearance-/' => 'appearance',
        '/^pointer-events-/' => 'pointer-events',
        '/^touch-/' => 'touch',
        '/^will-change-/' => 'will-change',
        '/^content-/' => 'content',
        '/^items-/' => 'items',
        '/^justify-/' => 'justify',
        '/^self-/' => 'self',
        '/^place-/' => 'place',
        '/^table-/' => 'table',
        '/^caption-/' => 'caption',
        '/^line-clamp-/' => 'line-clamp',
    ];

    /**
     * Exact class mappings for common classes without patterns.
     *
     * @var array<string, string>
     */
    private static array $exactClassGroups = [
        // Display
        'block' => 'display',
        'inline-block' => 'display',
        'inline' => 'display',
        'flex' => 'display',
        'inline-flex' => 'display',
        'table' => 'display',
        'inline-table' => 'display',
        'table-caption' => 'display',
        'table-cell' => 'display',
        'table-column' => 'display',
        'table-column-group' => 'display',
        'table-footer-group' => 'display',
        'table-header-group' => 'display',
        'table-row-group' => 'display',
        'table-row' => 'display',
        'flow-root' => 'display',
        'grid' => 'display',
        'inline-grid' => 'display',
        'contents' => 'display',
        'list-item' => 'display',
        'hidden' => 'display',
        // Position
        'static' => 'position',
        'fixed' => 'position',
        'absolute' => 'position',
        'relative' => 'position',
        'sticky' => 'position',
        // Visibility
        'visible' => 'visibility',
        'invisible' => 'visibility',
        'collapse' => 'visibility',
        // Float
        'float-right' => 'float',
        'float-left' => 'float',
        'float-none' => 'float',
        'float-start' => 'float',
        'float-end' => 'float',
        // Clear
        'clear-left' => 'clear',
        'clear-right' => 'clear',
        'clear-both' => 'clear',
        'clear-none' => 'clear',
        'clear-start' => 'clear',
        'clear-end' => 'clear',
        // Isolation
        'isolate' => 'isolation',
        'isolation-auto' => 'isolation',
        // Box
        'box-border' => 'box-sizing',
        'box-content' => 'box-sizing',
        'box-decoration-slice' => 'box-decoration',
        'box-decoration-clone' => 'box-decoration',
        // Container
        'container' => 'container',
        // Border width (standalone)
        'border' => 'border-width',
        'border-t' => 'border-width',
        'border-r' => 'border-width',
        'border-b' => 'border-width',
        'border-l' => 'border-width',
        'border-x' => 'border-width',
        'border-y' => 'border-width',
        // Ring width (standalone)
        'ring' => 'ring-width',
        // Outline width (standalone)
        'outline' => 'outline-width',
        'outline-none' => 'outline-width',
        // Font style
        'italic' => 'font-style',
        'not-italic' => 'font-style',
        // Font smoothing
        'antialiased' => 'font-smoothing',
        'subpixel-antialiased' => 'font-smoothing',
        // Text decoration
        'underline' => 'text-decoration',
        'overline' => 'text-decoration',
        'line-through' => 'text-decoration',
        'no-underline' => 'text-decoration',
        // Text transform
        'uppercase' => 'text-transform',
        'lowercase' => 'text-transform',
        'capitalize' => 'text-transform',
        'normal-case' => 'text-transform',
        // Text overflow
        'truncate' => 'text-overflow',
        'text-ellipsis' => 'text-overflow',
        'text-clip' => 'text-overflow',
        // Screen readers
        'sr-only' => 'sr',
        'not-sr-only' => 'sr',
        // Font variant numeric
        'normal-nums' => 'fvn-normal',
        'ordinal' => 'fvn-ordinal',
        'slashed-zero' => 'fvn-slashed-zero',
        'lining-nums' => 'fvn-figure',
        'oldstyle-nums' => 'fvn-figure',
        'proportional-nums' => 'fvn-spacing',
        'tabular-nums' => 'fvn-spacing',
        'diagonal-fractions' => 'fvn-fraction',
        'stacked-fractions' => 'fvn-fraction',
        // Transforms
        'transform' => 'transform',
        'transform-gpu' => 'transform',
        'transform-none' => 'transform',
        // Space reverse
        'space-x-reverse' => 'space-x-reverse',
        'space-y-reverse' => 'space-y-reverse',
        // Divide reverse
        'divide-x-reverse' => 'divide-x-reverse',
        'divide-y-reverse' => 'divide-y-reverse',
        // Ring inset
        'ring-inset' => 'ring-inset',
        // Touch
        'touch-pinch-zoom' => 'touch-pz',
    ];

    /**
     * Conflicting class groups - when a class is set, remove these conflicts.
     *
     * @var array<string, array<string>>
     */
    private static array $conflictingGroups = [
        'overflow' => ['overflow-x', 'overflow-y'],
        'overscroll' => ['overscroll-x', 'overscroll-y'],
        'inset' => ['inset-x', 'inset-y', 'start', 'end', 'top', 'right', 'bottom', 'left'],
        'inset-x' => ['right', 'left'],
        'inset-y' => ['top', 'bottom'],
        'gap' => ['gap-x', 'gap-y'],
        'padding' => ['padding-x', 'padding-y', 'padding-t', 'padding-r', 'padding-b', 'padding-l', 'padding-s', 'padding-e'],
        'margin' => ['margin-x', 'margin-y', 'margin-t', 'margin-r', 'margin-b', 'margin-l', 'margin-s', 'margin-e'],
        'rounded' => ['rounded-t', 'rounded-r', 'rounded-b', 'rounded-l', 'rounded-tl', 'rounded-tr', 'rounded-br', 'rounded-bl', 'rounded-s', 'rounded-e', 'rounded-ss', 'rounded-se', 'rounded-ee', 'rounded-es'],
        'border' => ['border-t', 'border-r', 'border-b', 'border-l', 'border-x', 'border-y', 'border-s', 'border-e'],
        'size' => ['width', 'height'],
        'scroll-m' => ['scroll-mx', 'scroll-my', 'scroll-ms', 'scroll-me', 'scroll-mt', 'scroll-mr', 'scroll-mb', 'scroll-ml'],
        'scroll-p' => ['scroll-px', 'scroll-py', 'scroll-ps', 'scroll-pe', 'scroll-pt', 'scroll-pr', 'scroll-pb', 'scroll-pl'],
    ];

    /**
     * Merge Tailwind CSS classes, resolving conflicts.
     *
     * @param  string|array<string>  ...$args
     */
    public function merge(string|array ...$args): string
    {
        $input = $this->flattenInput($args);

        if (trim($input) === '') {
            return '';
        }

        // Check cache first
        $cacheKey = $this->getCacheKey($input);
        if (isset($this->classGroupCache[$cacheKey])) {
            return $this->classGroupCache[$cacheKey];
        }

        $result = $this->processClasses($input);

        // Store in cache with size limit
        $this->storeInCache($cacheKey, $result);

        return $result;
    }

    /**
     * Flatten input arguments to a single string.
     *
     * @param  array<string|array<string>>  $args
     */
    private function flattenInput(array $args): string
    {
        $parts = [];
        foreach ($args as $arg) {
            if (is_array($arg)) {
                $parts[] = $this->flattenInput($arg);
            } elseif (is_string($arg)) {
                $parts[] = $arg;
            }
        }

        return implode(' ', $parts);
    }

    /**
     * Process classes and resolve conflicts.
     */
    private function processClasses(string $input): string
    {
        $classes = preg_split('/\s+/', trim($input), -1, PREG_SPLIT_NO_EMPTY);
        if ($classes === false || $classes === []) {
            return '';
        }

        $classGroups = [];
        $result = [];

        // Process in reverse order (later classes win)
        for ($i = count($classes) - 1; $i >= 0; $i--) {
            $class = $classes[$i];
            $parsed = $this->parseClass($class);

            if ($parsed === null) {
                // Unknown class, keep it
                $result[] = $class;

                continue;
            }

            $groupKey = $parsed['modifierId'].$parsed['groupId'];

            // Skip if we already have this group
            if (isset($classGroups[$groupKey])) {
                continue;
            }

            // Mark this group as used
            $classGroups[$groupKey] = true;

            // Mark conflicting groups as used
            if (isset(self::$conflictingGroups[$parsed['groupId']])) {
                foreach (self::$conflictingGroups[$parsed['groupId']] as $conflictingGroup) {
                    $classGroups[$parsed['modifierId'].$conflictingGroup] = true;
                }
            }

            $result[] = $class;
        }

        // Reverse back to original order
        return implode(' ', array_reverse($result));
    }

    /**
     * Parse a class and extract its group and modifiers.
     *
     * @return array{modifierId: string, groupId: string}|null
     */
    private function parseClass(string $class): ?array
    {
        // Extract modifiers (responsive, hover, etc.)
        $parts = explode(':', $class);
        $baseClass = array_pop($parts);
        $modifiers = $parts;

        // Handle important modifier
        $hasImportant = false;
        if (str_starts_with($baseClass, '!')) {
            $hasImportant = true;
            $baseClass = substr($baseClass, 1);
        }

        // Get the group ID for the base class
        $groupId = $this->getClassGroup($baseClass);

        if ($groupId === null) {
            return null;
        }

        // Sort modifiers for consistent ordering
        sort($modifiers);
        $modifierId = implode(':', $modifiers);
        if ($hasImportant) {
            $modifierId .= '!';
        }

        return [
            'modifierId' => $modifierId,
            'groupId' => $groupId,
        ];
    }

    /**
     * Get the group ID for a base class.
     */
    private function getClassGroup(string $baseClass): ?string
    {
        // Check exact matches first (faster)
        if (isset(self::$exactClassGroups[$baseClass])) {
            return self::$exactClassGroups[$baseClass];
        }

        // Handle negative values (e.g., -mt-4)
        $checkClass = $baseClass;
        if (str_starts_with($baseClass, '-')) {
            $checkClass = substr($baseClass, 1);
        }

        // Check pattern matches
        foreach (self::$classGroupPatterns as $pattern => $groupId) {
            if (preg_match($pattern, $checkClass)) {
                return $groupId;
            }
        }

        // Handle arbitrary values like [property:value]
        if (preg_match('/^\[.+\]$/', $baseClass)) {
            return 'arbitrary';
        }

        return null;
    }

    /**
     * Generate cache key for input.
     */
    private function getCacheKey(string $input): string
    {
        // Use xxh128 if available (faster), otherwise fall back to md5
        if (in_array('xxh128', hash_algos(), true)) {
            return hash('xxh128', $input);
        }

        return md5($input);
    }

    /**
     * Store result in cache with size limit.
     */
    private function storeInCache(string $key, string $value): void
    {
        if (count($this->classGroupCache) >= $this->cacheSize) {
            // Remove first half of entries using array_keys for better performance
            $keys = array_keys($this->classGroupCache);
            $keysToRemove = array_slice($keys, 0, (int) ($this->cacheSize / 2));
            foreach ($keysToRemove as $keyToRemove) {
                unset($this->classGroupCache[$keyToRemove]);
            }
        }

        $this->classGroupCache[$key] = $value;
    }

    /**
     * Clear the cache.
     */
    public function clearCache(): void
    {
        $this->classGroupCache = [];
    }

    /**
     * Set the cache size.
     */
    public function setCacheSize(int $size): void
    {
        $this->cacheSize = $size;
    }
}
