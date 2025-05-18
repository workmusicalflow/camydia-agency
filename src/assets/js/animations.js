/**
 * Système modulaire d'animations au scroll avec GSAP ScrollTrigger
 * Camydia Agency - 2024
 */

// Enregistrer les plugins GSAP nécessaires
if (typeof gsap !== 'undefined') {
  gsap.registerPlugin(ScrollTrigger, SplitText);
} else {
  console.error('GSAP n'est pas chargé');
}

// Configuration globale des animations
const AnimationConfig = {
  // Options par défaut pour ScrollTrigger
  defaultTriggerOptions: {
    start: "top 80%",
    end: "bottom 20%",
    toggleActions: "play none none none", // Standard selon la doc officielle
    markers: false, // Mettre à true pour debug
    invalidateOnRefresh: true // Recalculer au refresh
  },
  
  // Durées et délais par défaut
  defaultDuration: 0.8,
  defaultStagger: 0.1,
  
  // Easing par défaut
  defaultEase: "power3.out",
  
  // Scrub par défaut pour les effets parallax
  defaultScrub: 1 // Valeur numérique pour un scrub smooth
};

/**
 * Module principal d'animations
 */
const ScrollAnimations = {
  /**
   * Initialise toutes les animations au scroll
   */
  init() {
    // Initialiser les animations par type
    this.initFadeAnimations();
    this.initScaleAnimations();
    this.initSlideAnimations();
    this.initTextAnimations();
    this.initStaggerAnimations();
    this.initBatchAnimations(); // Ajouter les animations batch recommandées dans la doc
    this.initParallaxAnimations();
    
    // Rafraîchir ScrollTrigger après chargement des images
    window.addEventListener('load', () => {
      ScrollTrigger.refresh();
    });
    
    // Gestion du resize pour les animations responsives
    let resizeTimer;
    window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(() => {
        ScrollTrigger.refresh();
      }, 250);
    });
  },
  
  /**
   * Animations de fondu (fade)
   */
  initFadeAnimations() {
    gsap.utils.toArray('.fade-in').forEach(element => {
      gsap.fromTo(element, 
        {
          opacity: 0,
          y: 30
        },
        {
          opacity: 1,
          y: 0,
          duration: AnimationConfig.defaultDuration,
          ease: AnimationConfig.defaultEase,
          scrollTrigger: {
            trigger: element,
            ...AnimationConfig.defaultTriggerOptions
          }
        }
      );
    });
  },
  
  /**
   * Animations de mise à l'échelle (scale)
   */
  initScaleAnimations() {
    gsap.utils.toArray('.scale-in').forEach(element => {
      gsap.fromTo(element,
        {
          scale: 0.8,
          opacity: 0
        },
        {
          scale: 1,
          opacity: 1,
          duration: AnimationConfig.defaultDuration,
          ease: "back.out(1.7)",
          scrollTrigger: {
            trigger: element,
            ...AnimationConfig.defaultTriggerOptions
          }
        }
      );
    });
  },
  
  /**
   * Animations de glissement (slide)
   */
  initSlideAnimations() {
    // Slide from left
    gsap.utils.toArray('.slide-left').forEach(element => {
      gsap.fromTo(element,
        {
          x: -100,
          opacity: 0
        },
        {
          x: 0,
          opacity: 1,
          duration: AnimationConfig.defaultDuration,
          ease: AnimationConfig.defaultEase,
          scrollTrigger: {
            trigger: element,
            ...AnimationConfig.defaultTriggerOptions
          }
        }
      );
    });
    
    // Slide from right
    gsap.utils.toArray('.slide-right').forEach(element => {
      gsap.fromTo(element,
        {
          x: 100,
          opacity: 0
        },
        {
          x: 0,
          opacity: 1,
          duration: AnimationConfig.defaultDuration,
          ease: AnimationConfig.defaultEase,
          scrollTrigger: {
            trigger: element,
            ...AnimationConfig.defaultTriggerOptions
          }
        }
      );
    });
  },
  
  /**
   * Animations de texte avec SplitText
   */
  initTextAnimations() {
    gsap.utils.toArray('.text-reveal').forEach(element => {
      const split = SplitText.create(element, {
        type: "lines,words",
        linesClass: "split-line++",
        wordsClass: "split-word++",
        aria: "auto"
      });
      
      // Cacher les éléments initialement
      gsap.set(split.words, { opacity: 0, y: 20 });
      
      // Créer l'animation
      ScrollTrigger.create({
        trigger: element,
        ...AnimationConfig.defaultTriggerOptions,
        onEnter: (self) => { // Utiliser la signature recommandée dans la doc
          gsap.to(split.words, {
            opacity: 1,
            y: 0,
            duration: AnimationConfig.defaultDuration,
            stagger: 0.02,
            ease: AnimationConfig.defaultEase
          });
        }
      });
    });
  },
  
  /**
   * Animations avec décalage (stagger)
   */
  initStaggerAnimations() {
    gsap.utils.toArray('.stagger-container').forEach(container => {
      const elements = container.querySelectorAll('.stagger-item');
      
      gsap.fromTo(elements,
        {
          opacity: 0,
          y: 50
        },
        {
          opacity: 1,
          y: 0,
          duration: AnimationConfig.defaultDuration,
          stagger: AnimationConfig.defaultStagger,
          ease: AnimationConfig.defaultEase,
          scrollTrigger: {
            trigger: container,
            ...AnimationConfig.defaultTriggerOptions
          }
        }
      );
    });
  },
  
  /**
   * Animations de parallaxe
   */
  initParallaxAnimations() {
    gsap.utils.toArray('.parallax').forEach(element => {
      const speed = element.dataset.speed || 0.5;
      
      gsap.to(element, {
        yPercent: parseFloat(speed) * 100,
        ease: "none",
        scrollTrigger: {
          trigger: element,
          start: "top bottom",
          end: "bottom top",
          scrub: AnimationConfig.defaultScrub // Utiliser la valeur par défaut configurée
        }
      });
    });
  },
  
  /**
   * Animations par batch (recommandé dans la documentation officielle)
   */
  initBatchAnimations() {
    // Utiliser ScrollTrigger.batch pour les grilles d'éléments
    gsap.utils.toArray('.batch-container').forEach(container => {
      const items = container.querySelectorAll('.batch-item');
      
      if (items.length > 0) {
        ScrollTrigger.batch(items, {
          ...AnimationConfig.defaultTriggerOptions,
          onEnter: batch => {
            gsap.to(batch, {
              opacity: 1,
              y: 0,
              duration: AnimationConfig.defaultDuration,
              stagger: AnimationConfig.defaultStagger,
              ease: AnimationConfig.defaultEase,
              overwrite: 'auto'
            });
          },
          onLeave: batch => {
            gsap.set(batch, {
              opacity: 0,
              y: 30
            });
          },
          onEnterBack: batch => {
            gsap.to(batch, {
              opacity: 1,
              y: 0,
              duration: AnimationConfig.defaultDuration * 0.7,
              stagger: AnimationConfig.defaultStagger,
              ease: AnimationConfig.defaultEase,
              overwrite: 'auto'
            });
          },
          onLeaveBack: batch => {
            gsap.set(batch, {
              opacity: 0,
              y: -30
            });
          }
        });
      }
    });
  },
  
  /**
   * Méthode utilitaire pour créer des animations personnalisées
   */
  createCustomAnimation(selector, fromVars, toVars, triggerOptions = {}) {
    gsap.utils.toArray(selector).forEach(element => {
      gsap.fromTo(element, fromVars, {
        ...toVars,
        scrollTrigger: {
          trigger: element,
          ...AnimationConfig.defaultTriggerOptions,
          ...triggerOptions
        }
      });
    });
  }
};

// Exposer l'API globalement
if (typeof window !== 'undefined') {
  window.ScrollAnimations = ScrollAnimations;
}

// Auto-initialisation si DOM prêt
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    ScrollAnimations.init();
  });
} else {
  ScrollAnimations.init();
}

// Méthodes utilitaires recommandées dans la documentation officielle
if (typeof gsap !== 'undefined' && ScrollTrigger) {
  // Forcer le recalcul des positions au changement d'orientation
  window.addEventListener('orientationchange', () => {
    ScrollTrigger.refresh();
  });
  
  // Nettoyer toutes les animations avant le déchargement de la page
  window.addEventListener('beforeunload', () => {
    ScrollTrigger.killAll();
  });
}