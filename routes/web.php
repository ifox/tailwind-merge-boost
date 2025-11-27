<?php

use App\Services\BenchmarkComponentConfig;
use App\Services\TailwindMergeBoost;
use App\Services\TailwindMergeOnce;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use TailwindMerge\Contracts\TailwindMergeContract;
use TailwindMerge\Laravel\Facades\TailwindMerge;
use TailwindMerge\Support\Config;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/component-benchmark', function () {
    // For component benchmarks, render each component 100 times to show once() memoization benefit
    $repeatsPerRender = 100;
    $boost = app(TailwindMergeBoost::class);
    
    // Create TailwindMergeOnce instance
    Config::setAdditionalConfig(config('tailwind-merge', []));
    
    // Get component configurations from shared config (now with 10 variants each)
    $componentConfigs = BenchmarkComponentConfig::getConfigs();
    
    $componentResults = [];
    $components = [];
    $cacheStats = [];
    
    // Track TailwindMerge cache store calls using a decorated cache
    $cacheStore = app('cache')->store();
    $twmCacheGets = 0;
    $twmCachePuts = 0;
    
    // Benchmark each component with all its variants
    foreach ($componentConfigs as $name => $variants) {
        // Reset TailwindMerge cache stats for this component
        $componentTwmCacheGets = 0;
        $componentTwmCachePuts = 0;
        
        // TailwindMerge timing - render each variant 100 times
        // Count merge calls by tracking before/after
        $twmMergeCalls = count($variants) * $repeatsPerRender;
        $twmStart = hrtime(true);
        foreach ($variants as $config) {
            for ($r = 0; $r < $repeatsPerRender; $r++) {
                View::make("components.ui.{$name}", array_merge($config, ['merger' => 'twm', 'slot' => 'Content']))->render();
            }
        }
        $twmEnd = hrtime(true);
        $twmTime = ($twmEnd - $twmStart) / 1_000_000;
        
        // TailwindMergeOnce timing (bind fresh instance for each component)
        $once = new TailwindMergeOnce(Config::getMergedConfig(), app('cache')->store());
        app()->singleton(TailwindMergeContract::class, fn () => $once);
        $onceStart = hrtime(true);
        foreach ($variants as $config) {
            for ($r = 0; $r < $repeatsPerRender; $r++) {
                View::make("components.ui.{$name}", array_merge($config, ['merger' => 'once', 'slot' => 'Content']))->render();
            }
        }
        $onceEnd = hrtime(true);
        $onceTime = ($onceEnd - $onceStart) / 1_000_000;
        
        // Capture cache stats for Once (in-memory memoization)
        $onceMergeCalls = $once->getMergeCalls();
        $onceActualCalls = $once->getActualMergeCalls();
        $onceCacheHits = $once->getCacheHits();
        app()->forgetInstance(TailwindMergeContract::class);
        
        // TailwindMergeBoost timing
        $boost->clearCache();
        $boost->resetStats();
        $boostStart = hrtime(true);
        foreach ($variants as $config) {
            for ($r = 0; $r < $repeatsPerRender; $r++) {
                View::make("components.ui.{$name}", array_merge($config, ['merger' => 'boost', 'slot' => 'Content']))->render();
            }
        }
        $boostEnd = hrtime(true);
        $boostTime = ($boostEnd - $boostStart) / 1_000_000;
        
        // Capture cache stats for Boost (in-memory array cache)
        $boostMergeCalls = $boost->getMergeCalls();
        $boostCacheHits = $boost->getCacheHits();
        $boostCacheStores = $boost->getCacheStores();
        
        $componentResults[$name] = [
            'twm' => $twmTime,
            'once' => $onceTime,
            'boost' => $boostTime,
            'onceSpeedup' => $twmTime / max($onceTime, 0.001),
            'boostSpeedup' => $twmTime / max($boostTime, 0.001),
            'variants' => count($variants),
        ];
        
        // TailwindMerge uses external cache store (database/file/redis) - estimate based on unique variants
        // Each unique input results in a cache store call
        $twmUniqueInputs = count($variants); // Number of unique class combinations
        $twmCacheStoreEstimate = $twmUniqueInputs; // Each unique input = 1 cache store
        $twmCacheHitsEstimate = $twmMergeCalls - $twmUniqueInputs; // Remaining calls are cache hits
        
        $cacheStats[$name] = [
            'totalRenders' => count($variants) * $repeatsPerRender,
            // TailwindMerge - uses persistent cache store (database/file/redis)
            'twmMergeCalls' => $twmMergeCalls,
            'twmCacheStores' => $twmCacheStoreEstimate,
            'twmCacheHits' => $twmCacheHitsEstimate,
            // TailwindMergeOnce - uses in-memory once() memoization
            'onceMergeCalls' => $onceMergeCalls,
            'onceActualCalls' => $onceActualCalls,
            'onceCacheHits' => $onceCacheHits,
            // TailwindMergeBoost - uses in-memory array cache
            'boostMergeCalls' => $boostMergeCalls,
            'boostCacheHits' => $boostCacheHits,
            'boostCacheStores' => $boostCacheStores,
        ];
        
        // Render one variant of each component for display
        app()->singleton(TailwindMergeContract::class, fn () => new TailwindMergeOnce(Config::getMergedConfig(), app('cache')->store()));
        $componentVariants = [];
        foreach ($variants as $index => $config) {
            $componentVariants[] = [
                'variant' => $index + 1,
                'class' => $config['class'],
                'twm' => View::make("components.ui.{$name}", array_merge($config, ['merger' => 'twm', 'slot' => ucfirst($name)]))->render(),
                'once' => View::make("components.ui.{$name}", array_merge($config, ['merger' => 'once', 'slot' => ucfirst($name)]))->render(),
                'boost' => View::make("components.ui.{$name}", array_merge($config, ['merger' => 'boost', 'slot' => ucfirst($name)]))->render(),
            ];
        }
        app()->forgetInstance(TailwindMergeContract::class);
        
        $components[$name] = [
            'name' => $name,
            'variants' => $componentVariants,
        ];
    }
    
    // Calculate totals
    $twmTime = array_sum(array_column($componentResults, 'twm'));
    $onceTime = array_sum(array_column($componentResults, 'once'));
    $boostTime = array_sum(array_column($componentResults, 'boost'));
    $totalComponents = count($componentConfigs) * BenchmarkComponentConfig::getVariantsPerComponent() * $repeatsPerRender;
    
    // Calculate total cache stats
    $totalCacheStats = [
        'totalRenders' => array_sum(array_column($cacheStats, 'totalRenders')),
        // TailwindMerge totals (persistent cache)
        'twmMergeCalls' => array_sum(array_column($cacheStats, 'twmMergeCalls')),
        'twmCacheStores' => array_sum(array_column($cacheStats, 'twmCacheStores')),
        'twmCacheHits' => array_sum(array_column($cacheStats, 'twmCacheHits')),
        // TailwindMergeOnce totals (in-memory)
        'onceMergeCalls' => array_sum(array_column($cacheStats, 'onceMergeCalls')),
        'onceActualCalls' => array_sum(array_column($cacheStats, 'onceActualCalls')),
        'onceCacheHits' => array_sum(array_column($cacheStats, 'onceCacheHits')),
        // TailwindMergeBoost totals (in-memory)
        'boostMergeCalls' => array_sum(array_column($cacheStats, 'boostMergeCalls')),
        'boostCacheHits' => array_sum(array_column($cacheStats, 'boostCacheHits')),
        'boostCacheStores' => array_sum(array_column($cacheStats, 'boostCacheStores')),
    ];
    
    return view('component-benchmark', [
        'componentResults' => $componentResults,
        'cacheStats' => $cacheStats,
        'totalCacheStats' => $totalCacheStats,
        'components' => $components,
        'twmTime' => $twmTime,
        'onceTime' => $onceTime,
        'boostTime' => $boostTime,
        'onceSpeedup' => $twmTime / max($onceTime, 0.001),
        'boostSpeedup' => $twmTime / max($boostTime, 0.001),
        'totalComponents' => $totalComponents,
        'repeatsPerRender' => $repeatsPerRender,
        'variantsPerComponent' => BenchmarkComponentConfig::getVariantsPerComponent(),
    ]);
});

