#!/usr/bin/env bash
# APICS Phase I — API smoke for UAT gate (local).
# Usage: bash scripts/uat-api-smoke.sh [base_url]
set -euo pipefail

BASE="${1:-http://127.0.0.1:8001}"
API="$BASE/api/v1"

echo "== Unauthenticated admin denied =="
CODE=$(curl -s -o /dev/null -w "%{http_code}" "$API/admin/departments")
test "$CODE" = "401"

echo "== Admin login =="
LOGIN=$(curl -s -X POST "$API/auth/login" \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{"email":"admin@csfp.local","password":"Admin@12345","device_name":"uat-smoke"}')
TOKEN=$(php -r 'echo json_decode(stream_get_contents(STDIN), true)["data"]["token"] ?? "";' <<<"$LOGIN")
test -n "$TOKEN"

echo "== Admin departments OK =="
CODE=$(curl -s -o /dev/null -w "%{http_code}" "$API/admin/departments" -H "Authorization: Bearer $TOKEN")
test "$CODE" = "200"

echo "== Applicant cannot list departments =="
ALOGIN=$(curl -s -X POST "$API/auth/login" \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{"email":"applicant@csfp.local","password":"Applicant@123","device_name":"uat-smoke"}')
ATOKEN=$(php -r 'echo json_decode(stream_get_contents(STDIN), true)["data"]["token"] ?? "";' <<<"$ALOGIN")
CODE=$(curl -s -o /dev/null -w "%{http_code}" "$API/admin/departments" -H "Authorization: Bearer $ATOKEN")
test "$CODE" = "403"

echo "== Unsigned logbook print forbidden =="
# Use a random UUID path; signature middleware should 403 before/at model bind
CODE=$(curl -s -o /dev/null -w "%{http_code}" "$BASE/admin/logbooks/00000000-0000-4000-8000-000000000001/print")
test "$CODE" = "403" -o "$CODE" = "404"

echo "UAT API smoke passed against $BASE"
