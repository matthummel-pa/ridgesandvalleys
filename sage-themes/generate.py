#!/usr/bin/env python3
"""Build a Sage 11 (Roots) WordPress theme from each concept HTML folder."""

from __future__ import annotations

import json
import re
import shutil
import textwrap
from pathlib import Path

CONCEPT_ROOT = Path(
    "/workspace/web/app/themes/ridgesandvalleys-theme/concept"
)
SAGE_SRC = Path("/tmp/sage-src")
OUT_ROOT = Path("/workspace/sage-themes")

CONCEPTS = [
    {
        "folder": "gettysburg-hotel",
        "repo": "gettysburg-hotel-theme",
        "theme_name": "Lantern & Laurel Inn",
        "tagline": "Boutique historic inn concept for Gettysburg, PA",
    },
    {
        "folder": "hotel-cupola-field",
        "repo": "hotel-cupola-field-theme",
        "theme_name": "Cupola & Field Hotel",
        "tagline": "Modern boutique hotel concept for Gettysburg, PA",
    },
    {
        "folder": "gettysburg-restaurant",
        "repo": "gettysburg-restaurant-theme",
        "theme_name": "Field & Musket Tavern",
        "tagline": "Farm-to-table tavern concept for Gettysburg, PA",
    },
    {
        "folder": "restaurant-cannon-and-crumb",
        "repo": "restaurant-cannon-and-crumb-theme",
        "theme_name": "Cannon & Crumb",
        "tagline": "All-day cafe and bakery concept for Gettysburg, PA",
    },
    {
        "folder": "gettysburg-retail",
        "repo": "gettysburg-retail-theme",
        "theme_name": "Diamond & Ridge Mercantile",
        "tagline": "Downtown retail and gifts concept for Gettysburg, PA",
    },
    {
        "folder": "retail-ridgeline-outfitters",
        "repo": "retail-ridgeline-outfitters-theme",
        "theme_name": "Ridgeline Outfitters",
        "tagline": "Outdoor gear shop concept for Gettysburg, PA",
    },
    {
        "folder": "tour-hallowed-ground-tours",
        "repo": "tour-hallowed-ground-tours-theme",
        "theme_name": "Hallowed Ground Battlefield Tours",
        "tagline": "Guided battlefield tour concept for Gettysburg, PA",
    },
    {
        "folder": "tour-first-shot-food-tours",
        "repo": "tour-first-shot-food-tours-theme",
        "theme_name": "First Shot Food & History Tours",
        "tagline": "Food and history walking-tour concept for Gettysburg, PA",
    },
    {
        "folder": "realtor-ridgeline-realty",
        "repo": "realtor-ridgeline-realty-theme",
        "theme_name": "Ridgeline Realty",
        "tagline": "Gettysburg homes and historic-property realty concept",
    },
    {
        "folder": "realtor-keystone-homes-and-land",
        "repo": "realtor-keystone-homes-and-land-theme",
        "theme_name": "Keystone Homes & Land",
        "tagline": "Adams County farms, land, and acreage realty concept",
    },
]

SAGE_SKIP = {".git", ".github", "README.md", "screenshot.png"}

HOME_TOKEN = "[[RVHOME]]"


def slug_from_html(name: str) -> str:
    stem = Path(name).stem
    return "" if stem == "index" else stem


def title_from_html(html: str, fallback: str) -> str:
    m = re.search(r"<title>(.*?)</title>", html, re.S | re.I)
    if not m:
        return fallback
    raw = re.sub(r"<[^>]+>", "", m.group(1))
    raw = raw.replace("&amp;", "&").replace("&nbsp;", " ").strip()
    for sep in (" — ", " – ", " | ", " - "):
        if sep in raw:
            return raw.split(sep)[0].strip()
    return raw or fallback


