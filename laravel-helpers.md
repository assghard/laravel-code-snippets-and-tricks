# Laravel helpers

- [How to use helpers in Laravel](#how-to-use-helpers-in-laravel)
- [Call Artisan command in background](#call-artisan-command-in-background)
- [Replace value in array](#replace-value-in-array-recursively)
- [Parsing data from text helper](#find-string-in-text)

## How to use helpers in Laravel

1. Create `helpers.php` PHP file in place you need. Example: in `app` directory
2. Add `helpers.php` file to `composer.json` in autoload section
    ```json
        "autoload": {
            "psr-4": {
                "App\\": "app/",
                "Database\\Factories\\": "database/factories/",
                "Database\\Seeders\\": "database/seeders/"
            },
            "files": [
                "app/helpers.php" // <-- here
            ]
        },
    ```
3. Run: `composer dump-autoload`
4. Add helper function with implementation to `helpers.php` (see helpers below)


## Call Artisan command in background
`call_in_background()` helper. Example: `call_in_background('db:seed');`

Add `helpers.php` file to you project and use `call_in_background` helper in place where you need. 

* **Warning: works only on Linux OS**

    1. Add array key to `config/app.php`
    ```php
        
        /**
         * Path to certain PHP version in server
         */
        'php_path' => env('PHP_PATH', 'php'),

    ```

    2. Add variable to your `.env` and `.env.example` files
    ```php
        PHP_PATH=php # Absolute path to executable PHP on your server
    ```

    3. Add helper implementation to `helpers.php` file
    ```php
        if (function_exists('call_in_background') === false) {
            function call_in_background($command)
            {
                $php = config('app.php_path');
                $artisan = base_path('artisan');
                exec($php.' '.$artisan.' '.$command.' > /dev/null 2>&1 &');
            }
        }
        
    ```

    4. Use helper

    ```php
    // `db:seed` is just an example
    public function seedDatabase(): bool
    {
        call_in_background('db:seed'); // command will call in background without waiting for seed complete

        ... 
        // another stuff here will be executed without waiting for `db:seed` has finished

        return true;
    }
    ```

## Replace value in array recursively
`replace_in_array()` helper. Useful helper in case you have multidimensional array and you need to replace all values in all subarrays.

**Helper implementation:**
```php
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
```

**Usage:**
```php
    $array = [
        0 => [
            0 => 'yellow',
            1 => 'blue'
        ],
        1 => [
            0 => 'red',
            1 => 'black',
            2 => 'yellow',
            'key' => [
                0 => 'green',
                1 => 'yellow'
            ]
        ],
    ];

    $result = replace_in_array('yellow', 'cyan', $array); // all "yellow" will be replaced by "cyan"
```

## Find string in text
`find_string_in_text()` helper is usefull in parsing data from text


Example text: 
```
... The product price is 29 USD. And another text here. The producer is AAA LLC headquartered in London
```

**Helper implementation:**
```php
if (!function_exists('find_string_in_text')) {
    function find_string_in_text(string $content, string $startText, string $endText)
    {
        $strStart = strpos($content, $startText);
        if ($strStart === false) {
            return null;
        }

        $strEnd = strpos($content, $endText, $strStart);

        return trim(substr($content, $strStart + strlen($startText), $strEnd - $strStart - strlen($startText)));
    }
}
```

**Usage:**
```php
    $content = file_get_contents('Just an example text here. The product price is 29 USD. And another text here. The producer is AAA LLC headquartered in London');

    $price = find_string_in_text($content, 'price is ', ' USD.');
    dd($price); // "29"

    $producerName = find_string_in_text($content, 'producer is ', 'headquartered');
    dd($producerName); // "AAA LLC"
    
```