#!/bin/bash

set -e

PROJECT_NAME="dev"
COMPOSE_FILES="-f docker-compose.yml -f docker-compose.override.yml -f docker-compose.dev.yml"

echo "--- Seeding database ---"
if docker compose -p $PROJECT_NAME $COMPOSE_FILES exec backend ./seed.sh; then
  echo "[PASSED] Database seeded successfully"
else
  echo "[FAILED] Database seeding failed"
  exit 1
fi
