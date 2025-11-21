<?php

use Carbon\Carbon;

if (! function_exists('getFormattedDate')) {
    /**
     * Format a given date.
     *
     * - If the date is within the last 3 days → show "x hours ago".
     * - Otherwise → show as "d M Y, H:i".
     *
     * @param  string|\DateTime|null  $date
     */
    function getFormattedDate($date = null): string
    {
        if (! $date) {
            return '';
        }

        $created = Carbon::parse($date);

        return $created->greaterThan(now()->subDays(3))
            ? $created->diffForHumans()
            : $created->format('d M Y, H:i A');
    }
}
