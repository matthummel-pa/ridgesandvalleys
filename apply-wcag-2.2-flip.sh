#!/usr/bin/env bash
#
# apply-wcag-2.2-flip.sh
# -----------------------------------------------------------------------------
# Flips the Ridges & Valleys studio's *own* accessibility claim from
# WCAG 2.1 AA -> WCAG 2.2 AA across the bespoke theme (and repo docs), and
# rebuilds the live-audit checklist to the real 2.2 criteria set
# (adds the 6 new A/AA criteria, removes the obsolete 4.1.1 Parsing: 50 -> 55).
#
# It deliberately LEAVES the legal references untouched:
#   Section 508 -> WCAG 2.0 AA   |   ADA Title II / III -> WCAG 2.1 AA
#
# Safety first: it will not touch anything unless it confirms it is sitting in
# the correct repository, and it shows you the full diff and asks before it
# commits or pushes. Pushing to `main` auto-deploys the theme to the live site.
#
# Usage:
#   ./apply-wcag-2.2-flip.sh                 # auto-detect the repo, apply, review, prompt
#   ./apply-wcag-2.2-flip.sh /path/to/repo   # point it at the repo explicitly
#   ./apply-wcag-2.2-flip.sh --dry-run       # show what WOULD change, write nothing
#   ./apply-wcag-2.2-flip.sh --yes           # apply + commit + push without prompting
# -----------------------------------------------------------------------------
set -euo pipefail

# ---- what "the correct folder" means -------------------------------------------------
EXPECTED_SLUG="matthummel-pa/ridgesandvalleys"
THEME_REL="web/app/themes/ridgesandvalleys-theme"
BRANCH="main"
DEFAULT_PATH="$HOME/ClaudeCoWork/projects/ridgesandvalleys-bedrock"

# ---- args --------------------------------------------------------------------
DRY_RUN=0
ASSUME_YES=0
REPO_ARG=""
for arg in "$@"; do
  case "$arg" in
    --dry-run) DRY_RUN=1 ;;
    --yes|-y)  ASSUME_YES=1 ;;
    -h|--help) grep '^#' "$0" | sed 's/^# \{0,1\}//'; exit 0 ;;
    -*) echo "Unknown flag: $arg" >&2; exit 2 ;;
    *)  REPO_ARG="$arg" ;;
  esac
done

red()   { printf '\033[31m%s\033[0m\n' "$*"; }
green() { printf '\033[32m%s\033[0m\n' "$*"; }
bold()  { printf '\033[1m%s\033[0m\n'  "$*"; }
die()   { red "✗ $*"; exit 1; }

# ---- 1. Find and VERIFY the repo ---------------------------------------------
bold "Step 1 — locating the correct repository"

CANDIDATE="${REPO_ARG:-}"
if [ -z "$CANDIDATE" ]; then
  # Try the git repo that contains the current directory first.
  if CANDIDATE=$(git -C "$(pwd)" rev-parse --show-toplevel 2>/dev/null); then
    :
  elif [ -d "$DEFAULT_PATH" ]; then
    CANDIDATE="$DEFAULT_PATH"
  else
    die "Not inside a git repo, and the default path doesn't exist:
    $DEFAULT_PATH
Run me from inside your ridgesandvalleys clone, or pass the path:
    ./apply-wcag-2.2-flip.sh ~/path/to/ridgesandvalleys-bedrock"
  fi
fi

[ -d "$CANDIDATE" ] || die "Path does not exist: $CANDIDATE"
REPO_ROOT=$(git -C "$CANDIDATE" rev-parse --show-toplevel 2>/dev/null) \
  || die "Not a git repository: $CANDIDATE"
cd "$REPO_ROOT"

# Confirm the remote really is the studio site (not some other clone).
ORIGIN_URL=$(git remote get-url origin 2>/dev/null || echo "")
case "$ORIGIN_URL" in
  *"$EXPECTED_SLUG"*) : ;;
  *) die "Wrong repository. origin is:
    ${ORIGIN_URL:-<none>}
