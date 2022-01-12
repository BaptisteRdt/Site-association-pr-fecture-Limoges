module.exports = {
  content: ["./**/*.{html.twig, html}"],
  theme: {
    extend: {
      colors: {
        "navbar-background": "var(--navbar-background)",
        "article-price-background": "var(--article-price-background)",
      },
    },
  },
  plugins: [require('@tailwindcss/line-clamp')],
}
