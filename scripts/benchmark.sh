#!/usr/bin/env bash
# Measure TTFB and total load time for benchmark pages.
# Usage: bash scripts/benchmark.sh [url]

set -euo pipefail

PAGES=(
    "https://www.nigma.ae/"
)

if [ "${1:-}" != "" ]; then
    PAGES=("$1")
fi

echo ""
echo "NIGMA Performance Benchmark — TTFB & Load Time"
echo "================================================"
echo ""

for URL in "${PAGES[@]}"; do
    echo "Page: $URL"
    curl -s -o /dev/null -w \
        "  TTFB:       %{time_starttransfer}s\n  Total:      %{time_total}s\n  Size:       %{size_download} bytes\n  HTTP:       %{http_code}\n" \
        --max-time 30 \
        -H "Cache-Control: no-cache" \
        "$URL"
    echo ""
done
