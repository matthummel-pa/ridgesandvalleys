#!/usr/bin/env bash
#
# Dev launcher for the Ridges & Valleys Studio (Sage 11) theme.
#
# The MAIN dev environment is a Bedrock + DDEV site (your ClaudeCoWork convention),
# created once by:  ~/ClaudeCoWork/setup/ridgesandvalleys-dev-site.sh
# It lives at projects/ridgesandvalleys-site and this theme repo is bind-mounted in.
#
#   npm start                    Bring up the DDEV site (built assets) → https://ridgesandvalleys.ddev.site
#   npm run native               No Docker: local PHP + SQLite + Acorn + Vite HMR  → http://localhost:8899
#   bash dev/start.sh --now      WordPress Playground (php-wasm, no Docker)         → :8881
#   bash dev/start.sh --build    Build production assets and exit
#
set -euo pipefail
cd "$(dirname "${BASH_SOURCE[0]}")/.."

say()  { printf '\033[1;32m›\033[0m %s\n' "$1"; }
warn() { printf '\033[1;33m!\033[0m %s\n' "$1"; }
die()  { printf '\033[1;31m✗\033[0m %s\n' "$1" >&2; exit 1; }

command -v node >/dev/null || die "Node.js is required (https://nodejs.org)."
[ -d node_modules ] || { say "Installing npm packages (first run only)…"; npm install; }
[ -f vendor/autoload.php ] || { say "vendor/ missing — running composer install…"; command -v composer >/dev/null || die "Composer required."; composer install; }

MODE="${1:-}"

case "$MODE" in
  --build)
    say "Building production assets…"; npm run build; say "Done → public/build"; exit 0 ;;
  --now)
    say "Starting WordPress Playground (no Docker)…"; VITE_BASE=/wp-content/themes/ridgesandvalleys-theme/public/build/ npm run build
    exec npx @wp-playground/cli start --path . --port 8881 ;;
  --native)
    command -v php >/dev/null || die "PHP 8.3+ is required for --native."
    # Vanilla WP serves themes at /wp-content/themes/… — override the Bedrock default.
    export VITE_BASE=/wp-content/themes/ridgesandvalleys-theme/public/build/
    PORT="${PORT:-8899}"; SANDBOX="$PWD/.dev-wp"; SLUG="$(basename "$PWD")"
    # Pin WP-CLI's phar extraction (its built-in server's router.php) to a stable,
    # project-local temp dir. macOS periodically cleans /var/folders, and if it
    # removes the extracted router.php the running server fatals with
    # "Failed opening required '…wp-cli-extract-from-phar-…router.php'". Keeping it
    # here (inside .dev-wp) puts it out of the OS temp-cleaner's reach.
    export TMPDIR="$SANDBOX/tmp"; mkdir -p "$TMPDIR"
    WPCLI="$SANDBOX/wp-cli.phar"; URL="http://localhost:${PORT}"
    wp() { php -d memory_limit=512M "$WPCLI" --path="$SANDBOX/wp" --allow-root "$@"; }
    mkdir -p "$SANDBOX"
    [ -f "$WPCLI" ] || { say "Fetching WP-CLI…"; curl -sSL https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -o "$WPCLI"; }
    if [ ! -f "$SANDBOX/wp/wp-load.php" ]; then
      say "Setting up local WordPress (SQLite) — first run only…"
      wp core download --quiet
      curl -sSL https://downloads.wordpress.org/plugin/sqlite-database-integration.zip -o "$SANDBOX/sqlite.zip"
      unzip -oq "$SANDBOX/sqlite.zip" -d "$SANDBOX/wp/wp-content/plugins/"
      cp "$SANDBOX/wp/wp-content/plugins/sqlite-database-integration/db.copy" "$SANDBOX/wp/wp-content/db.php"
      sed -i.bak "s#{SQLITE_IMPLEMENTATION_FOLDER_PATH}#$SANDBOX/wp/wp-content/plugins/sqlite-database-integration#g" "$SANDBOX/wp/wp-content/db.php"
      sed -i.bak "s#{SQLITE_PLUGIN}#sqlite-database-integration/load.php#g" "$SANDBOX/wp/wp-content/db.php"
      rm -f "$SANDBOX/wp/wp-content/db.php.bak" "$SANDBOX/sqlite.zip"
      wp config create --dbname=wp --dbuser=root --dbpass= --dbhost=localhost --skip-check --quiet
      wp core install --url="$URL" --title="Ridges & Valleys (dev)" --admin_user=admin --admin_password=admin --admin_email=dev@example.com --skip-email --quiet
      wp rewrite structure '/%postname%/' --quiet
    fi
    mkdir -p "$SANDBOX/wp/wp-content/cache/acorn"
    rm -rf "$SANDBOX/wp/wp-content/themes/$SLUG"; ln -s "$PWD" "$SANDBOX/wp/wp-content/themes/$SLUG"
    # Ensure vanilla URLs (no /wp subpath) so theme/asset URLs resolve. Idempotent.
    wp option update home "$URL" >/dev/null 2>&1; wp option update siteurl "$URL" >/dev/null 2>&1
    wp theme activate "$SLUG" >/dev/null; wp rewrite flush --hard --quiet
    # Free the port if a previous (possibly broken) server is still bound to it —
    # otherwise a new server can't take over and the stale one keeps serving errors.
    command -v lsof >/dev/null && { lsof -ti "tcp:$PORT" 2>/dev/null | xargs kill -9 2>/dev/null || true; }
    php -d memory_limit=512M "$WPCLI" --path="$SANDBOX/wp" --allow-root server --host=localhost --port="$PORT" >/dev/null 2>&1 &
    WP_PID=$!; trap 'kill $WP_PID 2>/dev/null || true; rm -f "$PWD/public/hot" 2>/dev/null || true' EXIT INT TERM
    sleep 2
    printf '\n  \033[1;32m✔ WordPress + Acorn →\033[0m %s   (admin / admin)\n  Vite HMR starting… Ctrl-C stops both.\n\n' "$URL"
    # NOTE: run Vite in the FOREGROUND (not exec) so this shell stays alive and its
    # EXIT/INT trap fires on Ctrl-C — which kills the background WP server too.
    # Using `exec` here replaced the shell and orphaned the WP server as a zombie.
    npx vite ;;
esac

# ---------------------------------------------------------------- default: DDEV main site
SITE_DIR="$(cd .. && pwd)/ridgesandvalleys-site"
if [ ! -d "$SITE_DIR/.ddev" ]; then
  die "Main dev site isn't set up yet. Run this once on your Mac:
     ~/ClaudeCoWork/setup/ridgesandvalleys-dev-site.sh
   Then 'npm start' brings it up. (No Docker? use: npm run native)"
fi
command -v ddev >/dev/null || die "ddev not found — run ~/ClaudeCoWork/setup/install-mac-dev.sh first. (No Docker? use: npm run native)"

say "Building theme assets…"; npm run build
say "Starting the DDEV site (first run pulls containers)…"
( cd "$SITE_DIR" && ddev start )
URL=$( cd "$SITE_DIR" && ddev describe 2>/dev/null | grep -oE 'https://[a-z0-9-]+\.ddev\.site' | head -1 )
URL="${URL:-https://ridgesandvalleys.ddev.site}"
cat <<DONE

  ✔ Main dev site: ${URL}
    Admin:         ${URL}/wp/wp-admin   (admin / admin)
    Fast HMR (no Docker): npm run native
    Rebuild assets after edits: npm run build   (or 'npm run dev' for the Vite dev server)

DONE
( cd "$SITE_DIR" && ddev launch ) 2>/dev/null || true