Route::get('/benchmark', function () {
    $testCases = [
        'simple' => [
            'p-4 p-6',
            'mt-2 mb-4',
            'text-red-500 text-blue-500',
        ],
        'modifiers' => [
            'hover:bg-red-500 hover:bg-blue-500',
            'md:p-4 md:p-8',
            'dark:text-white dark:text-gray-100',
        ],
        'complex' => [
            'flex flex-col items-center justify-between p-4 p-6',
            'bg-red-500 hover:bg-blue-500 bg-green-500 hover:bg-yellow-500',
            'mt-4 mb-2 px-6 py-4 mt-8 px-8',
        ],
        'long' => [
            'flex flex-col items-center justify-between p-4 bg-white shadow-lg rounded-xl hover:shadow-xl transition-shadow duration-300 mx-auto max-w-md w-full space-y-4 p-8 bg-gray-100',
        ],
    ];

    // Number of times to repeat each case within a single request
    // This shows the benefit of once() memoization
    $repeatsPerCase = 100;
    $iterations = 5;
    $boost = new TailwindMergeBoost();
    
    // Create TailwindMergeOnce instance
    Config::setAdditionalConfig(config('tailwind-merge', []));
    $once = new TailwindMergeOnce(Config::getMergedConfig(), app('cache')->store());
    
    $results = [];
    $cacheStats = [];

    // Warmup
    foreach ($testCases as $cases) {
        foreach ($cases as $case) {
            TailwindMerge::merge($case);
            $once->merge($case);
            $boost->merge($case);
        }
    }
    
    // Reset stats after warmup
    $once->resetStats();
    $boost->clearCache();
    $boost->resetStats();

    // Benchmark each category
    foreach ($testCases as $category => $cases) {
        // TailwindMerge - same case repeated multiple times
        $twmStart = hrtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            foreach ($cases as $case) {
                // Repeat the same case multiple times to simulate multiple renders
                for ($r = 0; $r < $repeatsPerCase; $r++) {
                    TailwindMerge::merge($case);
                }
            }
        }
        $twmEnd = hrtime(true);
        $twmTime = ($twmEnd - $twmStart) / 1_000_000;

        // TailwindMergeOnce (create fresh instance for fair comparison)
        $once = new TailwindMergeOnce(Config::getMergedConfig(), app('cache')->store());
        $onceStart = hrtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            foreach ($cases as $case) {
                // Repeat the same case multiple times - once() will memoize
                for ($r = 0; $r < $repeatsPerCase; $r++) {
                    $once->merge($case);
                }
            }
        }
        $onceEnd = hrtime(true);
        $onceTime = ($onceEnd - $onceStart) / 1_000_000;

        // Store cache stats for Once
        $onceMergeCalls = $once->getMergeCalls();
        $onceActualCalls = $once->getActualMergeCalls();
        $onceCacheHits = $once->getCacheHits();

        // Clear cache for fair comparison
        $boost->clearCache();
        $boost->resetStats();

        // TailwindMergeBoost
        $boostStart = hrtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            foreach ($cases as $case) {
                // Repeat the same case multiple times - internal cache will help
                for ($r = 0; $r < $repeatsPerCase; $r++) {
                    $boost->merge($case);
                }
            }
        }
        $boostEnd = hrtime(true);
        $boostTime = ($boostEnd - $boostStart) / 1_000_000;

        // Store cache stats for Boost
        $boostMergeCalls = $boost->getMergeCalls();
        $boostCacheHits = $boost->getCacheHits();
        $boostCacheStores = $boost->getCacheStores();

        $results[$category] = [
            'twm' => $twmTime,
            'once' => $onceTime,
            'boost' => $boostTime,
            'onceSpeedup' => $twmTime / max($onceTime, 0.001),
            'boostSpeedup' => $twmTime / max($boostTime, 0.001),
        ];

        $cacheStats[$category] = [
            'totalCalls' => count($cases) * $iterations * $repeatsPerCase,
            'onceMergeCalls' => $onceMergeCalls,
            'onceActualCalls' => $onceActualCalls,
            'onceCacheHits' => $onceCacheHits,
            'boostMergeCalls' => $boostMergeCalls,
            'boostCacheHits' => $boostCacheHits,
            'boostCacheStores' => $boostCacheStores,
        ];
    }

    // Calculate totals
    $totalTwm = array_sum(array_column($results, 'twm'));
    $totalOnce = array_sum(array_column($results, 'once'));
    $totalBoost = array_sum(array_column($results, 'boost'));

    // Calculate total cache stats
    $totalCacheStats = [
        'totalCalls' => array_sum(array_column($cacheStats, 'totalCalls')),
        'onceMergeCalls' => array_sum(array_column($cacheStats, 'onceMergeCalls')),
        'onceActualCalls' => array_sum(array_column($cacheStats, 'onceActualCalls')),
        'onceCacheHits' => array_sum(array_column($cacheStats, 'onceCacheHits')),
        'boostMergeCalls' => array_sum(array_column($cacheStats, 'boostMergeCalls')),
        'boostCacheHits' => array_sum(array_column($cacheStats, 'boostCacheHits')),
        'boostCacheStores' => array_sum(array_column($cacheStats, 'boostCacheStores')),
    ];

    // Get example outputs
    $once = new TailwindMergeOnce(Config::getMergedConfig(), app('cache')->store());
    $examples = [];
    foreach ($testCases as $category => $cases) {
        $case = $cases[0];
        $examples[$category] = [
            'input' => $case,
            'twm' => TailwindMerge::merge($case),
            'once' => $once->merge($case),
            'boost' => $boost->merge($case),
        ];
    }

    return view('benchmark', [
        'results' => $results,
        'cacheStats' => $cacheStats,
        'totalCacheStats' => $totalCacheStats,
        'totalTwm' => $totalTwm,
        'totalOnce' => $totalOnce,
        'totalBoost' => $totalBoost,
        'totalOnceSpeedup' => $totalTwm / max($totalOnce, 0.001),
        'totalBoostSpeedup' => $totalTwm / max($totalBoost, 0.001),
        'iterations' => $iterations,
        'repeatsPerCase' => $repeatsPerCase,
        'examples' => $examples,
    ]);
});
