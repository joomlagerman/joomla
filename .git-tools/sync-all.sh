#!/usr/bin/env bash
# Sync all branches listed in .git-tools/branches.txt
# Usage: ./.git-tools/sync-all.sh [--force] [--merge] [--rebase]
set -euo pipefail

DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BRANCH_LIST="${DIR}/branches.txt"

if [ ! -f "${BRANCH_LIST}" ]; then
  echo "❌ Branch list not found at ${BRANCH_LIST}" >&2
  exit 1
fi

flags=("$@")

while IFS= read -r BRANCH || [ -n "$BRANCH" ]; do
  [[ -z "$BRANCH" || "$BRANCH" =~ ^# ]] && continue
  echo "────────────────────────────────────────"
  echo "🔄 Syncing ${BRANCH}…"
  "${DIR}/sync-branch.sh" "${BRANCH}" "${flags[@]}"
done < "${BRANCH_LIST}"

echo "✅ All listed branches processed."
