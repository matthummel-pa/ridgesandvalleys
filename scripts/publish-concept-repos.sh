#!/usr/bin/env bash
# Copy each Work-page concept site into its named GitHub repo and (unless
# --stage-only) push + enable GitHub Pages so future commits deploy the live demo.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
CONCEPT_DIR="$ROOT/web/app/themes/ridgesandvalleys-theme/concept"
MAP="$ROOT/scripts/concept-repos.tsv"
WORKFLOW_SRC="$ROOT/scripts/concept-repo-files/deploy-pages.yml"
OWNER="${CONCEPT_REPO_OWNER:-matthummel-pa}"
PAGES_ORIGIN="${CONCEPT_PAGES_ORIGIN:-https://matthummel-pa.github.io}"
STAGE_ROOT="${CONCEPT_STAGE_DIR:-$ROOT/.concept-repo-stage}"
TOKEN="${CONCEPT_REPOS_TOKEN:-${GH_TOKEN:-${GITHUB_TOKEN:-}}}"

STAGE_ONLY=0
PUSH=1
if [[ "${1:-}" == "--stage-only" ]]; then
  STAGE_ONLY=1
  PUSH=0
fi

if [[ ! -f "$MAP" || ! -d "$CONCEPT_DIR" ]]; then
  echo "Missing concept map or source folder" >&2
  exit 1
fi

git_identity() {
  git -C "$1" config user.email >/dev/null 2>&1 || git -C "$1" config user.email "41898282+github-actions[bot]@users.noreply.github.com"
  git -C "$1" config user.name >/dev/null 2>&1 || git -C "$1" config user.name "github-actions[bot]"
}

rewrite_urls() {
  local dir="$1" folder="$2" repo="$3"
  local old="https://example-concept.test/${folder}"
  local new="${PAGES_ORIGIN}/${repo}"
  find "$dir" -type f \( -name '*.html' -o -name '*.js' -o -name '*.css' -o -name '*.md' \) -print0 |
    xargs -0 sed -i "s#${old}#${new}#g"
}

write_readme() {
  local dest="$1" title="$2" repo="$3" folder="$4"
  cat >"$dest/README.md" <<EOF
# ${title}

Self-initiated concept website by [Ridges & Valleys Studio](https://ridgesandvalleys.com) for a Gettysburg / Adams County business.

**Live demo:** ${PAGES_ORIGIN}/${repo}/

This repo is the source of truth for the working HTML demo. Push to \`main\` and GitHub Pages republishes the live site.

## Develop

1. Edit the HTML / CSS / JS in this repo (same files as the live demo).
2. Open \`index.html\` locally, or serve the folder with any static server.
3. Commit and push to \`main\`. The **Deploy live demo** workflow publishes to GitHub Pages.

## First-time Pages setup

If the live URL 404s after the first push: **Settings → Pages → Source: GitHub Actions**.

## Source

Copied from \`web/app/themes/ridgesandvalleys-theme/concept/${folder}/\` in [matthummel-pa/ridgesandvalleys](https://github.com/matthummel-pa/ridgesandvalleys). Future updates belong here, not in the marketing site repo.
EOF
}

stage_one() {
  local folder="$1" repo="$2" title="$3"
  local src="$CONCEPT_DIR/$folder"
  local dest="$STAGE_ROOT/$repo"

  if [[ ! -d "$src" ]]; then
    echo "Missing source: $src" >&2
    exit 1
  fi
  if [[ ! -f "$src/index.html" ]]; then
    echo "Missing index.html in $src" >&2
    exit 1
  fi

  rm -rf "$dest"
  mkdir -p "$dest"
  rsync -a --exclude 'preview.jpg' "$src/" "$dest/"
  mkdir -p "$dest/.github/workflows"
  cp "$WORKFLOW_SRC" "$dest/.github/workflows/deploy-pages.yml"
  touch "$dest/.nojekyll"
  printf '%s\n' '.DS_Store' '_site/' >>"$dest/.gitignore"
  write_readme "$dest" "$title" "$repo" "$folder"
  rewrite_urls "$dest" "$folder" "$repo"

  if grep -R -n --include='*.html' 'example-concept.test' "$dest" >/dev/null; then
    echo "Unrewritten example-concept.test URLs remain in $repo" >&2
    grep -R -n --include='*.html' 'example-concept.test' "$dest" >&2 || true
    exit 1
  fi
}

remote_url() {
  local repo="$1"
  if [[ -n "$TOKEN" ]]; then
    printf 'https://x-access-token:%s@github.com/%s/%s.git' "$TOKEN" "$OWNER" "$repo"
  else
    printf 'https://github.com/%s/%s.git' "$OWNER" "$repo"
  fi
}

enable_pages() {
  local repo="$1"
  local payload='{"build_type":"workflow"}'
  if ! gh api -X POST "repos/${OWNER}/${repo}/pages" --input - <<<"$payload" >/dev/null 2>&1; then
    gh api -X PUT "repos/${OWNER}/${repo}/pages" --input - <<<"$payload" >/dev/null 2>&1 || true
  fi
  gh api -X PATCH "repos/${OWNER}/${repo}" \
    -f "homepage=${PAGES_ORIGIN}/${repo}/" >/dev/null 2>&1 || true
}

push_one() {
  local repo="$1" dest="$STAGE_ROOT/$repo"
  local tmp url heads
  tmp="$(mktemp -d)"
  url="$(remote_url "$repo")"

  heads="$(git ls-remote "$url" HEAD 2>/dev/null || true)"
  if [[ -n "$heads" ]]; then
    git clone --depth 1 "$url" "$tmp/repo"
  else
    mkdir -p "$tmp/repo"
    git -C "$tmp/repo" init -b main
    git -C "$tmp/repo" remote add origin "$url"
  fi

  git_identity "$tmp/repo"

  if [[ -f "$tmp/repo/LICENSE" && ! -f "$dest/LICENSE" ]]; then
    cp "$tmp/repo/LICENSE" "$dest/LICENSE"
  fi

  find "$tmp/repo" -mindepth 1 -maxdepth 1 ! -name '.git' -exec rm -rf {} +
  rsync -a "$dest/" "$tmp/repo/"

  git -C "$tmp/repo" add -A
  if git -C "$tmp/repo" diff --cached --quiet; then
    echo "No changes for $repo"
  else
    git -C "$tmp/repo" commit -m "Publish ${repo} live demo from ridgesandvalleys concept site"
    git -C "$tmp/repo" push -u origin HEAD:main
  fi

  enable_pages "$repo"
  rm -rf "$tmp"
}

mkdir -p "$STAGE_ROOT"
echo "Staging concept repos into $STAGE_ROOT"

while IFS=$'\t' read -r folder repo title; do
  [[ -z "${folder:-}" || "$folder" == \#* ]] && continue
  echo "==> $folder → $OWNER/$repo ($title)"
  stage_one "$folder" "$repo" "$title"
  if [[ "$PUSH" -eq 1 ]]; then
    push_one "$repo"
  fi
done <"$MAP"

echo "Staged $(find "$STAGE_ROOT" -mindepth 1 -maxdepth 1 -type d | wc -l) repos"
if [[ "$STAGE_ONLY" -eq 1 ]]; then
  echo "Stage-only complete (no push)."
fi