def rewrite_static_urls(text: str, folder: str) -> str:
    origins = [
        f"https://matthummel-pa.github.io/ridgesandvalleys/{folder}/",
        f"https://example-concept.test/{folder}/",
    ]

    def file_to_home(filename: str, frag: str = "") -> str:
        slug = slug_from_html(filename)
        path = "/" if not slug else f"/{slug}/"
        return HOME_TOKEN + path + (f"#{frag}" if frag else "")

    for origin in origins:
        def abs_repl(m: re.Match) -> str:
            rest = m.group(1)
            frag = ""
            if "#" in rest:
                rest, frag = rest.split("#", 1)
            if rest in ("", "index.html"):
                return file_to_home("index.html", frag)
            if rest.endswith(".html"):
                return file_to_home(rest, frag)
            return HOME_TOKEN + "/" + rest + (f"#{frag}" if frag else "")

        text = re.sub(
            re.escape(origin) + r"([^\"'\s<]*)",
            abs_repl,
            text,
        )

    def href_repl(m: re.Match) -> str:
        quote, url = m.group(1), m.group(2)
        if url.startswith(
            ("http://", "https://", "mailto:", "tel:", "#", HOME_TOKEN, "{")
        ):
            return m.group(0)
        if url.startswith("./"):
            url = url[2:]
        frag = ""
        path = url
        if "#" in url:
            path, frag = url.split("#", 1)
        if path.endswith(".html"):
            return f"href={quote}{file_to_home(path, frag)}{quote}"
        return m.group(0)

    text = re.sub(r'href=(["\'])([^"\']+)\1', href_repl, text)
    return text


def strip_static_assets(html: str) -> str:
    html = re.sub(
        r'\s*<link[^>]+href=["\']styles\.css["\'][^>]*>', "", html, flags=re.I
    )
    html = re.sub(
        r'\s*<script[^>]+src=["\'][^"\']+\.js["\'][^>]*>\s*</script>',
        "",
        html,
        flags=re.I,
    )
    return html


def blade_escape(text: str) -> str:
    return text.replace("@", "@@")


def inject_home_url(text: str) -> str:
    def repl(m: re.Match) -> str:
        rest = m.group(1)
        frag = ""
        if "#" in rest:
            rest, frag = rest.split("#", 1)
        rest = rest.strip("/")
        path = "/" if rest == "" else f"/{rest}/"
        blade = "{{ home_url('" + path + "') }}"
        if frag:
            blade += "#" + frag
        return blade

    return re.sub(re.escape(HOME_TOKEN) + r"([^\"'\s<]*)", repl, text)


def extract_font_links(html: str) -> list[str]:
    links = []
    for m in re.finditer(r"<link[^>]+>", html, re.I):
        tag = m.group(0)
        if "fonts.googleapis.com" in tag or "fonts.gstatic.com" in tag:
            links.append(tag)
    return links


def extract_jsonld(html: str) -> list[str]:
    return re.findall(
        r'<script type="application/ld\+json">.*?</script>',
        html,
        flags=re.S | re.I,
    )


def body_inner(html: str) -> tuple[str, str]:
    m = re.search(r"<body([^>]*)>(.*)</body>", html, re.S | re.I)
    if not m:
        raise ValueError("no <body>")
    return m.group(1).strip(), m.group(2).strip()


def copy_sage_base(dest: Path) -> None:
    if dest.exists():
        shutil.rmtree(dest)
    dest.mkdir(parents=True)

    for item in SAGE_SRC.iterdir():
        if item.name in SAGE_SKIP or item.name.startswith(".git"):
            continue
        target = dest / item.name
        if item.is_dir():
            shutil.copytree(
                item,
                target,
                ignore=shutil.ignore_patterns(".gitkeep"),
            )
        else:
            shutil.copy2(item, target)

    (dest / "public").mkdir(exist_ok=True)
    (dest / "public" / ".gitkeep").write_text("")
    (dest / "resources" / "fonts").mkdir(parents=True, exist_ok=True)
    (dest / "resources" / "images").mkdir(parents=True, exist_ok=True)
    (dest / "resources" / "fonts" / ".gitkeep").write_text("")
    (dest / "resources" / "images" / ".gitkeep").write_text("")


def write_style_css(dest: Path, meta: dict) -> None:
    (dest / "style.css").write_text(
        f"""/*
Theme Name:         {meta["theme_name"]}
Theme URI:          https://github.com/matthummel-pa/{meta["repo"]}
Author:             Matt Hummel — Ridges & Valleys Studio
Author URI:         https://ridgesandvalleys.com
Description:        {meta["tagline"]}. Sage 11 (Roots) — Blade, Vite, Acorn. Converted from a Ridges & Valleys Studio design concept.
Version:            1.0.0
Text Domain:        sage
License:            MIT License
License URI:        https://opensource.org/licenses/MIT
Requires PHP:       8.3
Requires at least:  6.6
Tested up to:       6.8
*/
"""
    )


