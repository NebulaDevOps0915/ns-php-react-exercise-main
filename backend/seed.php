<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config/doctrine.php';

use App\Models\Transaction;
use App\Models\Category;
use App\Models\Tag;

echo "Clearing existing data..." . PHP_EOL;

$em = createEntityManager();
$conn = $em->getConnection();

// Drop and recreate tables to handle schema changes
$conn->executeStatement("DROP TABLE IF EXISTS transaction_tags CASCADE");
// PostgreSQL doesn't support IF EXISTS with TRUNCATE, so check if tables exist first
$tablesExist = $conn->fetchOne("
    SELECT COUNT(*) FROM information_schema.tables 
    WHERE table_schema = 'public' 
    AND table_name IN ('transactions', 'categories', 'tags')
") == 3;

if ($tablesExist) {
    $conn->executeStatement("TRUNCATE TABLE transactions, categories, tags RESTART IDENTITY CASCADE");
}

echo "Loading seed data from centralized JSON files..." . PHP_EOL;

$seedDataPath = __DIR__ . '/../data';
$categoriesData = json_decode(file_get_contents($seedDataPath . '/categories.json'), true);
$transactionsData = json_decode(file_get_contents($seedDataPath . '/transactions.json'), true);

echo "Seeding database with initial data..." . PHP_EOL;

$categories = [];

foreach ($categoriesData as $catName) {
    $category = new Category();
    $category->setName($catName);
    $em->persist($category);
    $categories[$catName] = $category;
}

$em->flush();

// Create tags
$tagNames = ['work', 'travel', 'reimbursable', 'personal', 'business', 'recurring', 'one-time'];
$tags = [];

foreach ($tagNames as $tagName) {
    $tag = new Tag();
    $tag->setName($tagName);
    $em->persist($tag);
    $tags[$tagName] = $tag;
}

$em->flush();

// Create transactions and assign tags
$transactions = [];
foreach ($transactionsData as $data) {
    $transaction = new Transaction();
    $transaction->setDescription($data['description']);
    $transaction->setAmount($data['amount']);
    $transaction->setType($data['type']);
    $transaction->setCategory($categories[$data['category']]);
    $transaction->setUserId($data['user_id']);
    $transaction->setDate(new DateTime($data['date']));

    // Assign tags based on transaction characteristics
    if ($data['type'] === 'credit' && strpos(strtolower($data['description']), 'salary') !== false) {
        $transaction->addTag($tags['work']);
        $transaction->addTag($tags['recurring']);
    } elseif ($data['type'] === 'credit' && strpos(strtolower($data['description']), 'freelance') !== false) {
        $transaction->addTag($tags['work']);
        $transaction->addTag($tags['business']);
    } elseif ($data['type'] === 'credit' && strpos(strtolower($data['description']), 'refund') !== false) {
        $transaction->addTag($tags['reimbursable']);
    } elseif (in_array($data['category'], ['Travel', 'Transport'])) {
        $transaction->addTag($tags['travel']);
        if ($data['type'] === 'debit') {
            $transaction->addTag($tags['reimbursable']);
        }
    } elseif (in_array($data['category'], ['Work'])) {
        $transaction->addTag($tags['work']);
        $transaction->addTag($tags['business']);
    } elseif (in_array($data['category'], ['Housing', 'Utilities'])) {
        $transaction->addTag($tags['recurring']);
        $transaction->addTag($tags['personal']);
    } else {
        $transaction->addTag($tags['personal']);
    }

    $em->persist($transaction);
    $transactions[] = $transaction;
}

$em->flush();

echo "Database seeded successfully." . PHP_EOL;
echo "Seeded " . count($categoriesData) . " categories, " . count($tagNames) . " tags, and " . count($transactionsData) . " transactions" . PHP_EOL;

