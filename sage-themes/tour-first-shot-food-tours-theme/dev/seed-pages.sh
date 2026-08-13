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

echo "› Seeding pages for First Shot Food & History Tours…"
HOME_ID=$(mkpage home "Home" "")
BOOK=$(mkpage book "Book a Gettysburg Tour Date" "template-book.blade.php")
CONTACT=$(mkpage contact "Contact First Shot Food & History Tours" "template-contact.blade.php")
FAQ=$(mkpage faq "FAQ" "template-faq.blade.php")
ROUTE=$(mkpage route "The Gettysburg Walking Route & Local Eats" "template-route.blade.php")
TOURS=$(mkpage tours "Gettysburg Food & History Tours" "template-tours.blade.php")

wp option update show_on_front page >/dev/null
wp option update page_on_front "$HOME_ID" >/dev/null
wp option update blogname "First Shot Food & History Tours" >/dev/null 2>&1 || true
wp rewrite structure '/%postname%/' >/dev/null 2>&1 || true
wp rewrite flush --hard >/dev/null 2>&1 || true

echo "  ✔ Front page + interior concept pages are ready."

