<?php

namespace App\Services\Initialization;

interface InitializerInterface
{
    public function getName(): string;
    public function initialize(bool $dryRun = false): InitializationResult;
}
