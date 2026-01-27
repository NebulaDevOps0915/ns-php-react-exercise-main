#!/bin/bash

set -e

PROJECT_NAME="lint"
COMPOSE_FILES="-f docker-compose.yml -f docker-compose.override.yml"

echo "Building Docker images..."
docker compose -p $PROJECT_NAME $COMPOSE_FILES build --quiet

echo "--- Running Backend Linting ---"
if docker compose -p $PROJECT_NAME $COMPOSE_FILES run --rm backend ./lint.sh; then
  echo "[PASSED] Backend linting passed"
else
  echo "[FAILED] Backend linting failed"
  exit 1
fi

echo ""
echo "--- Running Frontend Linting ---"
if docker compose -p $PROJECT_NAME $COMPOSE_FILES run --rm frontend ./lint.sh; then
  echo "[PASSED] Frontend linting passed"
else
  echo "[FAILED] Frontend linting failed"
  exit 1
fi

echo ""
echo "[PASSED] All linting checks passed"
