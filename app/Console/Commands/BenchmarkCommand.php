<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\TailwindMergeBoost;
use Illuminate\Console\Command;
use TailwindMerge\Laravel\Facades\TailwindMerge;

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
    protected $description = 'Benchmark TailwindMerge vs TailwindMergeBoost';

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

        $this->info('╔════════════════════════════════════════════════════════════════════════╗');
        $this->info('║          TailwindMerge vs TailwindMergeBoost Benchmark                 ║');
        $this->info('╠════════════════════════════════════════════════════════════════════════╣');
        $this->info(sprintf('║  Iterations: %-10d Warmup: %-10d                          ║', $iterations, $warmup));
        $this->info('╚════════════════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $boost = new TailwindMergeBoost();

        // Verify both implementations produce same/similar results
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
     * Verify that both implementations produce compatible results.
     */
    private function verifyOutputs(TailwindMergeBoost $boost): void
    {
        $this->table(
            ['Input', 'TailwindMerge', 'TailwindMergeBoost', 'Match'],
            collect($this->testCases)
                ->flatten()
                ->take(10)
                ->map(function ($input) use ($boost) {
                    $twm = TailwindMerge::merge($input);
                    $tmb = $boost->merge($input);
                    $match = $this->compareResults($twm, $tmb) ? '✓' : '✗';

                    return [
                        strlen($input) > 50 ? substr($input, 0, 47).'...' : $input,
                        strlen($twm) > 30 ? substr($twm, 0, 27).'...' : $twm,
                        strlen($tmb) > 30 ? substr($tmb, 0, 27).'...' : $tmb,
                        $match,
                    ];
                })
                ->toArray()
        );
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

        for ($i = 0; $i < $warmup; $i++) {
            foreach ($allCases as $case) {
                TailwindMerge::merge($case);
                $boost->merge($case);
            }
        }
    }

    /**
     * Benchmark a category of test cases.
     *
     * @param  array<string>  $cases
     * @return array{twm: float, boost: float}
     */
    private function benchmarkCategory(TailwindMergeBoost $boost, array $cases, int $iterations): array
    {
        // Benchmark TailwindMerge
        $twmStart = hrtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            foreach ($cases as $case) {
                TailwindMerge::merge($case);
            }
        }
        $twmEnd = hrtime(true);
        $twmTime = ($twmEnd - $twmStart) / 1_000_000; // Convert to ms

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
            'boost' => $boostTime,
        ];
    }

    /**
     * Display benchmark results.
     *
     * @param  array<string, array{twm: float, boost: float}>  $results
     */
    private function displayResults(array $results, int $iterations): void
    {
        $this->newLine();
        $this->info('╔════════════════════════════════════════════════════════════════════════╗');
        $this->info('║                           BENCHMARK RESULTS                            ║');
        $this->info('╚════════════════════════════════════════════════════════════════════════╝');

        $tableData = [];
        $totalTwm = 0;
        $totalBoost = 0;

        foreach ($results as $category => $times) {
            $speedup = $times['twm'] / max($times['boost'], 0.001);
            $tableData[] = [
                ucfirst($category),
                sprintf('%.2f ms', $times['twm']),
                sprintf('%.2f ms', $times['boost']),
                sprintf('%.2fx', $speedup),
                $speedup > 1 ? '<fg=green>⚡ Boost wins</>' : '<fg=yellow>TailwindMerge wins</>',
            ];
            $totalTwm += $times['twm'];
            $totalBoost += $times['boost'];
        }

        // Add total row
        $totalSpeedup = $totalTwm / max($totalBoost, 0.001);
        $tableData[] = ['', '', '', '', ''];
        $tableData[] = [
            '<fg=cyan>TOTAL</>',
            sprintf('<fg=cyan>%.2f ms</>', $totalTwm),
            sprintf('<fg=cyan>%.2f ms</>', $totalBoost),
            sprintf('<fg=cyan>%.2fx</>', $totalSpeedup),
            $totalSpeedup > 1 ? '<fg=green;options=bold>⚡ Boost wins</>' : '<fg=yellow;options=bold>TailwindMerge wins</>',
        ];

        $this->table(
            ['Category', 'TailwindMerge', 'TailwindMergeBoost', 'Speedup', 'Winner'],
            $tableData
        );

        $this->newLine();
        $this->info(sprintf(
            'Total operations: %d (across %d iterations × %d test cases per category)',
            $iterations * array_sum(array_map('count', $this->testCases)),
            $iterations,
            count($this->testCases)
        ));

        if ($totalSpeedup > 1) {
            $this->info(sprintf(
                '<fg=green;options=bold>TailwindMergeBoost is %.2fx faster overall!</>',
                $totalSpeedup
            ));
        } else {
            $this->info(sprintf(
                '<fg=yellow;options=bold>TailwindMerge is %.2fx faster overall.</>',
                1 / $totalSpeedup
            ));
        }
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
            ['Metric', 'TailwindMerge', 'TailwindMergeBoost', 'Difference'],
            [
                [
                    'Memory Usage',
                    $this->formatBytes($twmMem),
                    $this->formatBytes($boostMem),
                    $boostMem < $twmMem
                        ? sprintf('<fg=green>-%.1f%%</>', (1 - $boostMem / max($twmMem, 1)) * 100)
                        : sprintf('<fg=yellow>+%.1f%%</>', ($boostMem / max($twmMem, 1) - 1) * 100),
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
