#!/bin/bash

set -e

PROJECT_NAME="dev"
COMPOSE_FILES="-f docker-compose.yml -f docker-compose.override.yml -f docker-compose.dev.yml"

trap "echo 'Stopping and cleaning up...'; docker compose -p $PROJECT_NAME $COMPOSE_FILES down -v; exit" SIGINT SIGTERM

echo "--- Cleaning up any existing containers ---"
docker compose -p $PROJECT_NAME $COMPOSE_FILES down -v

echo -e "\n--- Starting services in localhost mode ---"
echo "   Frontend: http://localhost:3000"
echo "   Backend: http://localhost:8000"
echo -e "\n📋 Press Ctrl+C to stop and cleanup\n"

VITE_BACKEND_URL=http://localhost:8000 docker compose -p $PROJECT_NAME $COMPOSE_FILES up --build
