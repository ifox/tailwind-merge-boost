<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\BenchmarkComponentConfig;
use App\Services\TailwindMergeBoost;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\View;

class ComponentBenchmarkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'benchmark:components
                            {--iterations=40 : Number of iterations per component (40 × 25 = 1000)}
                            {--warmup=5 : Number of warmup iterations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Benchmark Blade component rendering with TailwindMerge vs TailwindMergeBoost';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $iterations = (int) $this->option('iterations');
        $warmup = (int) $this->option('warmup');
        $componentConfigs = BenchmarkComponentConfig::getConfigs();
        $totalComponents = count($componentConfigs) * $iterations;

        $this->info('╔════════════════════════════════════════════════════════════════════════╗');
        $this->info('║           Component Rendering Benchmark                                ║');
        $this->info('╠════════════════════════════════════════════════════════════════════════╣');
        $this->info(sprintf('║  Components: %-8d Iterations: %-8d Total: %-8d         ║', 
            count($componentConfigs), $iterations, $totalComponents));
        $this->info('╚════════════════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $boost = app(TailwindMergeBoost::class);

        // Warmup
        $this->info('Warming up...');
        $this->runWarmup($componentConfigs, $warmup);
        $this->newLine();

        // Benchmark
        $this->info('Benchmarking component rendering...');
        $results = $this->benchmarkComponents($componentConfigs, $boost, $iterations);

        // Display results
        $this->displayResults($results, $totalComponents);

        return self::SUCCESS;
    }

    /**
     * Run warmup iterations.
     *
     * @param  array<string, array<string, string>>  $componentConfigs
     */
    private function runWarmup(array $componentConfigs, int $warmup): void
    {
        $bar = $this->output->createProgressBar(count($componentConfigs) * $warmup * 2);
        $bar->start();

        for ($i = 0; $i < $warmup; $i++) {
            foreach ($componentConfigs as $name => $config) {
                View::make("components.ui.{$name}", array_merge($config, ['merger' => 'twm', 'slot' => 'Test']))->render();
                $bar->advance();
                View::make("components.ui.{$name}", array_merge($config, ['merger' => 'boost', 'slot' => 'Test']))->render();
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
    }

    /**
     * Benchmark all components.
     *
     * @param  array<string, array<string, string>>  $componentConfigs
     * @return array<string, array{twm: float, boost: float, speedup: float}>
     */
    private function benchmarkComponents(array $componentConfigs, TailwindMergeBoost $boost, int $iterations): array
    {
        $results = [];
        $bar = $this->output->createProgressBar(count($componentConfigs) * 2);
        $bar->start();

        foreach ($componentConfigs as $name => $config) {
            // TailwindMerge timing
            $twmStart = hrtime(true);
            for ($i = 0; $i < $iterations; $i++) {
                View::make("components.ui.{$name}", array_merge($config, ['merger' => 'twm', 'slot' => 'Content']))->render();
            }
            $twmEnd = hrtime(true);
            $twmTime = ($twmEnd - $twmStart) / 1_000_000;
            $bar->advance();

            // TailwindMergeBoost timing
            $boost->clearCache();
            $boostStart = hrtime(true);
            for ($i = 0; $i < $iterations; $i++) {
                View::make("components.ui.{$name}", array_merge($config, ['merger' => 'boost', 'slot' => 'Content']))->render();
            }
            $boostEnd = hrtime(true);
            $boostTime = ($boostEnd - $boostStart) / 1_000_000;
            $bar->advance();

            $results[$name] = [
                'twm' => $twmTime,
                'boost' => $boostTime,
                'speedup' => $twmTime / max($boostTime, 0.001),
            ];
        }

        $bar->finish();
        $this->newLine();

        return $results;
    }

    /**
     * Display benchmark results.
     *
     * @param  array<string, array{twm: float, boost: float, speedup: float}>  $results
     */
    private function displayResults(array $results, int $totalComponents): void
    {
        $this->newLine();
        $this->info('╔════════════════════════════════════════════════════════════════════════╗');
        $this->info('║                      COMPONENT BENCHMARK RESULTS                       ║');
        $this->info('╚════════════════════════════════════════════════════════════════════════╝');

        $tableData = [];
        $totalTwm = 0;
        $totalBoost = 0;

        foreach ($results as $name => $times) {
            $speedup = $times['speedup'];
            $tableData[] = [
                ucfirst($name),
                sprintf('%.3f ms', $times['twm']),
                sprintf('%.3f ms', $times['boost']),
                sprintf('%.2fx', $speedup),
                $speedup > 1 ? '<fg=green>⚡ Boost</>' : '<fg=yellow>TailwindMerge</>',
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
            ['Component', 'TailwindMerge', 'TailwindMergeBoost', 'Speedup', 'Winner'],
            $tableData
        );

        $this->newLine();
        $this->info(sprintf('Total component renders: %d', $totalComponents));
        $this->info(sprintf('Average time per component (TailwindMerge): %.4f ms', $totalTwm / count($results)));
        $this->info(sprintf('Average time per component (Boost): %.4f ms', $totalBoost / count($results)));

        $this->newLine();
        if ($totalSpeedup > 1) {
            $this->info(sprintf(
                '<fg=green;options=bold>TailwindMergeBoost is %.2fx faster overall for component rendering!</>',
                $totalSpeedup
            ));
        } else {
            $this->info(sprintf(
                '<fg=yellow;options=bold>TailwindMerge is %.2fx faster overall for component rendering.</>',
                1 / $totalSpeedup
            ));
        }

        $this->newLine();
        $this->info('View the web benchmark at: /component-benchmark');
    }
}
