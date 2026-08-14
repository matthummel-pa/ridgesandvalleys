#!/usr/bin/env bash
# Copy each Work-page concept site into its named GitHub repo and (unless
# --stage-only) push to main and enable GitHub Pages from that branch.
#
# Dest repos serve static HTML from main (no Actions workflow), so a
# fine-grained PAT only needs Contents + Pages write — not Workflows.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
CONCEPT_DIR="$ROOT/web/app/themes/ridgesandvalleys-theme/concept"
MAP="$ROOT/scripts/concept-repos.tsv"
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

if [[ "$PUSH" -eq 1 && -z "$TOKEN" ]]; then
  echo "Missing CONCEPT_REPOS_TOKEN (or GH_TOKEN)." >&2
  echo "Create a fine-grained PAT (resource owner matthummel-pa) with Contents and Pages write on the ten *-theme repos, then set it as secret CONCEPT_REPOS_TOKEN." >&2
  exit 1
fi

git_identity() {
  git -C "$1" config user.email "41898282+github-actions[bot]@users.noreply.github.com"
  git -C "$1" config user.name "github-actions[bot]"
  git -C "$1" config commit.gpgsign false
}

public_remote() {
  printf 'https://github.com/%s/%s.git' "$OWNER" "$1"
}

# Fine-grained PATs authenticate git over HTTPS as x-access-token. A Bearer
# extraHeader is easy to split on spaces (git -c) and then GitHub 401s, which
# surfaces as "could not read Username for 'https://github.com'".
configure_git_auth() {
  export GIT_TERMINAL_PROMPT=0
  unset GIT_ASKPASS || true
  if [[ -n "$TOKEN" ]]; then
    git config --global --unset-all url.https://github.com/.insteadof >/dev/null 2>&1 || true
    git config --global url."https://x-access-token:${TOKEN}@github.com/".insteadOf "https://github.com/"
  fi
}

preflight_token() {
  local repo="gettysburg-hotel-theme"
  echo "Checking CONCEPT_REPOS_TOKEN can access ${OWNER}/${repo}"
  if ! gh api "repos/${OWNER}/${repo}" --jq .full_name >/dev/null; then
    echo "PAT cannot read ${OWNER}/${repo}. Recreate the fine-grained token with Contents + Pages write on all ten *-theme repos." >&2
    exit 1
  fi
  if ! git ls-remote "$(public_remote "$repo")" HEAD >/dev/null; then
    echo "git could not authenticate to ${OWNER}/${repo} with this PAT." >&2
    exit 1
  fi
}

rewrite_urls() {
  local dir="$1" folder="$2" repo="$3"
  local old="https://example-concept.test/${folder}"
  local new="${PAGES_ORIGIN}/${repo}"
  find "$dir" -type f \( -name '*.html' -o -name '*.js' -o -name '*.css' -o -name '*.md' \) -print0 |
    xargs -0 -r sed -i "s#${old}#${new}#g"
}

write_readme() {
  local dest="$1" title="$2" repo="$3" folder="$4"
  cat >"$dest/README.md" <<EOF
# ${title}

Self-initiated concept website by [Ridges & Valleys Studio](https://ridgesandvalleys.com) for a Gettysburg / Adams County business.

**Live demo:** ${PAGES_ORIGIN}/${repo}/

This repo is the source of truth for the working HTML demo. Push to \`main\` and GitHub Pages republishes the live site from the branch root.

## Develop

1. Edit the HTML / CSS / JS in this repo (same files as the live demo).
2. Open \`index.html\` locally, or serve the folder with any static server.
3. Commit and push to \`main\`. GitHub Pages serves \`/\` from \`main\`.

## First-time Pages setup

If the live URL 404s after the first push: **Settings → Pages → Deploy from a branch → \`main\` / (root)**.

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
  touch "$dest/.nojekyll"
  printf '%s\n' '.DS_Store' >"$dest/.gitignore"
  write_readme "$dest" "$title" "$repo" "$folder"
  rewrite_urls "$dest" "$folder" "$repo"

  if grep -R -n --include='*.html' 'example-concept.test' "$dest" >/dev/null; then
    echo "Unrewritten example-concept.test URLs remain in $repo" >&2
    grep -R -n --include='*.html' 'example-concept.test' "$dest" >&2 || true
    exit 1
  fi
}

enable_pages() {
  local repo="$1"
  local out rc=0
  local payload='{"build_type":"legacy","source":{"branch":"main","path":"/"}}'

  set +e
  out="$(gh api -X POST "repos/${OWNER}/${repo}/pages" --input - <<<"$payload" 2>&1)"
  rc=$?
  if [[ "$rc" -ne 0 ]]; then
    out="$(gh api -X PUT "repos/${OWNER}/${repo}/pages" --input - <<<"$payload" 2>&1)"
    rc=$?
  fi
  set -e

  if [[ "$rc" -ne 0 ]]; then
    echo "Could not enable Pages API for ${repo} (PAT needs Pages: write)."
    echo "$out"
    echo "Enable once in the GitHub UI: Settings → Pages → Deploy from a branch → main / (root)."
  else
    echo "Pages set to main / (root) for ${repo}"
  fi

  gh api -X PATCH "repos/${OWNER}/${repo}" \
    -f "homepage=${PAGES_ORIGIN}/${repo}/" >/dev/null 2>&1 || true
}

push_one() {
  local repo="$1" dest="$STAGE_ROOT/$repo"
  local tmp url heads
  tmp="$(mktemp -d)"
  url="$(public_remote "$repo")"

  heads="$(git ls-remote "$url" HEAD 2>/dev/null || true)"
  if [[ -n "$heads" ]]; then
    git clone --depth 1 "$url" "$tmp/repo"
  else
    echo "Empty dest repo ${repo}; initializing main"
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
    echo "Pushed ${OWNER}/${repo} main"
  fi

  enable_pages "$repo"
  rm -rf "$tmp"
}

mkdir -p "$STAGE_ROOT"
if [[ "$PUSH" -eq 1 ]]; then
  configure_git_auth
  preflight_token
fi
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
