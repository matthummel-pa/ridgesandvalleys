#!/usr/bin/env bash
# Per-boot reconciliation: bring up the local MariaDB service the WordPress
# dev stack needs. Idempotent and safe to run on every environment start.
set -euo pipefail

DATADIR=/var/lib/mysql
RUNDIR=/var/run/mysqld
SOCK="${RUNDIR}/mysqld.sock"
LOG=/var/log/mariadb-dev.log

sudo mkdir -p "${RUNDIR}"
sudo chown mysql:mysql "${RUNDIR}"

# If the daemon is already answering, nothing to do.
if sudo mysqladmin --socket="${SOCK}" ping >/dev/null 2>&1; then
  echo "MariaDB already running."
  exit 0
fi

# Clear stale runtime files left behind by a snapshot of a previously
# running server (the pid/socket point at a process that no longer exists).
sudo rm -f "${SOCK}" "${RUNDIR}"/*.pid 2>/dev/null || true

# First-ever boot on a clean image: initialise the system tables.
if [ ! -d "${DATADIR}/mysql" ]; then
  sudo mariadb-install-db --user=mysql --datadir="${DATADIR}" >/dev/null 2>&1 || true
fi
sudo chown -R mysql:mysql "${DATADIR}"

# Launch under sudo so root (not the calling user) opens the log in /var/log.
sudo sh -c "setsid mariadbd --user=mysql --datadir='${DATADIR}' --socket='${SOCK}' </dev/null >'${LOG}' 2>&1 &"

# Wait for readiness.
for _ in $(seq 1 90); do
  if sudo mysqladmin --socket="${SOCK}" ping >/dev/null 2>&1; then
    echo "MariaDB is up."
    exit 0
  fi
  sleep 1
done

# Never came up — surface the daemon log so the failure is diagnosable.
echo "ERROR: MariaDB did not become ready in time. Recent log:" >&2
sudo tail -n 60 "${LOG}" >&2 || true
exit 1
