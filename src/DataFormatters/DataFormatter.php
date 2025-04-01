<?php

namespace Luminee\Tracing\DataFormatters;

trait DataFormatter
{

    /**
     * @param float $seconds
     * @return string
     */
    public function formatDuration(float $seconds): string
    {
        if ($seconds < 0.001) {
            return round($seconds * 1000000) . 'μs';
        }

        if ($seconds < 0.1) {
            return round($seconds * 1000, 2) . 'ms';
        }

        if ($seconds < 1) {
            return round($seconds * 1000) . 'ms';
        }

        return round($seconds, 2) . 's';
    }


    /**
     * @param $size
     * @param int $precision
     * @return string
     */
    public function formatBytes($size, int $precision = 2): string
    {
        if ($size === 0 || $size === null) {
            return "0B";
        }

        $sign = $size < 0 ? '-' : '';
        $size = abs($size);

        $base = log($size) / log(1024);
        $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');
        return $sign . round(pow(1024, $base - floor($base)), $precision) . $suffixes[(int)floor($base)];
    }
}