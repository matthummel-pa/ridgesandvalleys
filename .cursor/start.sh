#!/usr/bin/env bash
# Per-boot reconciliation: bring up the local MariaDB service the WordPress
# dev stack needs. Idempotent and safe to run on every environment start.
set -euo pipefail

DATADIR=/var/lib/mysql
SOCK=/var/run/mysqld/mysqld.sock
LOG=/var/log/mariadb-dev.log

sudo mkdir -p /var/run/mysqld
sudo chown mysql:mysql /var/run/mysqld

# First-ever boot on a clean image: initialise the system tables.
if [ ! -d "${DATADIR}/mysql" ]; then
  sudo mariadb-install-db --user=mysql --datadir="${DATADIR}" >/dev/null 2>&1 || true
fi

# Start the daemon only if it is not already answering. The launch and its
# log redirect both run under sudo so root (not the calling user) opens the
# log file in /var/log.
if ! sudo mysqladmin --socket="${SOCK}" ping >/dev/null 2>&1; then
  sudo sh -c "setsid mariadbd --user=mysql --datadir='${DATADIR}' --socket='${SOCK}' </dev/null >'${LOG}' 2>&1 &"
  for _ in $(seq 1 60); do
    if sudo mysqladmin --socket="${SOCK}" ping >/dev/null 2>&1; then
      break
    fi
    sleep 1
  done
fi

# Fail loudly if the database never came up.
sudo mysqladmin --socket="${SOCK}" ping
echo "MariaDB is up."
