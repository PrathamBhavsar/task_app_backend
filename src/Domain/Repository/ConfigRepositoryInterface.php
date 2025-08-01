<?php

namespace Domain\Repository;

interface ConfigRepositoryInterface
{
    public function get(string $key): ?string;
    public function set(string $key, string $value): void;
}
