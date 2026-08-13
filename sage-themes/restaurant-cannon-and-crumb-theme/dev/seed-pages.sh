#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "${BASH_SOURCE[0]}")/.."

if ! command -v wp >/dev/null 2>&1; then
  echo "wp-cli (wp) is required to seed pages." >&2
  exit 1
fi

mkpage() {
  local slug="$1" title="$2" tpl="$3" id
  id=$(wp post list --post_type=page --name="$slug" --field=ID --format=ids 2>/dev/null | tr -d "[:space:]")
  if [ -z "$id" ]; then
    id=$(wp post create --post_type=page --post_status=publish --post_title="$title" --post_name="$slug" --porcelain)
    echo "  + created  $title  (/$slug/)" >&2
  else
    echo "  · exists   $title  (/$slug/)" >&2
  fi
  [ -n "$tpl" ] && wp post meta update "$id" _wp_page_template "$tpl" >/dev/null 2>&1
  printf "%s" "$id"
}

echo "› Seeding pages for Cannon & Crumb…"
HOME_ID=$(mkpage home "Home" "")
CATERING=$(mkpage catering "Catering & Events" "template-catering.blade.php")
CONTACT=$(mkpage contact "Contact" "template-contact.blade.php")
MENU=$(mkpage menu "Menu" "template-menu.blade.php")
ORDER=$(mkpage order "Order Online" "template-order.blade.php")
VISIT=$(mkpage visit "Visit Us on Lincoln Square" "template-visit.blade.php")

wp option update show_on_front page >/dev/null
wp option update page_on_front "$HOME_ID" >/dev/null
wp option update blogname "Cannon & Crumb" >/dev/null 2>&1 || true
wp rewrite structure '/%postname%/' >/dev/null 2>&1 || true
wp rewrite flush --hard >/dev/null 2>&1 || true

echo "  ✔ Front page + interior concept pages are ready."

