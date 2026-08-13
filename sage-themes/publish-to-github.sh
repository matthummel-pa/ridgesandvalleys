#!/usr/bin/env bash
# Create one public GitHub repo per Sage concept theme and push it.
# Requires a GitHub token that can create repositories (this Cursor token cannot).
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
OWNER="${GITHUB_OWNER:-matthummel-pa}"

themes=(
  "gettysburg-hotel-theme|Lantern & Laurel Inn — Sage 11 WordPress theme (Ridges & Valleys concept)"
  "hotel-cupola-field-theme|Cupola & Field Hotel — Sage 11 WordPress theme (Ridges & Valleys concept)"
  "gettysburg-restaurant-theme|Field & Musket Tavern — Sage 11 WordPress theme (Ridges & Valleys concept)"
  "restaurant-cannon-and-crumb-theme|Cannon & Crumb — Sage 11 WordPress theme (Ridges & Valleys concept)"
  "gettysburg-retail-theme|Diamond & Ridge Mercantile — Sage 11 WordPress theme (Ridges & Valleys concept)"
  "retail-ridgeline-outfitters-theme|Ridgeline Outfitters — Sage 11 WordPress theme (Ridges & Valleys concept)"
  "tour-hallowed-ground-tours-theme|Hallowed Ground Battlefield Tours — Sage 11 WordPress theme (Ridges & Valleys concept)"
  "tour-first-shot-food-tours-theme|First Shot Food & History Tours — Sage 11 WordPress theme (Ridges & Valleys concept)"
  "realtor-ridgeline-realty-theme|Ridgeline Realty — Sage 11 WordPress theme (Ridges & Valleys concept)"
  "realtor-keystone-homes-and-land-theme|Keystone Homes & Land — Sage 11 WordPress theme (Ridges & Valleys concept)"
)

if ! command -v gh >/dev/null 2>&1; then
  echo "gh (GitHub CLI) is required." >&2
  exit 1
fi

for spec in "${themes[@]}"; do
  repo="${spec%%|*}"
  desc="${spec#*|}"
  dir="$ROOT/$repo"
  [ -d "$dir" ] || { echo "missing $dir" >&2; exit 1; }

  echo "=== $OWNER/$repo ==="
  if gh repo view "$OWNER/$repo" >/dev/null 2>&1; then
    echo "  repo exists — pushing main"
  else
    gh repo create "$OWNER/$repo" --public --description "$desc" --disable-wiki --disable-issues=false
  fi

  tmp="$(mktemp -d)"
  git clone --depth 1 "https://github.com/$OWNER/$repo.git" "$tmp/repo" 2>/dev/null || {
    mkdir -p "$tmp/repo"
    git -C "$tmp/repo" init -b main
    git -C "$tmp/repo" remote add origin "https://github.com/$OWNER/$repo.git"
  }
  rsync -a --delete --exclude .git --exclude node_modules --exclude vendor --exclude public/build "$dir/" "$tmp/repo/"
  git -C "$tmp/repo" add -A
  if git -C "$tmp/repo" diff --cached --quiet; then
    echo "  no changes"
  else
    git -C "$tmp/repo" commit -m "Initial Sage 11 theme from Ridges & Valleys concept"
    git -C "$tmp/repo" push -u origin HEAD:main
  fi
  rm -rf "$tmp"
done

echo "Done. Connect each repo in Cursor, then we can iterate on the themes there."
