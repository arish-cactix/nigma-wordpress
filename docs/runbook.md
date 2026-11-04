# Operations Runbook

Version: 1.0

## Purpose

Step-by-step procedures for running the NIGMA production site: deploying, verifying, rolling back, updating WordPress, changing server-only configuration and renewing certificates.

Background and reasoning live elsewhere: `docs/deployment.md` (pipeline), `docs/production-environment.md` (the server), `docs/security.md` (controls and incident record), ADR-014 and ADR-015 in `docs/architecture-decisions.md`.

> **Living document:** update it whenever one of these procedures changes.

## At a glance

```
push to main ──► GitHub Actions: "PHP Coding Standards"  (PHPCS + PHPStan)
             └─► GitHub Actions: "Deploy to Production"  (waits, verifies, emails)

Production server (no inbound SSH; pull-based):
  nigma-deploy.timer, every minute:
    fetch origin/main ─► new commit? ─► fast-forward /var/www/nigma
      ─► fix ownership ─► clear caches ─► health check (/ and a search page = 200)
         ├─ pass: publish the commit at /deploy-version.txt
         └─ fail: roll back automatically; do not retry that commit
```

A **green** Deploy run means production is actually running the pushed commit. A **red** run means it is not (usually because the server rolled it back, or the deploy did not arrive within 10 minutes).

## Access

- There is no SSH. Use **AWS Systems Manager → Session Manager** (console or `aws ssm start-session`) to the EC2 instance tagged `Name = Nigma`, region `ap-south-1`. You need an AWS identity with SSM permissions.
- Commands below that start with `sudo` are run in that session.
- WP-CLI must run as the `ubuntu` user, with PHP's command-execution block lifted for the CLI only:

  ```bash
  wpx() { sudo -u ubuntu -H env WP_CLI_PHP_ARGS=-ddisable_functions= wp --path=/var/www/nigma "$@"; }
  wpx core version
  ```

  Never run WP-CLI as `root` against this site: it loads plugin code with full privileges.

## 1. Deploy a change (normal)

1. Create a branch, commit, open a pull request to `main`. The **PHP Coding Standards** check runs on the PR; fix anything it reports.
2. Merge to `main`.
3. Within about a minute the server pulls and deploys it. Watch **Actions → Deploy to Production**; it finishes green when the live site reports your commit, and sends the notification email.

Only `wp-content/` is deployed (sparse checkout). Changes outside it (docs, `scripts/`, workflows) do not change the site, but still flow through the same pipeline harmlessly.

### Verify what is live

```bash
curl -s "https://www.nigma.ae/deploy-version.txt?cb=$RANDOM"      # the commit production is running
git ls-remote https://github.com/arish-cactix/nigma-wordpress refs/heads/main   # the commit on main
```

On the server:

```bash
sudo nigma-deploy --status                   # current / live / pinned / last-failed commit
sudo tail -n 50 /var/log/nigma-deploy.log    # what the deployer did
systemctl list-timers nigma-deploy.timer     # the timer is active
```

## 2. A deploy failed

Symptoms: the Deploy run is red, or `deploy-version.txt` still shows the old commit.

1. Read `/var/log/nigma-deploy.log`. A health-check failure looks like `health check failed ... rolling back to <sha>`; the site is already back on the previous commit.
2. The failed commit is remembered (`nigma-deploy --status` → *last failed*) and **is not retried automatically**.
3. Fix forward: push a corrected commit (a new commit deploys normally), **or** revert the bad commit on `main`.
4. To retry the same commit deliberately: `sudo nigma-deploy --force`.
5. If the log says `could not move the working tree` or `server is not an ancestor of origin/main`, the server's checkout has local changes or history that conflict with `main`. Nothing was changed. See "Server-side updates must be committed" below.

## 3. Roll back

Pick the first that fits.

- **Revert commit (preferred):** `git revert <sha>` on `main` and push. Normal deploy applies it.
- **Rollback workflow:** *Actions → Rollback Production → Run workflow*, paste a known-good commit SHA. It commits that commit's `wp-content` tree on top of `main` ("Rollback: restore wp-content to …"); the normal deploy applies it. It fails if `wp-content` already matches the target.
- **Emergency, on the server** (GitHub down or the pipeline itself is broken):

  ```bash
  sudo nigma-deploy --pin <good-sha>    # deploys that commit and PAUSES auto-deploys
  # ... fix the problem on main ...
  sudo nigma-deploy --unpin             # resume deploying origin/main
  ```

  While pinned, pushes to `main` are not deployed. Always unpin.

After any rollback: check the home page, a search, the contact form, and the login page.

## 4. Update WordPress core, plugins or themes

Because code is read-only to PHP (ADR-014), updates are done from the command line, then **committed to git**.

