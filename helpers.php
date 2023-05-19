<?php

declare(strict_types=1);

if (!function_exists('call_in_background')) {
    function call_in_background(string $command)
    {
        exec(config('app.php_path').' '.base_path('artisan').' '.$command.' > /dev/null 2>&1 &');
    }
}

if (!function_exists('replace_in_array')) {
    function replace_in_array($find, $replace, &$array)
    {
        array_walk_recursive($array, function (&$array) use ($find, $replace) {
            if ($array === $find) {
                $array = $replace;
            }
        });

        return $array;
    }
}

if (!function_exists('find_string_in_text')) {
    /**
     * Helpful in parsing data from text
     */
    function find_string_in_text(string $content, string $startText, string $endText): string|null
    {
        $strStart = strpos($content, $startText);
        if ($strStart === false) {
            return null;
        }

        $strEnd = strpos($content, $endText, $strStart);

        return trim(substr($content, $strStart + strlen($startText), $strEnd - $strStart - strlen($startText)));
    }
}
