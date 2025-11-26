<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TailwindMerge Benchmark</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen py-12">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">
                TailwindMerge vs TailwindMergeBoost
            </h1>
            <p class="text-lg text-gray-600">
                Performance comparison of Tailwind CSS class mergers
            </p>
        </div>

        <!-- Summary Card -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Summary</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-blue-50 rounded-xl p-6 text-center">
                    <p class="text-sm font-medium text-blue-600 uppercase tracking-wide">TailwindMerge</p>
                    <p class="text-3xl font-bold text-blue-900 mt-2">{{ number_format($totalTwm, 2) }} ms</p>
                </div>
                <div class="bg-green-50 rounded-xl p-6 text-center">
                    <p class="text-sm font-medium text-green-600 uppercase tracking-wide">TailwindMergeBoost</p>
                    <p class="text-3xl font-bold text-green-900 mt-2">{{ number_format($totalBoost, 2) }} ms</p>
                </div>
                <div class="{{ $totalSpeedup > 1 ? 'bg-emerald-50' : 'bg-yellow-50' }} rounded-xl p-6 text-center">
                    <p class="text-sm font-medium {{ $totalSpeedup > 1 ? 'text-emerald-600' : 'text-yellow-600' }} uppercase tracking-wide">Speedup</p>
                    <p class="text-3xl font-bold {{ $totalSpeedup > 1 ? 'text-emerald-900' : 'text-yellow-900' }} mt-2">
                        {{ number_format($totalSpeedup, 2) }}x
                        @if($totalSpeedup > 1)
                            <span class="text-lg">⚡</span>
                        @endif
                    </p>
                </div>
            </div>
            <p class="text-center text-gray-500 mt-6">
                Based on {{ number_format($iterations) }} iterations per test case
            </p>
        </div>

        <!-- Results Table -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Detailed Results</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b-2 border-gray-200">
                            <th class="text-left py-4 px-4 font-semibold text-gray-700">Category</th>
                            <th class="text-right py-4 px-4 font-semibold text-gray-700">TailwindMerge</th>
                            <th class="text-right py-4 px-4 font-semibold text-gray-700">TailwindMergeBoost</th>
                            <th class="text-right py-4 px-4 font-semibold text-gray-700">Speedup</th>
                            <th class="text-center py-4 px-4 font-semibold text-gray-700">Winner</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($results as $category => $result)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4 px-4 font-medium text-gray-900 capitalize">{{ $category }}</td>
                            <td class="py-4 px-4 text-right text-gray-600">{{ number_format($result['twm'], 2) }} ms</td>
                            <td class="py-4 px-4 text-right text-gray-600">{{ number_format($result['boost'], 2) }} ms</td>
                            <td class="py-4 px-4 text-right font-semibold {{ $result['speedup'] > 1 ? 'text-green-600' : 'text-yellow-600' }}">
                                {{ number_format($result['speedup'], 2) }}x
                            </td>
                            <td class="py-4 px-4 text-center">
                                @if($result['speedup'] > 1)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        ⚡ Boost
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        TailwindMerge
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Output Examples -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Output Examples</h2>
            <div class="space-y-6">
                @foreach($examples as $category => $example)
                <div class="border border-gray-200 rounded-xl p-4">
                    <h3 class="font-semibold text-gray-700 capitalize mb-3">{{ $category }}</h3>
                    <div class="space-y-2">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <span class="text-xs font-medium text-gray-500 uppercase">Input</span>
                            <code class="block text-sm text-gray-800 mt-1 break-all">{{ $example['input'] }}</code>
                        </div>
                        <div class="bg-blue-50 rounded-lg p-3">
                            <span class="text-xs font-medium text-blue-600 uppercase">TailwindMerge Output</span>
                            <code class="block text-sm text-blue-800 mt-1 break-all">{{ $example['twm'] }}</code>
                        </div>
                        <div class="bg-green-50 rounded-lg p-3">
                            <span class="text-xs font-medium text-green-600 uppercase">TailwindMergeBoost Output</span>
                            <code class="block text-sm text-green-800 mt-1 break-all">{{ $example['boost'] }}</code>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Visual Comparison -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Performance Comparison</h2>
            <div class="space-y-4">
                @foreach($results as $category => $result)
                @php
                    $maxTime = max($result['twm'], $result['boost']);
                    $twmWidth = ($result['twm'] / $maxTime) * 100;
                    $boostWidth = ($result['boost'] / $maxTime) * 100;
                @endphp
                <div class="mb-6">
                    <h3 class="font-medium text-gray-700 capitalize mb-2">{{ $category }}</h3>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <span class="w-32 text-sm text-gray-500">TailwindMerge</span>
                            <div class="flex-1 bg-gray-200 rounded-full h-6 overflow-hidden">
                                <div class="bg-blue-500 h-full rounded-full flex items-center justify-end pr-2"
                                     style="width: {{ $twmWidth }}%">
                                    <span class="text-xs text-white font-medium">{{ number_format($result['twm'], 1) }} ms</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <span class="w-32 text-sm text-gray-500">Boost</span>
                            <div class="flex-1 bg-gray-200 rounded-full h-6 overflow-hidden">
                                <div class="bg-green-500 h-full rounded-full flex items-center justify-end pr-2"
                                     style="width: {{ $boostWidth }}%">
                                    <span class="text-xs text-white font-medium">{{ number_format($result['boost'], 1) }} ms</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-gray-500">
            <p>Run <code class="bg-gray-200 px-2 py-1 rounded text-sm">php artisan benchmark:tailwind-merge</code> for more detailed CLI benchmarks</p>
        </div>
    </div>
</body>
</html>
