<?php

declare(strict_types=1);

namespace Framework\Health;

interface HealthCheckInterface
{
    public function check(): CheckResult;
}