Expected it to contain: $EXPECTED_SLUG
This script only edits the Ridges & Valleys theme — refusing to run here." ;;
esac

# Confirm the bespoke theme is actually present.
[ -d "$THEME_REL" ] || die "Theme folder not found: $REPO_ROOT/$THEME_REL
This doesn't look like the Bedrock theme repo. Refusing to run."

CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "?")

green "✓ Repo:   $REPO_ROOT"
green "✓ Origin: $ORIGIN_URL"
green "✓ Theme:  $THEME_REL"
echo  "  Branch: $CURRENT_BRANCH"

if [ "$CURRENT_BRANCH" != "$BRANCH" ]; then
  red "! You are on '$CURRENT_BRANCH', not '$BRANCH'."
  echo "  The deploy only runs from '$BRANCH'. Switch with:  git checkout $BRANCH"
  if [ "$ASSUME_YES" -eq 0 ] && [ "$DRY_RUN" -eq 0 ]; then
    read -r -p "  Continue on '$CURRENT_BRANCH' anyway? [y/N] " ans
    [ "${ans:-N}" = "y" ] || [ "${ans:-N}" = "Y" ] || die "Stopped. Nothing changed."
  fi
fi

# Warn if the tree is dirty so our diff stays clean.
if [ -n "$(git status --porcelain)" ] && [ "$DRY_RUN" -eq 0 ]; then
  red "! Working tree has uncommitted changes. Our edits will mix with them."
  if [ "$ASSUME_YES" -eq 0 ]; then
    read -r -p "  Continue anyway? [y/N] " ans
    [ "${ans:-N}" = "y" ] || [ "${ans:-N}" = "Y" ] || die "Stopped. Commit or stash first."
  fi
fi

command -v python3 >/dev/null 2>&1 || die "python3 is required but not found."

# ---- 2. Apply the edits (Python does the precise, verified replacements) -----
echo
bold "Step 2 — applying the WCAG 2.2 edits"

REPO_ROOT="$REPO_ROOT" THEME_REL="$THEME_REL" DRY_RUN="$DRY_RUN" python3 <<'PYEOF'
import os, sys

root  = os.environ["REPO_ROOT"]
theme = os.environ["THEME_REL"].rstrip("/") + "/"
dry   = os.environ["DRY_RUN"] == "1"

T = theme  # shorthand for theme-relative files

# Each edit: file, kind, old, new(optional), guard(optional)
#   kind "sub"  -> replace every occurrence of old with new
#   kind "del"  -> remove old (exact block)
#   kind "ins"  -> old is an anchor line; new is anchor+added lines; guard = a
#                  unique marker that, if already present, means "already applied"
E = []
def sub(f, old, new): E.append((f, "sub", old, new, None))
def dele(f, old, guard): E.append((f, "del", old, "", guard))
def ins(f, anchor, added, guard): E.append((f, "ins", anchor, anchor + "\n" + added, guard))

# --- studio-claim label flips (2.1 -> 2.2) ---
sub(T+"app/home-sections.php", "WCAG 2.1 AA | accessibility, built in", "WCAG 2.2 AA | accessibility, built in")
sub(T+"resources/views/partials/home-included.blade.php", "Built to WCAG 2.1 AA", "Built to WCAG 2.2 AA")
sub(T+"app/page-fields.php", "Built to WCAG 2.1 AA", "Built to WCAG 2.2 AA")
sub(T+"resources/views/template-about.blade.php", "WCAG 2.1 AA", "WCAG 2.2 AA")
sub(T+"resources/views/template-faq.blade.php", "WCAG 2.1 AA", "WCAG 2.2 AA")
sub(T+"resources/views/template-grader.blade.php", "WCAG 2.1 AA", "WCAG 2.2 AA")
# tools blurb: reworded so it no longer ties 2.2 to what the *federal rules* require
sub(T+"resources/views/template-tools.blade.php",
    "WCAG 2.1 AA — the standard the federal rules point to",
    "WCAG 2.2 AA — the latest W3C standard")
