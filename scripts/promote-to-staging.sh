#!/usr/bin/env bash
# Promote theme + mu-plugin from development onto staging (no Docker/dev files, no push).
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

SOURCE_BRANCH="development"
TARGET_BRANCH="staging"
PATHS=(
  "wp-content/themes/rozgadana-jana/"
  "wp-content/mu-plugins/rj-reviews.php"
  "wp-content/mu-plugins/rj-reviews/"
)

DRY_RUN=0
if [[ "${1:-}" == "--dry-run" ]]; then
  DRY_RUN=1
elif [[ -n "${1:-}" ]]; then
  echo "Usage: $0 [--dry-run]" >&2
  exit 1
fi

if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
  echo "Error: not inside a git repository." >&2
  exit 1
fi

# Ignore untracked files; only dirty tracked files block promote/checkout.
if [[ -n "$(git status --porcelain --untracked-files=no)" ]]; then
  echo "Error: working tree is not clean. Commit or stash changes first." >&2
  exit 1
fi

for branch in "$SOURCE_BRANCH" "$TARGET_BRANCH"; do
  if ! git show-ref --verify --quiet "refs/heads/${branch}"; then
    echo "Error: local branch '${branch}' does not exist." >&2
    exit 1
  fi
done

ORIGINAL_BRANCH="$(git branch --show-current)"
RESTORE_BRANCH=1

restore_branch() {
  if [[ "${RESTORE_BRANCH}" -eq 1 && -n "${ORIGINAL_BRANCH}" ]]; then
    git checkout "${ORIGINAL_BRANCH}" >/dev/null 2>&1 || true
  fi
}
trap restore_branch EXIT

if [[ "${DRY_RUN}" -eq 1 ]]; then
  echo "Dry run: diff ${TARGET_BRANCH}...${SOURCE_BRANCH} for promote paths"
  echo
  git diff "${TARGET_BRANCH}...${SOURCE_BRANCH}" -- "${PATHS[@]}"
  exit 0
fi

git checkout "${TARGET_BRANCH}"
git checkout "${SOURCE_BRANCH}" -- "${PATHS[@]}"

if git diff --cached --quiet && git diff --quiet; then
  echo "Nothing to promote: theme and mu-plugin already match ${SOURCE_BRANCH}."
  exit 0
fi

# Stage any leftover unstaged path changes (checkout usually stages them).
git add -- "${PATHS[@]}"

echo "Staged changes for promote:"
git status --short -- "${PATHS[@]}"
echo

git commit -m "promote: theme + mu-plugin from development"

echo
echo "Promote commit created on ${TARGET_BRANCH}."
echo "Review on ${TARGET_BRANCH}, then push manually: git push origin ${TARGET_BRANCH}"
echo
git --no-pager show --stat --oneline -1
