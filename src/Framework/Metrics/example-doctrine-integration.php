<?php

/**
 * Example: Integrating Metrics with Doctrine EntityManager
 * 
 * This example shows how to configure Doctrine to use the DoctrineSQLLogger
 * for tracking database query metrics.
 */

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Configuration;
use Doctrine\DBAL\DriverManager;
use Framework\Metrics\MetricsCollector;
use Framework\Metrics\DoctrineSQLLogger;

// Create MetricsCollector
$metrics = new MetricsCollector();

// Create SQL Logger
$sqlLogger = new DoctrineSQLLogger($metrics);

// Configure Doctrine
$config = new Configuration();

// Set the SQL Logger
$config->setSQLLogger($sqlLogger);

// ... other Doctrine configuration (metadata, proxy, cache, etc.)

// Create EntityManager
$connection = DriverManager::getConnection([
    'driver' => 'pdo_mysql',
    'host' => $_ENV['DB_HOST'],
    'dbname' => $_ENV['DB_NAME'],
    'user' => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASS'],
], $config);

$entityManager = new EntityManager($connection, $config);

// Now all database queries will be tracked automatically
// The following metrics will be collected:
// - db_queries_total (counter)
// - db_query_duration_seconds (histogram)

// Example usage in EntityManagerFactory
class EntityManagerFactory
{
    private MetricsCollector $metrics;
    
    public function __construct(MetricsCollector $metrics)
    {
        $this->metrics = $metrics;
    }
    
    public function create(): EntityManager
    {
        $config = new Configuration();
        
        // Enable SQL logging with metrics
        $sqlLogger = new DoctrineSQLLogger($this->metrics);
        $config->setSQLLogger($sqlLogger);
        
        // ... rest of configuration
        
        return EntityManager::create($connectionParams, $config);
    }
}