# SEO title + meta description for the accessibility page
sub(T+"app/seo-meta.php", "(WCAG 2.1 AA)", "(WCAG 2.2 AA)")
sub(T+"app/seo-meta.php", "commitment to WCAG 2.1 Level AA", "commitment to WCAG 2.2 Level AA")

# --- accessibility template: targeted flips (legal refs left alone) ---
A = T+"resources/views/template-accessibility.blade.php"
sub(A, "/* WCAG 2.1 Level A + AA success criteria", "/* WCAG 2.2 Level A + AA success criteria")
sub(A, "Accessibility · WCAG 2.1 AA · Section 508", "Accessibility · WCAG 2.2 AA · Section 508")
sub(A, "__('WCAG 2.1 AA', 'sage')", "__('WCAG 2.2 AA', 'sage')")          # audit_accent
sub(A, "WCAG 2.1 AA live audit", "WCAG 2.2 AA live audit")                # aria-label
sub(A, "WCAG 2.1 Level AA — live audit", "WCAG 2.2 Level AA — live audit")  # scan title
sub(A, "meet WCAG 2.1 Level AA across the site", "meet WCAG 2.2 Level AA across the site")  # statement
sub(A, "through-line: WCAG 2.1 Level AA is the target", "through-line: WCAG 2.2 Level AA is the target")
sub(A, "8 of the 50 criteria", "8 of the 55 criteria")                    # intro count

# --- accessibility checklist: make it a real 2.2 set ---
dele(A, "    ['4.1.1', 'Parsing', 'A', 'Clean, valid markup that assistive tech can read reliably.'],\n", "'4.1.1', 'Parsing'")
ins(A, "    ['2.4.7', 'Focus Visible', 'AA', 'The keyboard focus indicator is always visible.'],",
    "    ['2.4.11', 'Focus Not Obscured (Minimum)', 'AA', 'A sticky header or footer never fully hides the item you tabbed to.'],",
    "'2.4.11'")
ins(A, "    ['2.5.4', 'Motion Actuation', 'A', 'Features triggered by motion have a button alternative.'],",
    "    ['2.5.7', 'Dragging Movements', 'AA', 'Anything you drag also works with a single tap or click.'],\n"
    "    ['2.5.8', 'Target Size (Minimum)', 'AA', 'Buttons and links are at least 24×24px, so they are easy to hit.'],",
    "'2.5.8'")
ins(A, "    ['3.2.4', 'Consistent Identification', 'AA', 'The same components are labeled the same way everywhere.'],",
    "    ['3.2.6', 'Consistent Help', 'A', 'Help — contact, phone, email — sits in the same place on every page.'],",
    "'3.2.6'")
ins(A, "    ['3.3.4', 'Error Prevention (Legal, Financial, Data)', 'AA', 'Important submissions can be reviewed and corrected.'],",
    "    ['3.3.7', 'Redundant Entry', 'A', 'You are never asked to re-enter information you already gave in the same step.'],\n"
    "    ['3.3.8', 'Accessible Authentication (Minimum)', 'AA', 'No login step that is a memory or puzzle test with no alternative.'],",
    "'3.3.8'")

# --- repo docs (harmless to deploy; kept consistent) ---
sub("README.md", "Accessibility — WCAG 2.1 AA on every page", "Accessibility — WCAG 2.2 AA on every page")
sub("README.md", "WCAG 2.1 AA · You own it", "WCAG 2.2 AA · You own it")
sub("docs/deploy-log.md", "Accessibility pass on the changed page (WCAG 2.1 AA)", "Accessibility pass on the changed page (WCAG 2.2 AA)")

