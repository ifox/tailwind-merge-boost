<?php

declare(strict_types=1);

namespace App\Services;

/**
 * TailwindMergeBoost - An efficient Tailwind CSS class merger.
 *
 * Optimized for performance using lookup tables, regex caching,
 * and minimized object creation.
 */
class TailwindMergeBoost
{
    /**
     * Cache for parsed class groups.
     * @var array<string, string>
     */
    private array $classGroupCache = [];

    /**
     * Maximum cache size.
     */
    private int $cacheSize = 500;

    /**
     * Stats
     */
    private int $mergeCalls = 0;
    private int $cacheHits = 0;
    private int $cacheStores = 0;

    /**
     * Pre-compiled regex patterns.
     */
    // Matches length units strictly
    private const SIZE_PATTERN = '/^-?(\d+(\.\d+)?)(px|em|rem|%|vw|vh|vmin|vmax|ch|ex|cm|mm|in|pt|pc|svh|svw|dvh|dvw|lvh|lvw|cqw|cqh|cqi|cqb|cqmin|cqmax)$/';
    // Matches hex, arbitrary hex, and short hex
    private const HEX_COLOR_PATTERN = '/^#(?:[0-9a-fA-F]{3,4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/';
    // Matches rgb/rgba/hsl/hsla/hwb
    private const COLOR_FUNCTION_PATTERN = '/^(rgba?|hsla?|hwb|lab|lch|color)\s*\(/i';
    private const URL_PATTERN = '/^url\s*\(/i';
    private const CALC_PATTERN = '/^(calc|min|max|clamp)\s*\(/';
    // Matches pure numbers (for unitless values like z-index or border width)
    private const NUMBER_PATTERN = '/^-?\d+(\.\d+)?$/';
    // Matches arbitrary properties
    private const ARBITRARY_PROPERTY_PATTERN = '/^\[[a-zA-Z0-9_-]+:/';

