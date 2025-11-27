# Tailwind Merge Boost

A Laravel application for benchmarking the [tailwind-merge-laravel](https://github.com/gehrisandro/tailwind-merge-laravel) package against alternative implementations of Tailwind CSS class mergers.

## Overview

This project provides:

1. **TailwindMergeOnce** - A production-ready wrapper around `tailwind-merge-laravel` that adds in-memory memoization using PHP's `once()` helper for significant performance gains
2. **TailwindMergeBoost** - An experimental high-performance implementation (⚠️ **not production-ready** - see [Test Status](#test-status))
3. **Benchmark CLI Command** - Compare performance between the implementations
4. **Benchmark Web UI** - Visual comparison of benchmark results

## Test Status

⚠️ **TailwindMergeBoost is NOT production-ready.** It does not have full feature parity with `tailwind-merge-laravel`.

To see which edge cases are not yet supported, run:

```bash
php artisan test --filter=BoostFeatureParity
```

**Recommendation:** For production use, use **TailwindMergeOnce** which wraps the official TailwindMerge with `once()` memoization for both correctness and performance.

## Installation

```bash
# Clone the repository
git clone https://github.com/ifox/tailwind-merge-boost.git
cd tailwind-merge-boost

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

## Running Benchmarks

### CLI Benchmark

Run the benchmark command for detailed performance comparison:

```bash
php artisan benchmark:tailwind-merge
```

Options:
- `--iterations=1000` - Number of iterations to run (default: 1000)
- `--warmup=100` - Number of warmup iterations (default: 100)

Example:
```bash
php artisan benchmark:tailwind-merge --iterations=5000 --warmup=200
```

### Web Benchmark

Start the development server and visit the benchmark page:

```bash
php artisan serve
```

Then open http://localhost:8000/benchmark in your browser.

## TailwindMergeBoost

> ⚠️ **Warning:** TailwindMergeBoost is an experimental implementation and does **not** have full feature parity with `tailwind-merge-laravel`. Many edge cases are not yet supported. For production use, we recommend **TailwindMergeOnce** instead.

The `TailwindMergeBoost` class provides a fast but incomplete alternative to `tailwind-merge-laravel`. It uses:

- Lookup tables for common class patterns
- Simple string operations instead of complex object creation
- Built-in caching with configurable size
- Regex-based pattern matching for class group identification

### Usage

```php
use App\Services\TailwindMergeBoost;

$merger = new TailwindMergeBoost();

// Merge conflicting classes (later classes win)
$result = $merger->merge('p-4 p-6'); // Returns: 'p-6'

// Handle modifiers
$result = $merger->merge('hover:bg-red-500 hover:bg-blue-500'); // Returns: 'hover:bg-blue-500'

// Complex class strings
$result = $merger->merge('flex flex-col p-4 bg-white shadow-lg p-8');
// Returns: 'flex flex-col bg-white shadow-lg p-8'

// Array input
$result = $merger->merge(['p-4', 'mt-4'], ['p-8']);
// Returns: 'mt-4 p-8'
```

### Configuration

```php
// Set cache size (default: 500)
$merger->setCacheSize(1000);

// Clear cache
$merger->clearCache();
```

## Benchmark Results

Results may vary based on your system. Typical speedups range from 1.5x to 3x depending on the complexity of the input classes.

### Test Categories

- **Simple**: Basic class conflicts (e.g., `p-4 p-6`)
- **Modifiers**: Classes with hover, focus, responsive modifiers
- **Complex**: Multiple conflicting class groups
- **Long**: Real-world component class strings
- **Edge Cases**: Important modifiers, negative values, arbitrary values

## Running Tests

```bash
# Run all tests
php artisan test

# Run only TailwindMergeBoost tests
php artisan test --filter=TailwindMergeBoostTest
```

## Project Structure

```
├── app/
│   ├── Console/Commands/
│   │   └── BenchmarkCommand.php      # CLI benchmark command
│   └── Services/
│       └── TailwindMergeBoost.php    # Efficient merger implementation
├── resources/views/
│   └── benchmark.blade.php           # Web benchmark UI
├── routes/
│   └── web.php                       # Benchmark route
└── tests/Unit/
    └── TailwindMergeBoostTest.php    # Unit tests
```

## License

MIT License - see [LICENSE](LICENSE) file for details.

## Credits

- [tailwind-merge-laravel](https://github.com/gehrisandro/tailwind-merge-laravel) by Sandro Gehri
- [tailwind-merge-php](https://github.com/gehrisandro/tailwind-merge-php) by Sandro Gehri
