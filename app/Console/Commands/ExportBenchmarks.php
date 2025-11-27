<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\BenchmarkComponentConfig;
use App\Services\TailwindMergeBoost;
use App\Services\TailwindMergeOnce;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use TailwindMerge\Contracts\TailwindMergeContract;
use TailwindMerge\Laravel\Facades\TailwindMerge;
use TailwindMerge\Support\Config;

class ExportBenchmarks extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'benchmark:export {--output=public/benchmarks : Output directory for static files}';

    /**
     * The console command description.
     */
    protected $description = 'Export benchmark results as static HTML files for GitHub Pages';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $outputDir = $this->option('output');
        
        $this->info('Exporting benchmark results to ' . $outputDir);
        
        // Create output directory
        if (!File::exists($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
        }
        
        // Export simple benchmark page
        $this->exportSimpleBenchmark($outputDir);
        
        // Export component benchmark page
        $this->exportComponentBenchmark($outputDir);
        
        // Create index page
        $this->createIndexPage($outputDir);
        
        $this->info('Benchmark export completed!');
        $this->info('Files saved to: ' . realpath($outputDir));
        
        return Command::SUCCESS;
    }

    /**
     * Create a fresh TailwindMergeOnce instance and bind it to the container.
     */
    private function bindTailwindMergeOnce(): void
    {
        Config::setAdditionalConfig(config('tailwind-merge', []));
        
        $this->laravel->singleton(
            TailwindMergeContract::class,
            fn () => new TailwindMergeOnce(Config::getMergedConfig(), app('cache')->store())
        );
    }

    /**
     * Restore the original TailwindMerge binding.
     */
    private function unbindTailwindMergeOnce(): void
    {
        $this->laravel->forgetInstance(TailwindMergeContract::class);
    }
    
    /**
     * Export simple benchmark results.
     */
    private function exportSimpleBenchmark(string $outputDir): void
    {
        $this->info('Running simple benchmarks...');
        
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
        
        // Warmup TailwindMergeOnce
        $this->bindTailwindMergeOnce();
        foreach ($testCases as $cases) {
            foreach ($cases as $case) {
                app(TailwindMergeContract::class)->merge($case);
            }
        }
        $this->unbindTailwindMergeOnce();

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

            // TailwindMergeOnce (bind fresh instance)
            $this->bindTailwindMergeOnce();
            $onceStart = hrtime(true);
            for ($i = 0; $i < $iterations; $i++) {
                foreach ($cases as $case) {
                    app(TailwindMergeContract::class)->merge($case);
                }
            }
            $onceEnd = hrtime(true);
            $onceTime = ($onceEnd - $onceStart) / 1_000_000;
            $this->unbindTailwindMergeOnce();

            // Clear boost cache for fair comparison
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
        $this->bindTailwindMergeOnce();
        $examples = [];
        foreach ($testCases as $category => $cases) {
            $case = $cases[0];
            $examples[$category] = [
                'input' => $case,
                'twm' => TailwindMerge::merge($case),
                'once' => app(TailwindMergeContract::class)->merge($case),
                'boost' => $boost->merge($case),
            ];
        }
        $this->unbindTailwindMergeOnce();

        $html = View::make('benchmark', [
            'results' => $results,
            'totalTwm' => $totalTwm,
            'totalOnce' => $totalOnce,
            'totalBoost' => $totalBoost,
            'totalOnceSpeedup' => $totalTwm / max($totalOnce, 0.001),
            'totalBoostSpeedup' => $totalTwm / max($totalBoost, 0.001),
            'iterations' => $iterations,
            'examples' => $examples,
            'exportedAt' => now()->toIso8601String(),
        ])->render();
        
        File::put($outputDir . '/benchmark.html', $html);
        $this->info('  ✓ benchmark.html');
    }
    
    /**
     * Export component benchmark results.
     */
    private function exportComponentBenchmark(string $outputDir): void
    {
        $this->info('Running component benchmarks...');
        
        $iterations = 4;
        $boost = app(TailwindMergeBoost::class);
        
        $componentConfigs = BenchmarkComponentConfig::getConfigs();
        
        $componentResults = [];
        $components = [];
        
        foreach ($componentConfigs as $name => $variants) {
            // TailwindMerge timing
            $twmStart = hrtime(true);
            for ($i = 0; $i < $iterations; $i++) {
                foreach ($variants as $config) {
                    View::make("components.ui.{$name}", array_merge($config, ['merger' => 'twm', 'slot' => 'Content']))->render();
                }
            }
            $twmEnd = hrtime(true);
            $twmTime = ($twmEnd - $twmStart) / 1_000_000;

            // TailwindMergeOnce timing (bind fresh instance)
            $this->bindTailwindMergeOnce();
            $onceStart = hrtime(true);
            for ($i = 0; $i < $iterations; $i++) {
                foreach ($variants as $config) {
                    View::make("components.ui.{$name}", array_merge($config, ['merger' => 'once', 'slot' => 'Content']))->render();
                }
            }
            $onceEnd = hrtime(true);
            $onceTime = ($onceEnd - $onceStart) / 1_000_000;
            $this->unbindTailwindMergeOnce();
            
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
            
            // Render all variants for display
            $this->bindTailwindMergeOnce();
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
            $this->unbindTailwindMergeOnce();
            
            $components[$name] = [
                'name' => $name,
                'variants' => $componentVariants,
            ];
        }
        
        $twmTime = array_sum(array_column($componentResults, 'twm'));
        $onceTime = array_sum(array_column($componentResults, 'once'));
        $boostTime = array_sum(array_column($componentResults, 'boost'));
        $totalComponents = count($componentConfigs) * BenchmarkComponentConfig::getVariantsPerComponent() * $iterations;
        
        $html = View::make('component-benchmark', [
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
            'exportedAt' => now()->toIso8601String(),
        ])->render();
        
        File::put($outputDir . '/component-benchmark.html', $html);
        $this->info('  ✓ component-benchmark.html');
    }
    
    /**
     * Create an index page linking to all benchmarks.
     */
    private function createIndexPage(string $outputDir): void
    {
        $exportedAt = now()->format('F j, Y \a\t g:i A T');
        
        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TailwindMergeBoost Benchmarks</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto py-12 px-4">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">TailwindMergeBoost Benchmarks</h1>
        <p class="text-lg text-gray-600 mb-8">
            Performance comparison between 
            <a href="https://github.com/gehrisandro/tailwind-merge-laravel" class="text-blue-600 hover:underline">tailwind-merge-laravel</a>, 
            TailwindMergeOnce, and TailwindMergeBoost.
        </p>
        
        <div class="grid gap-6 md:grid-cols-2">
            <a href="benchmark.html" class="block p-6 bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow">
                <h2 class="text-xl font-semibold text-gray-900 mb-2">📊 Simple Benchmark</h2>
                <p class="text-gray-600">
                    Basic performance tests comparing class merging operations across different complexity levels.
                </p>
            </a>
            
            <a href="component-benchmark.html" class="block p-6 bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow">
                <h2 class="text-xl font-semibold text-gray-900 mb-2">🧩 Component Benchmark</h2>
                <p class="text-gray-600">
                    Real-world component rendering benchmark with 25 UI components and 10 variants each.
                </p>
            </a>
        </div>
        
        <div class="mt-12 text-center text-gray-500">
            <p>Generated on {$exportedAt}</p>
            <p class="mt-2">
                <a href="https://github.com/ifox/tailwind-merge-boost" class="text-blue-600 hover:underline">View on GitHub</a>
            </p>
        </div>
    </div>
</body>
</html>
HTML;
        
        File::put($outputDir . '/index.html', $html);
        $this->info('  ✓ index.html');
    }
}