    /**
     * Class group patterns - ordered by specificity.
     * @var array<string, string>
     */
    private static array $classGroupPatterns = [
        // Spacing - padding
        '/^ps-/' => 'padding-s',
        '/^pe-/' => 'padding-e',
        '/^pt-/' => 'padding-t',
        '/^pr-/' => 'padding-r',
        '/^pb-/' => 'padding-b',
        '/^pl-/' => 'padding-l',
        '/^px-/' => 'padding-x',
        '/^py-/' => 'padding-y',
        '/^p-/' => 'padding',
        // Spacing - margin
        '/^-?ms-/' => 'margin-s',
        '/^-?me-/' => 'margin-e',
        '/^-?mt-/' => 'margin-t',
        '/^-?mr-/' => 'margin-r',
        '/^-?mb-/' => 'margin-b',
        '/^-?ml-/' => 'margin-l',
        '/^-?mx-/' => 'margin-x',
        '/^-?my-/' => 'margin-y',
        '/^-?m-/' => 'margin',
        // Sizing
        '/^min-w-/' => 'min-width',
        '/^max-w-/' => 'max-width',
        '/^w-/' => 'width',
        '/^min-h-/' => 'min-height',
        '/^max-h-/' => 'max-height',
        '/^h-/' => 'height',
        '/^size-/' => 'size',
        // Flex/Grid
        '/^flex-/' => 'flex',
        '/^basis-/' => 'flex-basis',
        '/^grow/' => 'flex-grow',
        '/^shrink/' => 'flex-shrink',
        '/^order-/' => 'order',
        '/^grid-cols-/' => 'grid-cols',
        '/^grid-rows-/' => 'grid-rows',
        '/^col-/' => 'grid-col',
        '/^row-/' => 'grid-row',
        '/^gap-x-/' => 'gap-x',
        '/^gap-y-/' => 'gap-y',
        '/^gap-/' => 'gap',
        // Typography
        // Matches text-lg, text-lg/7, text-lg/loose
        '/^text-(xs|sm|base|lg|xl|2xl|3xl|4xl|5xl|6xl|7xl|8xl|9xl)(?:\/.+)?$/' => 'text-size',
        '/^text-/' => 'text-color',
        '/^font-/' => 'font',
        '/^leading-/' => 'leading',
        '/^tracking-/' => 'tracking',
        '/^indent-/' => 'indent',
        '/^align-/' => 'align',
        '/^whitespace-/' => 'whitespace',
        '/^break-/' => 'break',
        '/^hyphens-/' => 'hyphens',
        '/^content-/' => 'content',
        // Borders
        '/^border-(0|2|4|8)$/' => 'border-width',
        '/^border-[xytblr]-(0|2|4|8)$/' => 'border-side-width',
        '/^border-t-/' => 'border-t-color',
        '/^border-r-/' => 'border-r-color',
        '/^border-b-/' => 'border-b-color',
        '/^border-l-/' => 'border-l-color',
        '/^border-x-/' => 'border-x-color',
        '/^border-y-/' => 'border-y-color',
        '/^border-/' => 'border-color',
        '/^divide-x-reverse$/' => 'divide-x-reverse',
        '/^divide-y-reverse$/' => 'divide-y-reverse',
        '/^divide-x-/' => 'divide-x',
        '/^divide-y-/' => 'divide-y',
        '/^divide-/' => 'divide-color',
        // Outline & Ring
        '/^outline-(0|1|2|4|8)$/' => 'outline-width',
        '/^outline-offset-/' => 'outline-offset',
        '/^outline-/' => 'outline-color',
        '/^ring-(0|1|2|4|8)$/' => 'ring-width',
        '/^ring-offset-/' => 'ring-offset',
        '/^ring-/' => 'ring-color',
        // Background
        '/^bg-gradient-/' => 'bg-gradient',
        '/^bg-(top|bottom|left|right|center)$/' => 'bg-position',
        '/^bg-(auto|cover|contain)$/' => 'bg-size',
        '/^bg-/' => 'bg-color',
        // Effects
        '/^shadow/' => 'shadow',
        '/^opacity-/' => 'opacity',
        '/^mix-blend-/' => 'mix-blend',
        '/^bg-blend-/' => 'bg-blend',
        // Filters
        '/^blur/' => 'blur',
        '/^brightness-/' => 'brightness',
        '/^contrast-/' => 'contrast',
        '/^grayscale/' => 'grayscale',
        '/^-?hue-rotate-/' => 'hue-rotate',
        '/^invert/' => 'invert',
        '/^saturate-/' => 'saturate',
        '/^sepia/' => 'sepia',
        '/^drop-shadow/' => 'drop-shadow',
        '/^backdrop-blur/' => 'backdrop-blur',
        '/^backdrop-brightness/' => 'backdrop-brightness',
        '/^backdrop-contrast/' => 'backdrop-contrast',
        '/^backdrop-grayscale/' => 'backdrop-grayscale',
        '/^backdrop-hue-rotate/' => 'backdrop-hue-rotate',
        '/^backdrop-invert/' => 'backdrop-invert',
        '/^backdrop-opacity/' => 'backdrop-opacity',
        '/^backdrop-saturate/' => 'backdrop-saturate',
        '/^backdrop-sepia/' => 'backdrop-sepia',
        // Transforms
        '/^scale-/' => 'scale',
        '/^-?rotate-/' => 'rotate',
        '/^-?translate-x-/' => 'translate-x',
        '/^-?translate-y-/' => 'translate-y',
        '/^-?skew-x-/' => 'skew-x',
        '/^-?skew-y-/' => 'skew-y',
        '/^origin-/' => 'origin',
        // Interactivity
        '/^cursor-/' => 'cursor',
        '/^select-/' => 'select',
        '/^resize/' => 'resize',
        '/^list-/' => 'list',
        '/^appearance-/' => 'appearance',
        '/^pointer-events-/' => 'pointer-events',
        '/^will-change-/' => 'will-change',
        '/^accent-/' => 'accent',
        '/^caret-/' => 'caret',
        // Layout
        '/^aspect-/' => 'aspect',
        '/^columns-/' => 'columns', // handling resolved in getArbitrary logic mostly
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
        // Spacing between
        '/^-?space-x-/' => 'space-x',
        '/^-?space-y-/' => 'space-y',
        // SVG
        '/^fill-/' => 'fill',
        '/^stroke-(0|1|2)$/' => 'stroke-width',
        '/^stroke-/' => 'stroke-color',
        // Table
        '/^table-/' => 'table',
        '/^caption-/' => 'caption',
        // Line clamp
        '/^line-clamp-/' => 'line-clamp',
        // Scroll
        '/^scroll-m[xytblrse]?-/' => 'scroll-m',
        '/^scroll-p[xytblrse]?-/' => 'scroll-p',
        '/^snap-align-/' => 'snap-align',
        '/^snap-stop-/' => 'snap-stop',
        '/^snap-type-/' => 'snap-type',
        '/^snap-/' => 'snap-strictness', // snap-mandatory, snap-proximity
        // Touch
        '/^touch-/' => 'touch', 
        // Gradient
        '/^from-/' => 'gradient-from',
        '/^via-/' => 'gradient-via',
        '/^to-/' => 'gradient-to',
        '/^decoration-(0|1|2|4|8|auto|from-font)$/' => 'text-decoration-thickness',
        '/^decoration-/' => 'text-decoration-color',
        // Transition
        '/^transition/' => 'transition',
        '/^duration-/' => 'duration',
        '/^ease-/' => 'ease',
        '/^delay-/' => 'delay',
        '/^animate-/' => 'animate',
    ];

