# Performance Benchmarking

Version: 1.0

## Purpose

Establish a repeatable process for measuring and tracking production performance. The goal is not to achieve a specific score in isolation, but to detect regressions over time and validate the impact of changes.

---

## Key Metrics

| Metric | Tool | Good | Needs Improvement | Poor |
|---|---|---|---|---|
| LCP (Largest Contentful Paint) | PageSpeed Insights | < 2.5s | 2.5s – 4.0s | > 4.0s |
| INP (Interaction to Next Paint) | PageSpeed Insights | < 200ms | 200ms – 500ms | > 500ms |
| CLS (Cumulative Layout Shift) | PageSpeed Insights | < 0.1 | 0.1 – 0.25 | > 0.25 |
| TTFB (Time to First Byte) | curl / PageSpeed | < 800ms | 800ms – 1.8s | > 1.8s |
| PageSpeed Score (Mobile) | PageSpeed Insights | 90 – 100 | 50 – 89 | 0 – 49 |
| PageSpeed Score (Desktop) | PageSpeed Insights | 90 – 100 | 50 – 89 | 0 – 49 |

Thresholds are Google Core Web Vitals standards as of 2025.

---

## Tools

**Google PageSpeed Insights** — primary tool. Measures real-world field data (Chrome UX Report) alongside lab data. Use for monthly benchmarks.

URL: https://pagespeed.web.dev/

**GTmetrix** — detailed waterfall view. Useful for diagnosing specific bottlenecks (large images, render-blocking scripts, third-party delays).

URL: https://gtmetrix.com/

**curl TTFB script** — quick server-response check. Run from the command line to measure warm-cache TTFB without loading a browser.

```bash
bash scripts/benchmark.sh
```

---

## Measurement Rules

Always measure the **warm cache** state (LiteSpeed cache populated) for real-world performance. Cold cache numbers are not representative of what visitors experience.

To ensure cache is warm: visit the page once in a browser before running measurements, or trigger a deploy which warms the cache on first request.

Measure from **outside the server** — PageSpeed Insights and GTmetrix do this by default. The curl script also runs against the live URL.

---

## Pages to Benchmark

| Page | URL |
|---|---|
| Homepage | https://www.nigma.ae/ |

Add further pages here as the site grows or key pages are identified.

---

## Cadence

**After every significant change** — run the curl TTFB script before and after the change to confirm no regression:

```bash
bash scripts/benchmark.sh
```

**Monthly** — run a full PageSpeed Insights test on all benchmark pages. Record results in the Baseline Record below.

**After plugin updates** — plugin updates can introduce JavaScript or CSS regressions. Run a PageSpeed check after bulk updates.

---

## Baseline Record

Record scores each month. Add a row per measurement.

| Date | Page | PSI Mobile | PSI Desktop | LCP | CLS | INP | TTFB |
|---|---|---|---|---|---|---|---|
| 2026-06-27 | Homepage | — | — | — | — | — | 449ms (curl baseline) |

Update this table after each monthly benchmark run.

---

## Interpreting Results

A single poor reading is not necessarily a problem — PageSpeed Insights results vary based on server load and network conditions. Look for **trends across multiple measurements**, not isolated scores.

If scores drop significantly (> 10 points) after a specific change:

1. Identify what changed (plugin update, new script, image added).
2. Check GTmetrix waterfall to pinpoint the bottleneck.
3. Resolve before the next deploy or revert if critical.

---

## LiteSpeed Cache Notes

LiteSpeed cache has a significant impact on TTFB and overall score. If benchmarks look unexpectedly poor:

- Confirm LiteSpeed cache is enabled and warmed.
- Check `/var/www/nigma/wp-content/litespeed/` is populated.
- Confirm the CSP mu-plugin is not interfering with cache headers.

---

## Related Documents

- docs/security-review.md
- docs/deployment.md
- wp-content/mu-plugins/security-headers-csp.php
