# Docker Database

This directory is reserved for Docker-specific database documentation or configuration.

Do not store SQL exports or imports here. Local database dump files belong in the root-level `database/` directory, which is ignored by Git.

Use `./docker/db/import-db.sh` to import the newest `.sql` or `.sql.gz` dump from the root-level `database/` directory into the local MariaDB container. You can also pass a dump path explicitly:

```bash
./docker/db/import-db.sh database/example.sql.gz
```