    /**
     * Exact class mappings.
     * @var array<string, string>
     */
    private static array $exactClassGroups = [
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
        'static' => 'position',
        'fixed' => 'position',
        'absolute' => 'position',
        'relative' => 'position',
        'sticky' => 'position',
        'visible' => 'visibility',
        'invisible' => 'visibility',
        'collapse' => 'visibility',
        'float-right' => 'float',
        'float-left' => 'float',
        'float-none' => 'float',
        'float-start' => 'float',
        'float-end' => 'float',
        'clear-left' => 'clear',
        'clear-right' => 'clear',
        'clear-both' => 'clear',
        'clear-none' => 'clear',
        'clear-start' => 'clear',
        'clear-end' => 'clear',
        'isolate' => 'isolation',
        'isolation-auto' => 'isolation',
        'box-border' => 'box-sizing',
        'box-content' => 'box-sizing',
        'box-decoration-slice' => 'box-decoration',
        'box-decoration-clone' => 'box-decoration',
        'container' => 'container',
        'border' => 'border-width',
        'border-t' => 'border-side-width',
        'border-r' => 'border-side-width',
        'border-b' => 'border-side-width',
        'border-l' => 'border-side-width',
        'border-x' => 'border-side-width',
        'border-y' => 'border-side-width',
        'ring' => 'ring-width',
        'ring-inset' => 'ring-inset',
        'outline' => 'outline-width',
        'outline-none' => 'outline-width',
        'italic' => 'font-style',
        'not-italic' => 'font-style',
        'antialiased' => 'font-smoothing',
        'subpixel-antialiased' => 'font-smoothing',
        'underline' => 'text-decoration',
        'overline' => 'text-decoration',
        'line-through' => 'text-decoration',
        'no-underline' => 'text-decoration',
        'uppercase' => 'text-transform',
        'lowercase' => 'text-transform',
        'capitalize' => 'text-transform',
        'normal-case' => 'text-transform',
        'truncate' => 'text-overflow',
        'text-ellipsis' => 'text-overflow',
        'text-clip' => 'text-overflow',
        'sr-only' => 'sr',
        'not-sr-only' => 'sr',
        // Font Variant Numeric
        'normal-nums' => 'fvn-normal',
        'ordinal' => 'fvn-ordinal',
        'slashed-zero' => 'fvn-slashed-zero',
        'lining-nums' => 'fvn-figure',
        'oldstyle-nums' => 'fvn-figure',
        'proportional-nums' => 'fvn-spacing',
        'tabular-nums' => 'fvn-spacing',
        'diagonal-fractions' => 'fvn-fraction',
        'stacked-fractions' => 'fvn-fraction',
        'transform' => 'transform',
        'transform-gpu' => 'transform',
        'transform-none' => 'transform',
        'space-x-reverse' => 'space-x-reverse',
        'space-y-reverse' => 'space-y-reverse',
        // Touch
        'touch-auto' => 'touch',
        'touch-none' => 'touch',
        'touch-pan-x' => 'touch-x',
        'touch-pan-left' => 'touch-x',
        'touch-pan-right' => 'touch-x',
        'touch-pan-y' => 'touch-y',
        'touch-pan-up' => 'touch-y',
        'touch-pan-down' => 'touch-y',
        'touch-pinch-zoom' => 'touch-pz',
        'touch-manipulation' => 'touch',
        // Background
        'bg-none' => 'bg-image',
        'bg-repeat' => 'bg-repeat',
        'bg-no-repeat' => 'bg-repeat',
        'bg-repeat-x' => 'bg-repeat',
        'bg-repeat-y' => 'bg-repeat',
        'bg-repeat-round' => 'bg-repeat',
        'bg-repeat-space' => 'bg-repeat',
        // Line clamp
        'line-clamp-none' => 'line-clamp',
    ];

