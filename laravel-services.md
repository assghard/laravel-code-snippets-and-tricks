# Laravel services

[Go to main README](README.md)

- [ProcessQueue Service for Artisan commands](#processqueue-Service-for-Artisan-commands)
- [Selenoid Service]
- [PdoQuery Service for no Eloquent queries](#pdoquery-service-for-no-eloquent-queries)

## ProcessQueue Service for Artisan commands
This service allows running Artisan commands in background in asynchronous processes. Each command is a separated process. This service is helpful when you need to run a lot of processes in one command. Each process is independent.

This service designed to facilitate the execution of multiple asynchronous processes within a single Artisan command in your Laravel projects. It provides a streamlined solution for handling concurrent tasks, improving performance and efficiency.

Features:
* **Effortless Asynchronous Processing**: With the service, you can effortlessly run a specified number of asynchronous processes in a single Artisan command, minimizing execution time and maximizing concurrency.
* **Flexible Configuration**: The service allows you to define the number of concurrent processes to be executed, timeouts and delays, giving you full control over the balance between performance and resource utilization.
* **Integration with Laravel**: Designed specifically for Laravel, this service seamlessly integrates with your existing Laravel project, leveraging its features, such as the Artisan commands and Symfony processes.

**Service implementation:**
See: [ProcessQueueService.php](https://github.com/assghard/laravel-code-snippets-and-tricks/blob/master/Services/ProcessQueueService.php)

**Example:**

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ProcessQueueService;

class MyPrimaryCommand extends Command
{
    protected $signature = 'my-command:primary';

    protected $description = 'This command will run subcommand in asynchronous processes';

    public function handle()
    {
        ...
        $processQueueService = new ProcessQueueService();
        $processQueueService->setPhpPath('/usr/bin/php');

        foreach ($items as $item) {
            $processQueueService->addCommandToQueue(['my-command:secondary-process', $item->id]); // just adding to queue without process starting
        }

        echo 'Queue count: '.$processQueueService->getQueueCount().PHP_EOL;

        $processQueueService->start(); // start queue processing

        ...
```

First of all you need to create a primary command: `my-command:primary`. In that command you fetch needed data and in foreach you add secondary command (`my-command:secondary-process {item_id}`) into the processes queue. The `$processQueueService->start();` method starts processing the queue


## PdoQuery Service for no Eloquent queries
In case you need to select a lot of data from database and process them row by row you can use `PdoQueryService`. Remember, in this approach, Eloquent, Scopes, Events and other features will not work.

```php
...
use App\Services\PdoQueryService;
...


$stmt = (new PdoQueryService())->prepareQueryStatement('SELECT * FROM users');

// fetch results one by one (in case you have large amount of data in the table)
while ($user = $stmt->fetchObject()) {
    echo $user->email.PHP_EOL;
}

// fetch all results in one array
$arrayOfAllUsers = (new PdoQueryService())->fetchAllResultsFromStatement($stmt);
foreach ($arrayOfAllUsers as $user) {
    echo $user['email'].PHP_EOL;
}


```