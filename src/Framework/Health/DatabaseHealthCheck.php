<?php

declare(strict_types=1);

namespace Framework\Health;

use Doctrine\ORM\EntityManagerInterface;

class DatabaseHealthCheck implements HealthCheckInterface
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function check(): CheckResult
    {
        try {
            $connection = $this->em->getConnection();
            $connection->executeQuery('SELECT 1');
            
            // Get driver class name (getName() was removed in DBAL 3.x)
            $driverClass = get_class($connection->getDriver());
            $driverName = substr($driverClass, strrpos($driverClass, '\\') + 1);
            
            return new CheckResult(
                healthy: true,
                message: 'Database connection is healthy',
                metadata: [
                    'driver' => $driverName,
                ]
            );
        } catch (\Throwable $e) {
            return new CheckResult(
                healthy: false,
                message: 'Database connection failed: ' . $e->getMessage()
            );
        }
    }
}
