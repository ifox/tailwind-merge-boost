<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Configuration for benchmark components with their class overrides.
 */
class BenchmarkComponentConfig
{
    /**
     * Get the component configurations for benchmarking.
     *
     * @return array<string, array<string, string>>
     */
    public static function getConfigs(): array
    {
        return [
            'button' => ['class' => 'bg-red-500 px-6 py-3'],
            'card' => ['class' => 'shadow-2xl p-8 bg-gray-50'],
            'badge' => ['class' => 'bg-blue-100 text-blue-800 px-4'],
            'input' => ['class' => 'border-red-500 focus:ring-red-500'],
            'alert' => ['class' => 'bg-red-50 border-red-400 p-6'],
            'avatar' => ['class' => 'w-16 h-16 bg-indigo-500 text-white'],
            'list-item' => ['class' => 'bg-white p-6 border-indigo-200'],
            'progress' => ['class' => 'h-4 bg-gray-300'],
            'checkbox' => ['class' => 'h-6 w-6'],
            'toggle' => ['class' => 'bg-indigo-600 h-8 w-14'],
            'label' => ['class' => 'text-lg font-bold text-gray-900'],
            'textarea' => ['class' => 'min-h-[200px] border-gray-400'],
            'select' => ['class' => 'border-2 border-indigo-500'],
            'divider' => ['class' => 'my-8 bg-gray-400 h-0.5'],
            'spinner' => ['class' => 'h-12 w-12 border-red-600'],
            'modal' => ['class' => 'bg-black bg-opacity-50'],
            'tabs' => ['class' => 'space-x-8 border-gray-300'],
            'tab' => ['class' => 'py-4 px-6 text-indigo-600 border-indigo-600'],
            'list' => ['class' => 'divide-gray-300 rounded-xl'],
            'breadcrumb' => ['class' => 'text-gray-800 gap-4'],
            'tag' => ['class' => 'bg-indigo-100 text-indigo-700 px-4 py-2'],
            'tooltip' => ['class' => 'shadow-2xl p-6 rounded-2xl'],
            'dropdown' => ['class' => 'w-72 mt-4 shadow-2xl'],
            'heading' => ['class' => 'text-4xl text-indigo-900 font-black'],
            'text' => ['class' => 'text-lg text-gray-800 leading-loose'],
        ];
    }

    /**
     * Get the number of components.
     */
    public static function getCount(): int
    {
        return count(self::getConfigs());
    }
}
