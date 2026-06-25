# WordPress Local Configuration

This directory contains local-only WordPress/PHP configuration.

The Docker Compose `wordpress` service uses the official WordPress PHP-FPM image and mounts the repository-owned `wp-content` files and subdirectories into the generated WordPress core volume.

The local entrypoint initializes WordPress core while excluding bundled default plugins and themes so the container does not write them into the repository.