# ---- validate everything before writing anything (transactional) ----
from collections import defaultdict
byfile = defaultdict(list)
for e in E:
    byfile[e[0]].append(e)

errors, plan, changed = [], [], {}
for rel, edits in byfile.items():
    path = os.path.join(root, rel)
    if not os.path.exists(path):
        # Only README/deploy-log are optional; theme files must exist.
        if rel in ("README.md", "docs/deploy-log.md"):
            plan.append(("skip (no file)", rel, ""))
            continue
        errors.append("MISSING FILE: " + rel)
        continue
    with open(path, "r", encoding="utf-8") as fh:
        text = orig = fh.read()
    for (_, kind, old, new, guard) in edits:
        if kind == "sub":
            n = text.count(old)
            if n:
                text = text.replace(old, new); plan.append((f"replace x{n}", rel, old[:40]))
            elif new in text:
                plan.append(("already applied", rel, old[:40]))
            else:
                errors.append(f"NOT FOUND in {rel}: {old[:60]!r}")
        elif kind == "del":
            if old in text:
                text = text.replace(old, ""); plan.append(("delete", rel, guard))
            elif guard not in text:
                plan.append(("already applied", rel, guard))
            else:
                errors.append(f"DELETE anchor changed in {rel}: {guard}")
        elif kind == "ins":
            if guard in text:
                plan.append(("already applied", rel, guard))
            elif text.count(old) == 1:
                text = text.replace(old, new); plan.append(("insert", rel, guard))
            elif old not in text:
                errors.append(f"INSERT anchor not found in {rel}: {guard}")
            else:
                errors.append(f"INSERT anchor not unique in {rel}: {guard}")
    if text != orig:
        changed[path] = text

# report
for status, rel, what in plan:
    print(f"   {status:16} {rel}  {what}")

if errors:
    print()
    print("✗ Aborting — nothing was written. Problems:")
    for e in errors:
        print("   - " + e)
    sys.exit(1)

if not changed:
    print("\n✓ Nothing to change — the flip is already applied.")
    sys.exit(0)

if dry:
    print(f"\n(dry run) {len(changed)} file(s) would change. Nothing written.")
    sys.exit(0)

for path, text in changed.items():
    with open(path, "w", encoding="utf-8") as fh:
        fh.write(text)
print(f"\n✓ Wrote {len(changed)} file(s).")
PYEOF
PY_STATUS=$?

[ "$PY_STATUS" -eq 0 ] || die "Edit step failed — see messages above. No commit made."
[ "$DRY_RUN" -eq 1 ] && { echo; bold "Dry run complete."; exit 0; }

# ---- 3. Review + commit + push ----------------------------------------------
echo
bold "Step 3 — review the changes"
git --no-pager diff --stat
echo
echo "Full diff:  git --no-pager diff"
echo

COMMIT_MSG="Accessibility: state WCAG 2.2 AA (studio claim); keep 508/ADA legal refs; live-audit checklist 50->55"

if [ "$ASSUME_YES" -eq 0 ]; then
  read -r -p "Commit and push to '$BRANCH' (this auto-deploys to the live site)? [y/N] " go
  case "${go:-N}" in
    y|Y) ;;
    *) echo "Left the edits staged in your working tree, uncommitted."; echo "Review with: git diff   |   undo with: git checkout -- ."; exit 0 ;;
  esac
fi

git add -A
git commit -m "$COMMIT_MSG"
git push origin "$BRANCH"

echo
green "✓ Pushed to $BRANCH. GitHub Actions ('Deploy theme to SiteGround') will build + rsync (~1–2 min)."
echo  "  Watch it:   gh run watch \$(gh run list --workflow=deploy-theme.yml -L1 --json databaseId -q '.[0].databaseId') 2>/dev/null || echo 'open GitHub → Actions'"
echo  "  Then verify the live UNCACHED url shows 2.2 (and the checklist now reads 55)."