1. **Back up first:** an EC2 image (AMI, no reboot) and a database export, e.g. `sudo mysqldump --single-transaction nigma_live | gzip > /root/pre-update-$(date +%F).sql.gz`.
2. Update, one batch at a time, testing between batches:

   ```bash
   wpx core update --version=<x.y.z>       # core
   wpx core update-db
   wpx plugin update <plugin-slug> [...]    # wordpress.org / licensed plugins with a download
   wpx core verify-checksums
   wpx plugin verify-checksums <plugin-slug>
   ```
3. **After updating LiteSpeed Cache**, refresh its Redis drop-in and the caches:

   ```bash
   sudo install -o ubuntu -g www-data -m 644 /var/www/nigma/wp-content/plugins/litespeed-cache/lib/object-cache.php /var/www/nigma/wp-content/object-cache.php
   redis-cli flushdb; sudo find /usr/local/lsws/cachedata/nigma -mindepth 1 -delete
   ```
4. Check: home page, a search, a form page, the sitemap, `wp-content/debug.log` for new PHP fatals.
5. **Commit the update to git** (next section).

Licence-gated plugins (Gravity Forms, ACF Pro, Avada/Fusion Builder/Core, WP Migrate DB Pro, and the WPMU DEV plugins) only download with a valid licence. If WP-CLI reports "no package", renew the licence first, or download the release from the vendor and replace the folder.

### Server-side updates must be committed

The deployer fast-forwards with `git merge --ff-only`. If files changed on the server are not in git and a later commit touches the same files, the merge is refused (nothing changes; the log explains). Therefore:

