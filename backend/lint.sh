#!/bin/bash
set -e

echo "=== PHP Linting ==="
echo ""

echo "Running PHP_CodeSniffer..."
if composer run cs; then
  echo "[PASSED] PHP_CodeSniffer"
else
  echo "[FAILED] PHP_CodeSniffer"
  exit 1
fi

echo ""
echo "Running PHPStan..."
if composer run stan; then
  echo "[PASSED] PHPStan"
else
  echo "[FAILED] PHPStan"
  exit 1
fi

echo ""
echo "[PASSED] All linting checks passed"
