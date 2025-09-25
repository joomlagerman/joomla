#!/usr/bin/env bash
# Sync a given branch from 'upstream' and push to 'origin'.
# Usage: ./.git-tools/sync-branch.sh <branch> [--force] [--merge] [--rebase]
set -euo pipefail

if [ $# -lt 1 ]; then
  echo "Usage: $0 <branch> [--force] [--merge] [--rebase]" >&2
  exit 2
fi

BRANCH="$1"; shift || true

force=false
mode="ff"   # ff | merge | rebase

for arg in "$@"; do
  case "$arg" in
    --force) force=true ;;
    --merge) mode="merge" ;;
    --rebase) mode="rebase" ;;
    *) echo "Unknown option: $arg" >&2; exit 2 ;;
  esac
done

if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
  echo "❌ Not inside a Git repository. cd into your repo first." >&2
  exit 1
fi

if ! git remote get-url upstream >/dev/null 2>&1; then
  echo "❌ Remote 'upstream' not configured." >&2
  exit 1
fi
if ! git remote get-url origin >/dev/null 2>&1; then
  echo "❌ Remote 'origin' not configured." >&2
  exit 1
fi

if ! $force; then
  if [ -n "$(git status --porcelain)" ]; then
    echo "❌ Working tree is dirty. Commit/stash or rerun with --force." >&2
    exit 1
  fi
fi

echo "➡️  Fetching from remotes…"
if ! git fetch --prune upstream "${BRANCH}"; then
  echo "❌ Failed to fetch from 'upstream'. Check your network connection and permissions." >&2
  exit 1
fi
if ! git fetch --prune origin "${BRANCH}"; then
  echo "❌ Failed to fetch from 'origin'. Check your network connection and permissions." >&2
  exit 1
fi
# Ensure local branch exists and switch to it
if git show-ref --verify --quiet "refs/heads/${BRANCH}"; then
  git switch "${BRANCH}"
else
  echo "ℹ️  Creating local branch ${BRANCH} tracking upstream/${BRANCH} (if exists)"
  if git show-ref --verify --quiet "refs/remotes/upstream/${BRANCH}"; then
    git switch -c "${BRANCH}" --track "upstream/${BRANCH}"
  else
    git switch -c "${BRANCH}"
  fi
fi

echo "➡️  Syncing local ${BRANCH} with upstream/${BRANCH} (mode=${mode})…"
if git show-ref --verify --quiet "refs/remotes/upstream/${BRANCH}"; then
  case "$mode" in
    ff)
      if ! git merge --ff-only "upstream/${BRANCH}"; then
        echo "❌ Fast-forward merge not possible. Use --merge or --rebase."
        exit 1
      fi
      ;;
    merge)
      git merge --no-edit "upstream/${BRANCH}"
      ;;
    rebase)
      git rebase "upstream/${BRANCH}"
      ;;
  esac
else
  echo "ℹ️  No upstream/${BRANCH} exists yet. Skipping merge/rebase."
fi

echo "➡️  Pushing ${BRANCH} to origin…"
git push -u origin "${BRANCH}"

echo "✅ Done. Branch ${BRANCH} is now synced (as applicable) and pushed to origin."