def write_composer(dest: Path, meta: dict) -> None:
    data = json.loads((SAGE_SRC / "composer.json").read_text())
    data["name"] = f"matthummel-pa/{meta['repo']}"
    data["description"] = (
        f"{meta['theme_name']} — Sage 11 WordPress theme ({meta['tagline']})"
    )
    data["homepage"] = f"https://github.com/matthummel-pa/{meta['repo']}"
    data["authors"] = [
        {"name": "Matt Hummel", "homepage": "https://ridgesandvalleys.com"}
    ]
    data["keywords"] = ["wordpress", "theme", "sage", "roots", "gettysburg"]
    data["support"] = {
        "issues": f"https://github.com/matthummel-pa/{meta['repo']}/issues"
    }
    data["config"]["platform"] = {"php": "8.3"}
    (dest / "composer.json").write_text(json.dumps(data, indent=4) + "\n")


def write_package_json(dest: Path, meta: dict) -> None:
    data = json.loads((SAGE_SRC / "package.json").read_text())
    data["name"] = meta["repo"]
    (dest / "package.json").write_text(json.dumps(data, indent=4) + "\n")


def write_vite_config(dest: Path, slug: str) -> None:
    (dest / "vite.config.js").write_text(
        f"""import {{ defineConfig }} from 'vite'
import tailwindcss from '@tailwindcss/vite'
import laravel from 'laravel-vite-plugin'
import {{ wordpressPlugin, wordpressThemeJson }} from '@roots/vite-plugin'

if (! process.env.APP_URL) {{
  process.env.APP_URL = 'http://example.test'
}}

const base = process.env.VITE_BASE || '/wp-content/themes/{slug}/public/build/'

export default defineConfig({{
  base,
  plugins: [
    tailwindcss(),
    laravel({{
      input: [
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/css/editor.css',
        'resources/js/editor.js',
      ],
      refresh: true,
      assets: ['resources/images/**', 'resources/fonts/**'],
    }}),
    wordpressPlugin(),
    wordpressThemeJson({{
      disableTailwindColors: false,
      disableTailwindFonts: false,
      disableTailwindFontSizes: false,
      disableTailwindBorderRadius: false,
    }}),
  ],
  resolve: {{
    alias: {{
      '@scripts': '/resources/js',
      '@styles': '/resources/css',
      '@fonts': '/resources/fonts',
      '@images': '/resources/images',
    }},
  }},
}})
"""
    )


def write_layout(dest: Path, font_links: list[str]) -> None:
    fonts = "\n    ".join(font_links)
    (dest / "resources/views/layouts/app.blade.php").write_text(
        f"""<!doctype html>
<html @php(language_attributes())>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {fonts}
    @php(do_action('get_header'))
    @php(wp_head())
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
  </head>
  <body @php(body_class())>
    @php(wp_body_open())
    @yield('content')
    @php(do_action('get_footer'))
    @php(wp_footer())
  </body>
</html>
"""
    )


def write_app_css(dest: Path) -> None:
    (dest / "resources/css/app.css").write_text(
        """@import "tailwindcss" theme(static);
@import "./concept.css";

@source "../../app/**/*.php";
@source "../**/*.blade.php";
@source "../**/*.js";
"""
    )


def write_app_js(dest: Path, js_files: list[str]) -> None:
    lines = ["import '@styles/app.css';", ""]
    for name in js_files:
        mod = Path(name).stem
        lines.append(f"import './{mod}';")
    if not js_files:
        lines.append("// Concept scripts land here.")
    (dest / "resources/js/app.js").write_text("\n".join(lines) + "\n")


def write_page_template(
    dest: Path,
    *,
    is_front: bool,
    slug: str,
    title: str,
    body: str,
    jsonld: list[str],
    folder: str,
) -> str:
    body = strip_static_assets(body)
    body = rewrite_static_urls(body, folder)
    jsonld_joined = rewrite_static_urls("\n".join(jsonld), folder)
    body = inject_home_url(blade_escape(body))
    jsonld_joined = inject_home_url(blade_escape(jsonld_joined))

    if is_front:
        filename = "front-page.blade.php"
        header = "{{-- Front page: concept home --}}\n"
    else:
        filename = f"template-{slug}.blade.php"
        header = (
            "{{--\n"
            f"  Template Name: {title}\n"
            "--}}\n"
        )

    head_stack = ""
    if jsonld_joined.strip():
        head_stack = "@push('head')\n" + jsonld_joined + "\n@endpush\n\n"

    (dest / "resources/views" / filename).write_text(
        header
        + "\n"
        + "@extends('layouts.app')\n\n"
        + head_stack
        + "@section('content')\n"
        + body
        + "\n@endsection\n"
    )
    return filename


