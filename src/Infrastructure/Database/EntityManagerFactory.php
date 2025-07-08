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
            paths: [__DIR__ . '/../../../Domain/Entity'],
            isDevMode: true
        );

        $connection = DriverManager::getConnection([
            'dbname'   => 'ds',
            'user'     => 'root',
            'password' => 'Nautilus@610#',
            'host'     => '127.0.0.1',
            'driver'   => 'pdo_mysql',
        ], $config);

        return new EntityManager($connection, $config);
    }
}
