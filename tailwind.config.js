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
        sans: ["Arial", "sans-serif"],
        serif: ["Georgia", "serif"],
      },
    },
  },
  plugins: [require("@tailwindcss/forms"), require("@tailwindcss/typography")],
};
