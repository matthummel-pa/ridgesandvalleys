#!/usr/bin/env bash
# Idempotent repository bootstrap for the Ridges & Valleys Bedrock + Sage site.
# Prepares dependencies, builds the theme, and provisions a local WordPress
# install against MariaDB. Safe to run repeatedly.
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "${REPO_ROOT}"

THEME_DIR="web/app/themes/ridgesandvalleys-theme"
WP_URL="http://localhost:8080"
DATADIR=/var/lib/mysql
SOCK=/run/mysqld/mysqld.sock

# --- 1. Bring up a reliable MariaDB -----------------------------------------
# A datadir captured while the server was running (e.g. from a VM snapshot)
# leaves InnoDB needing crash recovery, which hangs in the Cloud Agent
# sandbox. If an existing install is already up and healthy, keep it;
# otherwise reinitialise a clean datadir so startup never needs recovery.
if sudo mysqladmin --socket="${SOCK}" ping >/dev/null 2>&1 \
   && wp core is-installed >/dev/null 2>&1; then
  echo "Existing WordPress database detected; keeping it."
else
  echo "Provisioning a clean MariaDB datadir."
  sudo mysqladmin --socket="${SOCK}" shutdown >/dev/null 2>&1 || true
  sleep 2
  sudo rm -rf "${DATADIR}"
  sudo mkdir -p "${DATADIR}"
  sudo chown mysql:mysql "${DATADIR}"
  sudo mariadb-install-db --user=mysql --datadir="${DATADIR}" >/dev/null 2>&1
  bash "${REPO_ROOT}/.cursor/start.sh"
fi

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

# --- 7. Leave MariaDB cleanly shut down -------------------------------------
# The environment build snapshots the VM at the end of install. Shutting the
# server down flushes InnoDB to a clean state so a fresh pod's `start` phase
# can bring it up without crash recovery (which hangs in the sandbox).
sudo mysqladmin --socket="${SOCK}" shutdown >/dev/null 2>&1 || true

echo "Install complete. The environment 'start' phase runs .cursor/start.sh to"
echo "bring MariaDB back up; then start the dev server with:"
echo "  wp server --docroot=web --host=0.0.0.0 --port=8080 --path=web/wp"
