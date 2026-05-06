<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 620 160" style="height:100%; width:auto; display:block;" aria-label="BankBird">

    {{-- Icon: rounded square background --}}
    <rect x="8" y="8" width="144" height="144" rx="26" fill="#1E88E5"/>

    {{-- Subtle inner glow on the badge --}}
    <rect x="8" y="8" width="144" height="144" rx="26"
          fill="none" stroke="rgba(255,255,255,0.18)" stroke-width="2"/>

    {{-- Bird wing sweep (left, slightly translucent) --}}
    <path d="M 58,86 C 40,68 18,56 4,66 C 20,76 40,82 56,92 Z"
          fill="white" opacity="0.82"/>

    {{-- Bird body oval (tilted) --}}
    <ellipse cx="84" cy="94" rx="34" ry="20" fill="white"
             transform="rotate(-14 84 94)"/>

    {{-- Bird head circle --}}
    <circle cx="110" cy="73" r="21" fill="white"/>

    {{-- Eye: blue cutout --}}
    <circle cx="117" cy="68" r="5.5" fill="#1E88E5"/>

    {{-- Beak --}}
    <polygon points="128,70 146,74 128,80" fill="#EAF6FF"/>

    {{-- Wordmark --}}
    <text x="176" y="116"
          font-family="Inter, system-ui, -apple-system, sans-serif"
          font-size="80" font-weight="500" fill="#0B1F3A">BankBird</text>
</svg>
