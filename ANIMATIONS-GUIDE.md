# Guide d'utilisation du système d'animations GSAP

## Vue d'ensemble

Ce projet utilise GSAP 3.13.1 avec les plugins ScrollTrigger et SplitText pour créer des animations fluides et performantes.

## Architecture

### 1. Fichiers principaux

- `/src/assets/js/animations.js` : Module principal des animations
- `/views/layout.twig` : Inclut tous les scripts GSAP nécessaires
- `/views/index.twig` : Exemple d'implémentation des animations

### 2. Classes d'animation disponibles

Le système d'animations propose plusieurs classes CSS prêtes à l'emploi :

#### Animations de base

- `.fade-in` : Apparition en fondu avec mouvement vers le haut
- `.scale-in` : Apparition avec effet de zoom
- `.slide-left` : Glissement depuis la gauche
- `.slide-right` : Glissement depuis la droite

#### Animations de texte

- `.text-reveal` : Animation lettre par lettre ou mot par mot
- Utilise automatiquement SplitText pour diviser le texte

#### Animations groupées

- `.stagger-container` + `.stagger-item` : Animation en cascade des éléments enfants
- `.parallax` : Effet de parallaxe (utiliser `data-speed` pour contrôler la vitesse)
- `.batch-container` + `.batch-item` : Animation en groupe optimisée avec ScrollTrigger.batch()

## Utilisation

### 1. Application simple

Pour animer un élément, ajoutez simplement une classe d'animation :

```html
<h2 class="fade-in">Mon titre animé</h2>
<p class="slide-left">Mon paragraphe glissant</p>
```

### 2. Animation en cascade

Pour des éléments multiples qui apparaissent en cascade :

```html
<div class="stagger-container">
  <div class="card stagger-item">Card 1</div>
  <div class="card stagger-item">Card 2</div>
  <div class="card stagger-item">Card 3</div>
</div>
```

### 3. Parallaxe

Pour un effet de parallaxe sur une image de fond :

```html
<img src="background.jpg" class="parallax" data-speed="0.5" />
```

### 4. Animation de texte avancée

Pour révéler du texte mot par mot ou lettre par lettre :

```html
<h1 class="text-reveal">Ce texte apparaîtra progressivement</h1>
```

## Personnalisation

### 1. Créer une animation personnalisée

Utilisez la méthode `ScrollAnimations.createCustomAnimation()` :

```javascript
ScrollAnimations.createCustomAnimation(
  '.ma-classe',
  { opacity: 0, x: -100 }, // État initial
  { opacity: 1, x: 0, duration: 1 }, // État final
  { start: "top 75%" } // Options ScrollTrigger
);
```

### 2. Modifier les paramètres globaux

Éditez `AnimationConfig` dans `animations.js` :

```javascript
const AnimationConfig = {
  defaultTriggerOptions: {
    start: "top 80%", // Modifier le point de déclenchement
    markers: false // Activer pour debug
  },
  defaultDuration: 0.8, // Durée par défaut
  defaultStagger: 0.1, // Délai entre éléments
  defaultEase: "power3.out" // Courbe d'animation
};
```

## Exemples d'implémentation

### Page d'accueil (index.twig)

```javascript
// Animation des cartes de service
ScrollTrigger.batch(".service-card", {
  onEnter: elements => {
    gsap.fromTo(elements, 
      { opacity: 0, y: 50, scale: 0.9 },
      { opacity: 1, y: 0, scale: 1, duration: 0.8, stagger: 0.15 }
    );
  },
  once: true
});
```

### Autres pages

Pour appliquer le système à d'autres pages :

1. Assurez-vous que les scripts sont chargés (déjà fait dans layout.twig)
2. Ajoutez les classes d'animation aux éléments HTML
3. Optionnellement, créez des animations spécifiques dans un bloc `{% block scripts %}`

## Bonnes pratiques

1. **Performance** : Limitez le nombre d'animations simultanées
2. **Accessibilité** : Utilisez `prefers-reduced-motion` pour les utilisateurs sensibles
3. **Mobile** : Testez sur mobile et ajustez si nécessaire
4. **SEO** : Les animations n'affectent pas le contenu HTML

## Dépannage

### Les animations ne se déclenchent pas

1. Vérifiez que GSAP est bien chargé
2. Vérifiez la console pour les erreurs
3. Activez `markers: true` dans ScrollTrigger pour voir les zones de déclenchement

### Performance médiocre

1. Réduisez le nombre d'éléments animés
2. Utilisez `will-change: transform` avec parcimonie
3. Évitez d'animer des propriétés coûteuses (box-shadow, filter)

## Ressources

- [Documentation GSAP](https://gsap.com/docs/)
- [ScrollTrigger Guide](https://gsap.com/docs/v3/Plugins/ScrollTrigger/)
- [SplitText Documentation](https://gsap.com/docs/v3/Plugins/SplitText/)

## Notes de version

- Version : 1.1.0
- Date : Décembre 2024
- Compatibilité : GSAP 3.13.0+
- Mise à jour : Ajout des animations batch et alignement avec la documentation officielle ScrollTrigger