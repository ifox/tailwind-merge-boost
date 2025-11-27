<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\TailwindMergeBoost;
use App\Services\TailwindMergeOnce;
use Illuminate\Console\Command;
use TailwindMerge\Contracts\TailwindMergeContract;
use TailwindMerge\Laravel\Facades\TailwindMerge;
use TailwindMerge\Support\Config;

class BenchmarkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'benchmark:tailwind-merge
                            {--iterations=1000 : Number of iterations to run}
                            {--warmup=100 : Number of warmup iterations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Benchmark TailwindMerge vs TailwindMergeOnce vs TailwindMergeBoost';

    /**
     * Test cases for benchmarking.
     *
     * @var array<string, array<string>>
     */
    private array $testCases = [
        'simple' => [
            'p-4 p-6',
            'mt-2 mb-4',
            'text-red-500 text-blue-500',
        ],
        'modifiers' => [
            'hover:bg-red-500 hover:bg-blue-500',
            'md:p-4 md:p-8',
            'dark:text-white dark:text-gray-100',
            'focus:ring-2 focus:ring-4',
        ],
        'complex' => [
            'flex flex-col items-center justify-between p-4 p-6',
            'bg-red-500 hover:bg-blue-500 bg-green-500 hover:bg-yellow-500',
            'mt-4 mb-2 px-6 py-4 mt-8 px-8',
            'rounded-lg rounded-xl border-2 border-red-500 border-4 border-blue-500',
        ],
        'long' => [
            'flex flex-col items-center justify-between p-4 bg-white shadow-lg rounded-xl hover:shadow-xl transition-shadow duration-300 mx-auto max-w-md w-full space-y-4 p-8 bg-gray-100',
            'text-gray-800 text-lg font-semibold tracking-wide leading-tight mb-2 text-gray-900 text-xl font-bold',
            'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 gap-6 p-4 p-8 bg-white rounded-lg',
        ],
        'edge_cases' => [
            '!p-4 !p-8',
            '-mt-4 -mt-8',
            'w-[100px] w-[200px]',
            'bg-[#ff0000] bg-[#00ff00]',
            'text-sm/5 text-lg/7',
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $iterations = (int) $this->option('iterations');
        $warmup = (int) $this->option('warmup');

        $this->info('╔════════════════════════════════════════════════════════════════════════════════════════════╗');
        $this->info('║          TailwindMerge vs TailwindMergeOnce vs TailwindMergeBoost Benchmark                ║');
        $this->info('╠════════════════════════════════════════════════════════════════════════════════════════════╣');
        $this->info(sprintf('║  Iterations: %-10d Warmup: %-10d                                                ║', $iterations, $warmup));
        $this->info('╚════════════════════════════════════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $boost = new TailwindMergeBoost();

        // Verify all implementations produce same/similar results
        $this->info('Verifying output compatibility...');
        $this->verifyOutputs($boost);
        $this->newLine();

        // Warmup
        $this->info('Warming up...');
        $this->runWarmup($boost, $warmup);
        $this->newLine();

        // Run benchmarks
        $results = [];
        foreach ($this->testCases as $category => $cases) {
            $this->info("Benchmarking category: {$category}");
            $results[$category] = $this->benchmarkCategory($boost, $cases, $iterations);
        }

        // Display results
        $this->displayResults($results, $iterations);

        // Run memory benchmark
        $this->newLine();
        $this->info('Running memory benchmark...');
        $this->benchmarkMemory($boost, $iterations);

        return self::SUCCESS;
    }

    /**
     * Create a fresh TailwindMergeOnce instance and bind it to the container.
     * This ensures the once() memoization is fresh for each benchmark run.
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
        // Force Laravel to forget the current binding and re-resolve
        $this->laravel->forgetInstance(TailwindMergeContract::class);
    }

    /**
     * Verify that all implementations produce compatible results.
     */
    private function verifyOutputs(TailwindMergeBoost $boost): void
    {
        // Temporarily bind TailwindMergeOnce for verification
        $this->bindTailwindMergeOnce();

        $this->table(
            ['Input', 'TailwindMerge', 'TailwindMergeOnce', 'TailwindMergeBoost', 'Match'],
            collect($this->testCases)
                ->flatten()
                ->take(10)
                ->map(function ($input) use ($boost) {
                    $twm = TailwindMerge::merge($input);
                    $tmo = app(TailwindMergeContract::class)->merge($input);
                    $tmb = $boost->merge($input);
                    $matchOnce = $this->compareResults($twm, $tmo) ? '✓' : '✗';
                    $matchBoost = $this->compareResults($twm, $tmb) ? '✓' : '✗';

                    return [
                        strlen($input) > 40 ? substr($input, 0, 37).'...' : $input,
                        strlen($twm) > 20 ? substr($twm, 0, 17).'...' : $twm,
                        $matchOnce,
                        strlen($tmb) > 20 ? substr($tmb, 0, 17).'...' : $tmb,
                        $matchBoost,
                    ];
                })
                ->toArray()
        );

        $this->unbindTailwindMergeOnce();
    }

    /**
     * Compare results considering that order might differ.
     */
    private function compareResults(string $a, string $b): bool
    {
        $aClasses = explode(' ', $a);
        $bClasses = explode(' ', $b);
        sort($aClasses);
        sort($bClasses);

        return $aClasses === $bClasses;
    }

    /**
     * Run warmup iterations.
     */
    private function runWarmup(TailwindMergeBoost $boost, int $warmup): void
    {
        $allCases = collect($this->testCases)->flatten()->all();

        // Warmup TailwindMerge
        for ($i = 0; $i < $warmup; $i++) {
            foreach ($allCases as $case) {
                TailwindMerge::merge($case);
            }
        }

        // Warmup TailwindMergeOnce
        $this->bindTailwindMergeOnce();
        for ($i = 0; $i < $warmup; $i++) {
            foreach ($allCases as $case) {
                app(TailwindMergeContract::class)->merge($case);
            }
        }
        $this->unbindTailwindMergeOnce();

        // Warmup TailwindMergeBoost
        for ($i = 0; $i < $warmup; $i++) {
            foreach ($allCases as $case) {
                $boost->merge($case);
            }
        }
    }

    /**
     * Benchmark a category of test cases.
     *
     * @param  array<string>  $cases
     * @return array{twm: float, once: float, boost: float}
     */
    private function benchmarkCategory(TailwindMergeBoost $boost, array $cases, int $iterations): array
    {
        // Benchmark TailwindMerge (without TailwindMergeOnce binding)
        $twmStart = hrtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            foreach ($cases as $case) {
                TailwindMerge::merge($case);
            }
        }
        $twmEnd = hrtime(true);
        $twmTime = ($twmEnd - $twmStart) / 1_000_000; // Convert to ms

        // Benchmark TailwindMergeOnce (bind fresh instance for the benchmark)
        $this->bindTailwindMergeOnce();
        $onceStart = hrtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            foreach ($cases as $case) {
                app(TailwindMergeContract::class)->merge($case);
            }
        }
        $onceEnd = hrtime(true);
        $onceTime = ($onceEnd - $onceStart) / 1_000_000; // Convert to ms
        $this->unbindTailwindMergeOnce();

        // Clear boost cache for fair comparison
        $boost->clearCache();

        // Benchmark TailwindMergeBoost
        $boostStart = hrtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            foreach ($cases as $case) {
                $boost->merge($case);
            }
        }
        $boostEnd = hrtime(true);
        $boostTime = ($boostEnd - $boostStart) / 1_000_000; // Convert to ms

        return [
            'twm' => $twmTime,
            'once' => $onceTime,
            'boost' => $boostTime,
        ];
    }

    /**
     * Display benchmark results.
     *
     * @param  array<string, array{twm: float, once: float, boost: float}>  $results
     */
    private function displayResults(array $results, int $iterations): void
    {
        $this->newLine();
        $this->info('╔════════════════════════════════════════════════════════════════════════════════════════════╗');
        $this->info('║                                    BENCHMARK RESULTS                                       ║');
        $this->info('╚════════════════════════════════════════════════════════════════════════════════════════════╝');

        $tableData = [];
        $totalTwm = 0;
        $totalOnce = 0;
        $totalBoost = 0;

        foreach ($results as $category => $times) {
            $onceSpeedup = $times['twm'] / max($times['once'], 0.001);
            $boostSpeedup = $times['twm'] / max($times['boost'], 0.001);
            $fastest = min($times['twm'], $times['once'], $times['boost']);
            $winner = match ($fastest) {
                $times['boost'] => '<fg=green>⚡ Boost</>',
                $times['once'] => '<fg=blue>⚡ Once</>',
                default => '<fg=yellow>TailwindMerge</>',
            };
            $tableData[] = [
                ucfirst($category),
                sprintf('%.2f ms', $times['twm']),
                sprintf('%.2f ms (%.2fx)', $times['once'], $onceSpeedup),
                sprintf('%.2f ms (%.2fx)', $times['boost'], $boostSpeedup),
                $winner,
            ];
            $totalTwm += $times['twm'];
            $totalOnce += $times['once'];
            $totalBoost += $times['boost'];
        }

        // Add total row
        $totalOnceSpeedup = $totalTwm / max($totalOnce, 0.001);
        $totalBoostSpeedup = $totalTwm / max($totalBoost, 0.001);
        $totalFastest = min($totalTwm, $totalOnce, $totalBoost);
        $totalWinner = match ($totalFastest) {
            $totalBoost => '<fg=green;options=bold>⚡ Boost wins</>',
            $totalOnce => '<fg=blue;options=bold>⚡ Once wins</>',
            default => '<fg=yellow;options=bold>TailwindMerge wins</>',
        };
        $tableData[] = ['', '', '', '', ''];
        $tableData[] = [
            '<fg=cyan>TOTAL</>',
            sprintf('<fg=cyan>%.2f ms</>', $totalTwm),
            sprintf('<fg=cyan>%.2f ms (%.2fx)</>', $totalOnce, $totalOnceSpeedup),
            sprintf('<fg=cyan>%.2f ms (%.2fx)</>', $totalBoost, $totalBoostSpeedup),
            $totalWinner,
        ];

        $this->table(
            ['Category', 'TailwindMerge', 'TailwindMergeOnce', 'TailwindMergeBoost', 'Winner'],
            $tableData
        );

        $this->newLine();
        $this->info(sprintf(
            'Total operations: %d (across %d iterations × %d test cases per category)',
            $iterations * array_sum(array_map('count', $this->testCases)),
            $iterations,
            count($this->testCases)
        ));

        $this->newLine();
        $this->info(sprintf(
            '<fg=blue;options=bold>TailwindMergeOnce is %.2fx faster than TailwindMerge</>',
            $totalOnceSpeedup
        ));
        $this->info(sprintf(
            '<fg=green;options=bold>TailwindMergeBoost is %.2fx faster than TailwindMerge</>',
            $totalBoostSpeedup
        ));
    }

    /**
     * Benchmark memory usage.
     */
    private function benchmarkMemory(TailwindMergeBoost $boost, int $iterations): void
    {
        $allCases = collect($this->testCases)->flatten()->all();

        // TailwindMerge memory
        gc_collect_cycles();
        $twmMemStart = memory_get_usage();
        for ($i = 0; $i < $iterations; $i++) {
            foreach ($allCases as $case) {
                TailwindMerge::merge($case);
            }
        }
        $twmMemEnd = memory_get_usage();
        $twmMem = $twmMemEnd - $twmMemStart;

        // TailwindMergeOnce memory
        $this->bindTailwindMergeOnce();
        gc_collect_cycles();
        $onceMemStart = memory_get_usage();
        for ($i = 0; $i < $iterations; $i++) {
            foreach ($allCases as $case) {
                app(TailwindMergeContract::class)->merge($case);
            }
        }
        $onceMemEnd = memory_get_usage();
        $onceMem = $onceMemEnd - $onceMemStart;
        $this->unbindTailwindMergeOnce();

        // TailwindMergeBoost memory
        $boost->clearCache();
        gc_collect_cycles();
        $boostMemStart = memory_get_usage();
        for ($i = 0; $i < $iterations; $i++) {
            foreach ($allCases as $case) {
                $boost->merge($case);
            }
        }
        $boostMemEnd = memory_get_usage();
        $boostMem = $boostMemEnd - $boostMemStart;

        $this->table(
            ['Metric', 'TailwindMerge', 'TailwindMergeOnce', 'TailwindMergeBoost'],
            [
                [
                    'Memory Usage',
                    $this->formatBytes($twmMem),
                    sprintf('%s (%s)', 
                        $this->formatBytes($onceMem),
                        $onceMem < $twmMem
                            ? sprintf('<fg=green>-%.1f%%</>', (1 - $onceMem / max($twmMem, 1)) * 100)
                            : sprintf('<fg=yellow>+%.1f%%</>', ($onceMem / max($twmMem, 1) - 1) * 100)
                    ),
                    sprintf('%s (%s)', 
                        $this->formatBytes($boostMem),
                        $boostMem < $twmMem
                            ? sprintf('<fg=green>-%.1f%%</>', (1 - $boostMem / max($twmMem, 1)) * 100)
                            : sprintf('<fg=yellow>+%.1f%%</>', ($boostMem / max($twmMem, 1) - 1) * 100)
                    ),
                ],
            ]
        );
    }

    /**
     * Format bytes to human-readable string.
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return sprintf('%.2f %s', $bytes, $units[$pow]);
    }
}
