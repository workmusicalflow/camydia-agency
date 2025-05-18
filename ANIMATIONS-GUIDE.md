# Guide d'utilisation du système d'animations GSAP

## Vue d'ensemble

Ce projet utilise GSAP 3.13.0 avec les plugins ScrollTrigger et SplitText pour créer des animations fluides et performantes. Le système d'animations a été conçu pour être modulaire, réutilisable et facilement extensible.

## Architecture

### 1. Fichiers principaux

- `/src/assets/js/animations.js` : Module principal des animations
- `/views/layout.twig` : Inclut tous les scripts GSAP nécessaires
- `/views/index.twig` : Exemple d'implémentation avancée avec animations personnalisées

### 2. Classes d'animation disponibles

Le système d'animations propose plusieurs classes CSS prêtes à l'emploi :

#### Animations de base

- `.fade-in` : Apparition en fondu avec mouvement vers le haut
- `.scale-in` : Apparition avec effet de zoom
- `.slide-left` : Glissement depuis la gauche
- `.slide-right` : Glissement depuis la droite

#### Animations de texte

- `.text-reveal` : Animation lettre par lettre ou mot par mot avec SplitText
- Utilise automatiquement SplitText.create() avec les meilleures pratiques v3.13.0+

#### Animations groupées

- `.stagger-container` + `.stagger-item` : Animation en cascade des éléments enfants
- `.parallax` : Effet de parallaxe (utiliser `data-speed` pour contrôler la vitesse)
- `.batch-container` + `.batch-item` : Animation en groupe optimisée avec ScrollTrigger.batch()

## Animations avancées implémentées

### 1. Animation du titre principal (Hero Section)

```javascript
// Utilisation de SplitText.create() avec la signature v3.13.0+
SplitText.create(".hero-title", {
  type: "words,chars",
  charsClass: "char++", // Classes numérotées pour chaque caractère
  wordsClass: "word++", // Classes numérotées pour chaque mot
  aria: "auto", // Gestion automatique de l'accessibilité
  autoSplit: true, // Re-découpage automatique
  onSplit: (self) => {
    // Animation des caractères avec effets 3D
    gsap.fromTo(self.chars, 
      {
        opacity: 0,
        y: 60,
        rotationX: -90,
        scale: 0.7
      },
      {
        opacity: 1,
        y: 0,
        rotationX: 0,
        scale: 1,
        duration: 0.8,
        stagger: {
          amount: 0.8,
          from: "random",
          ease: "power2.inOut"
        },
        ease: "back.out(1.7)"
      }
    );
  }
});
```

### 2. Animation du bouton Hero avec effets continus

```javascript
// Animation d'entrée élastique
heroTimeline.fromTo(heroButton,
  {
    opacity: 0,
    scale: 0.5,
    rotation: -180
  },
  {
    opacity: 1,
    scale: 1,
    rotation: 0,
    duration: 0.8,
    ease: "elastic.out(1, 0.8)"
  }
);

// Animation continue "floating"
heroTimeline.to(heroButton, {
  y: -8,
  duration: 2,
  repeat: -1,
  yoyo: true,
  ease: "sine.inOut"
});

// Interactions au survol
heroButton.addEventListener('mouseenter', () => {
  gsap.to(heroButton, {
    scale: 1.08,
    duration: 0.25,
    ease: "power2.out"
  });
});
```

### 3. Animations au scroll optimisées avec batch

```javascript
// Animation efficace des cartes de service
ScrollTrigger.batch(".service-card", {
  onEnter: elements => {
    gsap.fromTo(elements, 
      { opacity: 0, y: 50, scale: 0.9 },
      { opacity: 1, y: 0, scale: 1, duration: 0.8, stagger: 0.15 }
    );
  },
  onLeave: elements => {
    gsap.set(elements, { opacity: 0, y: 50, scale: 0.9 });
  },
  start: "top 85%",
  end: "bottom 15%"
});
```

### 4. Effet de parallaxe pour images de fond

```javascript
gsap.to(".testimonials-section img", {
  yPercent: -20,
  ease: "none",
  scrollTrigger: {
    trigger: ".testimonials-section",
    start: "top bottom",
    end: "bottom top",
    scrub: 1.5, // Valeur numérique pour effet plus fluide
    invalidateOnRefresh: true
  }
});
```

### 5. Animation de texte avec effet de lisibilité amélioré

Pour les titres sur images, utilisation d'effets de style élégants :

```html
<h2 class="text-2xl sm:text-3xl font-bold text-primary relative z-20 inline-block
           bg-white/95 backdrop-blur-sm px-8 py-4 rounded-full
           shadow-xl border-2 border-primary/10
           bg-gradient-to-br from-white/98 to-white/92
           transform transition-all duration-300 hover:scale-105">
  Ce que nos clients disent
</h2>
```

Avec effet de lueur CSS :

```css
.testimonials-section h2::before {
  content: '';
  position: absolute;
  inset: -2px;
  border-radius: 9999px;
  background: linear-gradient(45deg, #ed1e79, #ff9b2e);
  opacity: 0.3;
  filter: blur(10px);
  z-index: -1;
  transition: opacity 0.3s ease;
}
```

