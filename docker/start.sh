#!/bin/sh
set -e

echo "Running deploy script..."
sh /var/www/html/scripts/00-laravel-deploy.sh

echo "Starting services..."
exec supervisord -c /etc/supervisord.conf
