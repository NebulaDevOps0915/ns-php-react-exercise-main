<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class ApiTest extends TestCase
{
    private Client $client;
    private string $baseUrl;

    protected function setUp(): void
    {
        $this->baseUrl = 'http://localhost:8000';
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'http_errors' => false,
        ]);
    }

    public function testHealthEndpoint(): void
    {
        $response = $this->client->get('/health');

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals('ok', $data['status']);
    }

    public function testGetTransactions(): void
    {
        $response = $this->client->get('/api/v1/transactions');

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($data);
        $this->assertCount(25, $data);
    }

    public function testGetTransactionsWithTrailingSlash(): void
    {
        $response = $this->client->get('/api/v1/transactions/');

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($data);
        $this->assertCount(25, $data);
    }

    public function testGetTransactionsPagination(): void
    {
        $response = $this->client->get('/api/v1/transactions?skip=5&limit=10');

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertIsArray($data);
        $this->assertCount(10, $data);
    }

    public function testGetSingleTransaction(): void
    {
        $response = $this->client->get('/api/v1/transactions/1');

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals(1, $data['id']);
        $this->assertEquals('Groceries', $data['description']);
        $this->assertArrayHasKey('category_rel', $data);
    }

    public function testGetNonExistentTransaction(): void
    {
        $response = $this->client->get('/api/v1/transactions/99999');

        $this->assertEquals(404, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('detail', $data);
        $this->assertEquals('Transaction not found', $data['detail']);
    }

    public function testAmountSerializedAsNumber(): void
    {
        $response = $this->client->get('/api/v1/transactions/1');

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertIsFloat($data['amount']);
        $this->assertEquals(50.25, $data['amount']);
    }

    public function testCreateTransaction(): void
    {
        $newTransaction = [
            'description' => 'Test Transaction',
            'amount' => 123.45,
            'type' => 'debit',
            'category_id' => 1,
            'user_id' => 1,
        ];

        $response = $this->client->post('/api/v1/transactions', [
            'json' => $newTransaction,
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals('Test Transaction', $data['description']);
        $this->assertEquals(123.45, $data['amount']);
        $this->assertIsFloat($data['amount']);
        $this->assertArrayHasKey('category_rel', $data);
    }

    public function testCreateTransactionInvalid(): void
    {
        $invalidTransaction = [
            'description' => '',
            'amount' => 50.0,
        ];

        $response = $this->client->post('/api/v1/transactions', [
            'json' => $invalidTransaction,
        ]);

        $this->assertEquals(422, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('detail', $data);
    }

    public function testUpdateTransaction(): void
    {
        $updates = [
            'description' => 'Updated Transaction',
            'amount' => 999.99,
            'type' => 'credit',
            'category_id' => 1,
            'user_id' => 1,
        ];

        $response = $this->client->put('/api/v1/transactions/1', [
            'json' => $updates,
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals('Updated Transaction', $data['description']);
        $this->assertEquals(999.99, $data['amount']);
        $this->assertArrayHasKey('category_rel', $data);
    }

    public function testUpdateNonExistentTransaction(): void
    {
        $updates = [
            'description' => 'Updated',
            'amount' => 100.0,
            'type' => 'debit',
            'category_id' => 1,
            'user_id' => 1,
        ];

        $response = $this->client->put('/api/v1/transactions/99999', [
            'json' => $updates,
        ]);

        $this->assertEquals(404, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('detail', $data);
        $this->assertEquals('Transaction not found', $data['detail']);
    }

    public function testDeleteTransaction(): void
    {
        // First create a transaction to delete
        $newTransaction = [
            'description' => 'To Be Deleted',
            'amount' => 50.0,
            'type' => 'debit',
            'category_id' => 1,
            'user_id' => 1,
        ];

        $createResponse = $this->client->post('/api/v1/transactions', [
            'json' => $newTransaction,
        ]);
        $created = json_decode($createResponse->getBody()->getContents(), true);
        $transactionId = $created['id'];

        // Now delete it
        $response = $this->client->delete("/api/v1/transactions/{$transactionId}");

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertEquals($transactionId, $data['id']);
        $this->assertEquals('To Be Deleted', $data['description']);
        $this->assertArrayHasKey('category_rel', $data);
        $this->assertIsFloat($data['amount']);

        // Verify it's deleted
        $getResponse = $this->client->get("/api/v1/transactions/{$transactionId}");
        $this->assertEquals(404, $getResponse->getStatusCode());
    }

    public function testDeleteNonExistentTransaction(): void
    {
        $response = $this->client->delete('/api/v1/transactions/99999');

        $this->assertEquals(404, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertArrayHasKey('detail', $data);
        $this->assertEquals('Transaction not found', $data['detail']);
    }

    public function testCORSHeaders(): void
    {
        $response = $this->client->request('OPTIONS', '/api/v1/transactions', [
            'headers' => [
                'Origin' => 'http://localhost:3000',
                'Access-Control-Request-Method' => 'GET',
            ],
        ]);

        $this->assertTrue($response->hasHeader('Access-Control-Allow-Origin'));
        $this->assertEquals('*', $response->getHeader('Access-Control-Allow-Origin')[0]);
    }
}
