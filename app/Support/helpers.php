<?php

if (! function_exists('getSubjectColor')) {
    /**
     * Returns a consistent color palette array for a given subject name.
     * Uses crc32 hash to deterministically assign a color set.
     */
    function getSubjectColor(string $name): array
    {
        $colors = [
            ['bg' => 'bg-indigo-50',  'border' => 'border-indigo-100', 'text' => 'text-indigo-700', 'sub' => 'text-indigo-500'],
            ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-100', 'text' => 'text-emerald-700', 'sub' => 'text-emerald-500'],
            ['bg' => 'bg-amber-50',   'border' => 'border-amber-100',  'text' => 'text-amber-700',  'sub' => 'text-amber-500'],
            ['bg' => 'bg-rose-50',    'border' => 'border-rose-100',   'text' => 'text-rose-700',   'sub' => 'text-rose-500'],
            ['bg' => 'bg-sky-50',     'border' => 'border-sky-100',    'text' => 'text-sky-700',    'sub' => 'text-sky-500'],
            ['bg' => 'bg-violet-50',  'border' => 'border-violet-100', 'text' => 'text-violet-700', 'sub' => 'text-violet-500'],
        ];

        $hash = crc32($name);

        return $colors[abs($hash) % count($colors)];
    }
}
