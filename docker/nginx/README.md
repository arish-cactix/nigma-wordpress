# Nginx Local Configuration

This directory contains local-only nginx configuration for the Docker development environment.

Nginx serves the local WordPress site on `LOCAL_HTTP_PORT`, which defaults to `8080`, and forwards PHP requests to the `wordpress` PHP-FPM service.
