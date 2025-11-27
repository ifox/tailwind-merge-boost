<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\BenchmarkComponentConfig;
use App\Services\TailwindMergeBoost;
use App\Services\TailwindMergeOnce;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\View;
use TailwindMerge\Contracts\TailwindMergeContract;
use TailwindMerge\Support\Config;

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
    protected $description = 'Benchmark Blade component rendering with TailwindMerge vs TailwindMergeOnce vs TailwindMergeBoost';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $iterations = (int) $this->option('iterations');
        $componentConfigs = BenchmarkComponentConfig::getConfigs();
        $variantsPerComponent = BenchmarkComponentConfig::getVariantsPerComponent();
        $totalComponents = count($componentConfigs) * $variantsPerComponent * $iterations;

        $this->info('╔════════════════════════════════════════════════════════════════════════════════════════════╗');
        $this->info('║                            Component Rendering Benchmark                                   ║');
        $this->info('╠════════════════════════════════════════════════════════════════════════════════════════════╣');
        $this->info(sprintf('║  Components: %-3d  Variants: %-3d  Iterations: %-3d  Total: %-6d                         ║', 
            count($componentConfigs), $variantsPerComponent, $iterations, $totalComponents));
        $this->info('╚════════════════════════════════════════════════════════════════════════════════════════════╝');
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
     * Benchmark all components with all variants.
     *
     * @param  array<string, array<int, array<string, string>>>  $componentConfigs
     * @return array<string, array{twm: float, once: float, boost: float, variants: int}>
     */
    private function benchmarkComponents(array $componentConfigs, TailwindMergeBoost $boost, int $iterations): array
    {
        $results = [];
        $bar = $this->output->createProgressBar(count($componentConfigs) * 3);
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
                'once' => $onceTime,
                'boost' => $boostTime,
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
     * @param  array<string, array{twm: float, once: float, boost: float, variants: int}>  $results
     */
    private function displayResults(array $results, int $totalComponents): void
    {
        $this->newLine();
        $this->info('╔════════════════════════════════════════════════════════════════════════════════════════════╗');
        $this->info('║                            COMPONENT BENCHMARK RESULTS                                     ║');
        $this->info('╚════════════════════════════════════════════════════════════════════════════════════════════╝');

        $tableData = [];
        $totalTwm = 0;
        $totalOnce = 0;
        $totalBoost = 0;

        foreach ($results as $name => $times) {
            $onceSpeedup = $times['twm'] / max($times['once'], 0.001);
            $boostSpeedup = $times['twm'] / max($times['boost'], 0.001);
            $fastest = min($times['twm'], $times['once'], $times['boost']);
            $winner = match ($fastest) {
                $times['boost'] => '<fg=green>⚡ Boost</>',
                $times['once'] => '<fg=blue>⚡ Once</>',
                default => '<fg=yellow>TailwindMerge</>',
            };
            $tableData[] = [
                ucfirst($name),
                $times['variants'],
                sprintf('%.3f ms', $times['twm']),
                sprintf('%.3f ms (%.2fx)', $times['once'], $onceSpeedup),
                sprintf('%.3f ms (%.2fx)', $times['boost'], $boostSpeedup),
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
        $tableData[] = ['', '', '', '', '', ''];
        $tableData[] = [
            '<fg=cyan>TOTAL</>',
            '<fg=cyan>' . array_sum(array_column($results, 'variants')) . '</>',
            sprintf('<fg=cyan>%.2f ms</>', $totalTwm),
            sprintf('<fg=cyan>%.2f ms (%.2fx)</>', $totalOnce, $totalOnceSpeedup),
            sprintf('<fg=cyan>%.2f ms (%.2fx)</>', $totalBoost, $totalBoostSpeedup),
            $totalWinner,
        ];

        $this->table(
            ['Component', 'Variants', 'TailwindMerge', 'TailwindMergeOnce', 'TailwindMergeBoost', 'Winner'],
            $tableData
        );

        $this->newLine();
        $this->info(sprintf('Total component renders: %d', $totalComponents));
        $this->info(sprintf('Average time per component (TailwindMerge): %.4f ms', $totalTwm / count($results)));
        $this->info(sprintf('Average time per component (TailwindMergeOnce): %.4f ms', $totalOnce / count($results)));
        $this->info(sprintf('Average time per component (TailwindMergeBoost): %.4f ms', $totalBoost / count($results)));

        $this->newLine();
        $this->info(sprintf(
            '<fg=blue;options=bold>TailwindMergeOnce is %.2fx faster than TailwindMerge for component rendering</>',
            $totalOnceSpeedup
        ));
        $this->info(sprintf(
            '<fg=green;options=bold>TailwindMergeBoost is %.2fx faster than TailwindMerge for component rendering</>',
            $totalBoostSpeedup
        ));

        $this->newLine();
        $this->info('View the web benchmark at: /component-benchmark');
    }
}
