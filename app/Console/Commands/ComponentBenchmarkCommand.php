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
                            {--iterations=4 : Number of iterations (4 × 25 components × 10 variants = 1000)}';

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
        $componentConfigs = BenchmarkComponentConfig::getConfigs();
        $variantsPerComponent = BenchmarkComponentConfig::getVariantsPerComponent();
        $totalComponents = count($componentConfigs) * $variantsPerComponent * $iterations;

        $this->info('╔════════════════════════════════════════════════════════════════════════╗');
        $this->info('║           Component Rendering Benchmark                                ║');
        $this->info('╠════════════════════════════════════════════════════════════════════════╣');
        $this->info(sprintf('║  Components: %-3d  Variants: %-3d  Iterations: %-3d  Total: %-6d     ║', 
            count($componentConfigs), $variantsPerComponent, $iterations, $totalComponents));
        $this->info('╚════════════════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $boost = app(TailwindMergeBoost::class);

        // Benchmark
        $this->info('Benchmarking component rendering...');
        $results = $this->benchmarkComponents($componentConfigs, $boost, $iterations);

        // Display results
        $this->displayResults($results, $totalComponents);

        return self::SUCCESS;
    }

    /**
     * Benchmark all components with all variants.
     *
     * @param  array<string, array<int, array<string, string>>>  $componentConfigs
     * @return array<string, array{twm: float, boost: float, speedup: float, variants: int}>
     */
    private function benchmarkComponents(array $componentConfigs, TailwindMergeBoost $boost, int $iterations): array
    {
        $results = [];
        $bar = $this->output->createProgressBar(count($componentConfigs) * 2);
        $bar->start();

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
            $bar->advance();

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
            $bar->advance();

            $results[$name] = [
                'twm' => $twmTime,
                'boost' => $boostTime,
                'speedup' => $twmTime / max($boostTime, 0.001),
                'variants' => count($variants),
            ];
        }

        $bar->finish();
        $this->newLine();

        return $results;
    }

    /**
     * Display benchmark results.
     *
     * @param  array<string, array{twm: float, boost: float, speedup: float, variants: int}>  $results
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
                $times['variants'],
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
        $tableData[] = ['', '', '', '', '', ''];
        $tableData[] = [
            '<fg=cyan>TOTAL</>',
            '<fg=cyan>' . array_sum(array_column($results, 'variants')) . '</>',
            sprintf('<fg=cyan>%.2f ms</>', $totalTwm),
            sprintf('<fg=cyan>%.2f ms</>', $totalBoost),
            sprintf('<fg=cyan>%.2fx</>', $totalSpeedup),
            $totalSpeedup > 1 ? '<fg=green;options=bold>⚡ Boost wins</>' : '<fg=yellow;options=bold>TailwindMerge wins</>',
        ];

        $this->table(
            ['Component', 'Variants', 'TailwindMerge', 'TailwindMergeBoost', 'Speedup', 'Winner'],
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
