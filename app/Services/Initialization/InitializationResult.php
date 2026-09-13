<?php

namespace App\Services\Initialization;

class InitializationResult
{
    private array $logs = [];

    public function record(string $action, string $description): void
    {
        $this->logs[] = sprintf('[%s] %s', strtoupper($action), $description);
    }

    public function getLogs(): array
    {
        return $this->logs;
    }
}