    /**
     * Conflicting class groups.
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
        'bg-image' => ['bg-color'], // In strict Tailwind, these might co-exist, but merge libraries often conflict them
        'bg-color' => ['bg-image'],
        // Font Variant Numeric: normal-nums resets everything
        'fvn-normal' => ['fvn-ordinal', 'fvn-slashed-zero', 'fvn-figure', 'fvn-spacing', 'fvn-fraction'],
        'fvn-ordinal' => ['fvn-normal'],
        'fvn-slashed-zero' => ['fvn-normal'],
        'fvn-figure' => ['fvn-normal'],
        'fvn-spacing' => ['fvn-normal'],
        'fvn-fraction' => ['fvn-normal'],
        // Touch
        'touch' => ['touch-x', 'touch-y', 'touch-pz'],
        'touch-x' => ['touch'],
        'touch-y' => ['touch'],
        'touch-pz' => ['touch'],
    ];

    public function merge(string|array ...$args): string
    {
        $this->mergeCalls++;
        $input = $this->flattenInput($args);

        if (trim($input) === '') {
            return '';
        }

        $cacheKey = $this->getCacheKey($input);
        if (isset($this->classGroupCache[$cacheKey])) {
            $this->cacheHits++;
            return $this->classGroupCache[$cacheKey];
        }

        $result = $this->processClasses($input);
        
        $this->storeInCache($cacheKey, $result);
        $this->cacheStores++;

        return $result;
    }

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

    private function processClasses(string $input): string
    {
        $classes = preg_split('/\s+/', trim($input), -1, PREG_SPLIT_NO_EMPTY);
        if ($classes === false || $classes === []) {
            return '';
        }

        $classGroups = [];
        $result = [];

        for ($i = count($classes) - 1; $i >= 0; $i--) {
            $class = $classes[$i];
            $parsed = $this->parseClass($class);

            if ($parsed === null) {
                $result[] = $class;
                continue;
            }

            $groupId = $parsed['groupId'];
            $modifierId = $parsed['modifierId'];
            $groupKey = $modifierId . $groupId;

            if (isset($classGroups[$groupKey])) {
                continue;
            }

            $classGroups[$groupKey] = true;

            // Handle conflicting groups
            if (isset(self::$conflictingGroups[$groupId])) {
                foreach (self::$conflictingGroups[$groupId] as $conflictingGroup) {
                    $classGroups[$modifierId . $conflictingGroup] = true;
                }
            }

            // Special handling for text-size with line-height modifier (e.g. text-lg/7)
            // This must conflict with leading-* classes
            if ($groupId === 'text-size' && str_contains($class, '/')) {
                $classGroups[$modifierId . 'leading'] = true;
            }

            $result[] = $class;
        }

        return implode(' ', array_reverse($result));
    }

    /**
     * Parses a class to extract modifiers and the base utility.
     * Handles arbitrary variants correctly (e.g., group-[:nth-of-type(3)_&]:block).
     */
    private function parseClass(string $class): ?array
    {
        $modifiers = [];
        $baseClass = $class;
        
        // Split by ':' but respect brackets []
        $start = 0;
        $depth = 0;
        $length = strlen($class);
        
        for ($i = 0; $i < $length; $i++) {
            $char = $class[$i];
            
            if ($char === '[') {
                $depth++;
            } elseif ($char === ']') {
                $depth--;
            } elseif ($char === ':' && $depth === 0) {
                // Found a separator
                $modifiers[] = substr($class, $start, $i - $start);
                $start = $i + 1;
            }
        }
        
        if ($start > 0 && $start < $length) {
            $baseClass = substr($class, $start);
        } elseif ($start >= $length) {
            // Edge case where class ends with colon? Invalid in Tailwind usually,
            // but for this logic, if we consumed everything, base is empty.
            return null;
        } else {
            // No modifiers found
            $baseClass = $class;
        }

        // Handle important modifier '!' at start of base class
        $hasImportant = false;
        if (str_starts_with($baseClass, '!')) {
            $hasImportant = true;
            $baseClass = substr($baseClass, 1);
        }

        $groupId = $this->getClassGroup($baseClass);
        if ($groupId === null) {
            return null;
        }

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

    private function getClassGroup(string $baseClass): ?string
    {
        if (isset(self::$exactClassGroups[$baseClass])) {
            return self::$exactClassGroups[$baseClass];
        }

        $checkClass = $baseClass;
        if (str_starts_with($baseClass, '-')) {
            $checkClass = substr($baseClass, 1);
        }

        // Handle Arbitrary Values: prefix-[value]
        if (preg_match('/^([a-z-]+)-\[(.+)\]$/', $checkClass, $matches)) {
            return $this->getArbitraryClassGroup($matches[1], $matches[2]);
        }

        foreach (self::$classGroupPatterns as $pattern => $groupId) {
            if (preg_match($pattern, $checkClass)) {
                return $groupId;
            }
        }

        // Arbitrary Property: [property:value]
        if (preg_match(self::ARBITRARY_PROPERTY_PATTERN, $baseClass, $matches)) {
            // Extract property name
            $prop = substr($matches[0], 1, -1);
            return 'arbitrary-' . $prop;
        }
        
        // Generic arbitrary
        if (preg_match('/^\[.+\]$/', $baseClass)) {
            return 'arbitrary';
        }

        return null;
    }

    private function getArbitraryClassGroup(string $prefix, string $arbitraryValue): ?string
    {
        // Handle explicit type hints (e.g. length:10px, color:red)
        if (str_starts_with($arbitraryValue, 'length:')) {
            $isSize = true;
            $isColor = false;
            $isUrl = false;
        } elseif (str_starts_with($arbitraryValue, 'color:')) {
            $isSize = false;
            $isColor = true;
            $isUrl = false;
        } elseif (str_starts_with($arbitraryValue, 'url:')) {
            $isUrl = true;
            $isSize = false;
            $isColor = false;
        } else {
            $isSize = $this->isArbitrarySize($arbitraryValue);
            $isColor = $this->isArbitraryColor($arbitraryValue);
            $isUrl = (bool) preg_match(self::URL_PATTERN, $arbitraryValue);
        }

        $prefixMappings = [
            // Border
            'border' => $isSize ? 'border-width' : ($isColor ? 'border-color' : 'border-arbitrary'),
            'border-t' => $isSize ? 'border-side-width' : ($isColor ? 'border-t-color' : 'border-t-arbitrary'),
            'border-r' => $isSize ? 'border-side-width' : ($isColor ? 'border-r-color' : 'border-r-arbitrary'),
            'border-b' => $isSize ? 'border-side-width' : ($isColor ? 'border-b-color' : 'border-b-arbitrary'),
            'border-l' => $isSize ? 'border-side-width' : ($isColor ? 'border-l-color' : 'border-l-arbitrary'),
            'border-x' => $isSize ? 'border-side-width' : ($isColor ? 'border-x-color' : 'border-x-arbitrary'),
            'border-y' => $isSize ? 'border-side-width' : ($isColor ? 'border-y-color' : 'border-y-arbitrary'),
            // Spacing & Sizing (always size for ambiguous)
            'p' => 'padding', 'pt' => 'padding-t', 'pr' => 'padding-r', 'pb' => 'padding-b', 'pl' => 'padding-l', 'px' => 'padding-x', 'py' => 'padding-y', 'ps' => 'padding-s', 'pe' => 'padding-e',
            'm' => 'margin', 'mt' => 'margin-t', 'mr' => 'margin-r', 'mb' => 'margin-b', 'ml' => 'margin-l', 'mx' => 'margin-x', 'my' => 'margin-y', 'ms' => 'margin-s', 'me' => 'margin-e',
            'w' => 'width', 'h' => 'height', 'min-w' => 'min-width', 'max-w' => 'max-width', 'min-h' => 'min-height', 'max-h' => 'max-height',
            'size' => 'size',
            'top' => 'top', 'right' => 'right', 'bottom' => 'bottom', 'left' => 'left',
            'inset' => 'inset', 'inset-x' => 'inset-x', 'inset-y' => 'inset-y', 'start' => 'start', 'end' => 'end',
            'gap' => 'gap', 'gap-x' => 'gap-x', 'gap-y' => 'gap-y',
            'space-x' => 'space-x', 'space-y' => 'space-y',
            'basis' => 'flex-basis',
            // Colors / Visuals
            'text' => $isSize ? 'text-size' : ($isColor ? 'text-color' : 'text-arbitrary'),
            'bg' => $isUrl ? 'bg-image' : ($isColor ? 'bg-color' : ($isSize ? 'bg-size' : 'bg-arbitrary')),
            'ring' => $isSize ? 'ring-width' : ($isColor ? 'ring-color' : 'ring-arbitrary'),
            'ring-offset' => $isSize ? 'ring-offset' : ($isColor ? 'ring-offset-color' : 'ring-offset-arbitrary'),
            'outline' => $isSize ? 'outline-width' : ($isColor ? 'outline-color' : 'outline-arbitrary'),
            'decoration' => $isSize ? 'text-decoration-thickness' : ($isColor ? 'text-decoration-color' : 'decoration-arbitrary'),
            'shadow' => 'shadow',
            'accent' => 'accent',
            'caret' => 'caret',
            'fill' => 'fill',
            'stroke' => $isSize ? 'stroke-width' : ($isColor ? 'stroke-color' : 'stroke-arbitrary'),
            'content' => 'content',
            // Others
            'opacity' => 'opacity',
            'z' => 'z-index',
            'order' => 'order',
            'flex' => 'flex',
            'grid-cols' => 'grid-cols',
            'grid-rows' => 'grid-rows',
            'columns' => $isSize ? 'columns-width' : 'columns-count',
            'line-clamp' => 'line-clamp',
        ];

        if (isset($prefixMappings[$prefix])) {
            return $prefixMappings[$prefix];
        }

        return null;
    }

    private function isArbitraryColor(string $value): bool
    {
        // 1. Explicit hex
        if (preg_match(self::HEX_COLOR_PATTERN, $value)) {
            return true;
        }
        // 2. CSS color functions (rgb, hsl, etc.)
        if (preg_match(self::COLOR_FUNCTION_PATTERN, $value)) {
            return true;
        }
        // 3. Common color keywords
        $keywords = ['transparent', 'currentColor', 'inherit', 'initial', 'unset', 'currentcolor', 'black', 'white'];
        if (in_array($value, $keywords, true)) {
            return true;
        }
        // 4. Variables are generally assumed to be colors in color-accepting contexts
        // unless they are explicitly lengths (which would be caught by hints)
        // Note: TailwindMerge checks if it *starts* with var(--.
        if (str_starts_with($value, 'var(--')) {
            return true;
        }

        return false;
    }

private function isArbitrarySize(string $value): bool
    {
        // 1. Explicit length units
        if (preg_match(self::SIZE_PATTERN, $value)) {
            return true;
        }
        // 2. Calculation functions
        if (preg_match(self::CALC_PATTERN, $value)) {
            return true;
        }
        // 3. Fractions (e.g. 1/2) for sizing
        if (str_contains($value, '/') && preg_match('/^[0-9.]+\/[0-9.]+$/', $value)) {
            return true;
        }
        // 4. Plain numbers (only for certain properties, but generic size check often allows them)
        if (preg_match(self::NUMBER_PATTERN, $value)) {
            return true;
        }

        return false;
    }

    private function getCacheKey(string $input): string
    {
        // xxh128 is significantly faster than md5
        if (version_compare(PHP_VERSION, '8.1.0', '>=') && in_array('xxh128', hash_algos(), true)) {
            return hash('xxh128', $input);
        }
        return md5($input);
    }

    private function storeInCache(string $key, string $value): void
    {
        if (count($this->classGroupCache) >= $this->cacheSize) {
            $this->classGroupCache = array_slice($this->classGroupCache, (int)($this->cacheSize / 2), null, true);
        }
        $this->classGroupCache[$key] = $value;
    }

    public function clearCache(): void
    {
        $this->classGroupCache = [];
    }

    public function resetStats(): void
    {
        $this->mergeCalls = 0;
        $this->cacheHits = 0;
        $this->cacheStores = 0;
    }

    public function getMergeCalls(): int { return $this->mergeCalls; }
    public function getCacheHits(): int { return $this->cacheHits; }
    public function getCacheStores(): int { return $this->cacheStores; }
    public function setCacheSize(int $size): void { $this->cacheSize = $size; }
}
