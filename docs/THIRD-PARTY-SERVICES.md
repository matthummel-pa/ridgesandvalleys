---
title: Third-party services & privacy
---

# Third-party services & privacy disclosure

Pressroot can talk to the external services below. **No service is contacted
until the feature that uses it runs**, and every one can be disabled. Site
owners are responsible for reflecting the services they enable in their own
privacy policy; this page is the complete inventory.

## AI text & image generation

Active only while AI features are on (Theme Settings → "Let AI help with
writing and images", plus the Pressroot AI addon toggle). One master switch
(`prt_ai_features_on`) stops every AI call.

| Service | Endpoint | When it's called | What's sent |
|---|---|---|---|
| Pollinations (keyless default) | `text.pollinations.ai`, `image.pollinations.ai` | AI-write, smart-copy priming, brand/page images when no paid provider is configured | The compiled site brief (business name, description, brand answers, business facts) + the generation prompt. **No account or key — treat prompts as public.** |
| Anthropic (Claude) | `api.anthropic.com` | When selected + key saved | Same brief + prompt, under your API agreement |
| OpenAI | `api.openai.com` | When selected + key saved | Same |
| Google Gemini | `generativelanguage.googleapis.com` | When selected + key saved | Same |
| Groq / OpenRouter | `api.groq.com` / `openrouter.ai` | When selected + key saved | Same |
| Stability AI | `api.stability.ai` | Image generation when selected + key saved | Image prompt |
| Luma / Runway | keys stored, **no calls yet** (video ships in a future release) | — | — |

API keys are stored server-side as theme mods, are never sent to the
browser, and are excluded from settings exports.

## Fonts

- **Google Fonts CSS** (`fonts.googleapis.com`, `fonts.gstatic.com`) — loaded
  for the selected families **unless** "Serve fonts locally" is enabled
  (Appearance → Local Fonts), which downloads the woff2 files once and
  removes every Google request. For EU/GDPR audiences, enable local serving.
- **Google Fonts collection** (block editor Font Library) — downloads
  families from Google on the owner's explicit request; self-hosted after.

## Stock image finder (hero builder)

Called only when the owner searches for images in the Customizer hero:
Openverse (`api.openverse.org`, keyless), Unsplash (`api.unsplash.com`) and
Pexels (`api.pexels.com`) with the owner's own API keys. Search terms are
sent; chosen images are downloaded into the Media Library (the importer
validates URLs and blocks private-network fetches).

## GitHub (Repofolio addon)

`api.github.com` — repository metadata for the portfolio grid and project
pages, plus optional OAuth device-flow login. The OAuth token is encrypted
at rest (libsodium). Disable the Repofolio addon to stop all GitHub calls.

## Google Analytics

Only if the owner pastes a GA4 Measurement ID in the Setup wizard: the
official `googletagmanager.com/gtag.js` snippet is injected and visitor data
flows to Google under the owner's Analytics property. Clearing the field
removes the snippet. Owners should pair this with the built-in cookie
notice and their privacy policy.

## What never happens

- No telemetry, phone-home, or usage tracking to the theme author.
- No content or keys are sent anywhere except the services listed above,
  each triggered by an explicit owner action or setting.
