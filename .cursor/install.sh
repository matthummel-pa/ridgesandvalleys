#!/usr/bin/env bash
# Idempotent repository bootstrap for the Ridges & Valleys Bedrock + Sage site.
# Prepares dependencies, builds the theme, and provisions a local WordPress
# install against MariaDB. Safe to run repeatedly.
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "${REPO_ROOT}"

THEME_DIR="web/app/themes/ridgesandvalleys-theme"
WP_URL="http://localhost:8080"

# --- 1. Make sure the database service is running (needed for wp-cli below) ---
bash "${REPO_ROOT}/.cursor/start.sh"

# --- 2. Local .env (dev-only creds + throwaway salts). Never overwrite. -------
if [ ! -f .env ]; then
  {
    echo "DB_NAME='ridgesandvalleys'"
    echo "DB_USER='rv'"
    echo "DB_PASSWORD='rvpass'"
    echo "DB_HOST='127.0.0.1'"
    echo "DB_PREFIX='ssb_'"
    echo ""
    echo "WP_ENV='development'"
    echo "WP_HOME='${WP_URL}'"
    echo 'WP_SITEURL="${WP_HOME}/wp"'
    echo ""
    for key in AUTH_KEY SECURE_AUTH_KEY LOGGED_IN_KEY NONCE_KEY \
               AUTH_SALT SECURE_AUTH_SALT LOGGED_IN_SALT NONCE_SALT; do
      val="$(LC_ALL=C tr -dc 'A-Za-z0-9' </dev/urandom | head -c 64)"
      echo "${key}='${val}'"
    done
  } > .env
  echo "Wrote development .env"
fi

# --- 3. PHP dependencies: WordPress core + plugins (Bedrock root) ------------
composer install --no-interaction --no-progress

# --- 4. Theme: Acorn deps, node modules, production asset build --------------
pushd "${THEME_DIR}" >/dev/null
composer install --no-interaction --no-progress
npm ci
npm run build
popd >/dev/null

# --- 5. Local database + application user ------------------------------------
sudo mysql <<'SQL'
CREATE DATABASE IF NOT EXISTS ridgesandvalleys;
CREATE USER IF NOT EXISTS 'rv'@'127.0.0.1' IDENTIFIED BY 'rvpass';
CREATE USER IF NOT EXISTS 'rv'@'localhost' IDENTIFIED BY 'rvpass';
GRANT ALL ON ridgesandvalleys.* TO 'rv'@'127.0.0.1';
GRANT ALL ON ridgesandvalleys.* TO 'rv'@'localhost';
FLUSH PRIVILEGES;
SQL

# --- 6. WordPress install + theme/plugin activation (first run only) ---------
# There is no live DB to pull in a Cloud Agent, so stand up a fresh install.
# The theme renders its page-field defaults, so the site is fully populated.
if ! wp core is-installed >/dev/null 2>&1; then
  wp core install \
    --url="${WP_URL}" \
    --title="Ridges & Valleys (Local Dev)" \
    --admin_user="admin" \
    --admin_password="admin" \
    --admin_email="matt@ridgesandvalleys.com" \
    --skip-email
fi

wp theme activate ridgesandvalleys-theme >/dev/null 2>&1 || true
wp plugin activate --all >/dev/null 2>&1 || true

echo "Install complete. Run 'bash .cursor/start.sh' then start the dev server:"
echo "  wp server --docroot=web --host=0.0.0.0 --port=8080 --path=web/wp"
