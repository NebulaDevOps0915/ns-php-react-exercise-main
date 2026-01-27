import { test, expect } from '@playwright/test';

const BACKEND_URL = process.env.VITE_BACKEND_URL!;

test('has title and loads transactions table', async ({ page }) => {
  await page.goto('/');

  // Wait for the page to be fully loaded
  await page.waitForLoadState('networkidle');

  // Check for the main page title
  await expect(page.getByRole('heading', { name: 'FinTech Dashboard' })).toBeVisible();

  // Check for the recent transactions table title
  await expect(page.getByRole('heading', { name: 'Recent Transactions' })).toBeVisible();

  // Check that the table header for "Description" is visible
  await expect(page.getByRole('columnheader', { name: 'Description' })).toBeVisible();

  // Check that at least one transaction row is rendered by looking for its description
  // This assumes the seed data is in place
  await expect(page.getByRole('cell', { name: 'Groceries' })).toBeVisible();
});

test('API: health endpoint responds correctly', async ({ request }) => {
  const response = await request.get(`${BACKEND_URL}/health`);
  expect(response.ok()).toBeTruthy();
});

test('API: GET /api/v1/transactions returns 25 transactions', async ({ request }) => {
  const response = await request.get(`${BACKEND_URL}/api/v1/transactions`);
  expect(response.ok()).toBeTruthy();

  const transactions = await response.json();
  expect(Array.isArray(transactions)).toBeTruthy();
  expect(transactions.length).toBe(25);
});

test('API: GET /api/v1/transactions/1 returns Groceries transaction', async ({ request }) => {
  const response = await request.get(`${BACKEND_URL}/api/v1/transactions/1`);
  expect(response.ok()).toBeTruthy();

  const transaction = await response.json();
  expect(transaction.description).toBe('Groceries');
});

test('API: transaction has category_rel field', async ({ request }) => {
  const response = await request.get(`${BACKEND_URL}/api/v1/transactions/1`);
  expect(response.ok()).toBeTruthy();

  const transaction = await response.json();
  expect(transaction).toHaveProperty('category_rel');
  expect(transaction.category_rel).toBeDefined();
});

test('API: pagination works correctly', async ({ request }) => {
  const response = await request.get(`${BACKEND_URL}/api/v1/transactions?skip=5&limit=10`);
  expect(response.ok()).toBeTruthy();

  const transactions = await response.json();
  expect(Array.isArray(transactions)).toBeTruthy();
  expect(transactions.length).toBe(10);
});

test('API: CORS headers are present', async ({ request }) => {
  const response = await request.get(`${BACKEND_URL}/api/v1/transactions`, {
    headers: {
      'Origin': 'http://localhost:3000'
    }
  });
  expect(response.ok()).toBeTruthy();

  const corsHeader = response.headers()['access-control-allow-origin'];
  expect(corsHeader).toBe('*');
});
