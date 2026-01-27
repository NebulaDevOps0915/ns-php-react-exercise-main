#!/bin/bash
set -e

echo "=== Frontend Linting ==="
echo ""

echo "Installing dependencies..."
npm install --silent

echo "Running ESLint..."
npm run lint

if [ $? -eq 0 ]; then
  echo ""
  echo "[PASSED] All linting checks passed"
  exit 0
fi
