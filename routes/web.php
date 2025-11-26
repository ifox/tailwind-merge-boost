<?php

use App\Services\BenchmarkComponentConfig;
use App\Services\TailwindMergeBoost;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use TailwindMerge\Laravel\Facades\TailwindMerge;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/component-benchmark', function () {
    $iterations = 40; // 40 iterations × 25 components = 1000 component renders
    $boost = app(TailwindMergeBoost::class);
    
    // Get component configurations from shared config
    $componentConfigs = BenchmarkComponentConfig::getConfigs();
    
    $componentResults = [];
    $components = [];
    
    // Warmup
    foreach ($componentConfigs as $name => $config) {
        View::make("components.ui.{$name}", array_merge($config, ['merger' => 'twm', 'slot' => 'Test']))->render();
        View::make("components.ui.{$name}", array_merge($config, ['merger' => 'boost', 'slot' => 'Test']))->render();
    }
    
    // Benchmark each component
    foreach ($componentConfigs as $name => $config) {
        // TailwindMerge timing
        $twmStart = hrtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            View::make("components.ui.{$name}", array_merge($config, ['merger' => 'twm', 'slot' => 'Content']))->render();
        }
        $twmEnd = hrtime(true);
        $twmTime = ($twmEnd - $twmStart) / 1_000_000;
        
        // TailwindMergeBoost timing
        $boost->clearCache();
        $boostStart = hrtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            View::make("components.ui.{$name}", array_merge($config, ['merger' => 'boost', 'slot' => 'Content']))->render();
        }
        $boostEnd = hrtime(true);
        $boostTime = ($boostEnd - $boostStart) / 1_000_000;
        
        $componentResults[$name] = [
            'twm' => $twmTime,
            'boost' => $boostTime,
            'speedup' => $twmTime / max($boostTime, 0.001),
        ];
        
        // Render one of each for display
        $components[] = [
            'name' => $name,
            'twm' => View::make("components.ui.{$name}", array_merge($config, ['merger' => 'twm', 'slot' => ucfirst($name)]))->render(),
            'boost' => View::make("components.ui.{$name}", array_merge($config, ['merger' => 'boost', 'slot' => ucfirst($name)]))->render(),
        ];
    }
    
    // Calculate totals
    $twmTime = array_sum(array_column($componentResults, 'twm'));
    $boostTime = array_sum(array_column($componentResults, 'boost'));
    $totalComponents = count($componentConfigs) * $iterations;
    
    return view('component-benchmark', [
        'componentResults' => $componentResults,
        'components' => $components,
        'twmTime' => $twmTime,
        'boostTime' => $boostTime,
        'speedup' => $twmTime / max($boostTime, 0.001),
        'totalComponents' => $totalComponents,
        'iterations' => $iterations,
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
    $results = [];

    // Warmup
    foreach ($testCases as $cases) {
        foreach ($cases as $case) {
            TailwindMerge::merge($case);
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
            'boost' => $boostTime,
            'speedup' => $twmTime / max($boostTime, 0.001),
        ];
    }

    // Calculate totals
    $totalTwm = array_sum(array_column($results, 'twm'));
    $totalBoost = array_sum(array_column($results, 'boost'));

    // Get example outputs
    $examples = [];
    foreach ($testCases as $category => $cases) {
        $case = $cases[0];
        $examples[$category] = [
            'input' => $case,
            'twm' => TailwindMerge::merge($case),
            'boost' => $boost->merge($case),
        ];
    }

    return view('benchmark', [
        'results' => $results,
        'totalTwm' => $totalTwm,
        'totalBoost' => $totalBoost,
        'totalSpeedup' => $totalTwm / max($totalBoost, 0.001),
        'iterations' => $iterations,
        'examples' => $examples,
    ]);
});
