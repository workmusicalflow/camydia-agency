# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Camydia Agency is a website for a professional hostess agency, built with PHP (Slim framework), Twig templates, Tailwind CSS, Alpine.js, and GSAP for animations. The site features multiple pages showcasing the agency's services, team, and contact information.

## Development Commands

### Frontend

```bash
# Watch mode for Tailwind CSS development
npm run dev

# Build CSS for production
npm run build

# Copy JS assets from src to public
npm run js-build
```

## Project Structure

- **Backend**: PHP with Slim framework using MVC architecture
  - Routes defined in `src/routes.php`
  - Controllers in `src/App/Controllers/`
  - Views are Twig templates in `views/`

- **Frontend**: 
  - Tailwind CSS for styling (`src/assets/css/main.css`) 
  - Alpine.js for interactive components 
  - GSAP for animations
  - Final compiled assets go to `public/` directory

## Key Components

- **Layout System**: 
  - Base layout in `views/layout.twig`
  - Page-specific templates extend the base layout
  - Partials for repeatable components in `views/partials/`

- **Styling**:
  - Theme colors defined in `tailwind.config.js`:
    - Primary: #ed1e79
    - Secondary: #808080
    - Accent: #ff9b2e

- **Interactive Components**:
  - Mobile menu toggle defined in Alpine.js

## Development Workflow

1. Run `npm run dev` to watch for CSS changes
2. Edit Twig templates in `views/` directory
3. Modify JS functionality in `src/assets/js/main.js`
4. Run `npm run js-build` to copy JS changes to public directory
5. For production, run `npm run build` to minify CSS

## Git Workflow

Commit messages should follow this pattern:
- [INIT] - Project initialization
- [HOME] - Home page changes
- [PAGE] - Other page changes
- [DESIGN] - Design/CSS changes
- [FIX] - Bug fixes