<?php
/**
 * sanitize-db.php — turn a COPY of the NIGMA WordPress database into a test-safe copy (ADR-016).
 *
 * Purpose: let a throw-away test host run the real posts, pages, forms and plugin/theme settings
 * WITHOUT user accounts, visitor data, form entries, e-mail logs or any credentials.
 *
 *   SANITIZE_USER=... SANITIZE_PASS=... php sanitize-db.php <scratch_database> [table_prefix]
 *   php sanitize-db.php --verify <sanitized_dump.sql> <secrets.tsv>
 *
 * NEVER point this at the live database: it deletes data. Load a copy into a scratch database first
 * (mysqldump live | mysql scratch), sanitize that, dump the scratch database, VERIFY, drop it.
 *
 * Credentials come from the environment: use a throw-away MariaDB user that can only touch the scratch
 * database, never the live database or the maintenance account. SANITIZE_DB_HOST and SANITIZE_SECRETS
 * are optional overrides (default: localhost, /root/secrets.tsv).
 *
 * The sanitizer prints a manifest of WHAT was kept/removed (names and counts only, never values) and
 * writes the removed token-shaped secret values to the secrets file (mode 600; name<TAB>base64(value)).
 * --verify then proves none of those values remain in the final dump, reporting the option NAME and
 * length of any leak (never the value). The caller must shred the secrets file.
 *
 * Exit codes: 0 ok, 2 bad setup/credentials, 3 SQL error, 4 self-check failed, 5 verification found a leak.
 */

if (PHP_SAPI !== 'cli') { exit(1); }