- For a wordpress.org plugin: download the exact release zip, replace `wp-content/plugins/<slug>/` in the repo, and compare a hash of the folder with the server (`find . -type f -print0 | LC_ALL=C sort -z | xargs -0 sha256sum | sha256sum`). Commit and merge. Remember `.gitignore` is an allowlist and ignores `*.sql` (an exception exists for LiteSpeed's schema files).
- Core is not in git; it is verified with `wpx core verify-checksums`.

### LiteSpeed Cache notices in the admin

PHP cannot write `.htaccess` or the object-cache drop-in (by design). If the LiteSpeed admin shows "please add these codes to .htaccess" or "could not write the drop-in", apply the setting from the CLI instead: `wpx litespeed-option set <name> <value>`. The runtime file `wp-content/.litespeed_conf.dat` is the one file PHP may write.

## 5. Server-only configuration (not deployed from git)

These live on the server. Keep the reference copy in the repo in sync after changing them.

| What | Server location | Repo reference | Apply |
|---|---|---|---|
| Deploy script, timer, installer | `/usr/local/sbin/nigma-deploy`, `/etc/systemd/system/nigma-deploy.*` | `scripts/deploy/` | copy the files to the server and run `sudo bash install.sh` |
| Edge rules (spam-URL 410, non-www redirect) | `/usr/local/lsws/conf/vhosts/nigma/vhconf.conf` (rewrite rules) | `scripts/deploy/vhost-edge-rules.conf` | edit, then `sudo /usr/local/lsws/bin/lswsctrl restart` |
| PHP settings | `/usr/local/lsws/lsphp83/etc/php/8.3/litespeed/php.ini` | — | edit, restart as above |

Procedure for any vhost change:

1. Back up: `sudo cp -a <file> /root/<name>.before-<change>.$(date +%F)`.
2. Edit, restart OpenLiteSpeed (graceful, no downtime for cached pages).
3. Test the cases both ways with `curl` (see ADR-015): spam URLs return 410, **every real URL still returns 200**, the apex host redirects in one hop, and `/.well-known/acme-challenge/` is not redirected.
4. If the home page is not 200, restore the backup and restart.

Before changing the spam patterns, test them against every real URL on the site (posts, pages, term links, sitemaps).

## 6. Certificates

- Let's Encrypt via `certbot`, certificate `nigma-ae` (covers `nigma.ae` and `www.nigma.ae`). It renews automatically; a deploy hook restarts OpenLiteSpeed so the new certificate is loaded.
- Renewal needs **port 80 open to the internet** and the `/.well-known/acme-challenge/` path served by the origin (the apex redirect rule excludes it). Do not close port 80 or change that exemption.
- Check: `sudo certbot certificates` (expiry dates) and `sudo certbot renew --dry-run`. If the dry run says "rateLimited", wait and retry later; it is not a configuration fault.
- `www.nigma.ae` is also fronted by the QUIC.cloud CDN, which presents its own certificate to browsers; the origin certificate matters for the CDN connection and for the apex.
- If a private key is ever exposed, revoke the certificate (`sudo certbot revoke --cert-name <name> --reason keycompromise`) and issue a new one.

## 7. Troubleshooting

| Symptom | Likely cause | What to do |
|---|---|---|
| Deploy run red: "Production did not reach <sha>" | Server rolled back, timer stopped, or the commit is pinned | `sudo nigma-deploy --status`, read `/var/log/nigma-deploy.log`; fix forward or `--unpin` |
| Deploy log: `could not move the working tree` | Local file changes on the server conflict with the commit | See "Server-side updates must be committed"; compare `git -C /var/www/nigma status` |
| Uncached pages 500, cached pages fine | PHP memory limit hit (WordPress 7 + Avada need ~130 MB) | Confirm `WP_MEMORY_LIMIT` is 256M in `wp-config.php`; check `wp-content/debug.log` for the real error |
| WP-CLI: "`proc_open()` / `exec()` has been disabled for security reasons" | PHP command execution is blocked, and the CLI inherits it | Use `wpx` (lifts it for the CLI only) |
| Admin shows LiteSpeed "could not write … drop-in / .htaccess" | PHP is read-only there on purpose | See "LiteSpeed Cache notices"; use `wpx litespeed-option` |
| Post SMTP notice "Branda is overriding wp_mail()" | Generic warning because Branda is active; Branda's SMTP module is off | Ignore, or dismiss; confirm mail from the Post SMTP log |
| `https://nigma.ae/...` loops or does not redirect | Edge redirect rule broken | Restore the vhost backup (section 5) and restart |
| A real page suddenly returns 410 | A new URL shape matches a spam pattern | Compare the URL with the patterns in `scripts/deploy/vhost-edge-rules.conf`; relax the pattern and retest against all real URLs |
| CPU high, site slow | Burstable instance out of CPU credits, or a bot flood | CloudWatch `CPUCreditBalance` and `CPUSurplusCreditBalance`; look at the access log (`/usr/local/lsws/logs/`) for floods |
| PHP Coding Standards red | Style or static-analysis error in custom code | Read the failing step; PHPCS covers only the two mu-plugins listed in `.phpcs.xml` and the child theme |

## 8. Build or rebuild a server (Docker runtime)

The live server is native. A **new or rebuilt** host can instead run the Docker stack, which builds its own environment (ADR-016). Details: `docker/prod/README.md`.

Prerequisites: Ubuntu 24.04, ports 80/443 open, an instance role with the S3 policy (`docker/prod/iam-policy.example.json`), and (for a real certificate) a DNS record pointing at the host.

```bash
# as root, via SSM
apt-get update && apt-get install -y git
git clone --filter=blob:none --no-checkout https://github.com/arish-cactix/nigma-wordpress.git /opt/nigma/infra
cd /opt/nigma/infra && git sparse-checkout init --no-cone && git sparse-checkout set /docker/prod/ /scripts/deploy/ && git checkout main
bash scripts/deploy/install.sh --runtime docker --domains nigma.ae,www.nigma.ae --cert-name nigma-ae \
  --media-s3 s3://YOUR-BUCKET/uploads --db-s3 s3://YOUR-BUCKET/db --backup-timer --now
```

Use `--acme-staging` on a test host. `--now` builds the environment immediately; otherwise the timer does it within a minute.

Verify (all containers `running+healthy`, SSL `ok` or `placeholder-pending` until DNS points here):

```bash
nigma-docker-bootstrap --status
nigma-deploy --status
tail -n 50 /var/log/nigma-bootstrap.log
curl -sk -o /dev/null -w '%{http_code}\n' --resolve www.nigma.ae:443:127.0.0.1 https://www.nigma.ae/
```

Data: the media library and database are restored from S3 when empty. Back up/restore by hand with `nigma-data backup`, `nigma-data media-restore`, `nigma-data db-restore [file|s3-uri|latest]`. Check the last backup in the S3 console; **test a restore periodically**.

Cut over a rebuilt host the same way as before: point the DNS/Elastic IP at it once `nigma-docker-bootstrap --status` is clean, then let the real certificate be issued (`nigma-docker-bootstrap --ssl --force`).

| Symptom | Cause | Action |
|---|---|---|
| Deploy log: "Docker environment is not healthy ... not deploying code yet" | A repair was attempted under 10 minutes ago | `tail /var/log/nigma-bootstrap.log`; fix the cause; `nigma-deploy --force` retries now |
| SSL status `placeholder-pending` | No name in `SITE_DOMAINS` resolves to this host yet | Point DNS at the host; then `nigma-docker-bootstrap --ssl --force` |
| Certificate request fails | Port 80 closed, DNS wrong, or Let's Encrypt rate limit | Check the log; use `ACME_STAGING=1` while testing |
| Database empty after bootstrap | `DB_BACKUP_S3_URI` unset or no dump uploaded | `nigma-data db-restore <file>` |

## 9. If you suspect a compromise

Run the **Compromise check** in `docs/security.md`. Do not "clean in place" and trust the result: rebuild from git and a verified core (ADR-014), and rotate credentials.

## Known gaps

- No branch protection on `main`: anyone with write access can change production. Enable it and require the PHP Coding Standards check.
- No automated backups and no CloudWatch alarm on CPU credit balance are configured (deferred by the project owner).
- Licence-gated plugins and theme cannot be updated until their licences are renewed.
- The server's deploy script and vhost configuration are not deployed from git (section 5).
- Campaign landing pages (e.g. the home-lift page) live only on the servers' disks and are not in git.

## Related Documents

- deployment.md
- production-environment.md
- security.md
- architecture-decisions.md
- troubleshooting.md