Animation spéciale au scroll :

```javascript
gsap.fromTo(".testimonials-section h2", 
  {
    opacity: 0,
    scale: 0.8,
    rotation: -5
  },
  {
    opacity: 1,
    scale: 1,
    rotation: 0,
    duration: 1,
    ease: "elastic.out(1, 0.5)",
    scrollTrigger: {
      trigger: ".testimonials-section h2",
      start: "top 80%",
      toggleActions: "play none none reset"
    }
  }
);
```

## Configuration avancée

### 1. Options par défaut du module

```javascript
const AnimationConfig = {
  defaultTriggerOptions: {
    start: "top 80%",
    end: "bottom 20%",
    toggleActions: "play none none none",
    markers: false, // Mettre à true pour debug
    invalidateOnRefresh: true
  },
  defaultDuration: 0.8,
  defaultStagger: 0.1,
  defaultEase: "power3.out",
  defaultScrub: 1
};
```

### 2. Gestion des événements responsive

```javascript
// Recalcul automatique au redimensionnement
let resizeTimer;
window.addEventListener('resize', () => {
  clearTimeout(resizeTimer);
  resizeTimer = setTimeout(() => {
    ScrollTrigger.refresh();
  }, 250);
});

// Gestion du changement d'orientation
window.addEventListener('orientationchange', () => {
  ScrollTrigger.refresh();
});
```

## Utilisation

### 1. Application simple

```html
<h2 class="fade-in">Mon titre animé</h2>
<p class="slide-left">Mon paragraphe glissant</p>
<div class="scale-in">Ma carte animée</div>
```

### 2. Animation de texte avancée

```html
<h1 class="text-reveal">Ce texte apparaîtra progressivement</h1>
```

### 3. Grilles animées

```html
<div class="batch-container">
  <div class="batch-item">Item 1</div>
  <div class="batch-item">Item 2</div>
  <div class="batch-item">Item 3</div>
</div>
```

### 4. Parallaxe personnalisée

```html
<img src="background.jpg" class="parallax" data-speed="0.5" />
```

## Personnalisation

### 1. Créer une animation personnalisée

```javascript
ScrollAnimations.createCustomAnimation(
  '.ma-classe',
  { opacity: 0, x: -100 }, // État initial
  { opacity: 1, x: 0, duration: 1 }, // État final
  { start: "top 75%" } // Options ScrollTrigger
);
```

### 2. Animation avec timeline complexe

```javascript
const tl = gsap.timeline({
  scrollTrigger: {
    trigger: ".my-section",
    start: "top center",
    end: "bottom center",
    scrub: 1
  }
});

tl.fromTo(".element1", { x: -100 }, { x: 0, duration: 1 })
  .fromTo(".element2", { y: 100 }, { y: 0, duration: 1 }, "-=0.5")
  .fromTo(".element3", { scale: 0 }, { scale: 1, duration: 0.5 });
```

## Bonnes pratiques

### 1. Performance

- Limitez le nombre d'animations simultanées
- Utilisez `will-change` avec parcimonie
- Évitez d'animer des propriétés coûteuses (box-shadow, filter)
- Utilisez ScrollTrigger.batch() pour les grilles d'éléments

### 2. Accessibilité

- Respectez `prefers-reduced-motion` pour les utilisateurs sensibles
- Utilisez `aria: "auto"` avec SplitText
- Assurez-vous que le contenu reste lisible sans animations

### 3. Mobile

- Testez sur mobile et ajustez les valeurs si nécessaire
- Considérez des animations plus simples sur mobile
- Utilisez `ScrollTrigger.isTouch` pour détecter les appareils tactiles

## Dépannage

### Les animations ne se déclenchent pas

1. Vérifiez que GSAP est bien chargé dans la console
2. Activez `markers: true` dans ScrollTrigger
3. Vérifiez que les sélecteurs CSS sont corrects
4. Assurez-vous que `ScrollAnimations.init()` est appelé

### Performance médiocre

1. Réduisez le nombre d'éléments animés simultanément
2. Utilisez `force3D: true` pour forcer l'accélération GPU
3. Évitez les animations sur des éléments avec box-shadow complexes
4. Utilisez `overwrite: 'auto'` pour éviter les conflits

### Problèmes sur mobile

1. Testez avec les outils de développement mobile
2. Ajustez les valeurs de déclenchement ScrollTrigger
3. Simplifiez ou désactivez certaines animations sur mobile

## Ressources

- [Documentation GSAP](https://gsap.com/docs/)
- [ScrollTrigger Guide](https://gsap.com/docs/v3/Plugins/ScrollTrigger/)
- [SplitText Documentation](https://gsap.com/docs/v3/Plugins/SplitText/)
- [Forums GreenSock](https://gsap.com/community/)

## Notes de version

- Version : 2.0.0
- Date : Janvier 2025
- Compatibilité : GSAP 3.13.0+
- Mise à jour : Documentation complète avec toutes les animations avancées implémentées, y compris les techniques de lisibilité pour texte sur image