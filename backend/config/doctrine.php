<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\DBAL\DriverManager;

function createEntityManager(): EntityManager
{
    $dbUrl = getenv('DATABASE_URL') ?: 'postgresql://fintech_user:fintech_password@db:5432/fintech_db';

    // Parse DATABASE_URL
    $parsed = parse_url($dbUrl);
    $conn = [
        'driver' => 'pdo_pgsql',
        'host' => $parsed['host'],
        'port' => $parsed['port'] ?? 5432,
        'user' => $parsed['user'],
        'password' => $parsed['pass'],
        'dbname' => ltrim($parsed['path'], '/'),
    ];

    $config = ORMSetup::createAttributeMetadataConfiguration(
        paths: [__DIR__ . '/../src/Models'],
        isDevMode: true,
    );

    $connection = DriverManager::getConnection($conn, $config);

    return new EntityManager($connection, $config);
}
