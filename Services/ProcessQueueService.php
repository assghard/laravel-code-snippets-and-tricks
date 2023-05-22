<?php

declare(strict_types=1);

namespace App\Services;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;

class ProcessQueueService
{
    private array $processList = [];
    private array $queue = [];
    private string $phpPath;

	public function __construct(
        private int $processesLimit = 10,
        private int $delay = 2,
        private int $processTimeout = 0
    )
	{
       $this->setPhpPath();
	}

    /**
     * Add command to the queue
     *
     * Example 1: `['generate-invoices']` - This means: "php artisan generate-invoices"
     * Example 2: `['generate-user-invoice', [150]]` - This means "php artisan generate-user-invoice 150"
     */
    public function addCommandToQueue(string|array $commandName): void
    {
        $this->queue[] = $commandName;
    }

    /**
     * Start processing the queue
     */
    public function start(): void
    {
        for ($i = 0; $i < $this->processesLimit; $i++) {
            $this->startNewProcess();
        }

        while (true) {
            if (empty($this->processList) && empty($this->queue)) {
                break;
            }

            foreach ($this->processList as $i => $runningProcess) {
                if ($runningProcess->isRunning()) {
                    echo $runningProcess->getOutput().PHP_EOL;

                    continue;
                }

                $this->stopProcess($i);
                $this->startNewProcess();
            }
        }
    }

    public function stop(bool $killRunningProcesses = false): void
    {
        $this->queue = [];
        if ($killRunningProcesses) {
            $this->killRunningProcesses();
        }
    }

    public function getQueueCount(): int
    {
        return count($this->queue);
    }

    public function getQueue(): array
    {
        return $this->queue;
    }

    public function getProcessesCount(): int
    {
        return count($this->processList);
    }

    public function getProcessList(): array
    {
        return $this->processList;
    }

    public function killRunningProcesses(): void
    {
        foreach ($this->processList as $pid => $runningProcess) {
            $this->stopProcess($pid);
        }
    }

    public function stopProcess(int $pid): void
    {
        $this->processList[$pid]->stop();
        unset($this->processList[$pid]);
    }

    public function setProcessesLimit(int $limit): void
    {
        $this->processesLimit = $limit;
    }

    public function setDelay(int $delay): void
    {
        $this->delay = $delay;
    }

    public function setProcessTimeout(int $timeout): void
    {
        $this->processTimeout = $timeout;
    }

    public function setPhpPath(string $path = ''): void
    {
        if (!empty($path)) {
            $this->phpPath = $path;
        } else {
            if (!empty(config('app.php_path'))) {
                $this->phpPath = config('app.php_path');
            } elseif (!empty(env('PHP_PATH'))) {
                $this->phpPath = env('PHP_PATH');
            } else {
                $this->phpPath = $this->getPhpExecutable();
                if (empty($this->phpPath)) {
                    $this->phpPath = 'php';
                }
            }
        }
    }

    private function startNewProcess(): void
    {
        if (!empty($this->queue)) {
            $firstQueueKey = array_key_first($this->queue);
            $commandName = reset($this->queue);

            $command = [$this->phpPath, base_path('artisan')];
            if (is_array($commandName)) {
                foreach ($commandName as $c) {
                    $command[] = $c;
                }
            } else {
                $command[] = $commandName;
            }

            $process = new Process($command);
            $process->setTimeout($this->processTimeout);
            $process->enableOutput();
            $process->start();

            $this->processList[$process->getPid()] = $process;
            unset($this->queue[$firstQueueKey]);
            sleep($this->delay);
        }
    }

    /**
     * Find the path to PHP executable
     */
    private function getPhpExecutable(): string
    {
        return (new PhpExecutableFinder)->find();
    }

}
