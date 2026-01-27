#!/bin/bash

set -e

PROJECT_NAME="test"
COMPOSE_FILES="-f docker-compose.yml -f docker-compose.override.yml -f docker-compose.test.yml"

# Function to print logs on error
cleanup_with_logs() {
  EXIT_CODE=$?
  if [ $EXIT_CODE -ne 0 ]; then
    echo -e "\n[FAILED] Tests failed! Printing container logs for debugging...\n"
    echo "=== Backend Logs ==="
    docker compose -p $PROJECT_NAME $COMPOSE_FILES logs backend
    echo -e "\n=== Frontend Logs ==="
    docker compose -p $PROJECT_NAME $COMPOSE_FILES logs frontend
    echo -e "\n=== Database Logs ==="
    docker compose -p $PROJECT_NAME $COMPOSE_FILES logs db
  fi
  echo -e "\n--- Cleaning up containers ---"
  docker compose -p $PROJECT_NAME $COMPOSE_FILES down -v
  exit $EXIT_CODE
}

trap cleanup_with_logs EXIT SIGINT SIGTERM

echo "--- Tearing down existing services to ensure a clean state ---"
docker compose -p $PROJECT_NAME $COMPOSE_FILES down -v

echo -e "\n--- Building and starting services ---"
docker compose -p $PROJECT_NAME $COMPOSE_FILES up -d --build

echo -e "\n--- Seeding database ---"
docker compose -p $PROJECT_NAME $COMPOSE_FILES exec backend ./seed.sh

echo -e "\n--- Running Backend Tests ---"
docker compose -p $PROJECT_NAME $COMPOSE_FILES exec backend ./tests.sh

echo -e "\n--- Re-seeding database for E2E tests ---"
docker compose -p $PROJECT_NAME $COMPOSE_FILES exec backend ./seed.sh

echo -e "\n--- Running Frontend E2E Tests (Playwright) ---"
docker compose -p $PROJECT_NAME $COMPOSE_FILES exec -T frontend ./tests.sh

echo -e "\n[PASSED] All tests passed successfully!"