<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Configuration for benchmark components with their class variants.
 * Each component has 10 variants ranging from simple to complex.
 */
class BenchmarkComponentConfig
{
    /**
     * Get the component configurations with 10 variants each for benchmarking.
     *
     * @return array<string, array<int, array<string, string>>>
     */
    public static function getConfigs(): array
    {
        return [
            'button' => [
                ['class' => 'bg-red-500'],
                ['class' => 'bg-blue-600 text-white'],
                ['class' => 'px-8 py-4 bg-green-500'],
                ['class' => 'rounded-full bg-purple-600 text-white font-bold'],
                ['class' => 'hover:bg-red-600 bg-red-500 text-white px-6'],
                ['class' => 'focus:ring-4 focus:ring-blue-300 bg-blue-500 hover:bg-blue-600'],
                ['class' => 'disabled:opacity-50 disabled:cursor-not-allowed bg-gray-500 text-white'],
                ['class' => 'transform hover:scale-105 transition-all duration-300 bg-indigo-600'],
                ['class' => 'shadow-lg hover:shadow-xl bg-gradient-to-r from-purple-500 to-pink-500 text-white px-8 py-3 rounded-lg'],
                ['class' => 'md:px-8 md:py-4 lg:text-lg bg-emerald-500 hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2 transition-colors'],
            ],
            'card' => [
                ['class' => 'shadow-md'],
                ['class' => 'shadow-lg p-6'],
                ['class' => 'bg-white rounded-lg shadow-xl'],
                ['class' => 'border border-gray-200 rounded-xl p-8'],
                ['class' => 'hover:shadow-2xl transition-shadow bg-gray-50'],
                ['class' => 'dark:bg-gray-800 dark:border-gray-700 bg-white p-6'],
                ['class' => 'overflow-hidden rounded-2xl shadow-lg border-2 border-indigo-100'],
                ['class' => 'backdrop-blur-sm bg-white/90 shadow-xl rounded-xl p-8 border'],
                ['class' => 'group hover:border-indigo-500 border-2 border-transparent transition-all duration-300 p-6 rounded-xl'],
                ['class' => 'md:p-8 lg:p-10 bg-gradient-to-br from-white to-gray-50 shadow-lg hover:shadow-xl rounded-2xl border border-gray-100 transition-all duration-300'],
            ],
            'badge' => [
                ['class' => 'bg-red-100'],
                ['class' => 'bg-green-100 text-green-800'],
                ['class' => 'px-3 py-1 bg-blue-100 text-blue-700'],
                ['class' => 'rounded-full bg-yellow-100 text-yellow-800 font-medium'],
                ['class' => 'text-xs uppercase tracking-wide bg-purple-100 text-purple-700'],
                ['class' => 'animate-pulse bg-red-500 text-white px-2 py-1 rounded'],
                ['class' => 'border border-green-500 bg-green-50 text-green-700 font-semibold'],
                ['class' => 'shadow-sm hover:shadow bg-indigo-100 text-indigo-700 px-4 py-1 transition'],
                ['class' => 'inline-flex items-center gap-1 bg-gradient-to-r from-blue-500 to-purple-500 text-white px-3 py-1 rounded-full'],
                ['class' => 'md:text-sm lg:px-4 bg-emerald-100 text-emerald-800 font-medium tracking-tight rounded-lg border border-emerald-200 hover:bg-emerald-200 transition-colors'],
            ],
            'input' => [
                ['class' => 'border-gray-400'],
                ['class' => 'border-2 border-blue-500'],
                ['class' => 'rounded-lg px-4 py-3 border'],
                ['class' => 'focus:border-indigo-500 focus:ring-indigo-500 border'],
                ['class' => 'bg-gray-50 border-gray-300 text-gray-900 rounded-xl'],
                ['class' => 'disabled:bg-gray-100 disabled:cursor-not-allowed border px-4'],
                ['class' => 'placeholder:text-gray-400 border-2 focus:border-blue-500 rounded-lg'],
                ['class' => 'shadow-inner bg-white border-gray-300 focus:ring-2 focus:ring-blue-500 px-4 py-3'],
                ['class' => 'dark:bg-gray-700 dark:border-gray-600 dark:text-white border rounded-xl focus:outline-none focus:ring-2'],
                ['class' => 'md:text-lg lg:px-6 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all placeholder:text-gray-500'],
            ],
            'alert' => [
                ['class' => 'bg-red-100'],
                ['class' => 'bg-yellow-50 border-yellow-500'],
                ['class' => 'p-4 rounded-lg bg-green-100 text-green-800'],
                ['class' => 'border-l-4 border-blue-500 bg-blue-50 p-4'],
                ['class' => 'shadow-md rounded-xl bg-orange-100 text-orange-800 p-6'],
                ['class' => 'flex items-center gap-3 bg-red-50 border border-red-200 p-4'],
                ['class' => 'animate-pulse bg-yellow-100 border-2 border-yellow-400 rounded-lg p-4'],
                ['class' => 'backdrop-blur bg-white/80 shadow-lg rounded-xl border-l-4 border-indigo-500 p-6'],
                ['class' => 'dark:bg-gray-800 dark:text-white bg-gray-100 text-gray-800 p-6 rounded-xl border'],
                ['class' => 'md:p-6 lg:p-8 bg-gradient-to-r from-red-50 to-orange-50 border-l-4 border-red-500 rounded-r-xl shadow-lg text-red-800 font-medium'],
            ],
            'avatar' => [
                ['class' => 'w-8 h-8'],
                ['class' => 'w-12 h-12 rounded-full'],
                ['class' => 'w-16 h-16 bg-blue-500 text-white'],
                ['class' => 'ring-2 ring-white w-10 h-10 rounded-full'],
                ['class' => 'shadow-lg w-20 h-20 rounded-full bg-gradient-to-r from-pink-500 to-purple-500'],
                ['class' => 'border-4 border-white shadow-xl w-24 h-24 rounded-full'],
                ['class' => 'hover:scale-110 transition-transform w-16 h-16 rounded-full bg-indigo-600'],
                ['class' => 'ring-4 ring-indigo-100 w-14 h-14 rounded-full bg-indigo-500 text-white font-bold'],
                ['class' => 'group-hover:ring-indigo-500 ring-2 ring-transparent transition-all w-12 h-12 rounded-full'],
                ['class' => 'md:w-16 md:h-16 lg:w-20 lg:h-20 bg-gradient-to-br from-emerald-400 to-cyan-500 rounded-full shadow-lg ring-4 ring-white text-white font-bold'],
            ],
            'list-item' => [
                ['class' => 'p-2'],
                ['class' => 'p-4 bg-white'],
                ['class' => 'border-b p-4 hover:bg-gray-50'],
                ['class' => 'flex items-center gap-4 p-6 bg-gray-50'],
                ['class' => 'rounded-lg shadow-sm p-4 mb-2 bg-white border'],
                ['class' => 'hover:bg-indigo-50 transition-colors p-4 border-l-4 border-transparent hover:border-indigo-500'],
                ['class' => 'group cursor-pointer p-6 bg-white rounded-xl shadow hover:shadow-lg transition-all'],
                ['class' => 'flex justify-between items-center p-4 bg-gradient-to-r from-white to-gray-50 rounded-lg'],
                ['class' => 'dark:bg-gray-800 dark:hover:bg-gray-700 bg-white hover:bg-gray-50 p-4 transition-colors border-b'],
                ['class' => 'md:p-6 lg:p-8 flex items-center gap-6 bg-white rounded-xl shadow-md hover:shadow-xl border border-gray-100 transition-all duration-300 cursor-pointer group'],
            ],
            'progress' => [
                ['class' => 'h-1'],
                ['class' => 'h-2 rounded-full'],
                ['class' => 'h-3 bg-gray-300 rounded-full'],
                ['class' => 'h-4 bg-blue-100 rounded-full overflow-hidden'],
                ['class' => 'h-2 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full'],
                ['class' => 'h-6 bg-gray-200 rounded-xl shadow-inner overflow-hidden'],
                ['class' => 'h-3 bg-emerald-100 rounded-full [&>div]:bg-emerald-500 [&>div]:rounded-full'],
                ['class' => 'h-4 bg-gray-200 rounded-full shadow overflow-hidden [&>div]:transition-all [&>div]:duration-500'],
                ['class' => 'h-5 bg-gradient-to-r from-gray-200 to-gray-300 rounded-full overflow-hidden shadow-inner'],
                ['class' => 'md:h-4 lg:h-6 bg-gray-100 rounded-full shadow-inner overflow-hidden [&>div]:bg-gradient-to-r [&>div]:from-indigo-500 [&>div]:to-purple-600 [&>div]:rounded-full'],
            ],
            'checkbox' => [
                ['class' => 'h-4 w-4'],
                ['class' => 'h-5 w-5 rounded'],
                ['class' => 'h-6 w-6 text-blue-600 rounded'],
                ['class' => 'h-5 w-5 border-2 border-gray-300 rounded-md'],
                ['class' => 'h-6 w-6 text-indigo-600 focus:ring-indigo-500 rounded-lg'],
                ['class' => 'h-5 w-5 text-emerald-500 border-emerald-300 rounded focus:ring-emerald-200'],
                ['class' => 'h-6 w-6 checked:bg-purple-600 border-2 border-gray-300 rounded-md focus:ring-2'],
                ['class' => 'h-5 w-5 accent-pink-500 rounded border-gray-400 focus:ring-pink-300 focus:ring-offset-2'],
                ['class' => 'h-6 w-6 text-indigo-600 bg-gray-100 border-gray-300 rounded-lg focus:ring-indigo-500 focus:ring-2'],
                ['class' => 'md:h-5 md:w-5 lg:h-6 lg:w-6 text-blue-600 bg-gray-50 border-2 border-gray-300 rounded-lg focus:ring-4 focus:ring-blue-100 transition-all cursor-pointer'],
            ],
            'toggle' => [
                ['class' => 'w-10 h-5'],
                ['class' => 'w-12 h-6 bg-gray-300'],
                ['class' => 'w-14 h-7 rounded-full bg-gray-200'],
                ['class' => 'w-12 h-6 bg-indigo-600 rounded-full cursor-pointer'],
                ['class' => 'w-16 h-8 bg-gray-300 rounded-full transition-colors duration-200'],
                ['class' => 'w-14 h-7 bg-emerald-500 rounded-full shadow-inner focus:ring-2 focus:ring-emerald-300'],
                ['class' => 'w-12 h-6 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full cursor-pointer'],
                ['class' => 'w-16 h-8 bg-gray-200 rounded-full relative [&>span]:bg-white [&>span]:shadow-lg [&>span]:rounded-full'],
                ['class' => 'w-14 h-7 peer-checked:bg-blue-600 bg-gray-300 rounded-full transition-all duration-300 cursor-pointer'],
                ['class' => 'md:w-14 md:h-7 lg:w-16 lg:h-8 bg-gray-200 rounded-full shadow-inner cursor-pointer focus:outline-none focus:ring-4 focus:ring-indigo-100 transition-all duration-300'],
            ],
            'label' => [
                ['class' => 'font-medium'],
                ['class' => 'text-sm font-semibold'],
                ['class' => 'text-gray-700 font-medium block'],
                ['class' => 'text-sm text-gray-600 uppercase tracking-wide'],
                ['class' => 'font-bold text-gray-800 mb-2 block'],
                ['class' => 'text-xs font-medium text-gray-500 uppercase tracking-wider'],
                ['class' => 'flex items-center gap-2 text-gray-700 font-medium cursor-pointer'],
                ['class' => 'text-sm font-semibold text-indigo-700 mb-1 block required:after:content-["*"] required:after:text-red-500'],
                ['class' => 'peer-disabled:opacity-50 peer-disabled:cursor-not-allowed text-gray-700 font-medium transition-opacity'],
                ['class' => 'md:text-base lg:text-lg font-bold text-gray-900 tracking-tight mb-2 block after:content-[""] after:ml-0.5 after:text-red-500'],
            ],
            'textarea' => [
                ['class' => 'border'],
                ['class' => 'border rounded-lg'],
                ['class' => 'min-h-[100px] border rounded-lg p-3'],
                ['class' => 'resize-none border-2 rounded-xl p-4 min-h-[120px]'],
                ['class' => 'focus:ring-2 focus:ring-blue-500 border rounded-lg p-4 min-h-[150px]'],
                ['class' => 'bg-gray-50 border-gray-300 rounded-xl p-4 resize-y min-h-[100px] max-h-[300px]'],
                ['class' => 'placeholder:text-gray-400 border-2 focus:border-indigo-500 rounded-xl p-4 min-h-[120px]'],
                ['class' => 'shadow-inner bg-white border-gray-200 rounded-xl p-4 min-h-[150px] focus:ring-4 focus:ring-indigo-100'],
                ['class' => 'dark:bg-gray-700 dark:border-gray-600 dark:text-white border rounded-xl p-4 min-h-[120px] focus:outline-none focus:ring-2'],
                ['class' => 'md:min-h-[150px] lg:min-h-[200px] bg-gray-50 border-2 border-gray-200 rounded-xl p-4 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all resize-y'],
            ],
            'select' => [
                ['class' => 'border'],
                ['class' => 'border rounded-lg'],
                ['class' => 'px-4 py-2 border rounded-lg bg-white'],
                ['class' => 'border-2 border-gray-300 rounded-xl px-4 py-3'],
                ['class' => 'focus:ring-2 focus:ring-blue-500 border rounded-lg px-4 py-2'],
                ['class' => 'bg-gray-50 border-gray-300 rounded-xl px-4 py-3 cursor-pointer'],
                ['class' => 'appearance-none bg-white border-2 rounded-xl px-4 py-3 pr-10 cursor-pointer'],
                ['class' => 'shadow-sm bg-white border-gray-200 rounded-xl px-4 py-3 focus:ring-4 focus:ring-indigo-100 cursor-pointer'],
                ['class' => 'dark:bg-gray-700 dark:border-gray-600 dark:text-white border rounded-xl px-4 py-3 focus:outline-none focus:ring-2'],
                ['class' => 'md:text-base lg:px-6 bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all cursor-pointer'],
            ],
            'divider' => [
                ['class' => 'my-2'],
                ['class' => 'my-4 border-gray-300'],
                ['class' => 'my-6 border-t-2 border-gray-200'],
                ['class' => 'my-8 border-dashed border-gray-300'],
                ['class' => 'my-4 h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent'],
                ['class' => 'my-6 border-t border-gray-200 dark:border-gray-700'],
                ['class' => 'my-8 h-0.5 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full'],
                ['class' => 'my-6 flex items-center [&>span]:px-4 [&>span]:text-gray-500 before:flex-1 before:border-t after:flex-1 after:border-t'],
                ['class' => 'my-8 border-t-2 border-dashed border-gray-300 opacity-50'],
                ['class' => 'md:my-8 lg:my-12 h-px bg-gradient-to-r from-transparent via-gray-400 to-transparent opacity-75'],
            ],
            'spinner' => [
                ['class' => 'h-4 w-4'],
                ['class' => 'h-6 w-6 border-blue-500'],
                ['class' => 'h-8 w-8 border-2 border-indigo-600'],
                ['class' => 'h-10 w-10 border-4 border-purple-500 border-t-transparent'],
                ['class' => 'h-6 w-6 border-2 border-gray-300 border-t-blue-500 animate-spin'],
                ['class' => 'h-8 w-8 border-4 border-emerald-200 border-t-emerald-600 rounded-full animate-spin'],
                ['class' => 'h-12 w-12 border-4 border-indigo-100 border-t-indigo-600 rounded-full animate-spin'],
                ['class' => 'h-10 w-10 border-[3px] border-gray-200 border-t-pink-500 rounded-full animate-spin'],
                ['class' => 'h-8 w-8 border-4 border-transparent border-t-blue-500 border-r-blue-500 rounded-full animate-spin'],
                ['class' => 'md:h-10 md:w-10 lg:h-12 lg:w-12 border-4 border-gray-200 border-t-indigo-600 rounded-full animate-spin shadow-lg'],
            ],
            'modal' => [
                ['class' => 'hidden bg-black/50'],
                ['class' => 'hidden bg-gray-900/75 backdrop-blur-sm'],
                ['class' => 'hidden fixed inset-0 bg-black/60 flex items-center justify-center'],
                ['class' => 'hidden bg-gray-900/80 backdrop-blur-md flex items-center justify-center p-4'],
                ['class' => 'hidden fixed inset-0 bg-gradient-to-br from-black/70 to-gray-900/70 flex items-center justify-center'],
                ['class' => 'hidden bg-black/50 backdrop-blur-lg flex items-center justify-center transition-opacity duration-300'],
                ['class' => 'hidden fixed inset-0 bg-gray-900/90 flex items-center justify-center p-4 z-50 overflow-y-auto'],
                ['class' => 'hidden bg-gradient-to-t from-black/80 to-transparent flex items-end sm:items-center justify-center min-h-screen'],
                ['class' => 'hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 transition-all duration-300 ease-out'],
                ['class' => 'hidden md:p-6 lg:p-8 fixed inset-0 bg-gray-900/80 backdrop-blur-md flex items-center justify-center z-50 overflow-y-auto transition-all duration-300'],
            ],
            'tabs' => [
                ['class' => 'flex gap-2'],
                ['class' => 'flex gap-4 border-b'],
                ['class' => 'flex space-x-4 border-b-2 border-gray-200'],
                ['class' => 'inline-flex rounded-lg bg-gray-100 p-1 gap-1'],
                ['class' => 'flex gap-1 border-b border-gray-200 overflow-x-auto'],
                ['class' => 'flex flex-wrap gap-2 border-b-2 border-gray-100 pb-2'],
                ['class' => 'inline-flex p-1 bg-gray-100 rounded-xl gap-1 shadow-inner'],
                ['class' => 'flex gap-4 border-b-2 border-gray-200 overflow-x-auto scrollbar-hide'],
                ['class' => 'grid grid-cols-3 gap-2 p-1 bg-gray-100 rounded-xl md:inline-flex md:gap-1'],
                ['class' => 'md:gap-6 lg:gap-8 flex flex-wrap gap-4 border-b-2 border-gray-100 pb-4 overflow-x-auto'],
            ],
            'tab' => [
                ['class' => 'px-3 py-2'],
                ['class' => 'px-4 py-2 rounded-lg'],
                ['class' => 'px-6 py-3 font-medium text-gray-600'],
                ['class' => 'px-4 py-2 rounded-lg bg-white shadow-sm font-medium'],
                ['class' => 'px-6 py-3 border-b-2 border-transparent hover:border-indigo-500 transition-colors'],
                ['class' => 'px-4 py-2 rounded-lg font-medium text-gray-600 hover:bg-gray-100 transition-all'],
                ['class' => 'px-6 py-3 text-sm font-semibold uppercase tracking-wide border-b-2 border-transparent'],
                ['class' => 'px-4 py-2.5 rounded-xl font-medium text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition-all'],
                ['class' => 'flex-1 px-4 py-3 text-center font-medium rounded-lg bg-transparent hover:bg-white hover:shadow transition-all'],
                ['class' => 'md:px-6 lg:px-8 px-4 py-3 font-semibold text-gray-600 hover:text-indigo-600 border-b-2 border-transparent hover:border-indigo-500 transition-all duration-200'],
            ],
            'list' => [
                ['class' => 'space-y-2'],
                ['class' => 'divide-y divide-gray-200'],
                ['class' => 'space-y-3 bg-white rounded-lg'],
                ['class' => 'divide-y divide-gray-100 bg-white rounded-xl shadow'],
                ['class' => 'space-y-2 bg-gray-50 p-4 rounded-xl'],
                ['class' => 'divide-y divide-gray-200 bg-white rounded-xl shadow-lg overflow-hidden'],
                ['class' => 'grid gap-3 bg-white p-4 rounded-xl shadow-sm border border-gray-100'],
                ['class' => 'space-y-4 bg-gradient-to-b from-white to-gray-50 p-6 rounded-2xl shadow'],
                ['class' => 'divide-y divide-gray-100 bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden'],
                ['class' => 'md:space-y-4 lg:space-y-6 bg-white rounded-2xl shadow-xl p-6 divide-y divide-gray-100 border border-gray-50'],
            ],
            'breadcrumb' => [
                ['class' => 'flex gap-2'],
                ['class' => 'flex items-center gap-2 text-sm'],
                ['class' => 'flex items-center gap-3 text-gray-600'],
                ['class' => 'flex flex-wrap items-center gap-2 text-sm text-gray-500'],
                ['class' => 'inline-flex items-center gap-2 text-sm font-medium text-gray-600'],
                ['class' => 'flex items-center gap-3 text-sm text-gray-500 [&>a]:hover:text-indigo-600 [&>a]:transition-colors'],
                ['class' => 'flex flex-wrap items-center gap-2 py-2 text-sm [&>span]:text-gray-400'],
                ['class' => 'inline-flex items-center gap-3 px-4 py-2 bg-gray-50 rounded-lg text-sm text-gray-600'],
                ['class' => 'flex items-center gap-2 text-sm font-medium [&>a]:text-gray-600 [&>a]:hover:text-indigo-600 [&>span]:text-gray-400'],
                ['class' => 'md:gap-4 lg:text-base flex flex-wrap items-center gap-2 text-sm text-gray-600 [&>a]:hover:text-indigo-600 [&>a]:transition-colors [&>svg]:text-gray-400'],
            ],
            'tag' => [
                ['class' => 'px-2 py-1'],
                ['class' => 'px-3 py-1 rounded-full'],
                ['class' => 'px-3 py-1 bg-gray-100 rounded-lg text-sm'],
                ['class' => 'inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-700 rounded-full'],
                ['class' => 'px-4 py-1.5 bg-indigo-100 text-indigo-700 rounded-full text-sm font-medium'],
                ['class' => 'inline-flex items-center gap-2 px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors'],
                ['class' => 'px-3 py-1 bg-gradient-to-r from-purple-100 to-pink-100 text-purple-700 rounded-full font-medium'],
                ['class' => 'inline-flex items-center gap-1.5 px-4 py-1.5 bg-emerald-100 text-emerald-700 rounded-full text-sm font-semibold'],
                ['class' => 'group px-3 py-1 bg-gray-100 hover:bg-red-100 rounded-full transition-colors [&>button]:opacity-0 [&>button]:group-hover:opacity-100'],
                ['class' => 'md:px-4 lg:text-base inline-flex items-center gap-2 px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-xl font-medium border border-indigo-100 hover:bg-indigo-100 transition-all'],
            ],
            'tooltip' => [
                ['class' => 'p-2 bg-black text-white'],
                ['class' => 'px-3 py-2 bg-gray-900 text-white rounded-lg'],
                ['class' => 'px-4 py-2 bg-gray-800 text-white text-sm rounded-lg shadow-lg'],
                ['class' => 'px-4 py-3 bg-gray-900 text-white rounded-xl shadow-xl text-sm'],
                ['class' => 'px-4 py-2 bg-indigo-900 text-white rounded-lg shadow-lg font-medium'],
                ['class' => 'px-4 py-3 bg-gray-800 text-white rounded-xl shadow-2xl text-sm max-w-xs'],
                ['class' => 'px-5 py-3 bg-gradient-to-r from-gray-900 to-gray-800 text-white rounded-xl shadow-xl'],
                ['class' => 'px-4 py-3 bg-white text-gray-900 border border-gray-200 rounded-xl shadow-xl text-sm'],
                ['class' => 'px-4 py-3 bg-gray-900/95 backdrop-blur-sm text-white rounded-xl shadow-2xl text-sm font-medium'],
                ['class' => 'md:max-w-sm lg:max-w-md px-5 py-4 bg-gray-900 text-white rounded-2xl shadow-2xl text-sm leading-relaxed'],
            ],
            'dropdown' => [
                ['class' => 'bg-white shadow-lg'],
                ['class' => 'bg-white rounded-lg shadow-xl'],
                ['class' => 'w-48 bg-white rounded-xl shadow-xl border'],
                ['class' => 'w-56 bg-white rounded-xl shadow-2xl py-2 border border-gray-100'],
                ['class' => 'w-64 bg-white rounded-xl shadow-xl py-1 divide-y divide-gray-100'],
                ['class' => 'w-56 bg-white rounded-2xl shadow-2xl py-2 border border-gray-100 overflow-hidden'],
                ['class' => 'w-72 bg-white rounded-xl shadow-2xl py-2 max-h-96 overflow-y-auto border'],
                ['class' => 'w-64 bg-white/95 backdrop-blur-lg rounded-2xl shadow-2xl py-2 border border-gray-100'],
                ['class' => 'w-56 bg-gradient-to-b from-white to-gray-50 rounded-2xl shadow-2xl py-3 border border-gray-100'],
                ['class' => 'md:w-64 lg:w-72 bg-white rounded-2xl shadow-2xl py-3 divide-y divide-gray-100 border border-gray-100 overflow-hidden'],
            ],
            'heading' => [
                ['class' => 'font-bold'],
                ['class' => 'text-2xl font-bold'],
                ['class' => 'text-3xl font-bold text-gray-900'],
                ['class' => 'text-4xl font-extrabold tracking-tight text-gray-900'],
                ['class' => 'text-2xl font-bold text-gray-800 border-b pb-4'],
                ['class' => 'text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600'],
                ['class' => 'text-4xl font-extrabold text-gray-900 tracking-tight leading-tight'],
                ['class' => 'text-3xl font-bold text-gray-900 flex items-center gap-3 [&>span]:text-indigo-600'],
                ['class' => 'text-5xl font-black text-gray-900 tracking-tight leading-none mb-4'],
                ['class' => 'md:text-4xl lg:text-5xl text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 tracking-tight'],
            ],
            'text' => [
                ['class' => 'text-gray-600'],
                ['class' => 'text-base text-gray-700'],
                ['class' => 'text-lg text-gray-600 leading-relaxed'],
                ['class' => 'text-gray-700 leading-7 max-w-prose'],
                ['class' => 'text-lg text-gray-600 leading-relaxed tracking-wide'],
                ['class' => 'text-gray-700 leading-loose max-w-2xl mx-auto text-center'],
                ['class' => 'text-lg text-gray-600 leading-relaxed [&>strong]:font-semibold [&>strong]:text-gray-900'],
                ['class' => 'text-gray-700 leading-8 [&>a]:text-indigo-600 [&>a]:underline [&>a]:hover:text-indigo-800'],
                ['class' => 'prose prose-lg prose-gray max-w-none [&>p]:text-gray-600 [&>p]:leading-relaxed'],
                ['class' => 'md:text-lg lg:text-xl text-base text-gray-600 leading-relaxed tracking-wide max-w-3xl [&>strong]:text-gray-900 [&>a]:text-indigo-600'],
            ],
        ];
    }

    /**
     * Get the number of components.
     */
    public static function getCount(): int
    {
        return count(self::getConfigs());
    }

    /**
     * Get the number of variants per component.
     */
    public static function getVariantsPerComponent(): int
    {
        return 10;
    }

    /**
     * Get total number of test cases (components × variants).
     */
    public static function getTotalTestCases(): int
    {
        return self::getCount() * self::getVariantsPerComponent();
    }
}
