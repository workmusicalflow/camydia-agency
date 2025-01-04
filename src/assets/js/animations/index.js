import { gsap } from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

// Initialisation des plugins GSAP
gsap.registerPlugin(ScrollTrigger);

// Export des fonctions d'animation
export function initAnimations() {
  // Animation de la section hero
  gsap.from(".hero-section", {
    duration: 1,
    opacity: 0,
    y: 50,
    ease: "power2.out",
  });

  // Animation des sections scroll
  gsap.utils.toArray(".scroll-section").forEach((section) => {
    gsap.from(section, {
      scrollTrigger: {
        trigger: section,
        start: "top 80%",
      },
      opacity: 0,
      y: 50,
      duration: 0.8,
      ease: "power2.out",
    });
  });

  // Animation des éléments au survol
  document.querySelectorAll(".hover-animate").forEach((element) => {
    element.addEventListener("mouseenter", () => {
      gsap.to(element, {
        scale: 1.05,
        duration: 0.3,
        ease: "power2.out",
      });
    });

    element.addEventListener("mouseleave", () => {
      gsap.to(element, {
        scale: 1,
        duration: 0.3,
        ease: "power2.out",
      });
    });
  });
}