def write_seed_script(dest: Path, pages: list[tuple[str, str, str]], theme_name: str) -> None:
    lines = [
        "#!/usr/bin/env bash",
        "set -euo pipefail",
        'cd "$(dirname "${BASH_SOURCE[0]}")/.."',
        "",
        'if ! command -v wp >/dev/null 2>&1; then',
        '  echo "wp-cli (wp) is required to seed pages." >&2',
        "  exit 1",
        "fi",
        "",
        "mkpage() {",
        '  local slug="$1" title="$2" tpl="$3" id',
        '  id=$(wp post list --post_type=page --name="$slug" --field=ID --format=ids 2>/dev/null | tr -d "[:space:]")',
        '  if [ -z "$id" ]; then',
        '    id=$(wp post create --post_type=page --post_status=publish --post_title="$title" --post_name="$slug" --porcelain)',
        '    echo "  + created  $title  (/$slug/)" >&2',
        "  else",
        '    echo "  · exists   $title  (/$slug/)" >&2',
        "  fi",
        '  [ -n "$tpl" ] && wp post meta update "$id" _wp_page_template "$tpl" >/dev/null 2>&1',
        '  printf "%s" "$id"',
        "}",
        "",
        f'echo "› Seeding pages for {theme_name}…"',
        'HOME_ID=$(mkpage home "Home" "")',
    ]
    ids = ["HOME_ID"]
    for slug, title, tpl in pages:
        var = slug.upper().replace("-", "_")
        lines.append(f'{var}=$(mkpage {slug} {json.dumps(title)} {json.dumps(tpl)})')
        ids.append(var)
    lines += [
        "",
        "wp option update show_on_front page >/dev/null",
        'wp option update page_on_front "$HOME_ID" >/dev/null',
        f'wp option update blogname {json.dumps(theme_name)} >/dev/null 2>&1 || true',
        "wp rewrite structure '/%postname%/' >/dev/null 2>&1 || true",
        "wp rewrite flush --hard >/dev/null 2>&1 || true",
        "",
        'echo "  ✔ Front page + interior concept pages are ready."',
        "",
    ]
    script = dest / "dev" / "seed-pages.sh"
    script.parent.mkdir(exist_ok=True)
    script.write_text("\n".join(lines) + "\n")
    script.chmod(0o755)


def write_theme_readme(dest: Path, meta: dict, pages: list[tuple[str, str, str]]) -> None:
    page_rows = "\n".join(
        f"| `{slug}/` | {title} | `{tpl}` |" for slug, title, tpl in pages
    )
    (dest / "README.md").write_text(
        f"""# {meta["theme_name"]}

Sage 11 (Roots) WordPress theme converted from the Ridges & Valleys Studio
concept in [`ridgesandvalleys`](https://github.com/matthummel-pa/ridgesandvalleys)
(`web/app/themes/ridgesandvalleys-theme/concept/{meta["folder"]}/`).

{meta["tagline"]}.

## Stack

- [Sage 11](https://roots.io/sage/) / [Acorn](https://roots.io/acorn/)
- Blade templates
- Vite 8 + Tailwind CSS v4 (editor) + the original concept CSS
- PHP 8.3+

## Local

```bash
composer install
npm install
npm run build
```

Drop this folder into `wp-content/themes/{meta["repo"]}` (or Bedrock
`web/app/themes/{meta["repo"]}`). Activate the theme, then seed pages:

```bash
bash dev/seed-pages.sh
```

Bedrock Vite base:

```bash
VITE_BASE=/app/themes/{meta["repo"]}/public/build/ npm run dev
```

## Pages

| Path | Title | Template |
| --- | --- | --- |
| `/` | Home | `front-page.blade.php` |
{page_rows}

## Note

This is a **design concept**, not a live business. Forms and checkout flows
are interactive demos.

MIT © Ridges & Valleys Studio. Sage starter is MIT © Roots.
"""
    )


