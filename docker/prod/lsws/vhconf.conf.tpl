# NIGMA OpenLiteSpeed virtual host for the Docker web container (ADR-016).
# Template: nigma-docker-bootstrap renders it to /etc/nigma/docker/lsws/vhconf.conf,
# replacing @@CERT_NAME@@ and inserting the shared edge rules (@@EDGE_RULES@@, taken
# from scripts/deploy/vhost-edge-rules.conf so there is one source of truth).

docRoot                   /var/www/nigma/
vhDomain                  *
enableGzip                1
enableBr                  1

errorlog /usr/local/lsws/logs/nigma.error.log {
  useServer               0
  logLevel                WARN
  rollingSize             10M
}
accesslog /usr/local/lsws/logs/nigma.access.log {
  useServer               0
  rollingSize             10M
  keepDays                30
  compressArchive         1
}
index  {
  useServer               0
  indexFiles              index.php
  autoIndex               0
}
scripthandler  {
  add                     lsapi:lsphp php
}
module cache {
  storagePath             /usr/local/lsws/cachedata
}

# Let's Encrypt HTTP-01 challenge files live outside the (read-only) code tree.
context /.well-known/acme-challenge/ {
  location                /var/www/acme/.well-known/acme-challenge/
  allowBrowse             1
  addDefaultCharset       off
}

rewrite  {
  enable                  1
  autoLoadHtaccess        1
  rules                   <<<END_rules
RewriteEngine On
# ACME challenges are served by the context above; stop all other rules for them.
RewriteRule ^/?\.well-known/acme-challenge/ - [L]
# never serve VCS, dotfiles, env or config
RewriteRule (^|/)\.(git|svn|env|ht|user) - [F,L]
RewriteRule ^/?wp-config\.php$ - [F,L]
# no PHP execution from uploads
RewriteRule ^/?wp-content/uploads/.*\.(php|phtml|pht|phar|php[0-9]+)$ - [F,NC,L]
# xmlrpc is unused and a common attack target
RewriteRule ^/?xmlrpc\.php$ - [F,L]
@@EDGE_RULES@@
# WordPress front controller
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
RewriteRule ^/?index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
END_rules
}

vhssl  {
  keyFile                 /etc/letsencrypt/live/@@CERT_NAME@@/privkey.pem
  certFile                /etc/letsencrypt/live/@@CERT_NAME@@/fullchain.pem
  certChain               0
  sslProtocol             24
  enableECDHE             1
  renegProtection         1
  sslSessionCache         1
  sslSessionTickets       1
}
