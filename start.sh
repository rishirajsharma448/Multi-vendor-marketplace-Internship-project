#!/bin/bash
set -e

# Configure Apache port based on Railway's $PORT environment variable
PORT_TO_USE="${PORT:-80}"

echo "Configuring Apache to listen on port ${PORT_TO_USE}..."
sed -i "s/Listen [0-9]*/Listen ${PORT_TO_USE}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost \*:${PORT_TO_USE}>/g" /etc/apache2/sites-available/000-default.conf

exec apache2-foreground