def write_gitignore(dest: Path) -> None:
    (dest / ".gitignore").write_text(
        """/node_modules
/vendor
/public/build
/public/hot
.env
npm-debug.log
.DS_Store
"""
    )


def copy_screenshot(src: Path, dest: Path) -> None:
    preview = src / "preview.jpg"
    target = dest / "screenshot.png"
    if not preview.exists():
        shutil.copy2(SAGE_SRC / "screenshot.png", target)
        return
    try:
        from PIL import Image

        Image.open(preview).convert("RGB").save(target, "PNG")
    except Exception:
        shutil.copy2(preview, target)


def build_one(meta: dict) -> dict:
    src = CONCEPT_ROOT / meta["folder"]
    dest = OUT_ROOT / meta["repo"]
    if not src.is_dir():
        raise SystemExit(f"missing concept folder: {src}")

    copy_sage_base(dest)
    write_style_css(dest, meta)
    write_composer(dest, meta)
    write_package_json(dest, meta)
    write_vite_config(dest, meta["repo"])
    write_gitignore(dest)
    copy_screenshot(src, dest)
    (dest / "resources/js/editor.js").write_text(
        "// Block editor entry — extend when adding custom blocks.\n"
    )

    css = src / "styles.css"
    if css.exists():
        shutil.copy2(css, dest / "resources/css/concept.css")
    else:
        (dest / "resources/css/concept.css").write_text("/* no concept stylesheet */\n")
    write_app_css(dest)

    js_files = sorted(p.name for p in src.glob("*.js"))
    for name in js_files:
        shutil.copy2(src / name, dest / "resources/js" / name)
    write_app_js(dest, js_files)

    html_pages = sorted(src.glob("*.html"))
    index_html = (src / "index.html").read_text()
    write_layout(dest, extract_font_links(index_html))

    pages_meta: list[tuple[str, str, str]] = []
    for html_path in html_pages:
        html = html_path.read_text()
        slug = slug_from_html(html_path.name)
        title = title_from_html(html, html_path.stem.replace("-", " ").title())
        _attrs, body = body_inner(html)
        jsonld = extract_jsonld(html)
        is_front = html_path.name == "index.html"
        filename = write_page_template(
            dest,
            is_front=is_front,
            slug=slug or "home",
            title=title,
            body=body,
            jsonld=jsonld,
            folder=meta["folder"],
        )
        if not is_front:
            pages_meta.append((slug, title, filename))

    write_seed_script(dest, pages_meta, meta["theme_name"])
    write_theme_readme(dest, meta, pages_meta)

    # Keep Sage blog templates, but front-page is the concept home.
    return {
        "repo": meta["repo"],
        "theme": meta["theme_name"],
        "pages": 1 + len(pages_meta),
        "js": js_files,
    }


def main() -> None:
    if not SAGE_SRC.exists():
        raise SystemExit(f"Sage source missing at {SAGE_SRC} — clone roots/sage first.")

    OUT_ROOT.mkdir(parents=True, exist_ok=True)
    results = []
    for meta in CONCEPTS:
        print(f"→ {meta['repo']}")
        results.append(build_one(meta))

    index_lines = [
        "# Sage concept themes",
        "",
        "Each folder is a **Sage 11 (Roots)** WordPress theme converted from",
        "`web/app/themes/ridgesandvalleys-theme/concept/`.",
        "",
        "This Cursor token cannot create GitHub repositories. After you create",
        "empty repos (or run `publish-to-github.sh` with a token that can),",
        "connect each repo in Cursor so follow-up work can land there.",
        "",
        "| Local folder | Suggested GitHub repo | Concept |",
        "| --- | --- | --- |",
    ]
    for meta in CONCEPTS:
        index_lines.append(
            f"| `{meta['repo']}/` | `matthummel-pa/{meta['repo']}` | {meta['theme_name']} |"
        )
    index_lines += [
        "",
        "## Publish",
        "",
        "From a machine whose `gh` login can create repositories:",
        "",
        "```bash",
        "bash sage-themes/publish-to-github.sh",
        "```",
        "",
        "Regenerate from the HTML concepts:",
        "",
        "```bash",
        "python3 sage-themes/generate.py",
        "```",
        "",
    ]
    (OUT_ROOT / "README.md").write_text("\n".join(index_lines) + "\n")
    print(json.dumps(results, indent=2))


if __name__ == "__main__":
    main()
