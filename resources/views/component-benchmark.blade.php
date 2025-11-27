<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Component Rendering Benchmark</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Ensure Tailwind CDN classes are applied */
        [class] { /* Force specificity */ }
        
        /* Contain fixed/absolute positioned elements (like modals) within preview areas */
        .component-preview {
            position: relative;
            isolation: isolate;
            contain: layout;
            overflow: hidden;
        }
        .component-preview [class*="fixed"],
        .component-preview [class*="absolute"],
        .component-preview [class*="inset-"] {
            position: relative !important;
            inset: auto !important;
            z-index: auto !important;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">
                Component Rendering Benchmark
            </h1>
            <p class="text-lg text-gray-600">
                Rendering {{ number_format($totalComponents) }} Blade components with class merging
            </p>
            <p class="text-sm text-gray-500 mt-2">
                25 components × {{ $variantsPerComponent }} variants × {{ $repeatsPerRender }} repeats each
            </p>
        </div>

        <!-- Performance Summary -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Performance Summary</h2>
            <div class="grid grid-cols-1 md:grid-cols-6 gap-6">
                <div class="bg-blue-50 rounded-xl p-6 text-center">
                    <p class="text-sm font-medium text-blue-600 uppercase tracking-wide">TailwindMerge</p>
                    <p class="text-3xl font-bold text-blue-900 mt-2">{{ number_format($twmTime, 2) }} ms</p>
                </div>
                <div class="bg-purple-50 rounded-xl p-6 text-center">
                    <p class="text-sm font-medium text-purple-600 uppercase tracking-wide">TailwindMergeOnce</p>
                    <p class="text-3xl font-bold text-purple-900 mt-2">{{ number_format($onceTime, 2) }} ms</p>
                </div>
                <div class="bg-green-50 rounded-xl p-6 text-center">
                    <p class="text-sm font-medium text-green-600 uppercase tracking-wide">TailwindMergeBoost</p>
                    <p class="text-3xl font-bold text-green-900 mt-2">{{ number_format($boostTime, 2) }} ms</p>
                </div>
                <div class="{{ $onceSpeedup > 1 ? 'bg-purple-50' : 'bg-yellow-50' }} rounded-xl p-6 text-center">
                    <p class="text-sm font-medium {{ $onceSpeedup > 1 ? 'text-purple-600' : 'text-yellow-600' }} uppercase tracking-wide">Once Speedup</p>
                    <p class="text-3xl font-bold {{ $onceSpeedup > 1 ? 'text-purple-900' : 'text-yellow-900' }} mt-2">
                        {{ number_format($onceSpeedup, 2) }}x
                        @if($onceSpeedup > 1)
                            <span class="text-lg">⚡</span>
                        @endif
                    </p>
                </div>
                <div class="{{ $boostSpeedup > 1 ? 'bg-emerald-50' : 'bg-yellow-50' }} rounded-xl p-6 text-center">
                    <p class="text-sm font-medium {{ $boostSpeedup > 1 ? 'text-emerald-600' : 'text-yellow-600' }} uppercase tracking-wide">Boost Speedup</p>
                    <p class="text-3xl font-bold {{ $boostSpeedup > 1 ? 'text-emerald-900' : 'text-yellow-900' }} mt-2">
                        {{ number_format($boostSpeedup, 2) }}x
                        @if($boostSpeedup > 1)
                            <span class="text-lg">⚡</span>
                        @endif
                    </p>
                </div>
                <div class="bg-indigo-50 rounded-xl p-6 text-center">
                    <p class="text-sm font-medium text-indigo-600 uppercase tracking-wide">Total Renders</p>
                    <p class="text-3xl font-bold text-indigo-900 mt-2">{{ number_format($totalComponents) }}</p>
                </div>
            </div>
        </div>

        <!-- Cache Statistics -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Cache Statistics</h2>
            <p class="text-gray-600 mb-4">Each component variant is rendered {{ number_format($repeatsPerRender) }} times to demonstrate memoization benefits.</p>
            
            <!-- Cache Type Legend -->
            <div class="bg-gray-100 rounded-lg p-4 mb-6">
                <h4 class="font-semibold text-gray-700 mb-2">Cache Types:</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="flex items-start gap-2">
                        <span class="inline-block w-3 h-3 rounded-full bg-blue-500 mt-1"></span>
                        <div>
                            <span class="font-medium text-blue-700">Persistent Cache (TailwindMerge)</span>
                            <p class="text-gray-600">Uses Laravel cache store (database/file/Redis). Cache persists across requests but has I/O overhead.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="inline-block w-3 h-3 rounded-full bg-purple-500 mt-1"></span>
                        <div>
                            <span class="font-medium text-purple-700">In-Memory Cache (Once/Boost)</span>
                            <p class="text-gray-600">Pure PHP memory. No I/O overhead, but resets each request.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-gray-50 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Total Renders</h3>
                    <p class="text-3xl font-bold text-gray-900">{{ number_format($totalCacheStats['totalRenders']) }}</p>
                </div>
                <div class="bg-blue-50 rounded-xl p-6 border-2 border-blue-200">
                    <h3 class="text-lg font-semibold text-blue-700 mb-2">TailwindMerge</h3>
                    <p class="text-xs text-blue-500 mb-3">📦 Persistent Cache (DB/File/Redis)</p>
                    <div class="space-y-2">
                        <p class="text-sm text-blue-600">Merge calls: <span class="font-bold">{{ number_format($totalCacheStats['twmMergeCalls']) }}</span></p>
                        <p class="text-sm text-blue-600">Cache stores: <span class="font-bold text-orange-600">{{ number_format($totalCacheStats['twmCacheStores']) }}</span></p>
                        <p class="text-sm text-blue-600">Cache hits: <span class="font-bold text-blue-800">{{ number_format($totalCacheStats['twmCacheHits']) }}</span></p>
                        <p class="text-sm text-blue-800 font-semibold">Hit rate: {{ number_format(($totalCacheStats['twmCacheHits'] / max($totalCacheStats['twmMergeCalls'], 1)) * 100, 1) }}%</p>
                    </div>
                </div>
                <div class="bg-purple-50 rounded-xl p-6 border-2 border-purple-200">
                    <h3 class="text-lg font-semibold text-purple-700 mb-2">TailwindMergeOnce</h3>
                    <p class="text-xs text-purple-500 mb-3">💨 In-Memory (once() helper)</p>
                    <div class="space-y-2">
                        <p class="text-sm text-purple-600">Merge calls: <span class="font-bold">{{ number_format($totalCacheStats['onceMergeCalls']) }}</span></p>
                        <p class="text-sm text-purple-600">Actual processing: <span class="font-bold text-orange-600">{{ number_format($totalCacheStats['onceActualCalls']) }}</span></p>
                        <p class="text-sm text-purple-600">Cache hits: <span class="font-bold text-purple-800">{{ number_format($totalCacheStats['onceCacheHits']) }}</span></p>
                        <p class="text-sm text-purple-800 font-semibold">Hit rate: {{ number_format(($totalCacheStats['onceCacheHits'] / max($totalCacheStats['onceMergeCalls'], 1)) * 100, 1) }}%</p>
                    </div>
                </div>
                <div class="bg-green-50 rounded-xl p-6 border-2 border-green-200">
                    <h3 class="text-lg font-semibold text-green-700 mb-2">TailwindMergeBoost</h3>
                    <p class="text-xs text-green-500 mb-3">💨 In-Memory (array cache)</p>
                    <div class="space-y-2">
                        <p class="text-sm text-green-600">Merge calls: <span class="font-bold">{{ number_format($totalCacheStats['boostMergeCalls']) }}</span></p>
                        <p class="text-sm text-green-600">Cache stores: <span class="font-bold text-orange-600">{{ number_format($totalCacheStats['boostCacheStores']) }}</span></p>
                        <p class="text-sm text-green-600">Cache hits: <span class="font-bold text-green-800">{{ number_format($totalCacheStats['boostCacheHits']) }}</span></p>
                        <p class="text-sm text-green-800 font-semibold">Hit rate: {{ number_format(($totalCacheStats['boostCacheHits'] / max($totalCacheStats['boostMergeCalls'], 1)) * 100, 1) }}%</p>
                    </div>
                </div>
            </div>
            
            <!-- Key Insight -->
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                <h4 class="font-semibold text-amber-800 mb-2">💡 Key Insight: Cache Store Calls</h4>
                <p class="text-sm text-amber-700">
                    <strong>TailwindMerge</strong> makes <strong class="text-orange-600">{{ number_format($totalCacheStats['twmCacheStores']) }}</strong> cache store calls (database/file I/O).
                    <strong>TailwindMergeOnce</strong> makes <strong class="text-green-600">0</strong> external cache calls (pure in-memory).
                    <strong>TailwindMergeBoost</strong> makes <strong class="text-green-600">0</strong> external cache calls (pure in-memory).
                </p>
            </div>
        </div>

        <!-- Per-Component Breakdown -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Per-Component Performance ({{ $variantsPerComponent }} variants × {{ $repeatsPerRender }} repeats)</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b-2 border-gray-200">
                            <th class="text-left py-4 px-4 font-semibold text-gray-700">Component</th>
                            <th class="text-center py-4 px-4 font-semibold text-gray-700">Variants</th>
                            <th class="text-right py-4 px-4 font-semibold text-gray-700">TailwindMerge</th>
                            <th class="text-right py-4 px-4 font-semibold text-gray-700">TailwindMergeOnce</th>
                            <th class="text-right py-4 px-4 font-semibold text-gray-700">TailwindMergeBoost</th>
                            <th class="text-center py-4 px-4 font-semibold text-gray-700">Winner</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($componentResults as $name => $result)
                        @php
                            $fastest = min($result['twm'], $result['once'], $result['boost']);
                            $winner = match($fastest) {
                                $result['boost'] => 'boost',
                                $result['once'] => 'once',
                                default => 'twm',
                            };
                        @endphp
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4 px-4 font-medium text-gray-900">{{ ucfirst($name) }}</td>
                            <td class="py-4 px-4 text-center text-gray-600">{{ $result['variants'] }}</td>
                            <td class="py-4 px-4 text-right text-gray-600">{{ number_format($result['twm'], 3) }} ms</td>
                            <td class="py-4 px-4 text-right font-semibold {{ $result['onceSpeedup'] > 1 ? 'text-purple-600' : 'text-yellow-600' }}">
                                {{ number_format($result['once'], 3) }} ms ({{ number_format($result['onceSpeedup'], 2) }}x)
                            </td>
                            <td class="py-4 px-4 text-right font-semibold {{ $result['boostSpeedup'] > 1 ? 'text-green-600' : 'text-yellow-600' }}">
                                {{ number_format($result['boost'], 3) }} ms ({{ number_format($result['boostSpeedup'], 2) }}x)
                            </td>
                            <td class="py-4 px-4 text-center">
                                @if($winner === 'boost')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        ⚡ Boost
                                    </span>
                                @elseif($winner === 'once')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                        ⚡ Once
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

        <!-- Side by Side Comparison with All Variants -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-2">Component Comparison</h2>
            <p class="text-gray-600 mb-6">All 25 components with 10 class variants each (simple to complex)</p>
            
            @foreach($components as $componentName => $componentData)
            <div class="mb-12 border-b border-gray-200 pb-8 last:border-b-0 last:pb-0">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                    {{ ucfirst($componentName) }}
                    <span class="text-sm font-normal text-gray-500">({{ count($componentData['variants']) }} variants)</span>
                </h3>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- TailwindMerge Column -->
                    <div class="border-2 border-blue-200 rounded-xl p-4 bg-blue-50/50">
                        <h4 class="text-lg font-bold text-blue-800 mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                            TailwindMerge
                        </h4>
                        <div class="space-y-3">
                            @foreach($componentData['variants'] as $variant)
                            <div class="bg-white rounded-lg p-3 shadow-sm">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-xs font-bold text-blue-600 bg-blue-100 px-2 py-0.5 rounded">V{{ $variant['variant'] }}</span>
                                    <code class="text-xs text-gray-500 truncate flex-1" title="{{ $variant['class'] }}">{{ Str::limit($variant['class'], 40) }}</code>
                                </div>
                                <div class="p-2 bg-gray-50 rounded border component-preview">
                                    {!! $variant['twm'] !!}
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- TailwindMergeOnce Column -->
                    <div class="border-2 border-purple-200 rounded-xl p-4 bg-purple-50/50">
                        <h4 class="text-lg font-bold text-purple-800 mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-purple-600"></span>
                            TailwindMergeOnce
                        </h4>
                        <div class="space-y-3">
                            @foreach($componentData['variants'] as $variant)
                            <div class="bg-white rounded-lg p-3 shadow-sm">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-xs font-bold text-purple-600 bg-purple-100 px-2 py-0.5 rounded">V{{ $variant['variant'] }}</span>
                                    <code class="text-xs text-gray-500 truncate flex-1" title="{{ $variant['class'] }}">{{ Str::limit($variant['class'], 40) }}</code>
                                </div>
                                <div class="p-2 bg-gray-50 rounded border component-preview">
                                    {!! $variant['once'] !!}
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- TailwindMergeBoost Column -->
                    <div class="border-2 border-green-200 rounded-xl p-4 bg-green-50/50">
                        <h4 class="text-lg font-bold text-green-800 mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-green-600"></span>
                            TailwindMergeBoost
                        </h4>
                        <div class="space-y-3">
                            @foreach($componentData['variants'] as $variant)
                            <div class="bg-white rounded-lg p-3 shadow-sm">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-xs font-bold text-green-600 bg-green-100 px-2 py-0.5 rounded">V{{ $variant['variant'] }}</span>
                                    <code class="text-xs text-gray-500 truncate flex-1" title="{{ $variant['class'] }}">{{ Str::limit($variant['class'], 40) }}</code>
                                </div>
                                <div class="p-2 bg-gray-50 rounded border component-preview">
                                    {!! $variant['boost'] !!}
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Performance Visualization -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Performance Visualization</h2>
            <div class="space-y-4">
                @foreach($componentResults as $name => $result)
                @php
                    $maxTime = max($result['twm'], $result['once'], $result['boost']);
                    $twmWidth = ($result['twm'] / max($maxTime, 0.001)) * 100;
                    $onceWidth = ($result['once'] / max($maxTime, 0.001)) * 100;
                    $boostWidth = ($result['boost'] / max($maxTime, 0.001)) * 100;
                    $fastest = min($result['twm'], $result['once'], $result['boost']);
                    $fastestSpeedup = $result['twm'] / max($fastest, 0.001);
                @endphp
                <div class="mb-4">
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-medium text-gray-700 text-sm">{{ ucfirst($name) }}</span>
                        <span class="text-xs text-gray-500">Best: {{ number_format($fastestSpeedup, 1) }}x speedup</span>
                    </div>
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="w-10 text-xs text-gray-400">TW</span>
                            <div class="flex-1 bg-gray-200 rounded-full h-4 overflow-hidden">
                                <div class="bg-blue-500 h-full rounded-full transition-all duration-500" style="width: {{ $twmWidth }}%"></div>
                            </div>
                            <span class="w-16 text-xs text-gray-500 text-right">{{ number_format($result['twm'], 2) }}ms</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-10 text-xs text-gray-400">Once</span>
                            <div class="flex-1 bg-gray-200 rounded-full h-4 overflow-hidden">
                                <div class="bg-purple-500 h-full rounded-full transition-all duration-500" style="width: {{ $onceWidth }}%"></div>
                            </div>
                            <span class="w-16 text-xs text-gray-500 text-right">{{ number_format($result['once'], 2) }}ms</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-10 text-xs text-gray-400">Boost</span>
                            <div class="flex-1 bg-gray-200 rounded-full h-4 overflow-hidden">
                                <div class="bg-green-500 h-full rounded-full transition-all duration-500" style="width: {{ $boostWidth }}%"></div>
                            </div>
                            <span class="w-16 text-xs text-gray-500 text-right">{{ number_format($result['boost'], 2) }}ms</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-gray-500">
            <p>Run <code class="bg-gray-200 px-2 py-1 rounded text-sm">php artisan benchmark:components</code> for CLI benchmark</p>
            <p class="mt-2">
                <a href="/benchmark" class="text-indigo-600 hover:underline">← Back to merge benchmark</a>
            </p>
        </div>
    </div>
</body>
</html>
