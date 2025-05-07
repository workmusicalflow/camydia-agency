# DEVBOOK : Site Web de Camydia Agency

## Objectif

Construire le site web de **Camydia Agency** en suivant ce plan de développement optimisé. Ce document sert de checklist pour suivre les implémentations et objectifs à chaque phase du projet.

---

### **Phase 1 : Initialisation du projet**

- [x] Configurer l'environnement (CPanel, PHP, SQLite, Composer)
- [x] Installer Tailwind CSS et Alpine.js
- [x] Structurer le projet (dossiers `public/`, `src/`, `assets/`)
- [x] Initialiser un fichier SQLite pour la base de données
- [x] Générer la structure de base du projet (MVC, routes)

---

### **Phase 2 : Page d'accueil** [TERMINÉ]

- [x] Créer la structure de navigation fixe
- [x] Développer la section d'introduction
- [x] Intégrer la présentation des services phares
- [x] Ajouter la section témoignages
- [x] Implémenter l'appel à l'action principal

---

### **Phase 3 : Pages principales**

- [~] Créer la page "Notre Histoire & Approche" :

  - [x] Template de base créé (about.twig)
  - [ ] Section histoire
  - [ ] Section engagement
  - [ ] Section approche personnalisée
  - [ ] Liste des partenaires

- [~] Développer la page "Services & Réalisations" :

  - [x] Template de base créé (services.twig)
  - [ ] Présentation des services
  - [ ] Galerie photos interactive
  - [ ] Intégration des témoignages
  - [ ] Liste des types d'événements

- [~] Réaliser la page "Notre Équipe" :
  - [x] Template de base créé (team.twig)
  - [ ] Présentation des hôtesses
  - [ ] Section compétences
  - [ ] Galerie photos professionnelles
  - [ ] Description du processus de sélection

---

### **Phase 4 : Page Contact et Fonctionnalités**

- [~] Créer la page "Contact" :
  - [x] Template de base créé (contact.twig)
  - [ ] Formulaire de contact
  - [ ] Coordonnées
  - [ ] FAQ dynamique avec animations
- [ ] Implémenter la validation en temps réel du formulaire
- [ ] Configurer le système d'emails via SMTP :
  - Configuration du serveur SMTP sécurisé
  - Paramétrage des identifiants SMTP
  - Test de la connexion SMTP
  - Mise en place des templates d'emails :
    - Email de confirmation automatique au client
    - Copie au secrétariat de l'agence (secretariat@camydia-agency.site)
    - Template personnalisé incluant :
      - Remerciement pour la prise de contact
      - Récapitulatif des informations soumises
      - Délai de réponse estimé
      - Signature de l'agence
  - Tests d'envoi des emails
- [ ] Intégrer les réseaux sociaux

---

### **Phase 5 : Design, Animations et Responsive**

- [x] Appliquer la charte graphique (couleurs : #ed1e79; #808080; #ff9b2e)
- [x] Installer et configurer GSAP
- [~] Implémenter les animations :
  - [x] Transitions de page fluides
  - [x] Apparition progressive des sections au scroll
  - [x] Animations des images et textes
  - [ ] Effets de parallaxe subtils
  - [x] Transitions du menu mobile (correction de la synchronisation des états open/isOpen)
- [x] Optimiser le responsive design
- [x] Optimiser les images et performances
- [ ] Ajouter le bouton retour en haut de page animé
- [x] Finaliser la navigation mobile avec animations

---

### **Phase 6 : Tests et Déploiement**

- [ ] Tester toutes les fonctionnalités
- [ ] Optimiser les performances
- [ ] Vérifier la compatibilité cross-browser
- [ ] Déployer sur CPanel
- [ ] Configurer le SSL

---

## Suivi des commits

- Mettre à jour ce fichier après chaque implémentation réussie
- Chaque commit doit être accompagné d'un message clair et concis et un push sur le dépôt distant doit être également fait; "https://github.com/workmusicalflow/camydia-agency.git"
- Utiliser des préfixes de commit : [INIT], [HOME], [PAGE], [DESIGN], [FIX]

## Prochaines étapes

1. Installer et configurer GSAP pour les animations avancées
2. Développer la page "Notre Histoire & Approche" :
   - Section histoire
   - Section engagement
   - Section approche personnalisée
   - Liste des partenaires
3. Optimiser les performances et le responsive design
