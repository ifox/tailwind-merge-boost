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
    $iterations = 4; // 4 iterations × 25 components × 10 variants = 1000 component renders
    $boost = app(TailwindMergeBoost::class);
    
    // Create TailwindMergeOnce instance
    Config::setAdditionalConfig(config('tailwind-merge', []));
    $once = new TailwindMergeOnce(Config::getMergedConfig(), app('cache')->store());
    
    // Get component configurations from shared config (now with 10 variants each)
    $componentConfigs = BenchmarkComponentConfig::getConfigs();
    
    $componentResults = [];
    $components = [];
    
    // Benchmark each component with all its variants
    foreach ($componentConfigs as $name => $variants) {
        // TailwindMerge timing - iterate through all variants
        $twmStart = hrtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            foreach ($variants as $config) {
                View::make("components.ui.{$name}", array_merge($config, ['merger' => 'twm', 'slot' => 'Content']))->render();
            }
        }
        $twmEnd = hrtime(true);
        $twmTime = ($twmEnd - $twmStart) / 1_000_000;
        
        // TailwindMergeOnce timing (bind fresh instance for each component)
        app()->singleton(TailwindMergeContract::class, fn () => new TailwindMergeOnce(Config::getMergedConfig(), app('cache')->store()));
        $onceStart = hrtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            foreach ($variants as $config) {
                View::make("components.ui.{$name}", array_merge($config, ['merger' => 'once', 'slot' => 'Content']))->render();
            }
        }
        $onceEnd = hrtime(true);
        $onceTime = ($onceEnd - $onceStart) / 1_000_000;
        app()->forgetInstance(TailwindMergeContract::class);
        
        // TailwindMergeBoost timing
        $boost->clearCache();
        $boostStart = hrtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            foreach ($variants as $config) {
                View::make("components.ui.{$name}", array_merge($config, ['merger' => 'boost', 'slot' => 'Content']))->render();
            }
        }
        $boostEnd = hrtime(true);
        $boostTime = ($boostEnd - $boostStart) / 1_000_000;
        
        $componentResults[$name] = [
            'twm' => $twmTime,
            'once' => $onceTime,
            'boost' => $boostTime,
            'onceSpeedup' => $twmTime / max($onceTime, 0.001),
            'boostSpeedup' => $twmTime / max($boostTime, 0.001),
            'variants' => count($variants),
        ];
        
        // Render all 10 variants of each component for display
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
    $totalComponents = count($componentConfigs) * BenchmarkComponentConfig::getVariantsPerComponent() * $iterations;
    
    return view('component-benchmark', [
        'componentResults' => $componentResults,
        'components' => $components,
        'twmTime' => $twmTime,
        'onceTime' => $onceTime,
        'boostTime' => $boostTime,
        'onceSpeedup' => $twmTime / max($onceTime, 0.001),
        'boostSpeedup' => $twmTime / max($boostTime, 0.001),
        'totalComponents' => $totalComponents,
        'iterations' => $iterations,
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

    $iterations = 500;
    $boost = new TailwindMergeBoost();
    
    // Create TailwindMergeOnce instance
    Config::setAdditionalConfig(config('tailwind-merge', []));
    $once = new TailwindMergeOnce(Config::getMergedConfig(), app('cache')->store());
    
    $results = [];

    // Warmup
    foreach ($testCases as $cases) {
        foreach ($cases as $case) {
            TailwindMerge::merge($case);
            $once->merge($case);
            $boost->merge($case);
        }
    }

    // Benchmark each category
    foreach ($testCases as $category => $cases) {
        // TailwindMerge
        $twmStart = hrtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            foreach ($cases as $case) {
                TailwindMerge::merge($case);
            }
        }
        $twmEnd = hrtime(true);
        $twmTime = ($twmEnd - $twmStart) / 1_000_000;

        // TailwindMergeOnce (create fresh instance for fair comparison)
        $once = new TailwindMergeOnce(Config::getMergedConfig(), app('cache')->store());
        $onceStart = hrtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            foreach ($cases as $case) {
                $once->merge($case);
            }
        }
        $onceEnd = hrtime(true);
        $onceTime = ($onceEnd - $onceStart) / 1_000_000;

        // Clear cache for fair comparison
        $boost->clearCache();

        // TailwindMergeBoost
        $boostStart = hrtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            foreach ($cases as $case) {
                $boost->merge($case);
            }
        }
        $boostEnd = hrtime(true);
        $boostTime = ($boostEnd - $boostStart) / 1_000_000;

        $results[$category] = [
            'twm' => $twmTime,
            'once' => $onceTime,
            'boost' => $boostTime,
            'onceSpeedup' => $twmTime / max($onceTime, 0.001),
            'boostSpeedup' => $twmTime / max($boostTime, 0.001),
        ];
    }

    // Calculate totals
    $totalTwm = array_sum(array_column($results, 'twm'));
    $totalOnce = array_sum(array_column($results, 'once'));
    $totalBoost = array_sum(array_column($results, 'boost'));

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
        'totalTwm' => $totalTwm,
        'totalOnce' => $totalOnce,
        'totalBoost' => $totalBoost,
        'totalOnceSpeedup' => $totalTwm / max($totalOnce, 0.001),
        'totalBoostSpeedup' => $totalTwm / max($totalBoost, 0.001),
        'iterations' => $iterations,
        'examples' => $examples,
    ]);
});
