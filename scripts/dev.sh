#!/bin/bash

# Development helper script for macOS
case "$1" in
    "start")
        docker-compose up -d
        echo "Application started at http://localhost:8000"
        ;;
    "stop")
        docker-compose down
        ;;
    "restart")
        docker-compose restart
        ;;
    "logs")
        docker-compose logs -f app
        ;;
    "shell")
        docker-compose exec app bash
        ;;
    "artisan")
        shift
        docker-compose exec app php artisan "$@"
        ;;
    "composer")
        shift
        docker-compose exec app composer "$@"
        ;;
    "build-desktop")
        # Build NativePHP desktop app
        npm install
        docker-compose exec app php artisan native:build
        ;;
    "fresh")
        docker-compose down -v
        docker-compose up --build -d
        ;;
    *)
        echo "Usage: ./scripts/dev.sh {start|stop|restart|logs|shell|artisan|composer|build-desktop|fresh}"
        echo ""
        echo "Examples:"
        echo "  ./scripts/dev.sh artisan make:controller UserController"
        echo "  ./scripts/dev.sh artisan migrate"
        echo "  ./scripts/dev.sh composer install"
        ;;
esac