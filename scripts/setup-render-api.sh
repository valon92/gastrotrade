#!/usr/bin/env bash
# Krijon shërbimin Render nga render.yaml përmes API.
# Përdorimi: RENDER_API_KEY=rnd_xxx ./scripts/setup-render-api.sh

set -euo pipefail

if [[ -z "${RENDER_API_KEY:-}" ]]; then
  echo "Mungon RENDER_API_KEY."
  echo "Merre nga: https://dashboard.render.com/u/settings#api-keys"
  exit 1
fi

REPO="${RENDER_REPO:-https://github.com/valon92/gastrotrade}"
BRANCH="${RENDER_BRANCH:-main}"

echo "Duke krijuar Blueprint nga $REPO ($BRANCH)..."

RESPONSE=$(curl -sS -X POST "https://api.render.com/v1/blueprints" \
  -H "Authorization: Bearer ${RENDER_API_KEY}" \
  -H "Content-Type: application/json" \
  -d "{\"repo\":\"${REPO}\",\"branch\":\"${BRANCH}\"}")

echo "$RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$RESPONSE"

echo ""
echo "Pas deploy-it:"
echo "  1. Render Dashboard → arontrade-api → Settings → Custom Domains → api.arontrade.net"
echo "  2. Namecheap → CNAME api → (hostname nga Render)"
echo "  3. Vendos MAIL_* dhe OFFICIAL_ORDER_EMAIL në Render Environment"
