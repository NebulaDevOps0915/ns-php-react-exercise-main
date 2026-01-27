<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config/doctrine.php';

use App\Models\Transaction;
use App\Models\Category;

echo "Creating database tables..." . PHP_EOL;

$em = createEntityManager();
$schemaTool = new \Doctrine\ORM\Tools\SchemaTool($em);

$classes = [
    $em->getClassMetadata(Category::class),
    $em->getClassMetadata(Transaction::class),
];

try {
    $schemaTool->createSchema($classes);
    echo "Tables created." . PHP_EOL;
} catch (\Exception $e) {
    // Tables might already exist, that's okay
    echo "Tables already exist or error: " . $e->getMessage() . PHP_EOL;
}
