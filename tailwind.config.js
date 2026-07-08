const path = require('path');

// Mirror Nova's dynamic "gray" palette. Nova remaps Tailwind's `gray` onto
// theme-driven CSS variables (its gray used to be a static "slate"), so we
// resolve `o1-*-gray-*` to the same `rgba(var(--colors-gray-*))` values Nova
// exposes at runtime. This keeps the field in sync with the active theme
// instead of hardcoding a static slate/gray.
const grayShades = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950];
const gray = Object.fromEntries(
  grayShades.map(shade => [shade, `rgba(var(--colors-gray-${shade}))`])
);

module.exports = {
  content: [path.resolve(__dirname, 'resources/**/*.{vue,js,ts,jsx,tsx,scss}')],
  prefix: 'o1-',
  darkMode: 'class',
  theme: {
    extend: {
      colors: { gray },
    },
  },
  safelist: [],
};
