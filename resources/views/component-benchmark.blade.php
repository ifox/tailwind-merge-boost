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
                25 components × {{ $variantsPerComponent }} variants × {{ $iterations }} iterations
            </p>
        </div>

        <!-- Performance Summary -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Performance Summary</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-blue-50 rounded-xl p-6 text-center">
                    <p class="text-sm font-medium text-blue-600 uppercase tracking-wide">TailwindMerge</p>
                    <p class="text-3xl font-bold text-blue-900 mt-2">{{ number_format($twmTime, 2) }} ms</p>
                </div>
                <div class="bg-green-50 rounded-xl p-6 text-center">
                    <p class="text-sm font-medium text-green-600 uppercase tracking-wide">TailwindMergeBoost</p>
                    <p class="text-3xl font-bold text-green-900 mt-2">{{ number_format($boostTime, 2) }} ms</p>
                </div>
                <div class="{{ $speedup > 1 ? 'bg-emerald-50' : 'bg-yellow-50' }} rounded-xl p-6 text-center">
                    <p class="text-sm font-medium {{ $speedup > 1 ? 'text-emerald-600' : 'text-yellow-600' }} uppercase tracking-wide">Speedup</p>
                    <p class="text-3xl font-bold {{ $speedup > 1 ? 'text-emerald-900' : 'text-yellow-900' }} mt-2">
                        {{ number_format($speedup, 2) }}x
                        @if($speedup > 1)
                            <span class="text-lg">⚡</span>
                        @endif
                    </p>
                </div>
                <div class="bg-purple-50 rounded-xl p-6 text-center">
                    <p class="text-sm font-medium text-purple-600 uppercase tracking-wide">Total Renders</p>
                    <p class="text-3xl font-bold text-purple-900 mt-2">{{ number_format($totalComponents) }}</p>
                </div>
            </div>
        </div>

        <!-- Per-Component Breakdown -->
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Per-Component Performance ({{ $variantsPerComponent }} variants each)</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b-2 border-gray-200">
                            <th class="text-left py-4 px-4 font-semibold text-gray-700">Component</th>
                            <th class="text-center py-4 px-4 font-semibold text-gray-700">Variants</th>
                            <th class="text-right py-4 px-4 font-semibold text-gray-700">TailwindMerge</th>
                            <th class="text-right py-4 px-4 font-semibold text-gray-700">TailwindMergeBoost</th>
                            <th class="text-right py-4 px-4 font-semibold text-gray-700">Speedup</th>
                            <th class="text-center py-4 px-4 font-semibold text-gray-700">Winner</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($componentResults as $name => $result)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-4 px-4 font-medium text-gray-900">{{ ucfirst($name) }}</td>
                            <td class="py-4 px-4 text-center text-gray-600">{{ $result['variants'] }}</td>
                            <td class="py-4 px-4 text-right text-gray-600">{{ number_format($result['twm'], 3) }} ms</td>
                            <td class="py-4 px-4 text-right text-gray-600">{{ number_format($result['boost'], 3) }} ms</td>
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
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
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
                                    <code class="text-xs text-gray-500 truncate flex-1" title="{{ $variant['class'] }}">{{ Str::limit($variant['class'], 60) }}</code>
                                </div>
                                <div class="p-2 bg-gray-50 rounded border component-preview">
                                    {!! $variant['twm'] !!}
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
                                    <code class="text-xs text-gray-500 truncate flex-1" title="{{ $variant['class'] }}">{{ Str::limit($variant['class'], 60) }}</code>
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
                    $maxTime = max($result['twm'], $result['boost']);
                    $twmWidth = ($result['twm'] / max($maxTime, 0.001)) * 100;
                    $boostWidth = ($result['boost'] / max($maxTime, 0.001)) * 100;
                @endphp
                <div class="mb-4">
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-medium text-gray-700 text-sm">{{ ucfirst($name) }}</span>
                        <span class="text-xs text-gray-500">{{ number_format($result['speedup'], 1) }}x speedup</span>
                    </div>
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="w-8 text-xs text-gray-400">TW</span>
                            <div class="flex-1 bg-gray-200 rounded-full h-4 overflow-hidden">
                                <div class="bg-blue-500 h-full rounded-full transition-all duration-500" style="width: {{ $twmWidth }}%"></div>
                            </div>
                            <span class="w-16 text-xs text-gray-500 text-right">{{ number_format($result['twm'], 2) }}ms</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-8 text-xs text-gray-400">Boost</span>
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
