/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./views/**/*.twig", "./public/**/*.php", "./src/**/*.php"],
  theme: {
    extend: {
      colors: {
        primary: "#ed1e79",
        secondary: "#808080",
        accent: "#ff9b2e",
      },
      fontFamily: {
        sans: ["Inter", "sans-serif"],
        serif: ["Bodoni Moda", "serif"],
      },
      opacity: {
        75: "0.75",
      },
    },
  },
  plugins: [require("@tailwindcss/forms"), require("@tailwindcss/typography")],
};