// ================================================================== verify mode
if (($argv[1] ?? '') === '--verify') {
    $dumpFile = $argv[2] ?? ''; $tsv = $argv[3] ?? '';
    if (!is_file($dumpFile) || !is_file($tsv)) { fwrite(STDERR, "usage: --verify <dump.sql> <secrets.tsv>\n"); exit(2); }
    $dump = file_get_contents($dumpFile); $leaks = []; $n = 0;
    foreach (file($tsv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        [$name, $b64] = array_pad(explode("\t", $line, 2), 2, ''); $v = base64_decode($b64, true);
        if ($v === false || $v === '') { continue; }
        $n++;
        $escaped = str_replace(["\\", "'"], ["\\\\", "\\'"], $v);
        if (strpos($dump, $v) !== false || strpos($dump, $escaped) !== false) { $leaks[] = sprintf("%s (value length %d)", $name, strlen($v)); }
    }
    echo "verified $n token-shaped secret values against the dump\n";
    if ($leaks) { echo "LEAKS FOUND (" . count($leaks) . "):\n"; foreach (array_unique($leaks) as $l) { echo "  - $l\n"; } exit(5); }
    echo "no removed secret value appears in the dump\n"; exit(0);
}

// ================================================================== sanitize mode
$db = $argv[1] ?? null; $p = $argv[2] ?? 'ngm_';
if (!$db || $db === 'nigma_live') { fwrite(STDERR, "refusing: give a SCRATCH database name, never the live one\n"); exit(2); }
$user = getenv('SANITIZE_USER'); $pass = getenv('SANITIZE_PASS');
if ($user === false || $pass === false || $user === '') { fwrite(STDERR, "set SANITIZE_USER and SANITIZE_PASS (a user limited to the scratch database)\n"); exit(2); }
$m = new mysqli(getenv('SANITIZE_DB_HOST') ?: 'localhost', $user, $pass, $db);
if ($m->connect_errno) { fwrite(STDERR, "connect failed\n"); exit(2); }
$m->set_charset('utf8mb4');

$secrets = [];   // value => option/meta name it came from
function tokenlike($v) {   // looks like an API key / token / password, not a word, URL, host or path
    return strlen($v) >= 12 && !preg_match('/\s/', $v) && strpos($v, '/') === false && strpos($v, '@') === false
        && preg_match('/[A-Za-z]/', $v) && preg_match('/\d/', $v) && !preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/i', $v);
}
function note_secret($v, $name, $strict = true) {
    global $secrets;
    if (!is_string($v) || $v === '' || strlen($v) < 8) { return; }
    if ($strict ? !tokenlike($v) : preg_match('/^[0-9.\-]+$/', $v)) { return; }
    $secrets[$v] = $name;
}
function q($sql) { global $m; $r = $m->query($sql); if ($r === false) { fwrite(STDERR, "FATAL SQL error (aborting, database is NOT sanitized): " . $m->error . "\n"); exit(3); } return $r; }
function one($sql) { $r = q($sql); $row = $r ? $r->fetch_row() : [0]; return $row[0]; }
function line($s = '') { echo $s, "\n"; }

// ---- which tables keep their data -------------------------------------------------------
$KEEP = ['posts', 'postmeta', 'terms', 'term_taxonomy', 'term_relationships', 'termmeta', 'options', 'links',
         'gf_form', 'gf_form_meta', 'gf_form_revisions', 'rank_math_redirections', 'rank_math_redirections_cache',
         'rank_math_internal_links', 'rank_math_internal_meta', 'fusion_forms', 'fusion_form_fields'];
$keepSet = array_flip(array_map(function ($t) use ($p) { return $p . $t; }, $KEEP));

line("=== TABLES ===");
$tables = [];
$r = q("SHOW TABLES"); while ($row = $r->fetch_row()) { $tables[] = $row[0]; }
sort($tables);
foreach ($tables as $t) {
    $n = (int) one("SELECT COUNT(*) FROM `$t`");
    if (isset($keepSet[$t])) { line(sprintf("  KEEP     %-44s %7d rows", $t, $n)); continue; }
    q("TRUNCATE TABLE `$t`");
    line(sprintf("  EMPTIED  %-44s %7d rows removed", $t, $n));
}

// ---- posts ---------------------------------------------------------------------------------
line(); line("=== POSTS ===");
$before = (int) one("SELECT COUNT(*) FROM {$p}posts");
$r = q("SELECT post_type, post_status, COUNT(*) c FROM {$p}posts WHERE post_type = 'postman_sent_mail' OR post_status NOT IN ('publish','inherit') GROUP BY 1,2 ORDER BY 3 DESC");
while ($row = $r->fetch_row()) { line(sprintf("  DELETED  %-22s %-12s %6d", $row[0], $row[1], $row[2])); }
q("DELETE FROM {$p}posts WHERE post_type = 'postman_sent_mail' OR post_status NOT IN ('publish','inherit')");
q("DELETE FROM {$p}posts WHERE post_status = 'inherit' AND post_type <> 'attachment' AND post_parent NOT IN (SELECT ID FROM (SELECT ID FROM {$p}posts) x)");   // orphaned revisions
$pw = (int) one("SELECT COUNT(*) FROM {$p}posts WHERE post_password <> ''");
$r = q("SELECT post_password FROM {$p}posts WHERE post_password <> ''"); while ($row = $r->fetch_row()) { note_secret($row[0], 'post_password', false); }
q("UPDATE {$p}posts SET post_password = ''");
line("  password-protected posts unprotected: $pw");
q("DELETE FROM {$p}postmeta WHERE post_id NOT IN (SELECT ID FROM {$p}posts)");
$secretMeta = '((^|[_-])(api|apikey|key|keys|secret|token|passw|password|pwd|private|licen[cs]e|purchase|credential|webhook)($|[_-])|[a-z]key$|secret|passw)';
$r = q("SELECT meta_key, COUNT(*) FROM {$p}postmeta WHERE meta_key REGEXP '$secretMeta' GROUP BY 1");
while ($row = $r->fetch_row()) { line(sprintf("  postmeta key removed: %-40s %d rows", $row[0], $row[1])); }
$r = q("SELECT meta_key, meta_value FROM {$p}postmeta WHERE meta_key REGEXP '$secretMeta'"); while ($row = $r->fetch_row()) { note_secret($row[1], 'postmeta:' . $row[0], false); }
q("DELETE FROM {$p}postmeta WHERE meta_key REGEXP '$secretMeta'");
line("  posts: $before -> " . one("SELECT COUNT(*) FROM {$p}posts") . " ; postmeta rows: " . one("SELECT COUNT(*) FROM {$p}postmeta"));

// ---- options --------------------------------------------------------------------------------
line(); line("=== OPTIONS ===");
// structural options are never deleted; user_roles is never scrubbed (its keys are capabilities, not secrets)
$protect  = '/^(siteurl|home|blogname|blogdescription|template|stylesheet|active_plugins|permalink_structure|rewrite_rules|page_on_front|page_for_posts|show_on_front|blog_public|timezone_string|gmt_offset|WPLANG|db_version|initial_db_version|' . preg_quote($p, '/') . 'user_roles|fusion_options|avada_.*|fusion_builder_.*|theme_mods_.*|sidebars_widgets|widget_.*|nav_menu_options|category_base|tag_base|uploads_use_yearmonth_folders|upload_path|(thumbnail|medium|large)_.*|image_default_.*|posts_per_page|date_format|time_format|default_.*)$/i';
$noScrub  = '/^' . preg_quote($p, '/') . 'user_roles$/';
$dropName = '/(^_transient|^_site_transient|key|secret|token|passw|licen[cs]e|credential|salt|nonce|oauth|webhook|smtp|postman|wpmudev|^wdp_|^wd_|defender|dash_|^hub|snapshot|branda|^ub_|jetpack|gform|gravityforms|^rg_|acf_pro|fusion_registration|^litespeed\.(conf\.(api|cdn|crawler|qc)|cloud|data|admin_display|_summary|qc)|rank_math_(connect|google|analytic)|rank-math-(analytics|connect)|recovery|^cron$|^ping|mailserver|contentful|imdaad|new_admin_email|adminhash|api_|_api|^auth|oembed_cache)/i';
$secretish = '/(key|secret|token|passw|licen[cs]e|credential|salt|nonce|oauth|api|auth|smtp|mailserver|cloudflare|captcha)/i';
$secretKey = '/((^|[_-])(api|apikey|key|keys|secret|token|passw|password|pwd|private|licen[cs]e|purchase|credential|webhook)($|[_-])|[a-z]key$|secret|passw)/i';

$curOpt = ''; $objectsSeen = 0;
function scrub($v, $secretKey, &$count) {
    global $curOpt;
    if (is_array($v)) {
        foreach ($v as $k => $x) {
            if (is_string($k) && preg_match($secretKey, $k) && is_scalar($x) && $x !== '') { note_secret((string) $x, $curOpt, false); $v[$k] = ''; $count++; }
            else { $v[$k] = scrub($x, $secretKey, $count); }
        }
        return $v;
    }
    if (is_object($v)) { $GLOBALS['objectsSeen']++; return $v; }   // classes are never loaded (allowed_classes=false); objects cannot be scrubbed safely
    return $v;
}

$deleted = []; $scrubbed = []; $kept = 0; $keptWithObjects = [];
$r = q("SELECT option_id, option_name, option_value FROM {$p}options"); $rows = [];
while ($row = $r->fetch_row()) { $rows[] = $row; }
foreach ($rows as [$id, $name, $val]) {
    $isProtected = preg_match($protect, $name); $curOpt = $name; $objectsSeen = 0;
    $u = @unserialize($val, ['allowed_classes' => false]); $isSer = ($u !== false || $val === 'b:0;');

    if (!$isProtected && preg_match($dropName, $name)) {
        if (preg_match($secretish, $name)) {
            if ($isSer) { $it = new RecursiveIteratorIterator(new RecursiveArrayIterator(is_array($u) ? $u : [])); foreach ($it as $s) { note_secret($s, $name); } }
            else { note_secret($val, $name); }
        }
        q("DELETE FROM {$p}options WHERE option_id = " . (int) $id); $deleted[] = $name; continue;
    }
    if ($isSer) {
        $c = 0; $new = preg_match($noScrub, $name) ? $u : scrub($u, $secretKey, $c);
        if ($objectsSeen > 0) {
            // serialized PHP objects cannot be scrubbed safely: drop the option unless it is structural
            if (!$isProtected) {
                preg_match_all('/s:\d+:"([^"]{8,})";/', $val, $mm); foreach ($mm[1] as $x) { note_secret($x, $name); }
                q("DELETE FROM {$p}options WHERE option_id = " . (int) $id); $deleted[] = $name . ' (contains objects)'; continue;
            }
            $keptWithObjects[] = $name;
        }
        if ($c > 0) { $s = $m->real_escape_string(serialize($new)); q("UPDATE {$p}options SET option_value = '$s' WHERE option_id = " . (int) $id); $scrubbed[$name] = $c; }
    } elseif (!$isProtected && preg_match('/(secret|passw|token|api[_-]?key)/i', $val)) {
        note_secret($val, $name); q("DELETE FROM {$p}options WHERE option_id = " . (int) $id); $deleted[] = $name . ' (by value)'; continue;
    }
    $kept++;
}
q("UPDATE {$p}options SET option_value = 'test@example.invalid' WHERE option_name = 'admin_email'");
line("  options kept: $kept ; deleted: " . count($deleted));
line("  DELETED option families (count): transients and caches, anything named key/secret/token/password/license/credential, and the settings of Defender, WPMU DEV, Snapshot, Branda, Gravity Forms, Post SMTP, LiteSpeed cloud/CDN/crawler, Rank Math Google/analytics, Contentful");
$fam = [];
foreach ($deleted as $d) {
    $f = preg_match('/^_site_transient_/', $d) ? '_site_transient_*' : (preg_match('/^_transient_/', $d) ? '_transient_*' : (preg_match('/^(wd_|wdp_|wp_?defender|defender|wpdefender)/', $d) ? 'defender/wpmudev' : (preg_match('/^(litespeed)/', $d) ? 'litespeed.*' : (preg_match('/^(gform|gravityforms|rg_|gf_)/', $d) ? 'gravity forms' : (preg_match('/^(snapshot)/', $d) ? 'snapshot' : (preg_match('/^(ub_|branda)/', $d) ? 'branda' : (preg_match('/^(postman|mailserver)/', $d) ? 'mail/smtp' : (preg_match('/^(fs_)/', $d) ? 'freemius (objects)' : 'other')))))))) ;
    $fam[$f] = ($fam[$f] ?? 0) + 1;
}
arsort($fam); foreach ($fam as $f => $c) { line(sprintf("    %-22s %4d", $f, $c)); }
$other = array_values(array_filter($deleted, function ($d) { return !preg_match('/^(_site_transient_|_transient_|wd_|wdp_|wp_?defender|defender|wpdefender|litespeed|gform|gravityforms|rg_|gf_|snapshot|ub_|branda|postman|mailserver|fs_)/', $d); }));
sort($other); line("  other deleted option names: " . implode(', ', $other));
line("  SCRUBBED (secret-named keys blanked inside kept options):");
foreach ($scrubbed as $n => $c) { line(sprintf("    %-48s %d value(s) blanked", $n, $c)); }
line("  admin_email replaced with test@example.invalid");
if ($keptWithObjects) { line("  kept structural options that contain objects (not scrubbed): " . implode(', ', $keptWithObjects)); }

// ---- self-check: refuse to report success unless the sensitive tables really are empty -----------
$bad = [];
foreach (['users', 'usermeta', 'comments', 'gf_entry', 'gf_entry_meta', 'post_smtp_logs', 'defender_lockout', 'defender_lockout_log'] as $t) {
    $exists = (int) one("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = '{$p}$t'");
    if ($exists && (int) one("SELECT COUNT(*) FROM `{$p}$t`") > 0) { $bad[] = $t; }
}
if ((int) one("SELECT COUNT(*) FROM {$p}posts WHERE post_status NOT IN ('publish','inherit') OR post_type = 'postman_sent_mail' OR post_password <> ''") > 0) { $bad[] = 'posts(non-public)'; }
if ($bad) { fwrite(STDERR, "SELF-CHECK FAILED, still contains data in: " . implode(', ', $bad) . "\n"); exit(4); }
line(); line("=== self-check passed: users, usermeta, comments, form entries, mail logs, lockouts are empty; only public posts remain ===");

// ---- secrets file for --verify ----------------------------------------------------------------
$f = getenv('SANITIZE_SECRETS') ?: '/root/secrets.tsv'; $old = umask(077);
$out = ''; foreach ($secrets as $v => $name) { $out .= $name . "\t" . base64_encode($v) . "\n"; }
file_put_contents($f, $out); umask($old); chmod($f, 0600);
line("=== token-shaped secret values recorded for verification: " . count($secrets) . " (file mode 600, shred it) ===");
