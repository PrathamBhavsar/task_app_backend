<?php

namespace Infrastructure\Database;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\DBAL\DriverManager;

class EntityManagerFactory
{
    public static function create(): EntityManagerInterface
    {
        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: [__DIR__ . '/../../../src/Domain/Entity'],
            isDevMode: $_ENV['APP_DEBUG'] ?? true
        );

        $connection = DriverManager::getConnection([
            'dbname'   => $_ENV['DB_NAME'] ?? 'ds',
            'user'     => $_ENV['DB_USER'] ?? 'root',
            'password' => $_ENV['DB_PASS'] ?? '',
            'host'     => $_ENV['DB_HOST'] ?? 'localhost',
            'driver'   => 'pdo_mysql',
            'charset'  => 'utf8mb4',
        ], $config);

        return new EntityManager($connection, $config);
    }
}
