{{--
  Brand lockup — full "Ridges & Valleys / Web Studio" logo.
  Two inline SVGs (light + dark); the theme's data-theme attribute swaps them
  (see .rv-logo rules in assets/rv-enhancements.css). Fonts are the theme's own
  self-hosted Outfit / Instrument Serif / JetBrains Mono (no external @import),
  so the mark adds no extra network request and stays crisp at any size.
  The wrapping link carries the accessible name, so both SVGs are aria-hidden.
--}}
<svg class="rv-logo rv-logo-light" width="512" height="120" viewBox="0 0 512 120" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
  <title>Ridges &amp; Valleys Studio</title>
  <defs><clipPath id="rvLogoClipL"><rect x="8" y="10" width="100" height="100" rx="24"/></clipPath></defs>
  <rect x="8" y="10" width="100" height="100" rx="24" fill="#EFE7D8" stroke="#E7DCC8" stroke-width="1.5"/>
  <g clip-path="url(#rvLogoClipL)">
    <circle cx="83" cy="35" r="9.5" fill="#E0A73C"/>
    <path d="M4 112 V58 L34 40 L58 54 L84 36 L114 56 V112 Z" fill="#97A88E"/>
    <path d="M4 112 V66 L30 46 L52 62 L74 42 L98 60 L114 68 V112 Z" fill="#B0553A"/>
    <path d="M4 112 V80 L28 52 L46 76 L64 46 L84 74 L104 56 L114 78 V112 Z" fill="#2E5245"/>
  </g>
  <text x="132" y="62" font-family="'Outfit',system-ui,sans-serif" font-weight="700" letter-spacing="-0.6" font-size="40" fill="#23201B">Ridges <tspan font-family="'Instrument Serif',Georgia,serif" font-style="italic" font-size="45" fill="#B0553A">&amp;</tspan> Valleys</text>
  <text x="134" y="90" font-family="'JetBrains Mono',ui-monospace,monospace" font-weight="600" letter-spacing="2.75" font-size="12.5" fill="#6E6558">WEB STUDIO · GETTYSBURG, PA</text>
</svg>
<svg class="rv-logo rv-logo-dark" width="512" height="120" viewBox="0 0 512 120" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
  <title>Ridges &amp; Valleys Studio</title>
  <defs><clipPath id="rvLogoClipD"><rect x="8" y="10" width="100" height="100" rx="24"/></clipPath></defs>
  <rect x="8" y="10" width="100" height="100" rx="24" fill="#EFE7D8"/>
  <g clip-path="url(#rvLogoClipD)">
    <circle cx="83" cy="35" r="9.5" fill="#E0A73C"/>
    <path d="M4 112 V58 L34 40 L58 54 L84 36 L114 56 V112 Z" fill="#97A88E"/>
    <path d="M4 112 V66 L30 46 L52 62 L74 42 L98 60 L114 68 V112 Z" fill="#B0553A"/>
    <path d="M4 112 V80 L28 52 L46 76 L64 46 L84 74 L104 56 L114 78 V112 Z" fill="#2E5245"/>
  </g>
  <text x="132" y="62" font-family="'Outfit',system-ui,sans-serif" font-weight="700" letter-spacing="-0.6" font-size="40" fill="#F7F1E6">Ridges <tspan font-family="'Instrument Serif',Georgia,serif" font-style="italic" font-size="45" fill="#E0A73C">&amp;</tspan> Valleys</text>
  <text x="134" y="90" font-family="'JetBrains Mono',ui-monospace,monospace" font-weight="600" letter-spacing="2.75" font-size="12.5" fill="#9AA394">WEB STUDIO · GETTYSBURG, PA</text>
</svg>
