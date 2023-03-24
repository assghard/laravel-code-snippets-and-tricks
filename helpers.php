<?php

if (function_exists('call_in_background') === false) {
    function call_in_background($command)
    {
        exec(config('app.php_path').' '.base_path('artisan').' '.$command.' > /dev/null 2>&1 &');
    }
}

if (function_exists('replace_in_array') === false) {
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
