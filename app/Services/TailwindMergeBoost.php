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
     * Includes comprehensive support for arbitrary values.
     *
     * @var array<string, string>
     */
    private static array $classGroupPatterns = [
        // Spacing - padding (more specific first) - includes arbitrary values
        '/^ps-/' => 'padding-s',
        '/^pe-/' => 'padding-e',
        '/^pt-/' => 'padding-t',
        '/^pr-/' => 'padding-r',
        '/^pb-/' => 'padding-b',
        '/^pl-/' => 'padding-l',
        '/^px-/' => 'padding-x',
        '/^py-/' => 'padding-y',
        '/^p-/' => 'padding',
        // Spacing - margin (more specific first, handles negative values and arbitrary)
        '/^-?ms-/' => 'margin-s',
        '/^-?me-/' => 'margin-e',
        '/^-?mt-/' => 'margin-t',
        '/^-?mr-/' => 'margin-r',
        '/^-?mb-/' => 'margin-b',
        '/^-?ml-/' => 'margin-l',
        '/^-?mx-/' => 'margin-x',
        '/^-?my-/' => 'margin-y',
        '/^-?m-/' => 'margin',
        // Width/Height/Size - includes arbitrary values
        '/^min-w-/' => 'min-width',
        '/^max-w-/' => 'max-width',
        '/^w-/' => 'width',
        '/^min-h-/' => 'min-height',
        '/^max-h-/' => 'max-height',
        '/^h-/' => 'height',
        '/^size-/' => 'size',
        // Flex - includes arbitrary values
        '/^flex-/' => 'flex',
        '/^basis-/' => 'flex-basis',
        '/^grow/' => 'flex-grow',
        '/^shrink/' => 'flex-shrink',
        '/^order-/' => 'order',
        // Grid - includes arbitrary values
        '/^grid-cols-/' => 'grid-cols',
        '/^grid-rows-/' => 'grid-rows',
        '/^col-/' => 'grid-col',
        '/^row-/' => 'grid-row',
        '/^gap-x-/' => 'gap-x',
        '/^gap-y-/' => 'gap-y',
        '/^gap-/' => 'gap',
        // Text size (must be before text color) - includes arbitrary values
        '/^text-(xs|sm|base|lg|xl|2xl|3xl|4xl|5xl|6xl|7xl|8xl|9xl)$/' => 'text-size',
        // Text color (general pattern for colors) - includes arbitrary
        '/^text-/' => 'text-color',
        // Border width (must be before border color) - border, border-0, border-2, border-4, border-8
        '/^border-(0|2|4|8)$/' => 'border-width',
        // Border side widths - border-x, border-y, border-t, border-r, border-b, border-l with width
        '/^border-[xytblr]-(0|2|4|8)$/' => 'border-side-width',
        // Border side colors - border-t-*, border-r-*, border-b-*, border-l-*, border-x-*, border-y-* with color
        '/^border-t-/' => 'border-t-color',
        '/^border-r-/' => 'border-r-color',
        '/^border-b-/' => 'border-b-color',
        '/^border-l-/' => 'border-l-color',
        '/^border-x-/' => 'border-x-color',
        '/^border-y-/' => 'border-y-color',
        // Border color (general) - includes arbitrary
        '/^border-/' => 'border-color',
        // Ring width (must be before ring color) - ring, ring-0, ring-1, ring-2, ring-4, ring-8
        '/^ring-(0|1|2|4|8)$/' => 'ring-width',
        // Ring offset - includes arbitrary
        '/^ring-offset-/' => 'ring-offset',
        // Ring color - includes arbitrary
        '/^ring-/' => 'ring-color',
        // Outline width (must be before outline color)
        '/^outline-(0|1|2|4|8)$/' => 'outline-width',
        // Outline offset - includes arbitrary
        '/^outline-offset-/' => 'outline-offset',
        // Outline color - includes arbitrary
        '/^outline-/' => 'outline-color',
        // Background gradient (must be before bg color)
        '/^bg-gradient-/' => 'bg-gradient',
        // Background position
        '/^bg-(top|bottom|left|right|center)$/' => 'bg-position',
        // Background size
        '/^bg-(auto|cover|contain)$/' => 'bg-size',
        // Background image (none)
        '/^bg-none$/' => 'bg-image',
        // Background color - includes arbitrary
        '/^bg-/' => 'bg-color',
        // Fill - includes arbitrary
        '/^fill-/' => 'fill',
        // Stroke width - includes arbitrary
        '/^stroke-(0|1|2)$/' => 'stroke-width',
        // Stroke color - includes arbitrary
        '/^stroke-/' => 'stroke-color',
        // Shadow - includes arbitrary
        '/^shadow/' => 'shadow',
        '/^accent-/' => 'accent',
        '/^caret-/' => 'caret',
        '/^decoration-/' => 'decoration',
        '/^divide-/' => 'divide',
        '/^placeholder-/' => 'placeholder',
        // Gradient - includes arbitrary
        '/^from-/' => 'gradient-from',
        '/^via-/' => 'gradient-via',
        '/^to-/' => 'gradient-to',
        // Typography - includes arbitrary values
        '/^font-/' => 'font',
        '/^leading-/' => 'leading',
        '/^tracking-/' => 'tracking',
        '/^indent-/' => 'indent',
        '/^align-/' => 'align',
        '/^whitespace-/' => 'whitespace',
        '/^break-/' => 'break',
        '/^hyphens-/' => 'hyphens',
        // Border radius - includes arbitrary
        '/^rounded/' => 'rounded',
        // Transforms - includes arbitrary
        '/^scale-/' => 'scale',
        '/^-?rotate-/' => 'rotate',
        '/^-?translate-x-/' => 'translate-x',
        '/^-?translate-y-/' => 'translate-y',
        '/^-?skew-x-/' => 'skew-x',
        '/^-?skew-y-/' => 'skew-y',
        '/^origin-/' => 'origin',
        // Transitions - includes arbitrary
        '/^transition/' => 'transition',
        '/^duration-/' => 'duration',
        '/^ease-/' => 'ease',
        '/^delay-/' => 'delay',
        '/^animate-/' => 'animate',
        // Filters - includes arbitrary
        '/^blur/' => 'blur',
        '/^brightness-/' => 'brightness',
        '/^contrast-/' => 'contrast',
        '/^grayscale/' => 'grayscale',
        '/^-?hue-rotate-/' => 'hue-rotate',
        '/^invert/' => 'invert',
        '/^saturate-/' => 'saturate',
        '/^sepia/' => 'sepia',
        '/^drop-shadow/' => 'drop-shadow',
        '/^backdrop-/' => 'backdrop',
        // Layout - includes arbitrary
        '/^aspect-/' => 'aspect',
        '/^columns-\d+$/' => 'columns-count',
        '/^columns-auto$/' => 'columns-count',
        '/^columns-\[/' => 'columns-width',
        '/^object-/' => 'object',
        '/^overflow-x-/' => 'overflow-x',
        '/^overflow-y-/' => 'overflow-y',
        '/^overflow-/' => 'overflow',
        '/^overscroll-x-/' => 'overscroll-x',
        '/^overscroll-y-/' => 'overscroll-y',
        '/^overscroll-/' => 'overscroll',
        '/^-?inset-x-/' => 'inset-x',
        '/^-?inset-y-/' => 'inset-y',
        '/^-?inset-/' => 'inset',
        '/^-?top-/' => 'top',
        '/^-?right-/' => 'right',
        '/^-?bottom-/' => 'bottom',
        '/^-?left-/' => 'left',
        '/^-?start-/' => 'start',
        '/^-?end-/' => 'end',
        '/^-?z-/' => 'z-index',
        // Spacing between - includes arbitrary
        '/^-?space-x-/' => 'space-x',
        '/^-?space-y-/' => 'space-y',
        // Scroll - includes arbitrary
        '/^scroll-m[xytblrse]?-/' => 'scroll-m',
        '/^scroll-p[xytblrse]?-/' => 'scroll-p',
        '/^snap-/' => 'snap',
        // Other - includes arbitrary
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
        'border-t' => 'border-side-width',
        'border-r' => 'border-side-width',
        'border-b' => 'border-side-width',
        'border-l' => 'border-side-width',
        'border-x' => 'border-side-width',
        'border-y' => 'border-side-width',
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
        // Background image
        'bg-none' => 'bg-image',
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
        'border-width' => ['border-side-width'],
        'border-color' => ['border-t-color', 'border-r-color', 'border-b-color', 'border-l-color', 'border-x-color', 'border-y-color'],
        'size' => ['width', 'height'],
        'scroll-m' => ['scroll-mx', 'scroll-my', 'scroll-ms', 'scroll-me', 'scroll-mt', 'scroll-mr', 'scroll-mb', 'scroll-ml'],
        'scroll-p' => ['scroll-px', 'scroll-py', 'scroll-ps', 'scroll-pe', 'scroll-pt', 'scroll-pr', 'scroll-pb', 'scroll-pl'],
        'bg-image' => ['bg-color'],
        'bg-color' => ['bg-image'],
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
        // Handle standalone arbitrary properties like [color:red] or hover:[color:red]
        // These are different from utility classes with arbitrary values like ring-[#ff0000]
        // Standalone arbitrary properties start with '[' (after any modifiers) and contain ':'
        $bracketPos = strpos($class, '[');
        $isArbitraryProperty = false;
        
        if ($bracketPos !== false) {
            // Check if this is a standalone arbitrary property (starts with [ after modifiers)
            // vs a utility class with arbitrary value (has a prefix before [)
            $afterBracket = substr($class, $bracketPos);
            // Arbitrary properties have format [property:value]
            // Arbitrary values have format prefix-[value]
            if (preg_match('/^\[[a-zA-Z_-]+:/', $afterBracket)) {
                $isArbitraryProperty = true;
            }
        }
        
        if ($isArbitraryProperty) {
            if ($bracketPos > 0) {
                // There are modifiers before the bracket, e.g., hover:[color:red]
                $beforeBracket = substr($class, 0, $bracketPos);
                $baseClass = substr($class, $bracketPos);
                
                // Remove trailing colon from modifiers if present
                $beforeBracket = rtrim($beforeBracket, ':');
                $modifiers = $beforeBracket !== '' ? explode(':', $beforeBracket) : [];
            } else {
                // No modifiers, just the arbitrary property like [color:red]
                $baseClass = $class;
                $modifiers = [];
            }
        } else {
            // Standard class handling - extract modifiers (responsive, hover, etc.)
            $parts = explode(':', $class);
            $baseClass = array_pop($parts);
            $modifiers = $parts;
        }

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

        // Handle special cases for arbitrary values that need type detection
        if (preg_match('/^([a-z-]+)-\[(.+)\]$/', $checkClass, $matches)) {
            $prefix = $matches[1];
            $arbitraryValue = $matches[2];
            
            // Determine the group based on the prefix and arbitrary value type
            return $this->getArbitraryClassGroup($prefix, $arbitraryValue);
        }

        // Check pattern matches
        foreach (self::$classGroupPatterns as $pattern => $groupId) {
            if (preg_match($pattern, $checkClass)) {
                return $groupId;
            }
        }

        // Handle arbitrary properties like [property:value] or [--custom:value]
        if (preg_match('/^\[([a-zA-Z_-]+):/', $baseClass, $matches)) {
            // Extract the property name and use it as the group
            return 'arbitrary-' . $matches[1];
        }

        // Handle arbitrary CSS like [color:red] or [mask-type:alpha]
        if (preg_match('/^\[.+\]$/', $baseClass)) {
            return 'arbitrary';
        }

        return null;
    }

    /**
     * Get the group ID for an arbitrary value class.
     */
    private function getArbitraryClassGroup(string $prefix, string $arbitraryValue): ?string
    {
        // Determine if the arbitrary value is a color (only hex colors recognized for compatibility)
        $isColor = $this->isArbitraryColor($arbitraryValue);
        // Determine if the arbitrary value is a size/length
        $isSize = $this->isArbitrarySize($arbitraryValue);
        // Determine if the arbitrary value is a URL (for bg-image)
        $isUrl = preg_match('/^url\s*\(/i', $arbitraryValue);
        
        // For utilities that can have either color or size values,
        // only merge if we can definitively identify the type.
        // This matches TailwindMerge v1.1.2 behavior.
        $isKnownType = $isColor || $isSize || $isUrl;

        // Map prefixes to their correct groups based on value type
        // For utilities with ambiguous types, return a unique arbitrary group if type is unknown
        $prefixMappings = [
            'border' => $isSize ? 'border-width' : ($isColor ? 'border-color' : 'border-arbitrary'),
            'border-t' => $isSize ? 'border-side-width' : ($isColor ? 'border-t-color' : 'border-t-arbitrary'),
            'border-r' => $isSize ? 'border-side-width' : ($isColor ? 'border-r-color' : 'border-r-arbitrary'),
            'border-b' => $isSize ? 'border-side-width' : ($isColor ? 'border-b-color' : 'border-b-arbitrary'),
            'border-l' => $isSize ? 'border-side-width' : ($isColor ? 'border-l-color' : 'border-l-arbitrary'),
            'border-x' => $isSize ? 'border-side-width' : ($isColor ? 'border-x-color' : 'border-x-arbitrary'),
            'border-y' => $isSize ? 'border-side-width' : ($isColor ? 'border-y-color' : 'border-y-arbitrary'),
            'ring' => $isSize ? 'ring-width' : ($isColor ? 'ring-color' : 'ring-arbitrary'),
            'outline' => $isSize ? 'outline-width' : ($isColor ? 'outline-color' : 'outline-arbitrary'),
            'stroke' => $isSize ? 'stroke-width' : ($isColor ? 'stroke-color' : 'stroke-arbitrary'),
            'text' => $isSize ? 'text-size' : ($isColor ? 'text-color' : 'text-arbitrary'),
            'bg' => $isUrl ? 'bg-image' : ($isColor ? 'bg-color' : 'bg-arbitrary'),
            'p' => 'padding',
            'pt' => 'padding-t',
            'pr' => 'padding-r',
            'pb' => 'padding-b',
            'pl' => 'padding-l',
            'px' => 'padding-x',
            'py' => 'padding-y',
            'ps' => 'padding-s',
            'pe' => 'padding-e',
            'm' => 'margin',
            'mt' => 'margin-t',
            'mr' => 'margin-r',
            'mb' => 'margin-b',
            'ml' => 'margin-l',
            'mx' => 'margin-x',
            'my' => 'margin-y',
            'ms' => 'margin-s',
            'me' => 'margin-e',
            'w' => 'width',
            'h' => 'height',
            'min-w' => 'min-width',
            'max-w' => 'max-width',
            'min-h' => 'min-height',
            'max-h' => 'max-height',
            'size' => 'size',
            'gap' => 'gap',
            'gap-x' => 'gap-x',
            'gap-y' => 'gap-y',
            'rounded' => 'rounded',
            'z' => 'z-index',
            'top' => 'top',
            'right' => 'right',
            'bottom' => 'bottom',
            'left' => 'left',
            'inset' => 'inset',
            'inset-x' => 'inset-x',
            'inset-y' => 'inset-y',
            'start' => 'start',
            'end' => 'end',
            'space-x' => 'space-x',
            'space-y' => 'space-y',
            'grid-cols' => 'grid-cols',
            'grid-rows' => 'grid-rows',
            'col' => 'grid-col',
            'row' => 'grid-row',
            'translate-x' => 'translate-x',
            'translate-y' => 'translate-y',
            'rotate' => 'rotate',
            'skew-x' => 'skew-x',
            'skew-y' => 'skew-y',
            'scale' => 'scale',
            'from' => 'gradient-from',
            'via' => 'gradient-via',
            'to' => 'gradient-to',
            'opacity' => 'opacity',
            'shadow' => 'shadow',
            'blur' => 'blur',
            'brightness' => 'brightness',
            'contrast' => 'contrast',
            'saturate' => 'saturate',
            'hue-rotate' => 'hue-rotate',
            'duration' => 'duration',
            'delay' => 'delay',
            'ease' => 'ease',
            'animate' => 'animate',
            'font' => 'font',
            'leading' => 'leading',
            'tracking' => 'tracking',
            'indent' => 'indent',
            'line-clamp' => 'line-clamp',
            'aspect' => 'aspect',
            'columns' => $isSize ? 'columns-width' : 'columns-count',
            'order' => 'order',
            'basis' => 'flex-basis',
            'flex' => 'flex',
            'fill' => 'fill',
            'accent' => 'accent',
            'caret' => 'caret',
            'content' => 'content',
            'cursor' => 'cursor',
            'ring-offset' => 'ring-offset',
            'outline-offset' => 'outline-offset',
        ];

        if (isset($prefixMappings[$prefix])) {
            return $prefixMappings[$prefix];
        }

        // Fallback: check pattern matches with the full class
        $fullClass = $prefix . '-[' . $arbitraryValue . ']';
        foreach (self::$classGroupPatterns as $pattern => $groupId) {
            if (preg_match($pattern, $fullClass)) {
                return $groupId;
            }
        }

        return null;
    }

    /**
     * Check if an arbitrary value represents a color.
     * This matches TailwindMerge v1.1.2 behavior which recognizes hex and rgb colors.
     */
    private function isArbitraryColor(string $value): bool
    {
        // Hex colors: #fff, #ffffff, #ffffffff
        if (preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $value)) {
            return true;
        }

        // RGB/RGBA colors - TailwindMerge v1.1.2 recognizes these
        if (preg_match('/^rgba?\s*\(/i', $value)) {
            return true;
        }

        // CSS variables - TailwindMerge treats var() as colors in color contexts
        if (preg_match('/^var\s*\(/i', $value)) {
            return true;
        }

        // Named colors (common ones)
        $colorKeywords = ['transparent', 'currentColor', 'inherit', 'initial', 'unset', 'currentcolor'];
        if (in_array($value, $colorKeywords, true)) {
            return true;
        }

        // Note: TailwindMerge v1.1.2 doesn't recognize hsl() in arbitrary values
        // So we don't match them here to maintain compatibility

        return false;
    }

    /**
     * Check if an arbitrary value represents a size/length.
     */
    private function isArbitrarySize(string $value): bool
    {
        // CSS length units: px, em, rem, %, vw, vh, ch, etc.
        if (preg_match('/^-?[\d.]+\s*(px|em|rem|%|vw|vh|vmin|vmax|ch|ex|cm|mm|in|pt|pc|svh|svw|dvh|dvw|lvh|lvw)$/', $value)) {
            return true;
        }

        // Plain numbers (like border-0, border-2)
        if (preg_match('/^\d+$/', $value)) {
            return true;
        }

        // calc(), min(), max(), clamp() functions typically for sizing
        if (preg_match('/^(calc|min|max|clamp)\s*\(/', $value)) {
            return true;
        }

        return false;
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
