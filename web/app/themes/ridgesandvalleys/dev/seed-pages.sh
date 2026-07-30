#!/usr/bin/env bash
#
# seed-pages.sh — create the Ridges & Valleys starter pages, assign their page
# templates, set the front/blog pages, and build the primary + footer menus.
# Idempotent: existing pages (matched by slug) are reused, not duplicated.
#
#   bash dev/seed-pages.sh          # native dev server (.dev-wp)
#   bash dev/seed-pages.sh --ddev   # the Bedrock+DDEV site (../ridgesandvalleys-site)
#
set -euo pipefail
cd "$(dirname "${BASH_SOURCE[0]}")/.."

if [ "${1:-}" = "--ddev" ]; then
  SITE="$(cd .. && pwd)/ridgesandvalleys-site"
  [ -d "$SITE/.ddev" ] || { echo "No DDEV site at ../ridgesandvalleys-site"; exit 1; }
  wp() { ( cd "$SITE" && ddev wp "$@" ); }
else
  SB="$PWD/.dev-wp"
  [ -f "$SB/wp/wp-load.php" ] || { echo "No local WordPress at .dev-wp — run 'npm run native' first."; exit 1; }
  wp() { php -d memory_limit=512M "$SB/wp-cli.phar" --path="$SB/wp" --allow-root "$@"; }
fi

# mkpage <slug> <title> <template>  -> prints the page ID
mkpage() {
  local slug="$1" title="$2" tpl="$3" id
  id=$(wp post list --post_type=page --name="$slug" --field=ID --format=ids 2>/dev/null | tr -d '[:space:]')
  if [ -z "$id" ]; then
    id=$(wp post create --post_type=page --post_status=publish --post_title="$title" --post_name="$slug" --porcelain)
    echo "  + created  $title  (/$slug/)" >&2
  else
    echo "  · exists   $title  (/$slug/)" >&2
  fi
  [ -n "$tpl" ] && wp post meta update "$id" _wp_page_template "$tpl" >/dev/null 2>&1
  printf '%s' "$id"
}

echo "› Creating pages + assigning templates…"
HOME=$(mkpage home         "Home"          "")
ABOUT=$(mkpage about        "About"         "template-about.blade.php")
SERVICES=$(mkpage services  "Services"      "template-services.blade.php")
WORK=$(mkpage work          "Work"          "template-work.blade.php")
FAQ=$(mkpage faq            "FAQ"           "template-faq.blade.php")
TOOLS=$(mkpage free-tools   "Free Tools"    "template-tools.blade.php")
CONTACT=$(mkpage contact    "Contact"       "template-contact.blade.php")
A11Y=$(mkpage accessibility "Accessibility" "template-accessibility.blade.php")
JOURNAL=$(mkpage journal    "Journal"       "")

echo "› Setting front page + blog page…"
wp option update show_on_front page >/dev/null
wp option update page_on_front "$HOME" >/dev/null
wp option update page_for_posts "$JOURNAL" >/dev/null
wp option update blogname "Ridges & Valleys Studio" >/dev/null 2>&1 || true
wp rewrite structure '/%postname%/' >/dev/null 2>&1 || true
wp rewrite flush --hard >/dev/null 2>&1 || true

echo "› Building menus…"
mkmenu() {  # name location  page-ids...
  local name="$1" loc="$2"; shift 2
  local old; old=$(wp menu list --fields=term_id,name --format=csv 2>/dev/null | awk -F, -v n="$name" '$2==n{print $1}' | head -1)
  [ -n "$old" ] && wp menu delete "$old" >/dev/null 2>&1 || true
  local m; m=$(wp menu create "$name" --porcelain)
  local p; for p in "$@"; do [ -n "$p" ] && wp menu item add-post "$m" "$p" >/dev/null 2>&1 || true; done
  wp menu location assign "$m" "$loc" >/dev/null 2>&1 || true
}
mkmenu "Primary" primary "$ABOUT" "$SERVICES" "$WORK" "$FAQ" "$TOOLS" "$CONTACT"
mkmenu "Footer" footer "$SERVICES" "$WORK" "$JOURNAL" "$A11Y" "$CONTACT"

echo ""
echo "  ✔ Pages + menus ready: Home · About · Services · Work · FAQ · Free Tools · Contact · Accessibility · Journal"
