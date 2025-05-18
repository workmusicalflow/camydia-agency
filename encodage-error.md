Constat fait du rendu lors du partage du lien du site web dans une conversation whatsapp :
les caractères `�` (souvent appelés "losanges avec point d'interrogation" ou "mojibake") indiquent un **problème d'encodage des caractères**.

Votre fichier HTML déclare bien `meta charset="UTF-8"`, ce qui est la bonne pratique. Le problème se situe très probablement à l'un des niveaux suivants :

1.  **L'encodage du fichier `.twig` lui-même** : Même si vous déclarez UTF-8 dans le HTML, si le fichier source (votre template Twig, ou le `layout.twig` où est défini le bloc `ogdescription` par défaut) est enregistré avec un autre encodage (par exemple ISO-8859-1 ou Windows-1252), les caractères accentués comme "é", "è", "à" ne seront pas interprétés correctement comme de l'UTF-8.
2.  **Traitement par le serveur/PHP** : Moins probable si vous utilisez un framework moderne comme Slim qui gère bien l'UTF-8 par défaut, mais il est possible qu'une configuration serveur ou une manipulation de chaîne en PHP sans prise en compte de l'UTF-8 puisse corrompre les caractères.
3.  **La façon dont la plateforme de partage (WhatsApp, Facebook, etc.) récupère et interprète le contenu** : Elles se fient généralement à l'en-tête HTTP `Content-Type` et subsidiairement à la balise `<meta charset>`. Si l'en-tête HTTP n'est pas explicitement UTF-8, elles pourraient mal interpréter.

**La cause la plus fréquente est la première : l'encodage du fichier source.**

Voici comment corriger et améliorer vos balises meta :

**Corrections principales (Encodage) :**

1.  **Vérifiez et re-enregistrez vos fichiers `.twig` en UTF-8 :**

    - Ouvrez le fichier `layout.twig` (ou le fichier principal qui contient ces balises meta) dans votre éditeur de code (VS Code, Sublime Text, PhpStorm, etc.).
    - Cherchez une option comme "Enregistrer avec encodage" (Save with Encoding) ou "Réouvrir avec encodage" (Reopen with Encoding).
    - Choisissez **UTF-8**.
    - Ré-enregistrez le fichier.
    - Faites de même pour tous les fichiers `.twig` qui contiennent du texte susceptible d'être utilisé dans les balises meta (par exemple, si une page spécifique écrase le bloc `ogdescription`).

2.  **Assurez-vous que PHP envoie l'en-tête HTTP correct (généralement géré par Slim) :**
    Dans votre `index.php` ou un middleware, vous devriez avoir quelque chose qui s'assure que l'en-tête `Content-Type` est bien `text/html; charset=UTF-8`. Slim le fait souvent automatiquement si la configuration est correcte. Vous pouvez le vérifier dans les outils de développement de votre navigateur (onglet Réseau/Network, sélectionnez la requête HTML principale, regardez les en-têtes de réponse).

**Améliorations des balises Meta :**

Votre structure de blocs Twig est bonne pour la personnalisation par page. Voici quelques suggestions :

```html
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    {% set site_name = "Camydia Agency" %} {% set default_description = "Camydia
    Agency transforme vos événements en expériences mémorables grâce à une gamme
    complète de services professionnels pour hôtesses en Côte d'Ivoire." %} {%
    set default_og_image =
    "https://camydia-agency.site/public/images/camydia-meta-banner.jpg" %} {#
    Assurez-vous que cette URL est absolue et accessible publiquement #} {% set
    base_url = "https://camydia-agency.site" %} {# Ou une variable globale si
    vous en avez une #}

    <title>{% block title %}{{ site_name }}{% endblock %}</title>
    <meta
      name="description"
      content="{% block metadescription %}{{ default_description }}{% endblock %}"
    />
    <meta name="theme-color" content="#ed1e79" />
    {# Couleur principale de votre marque #}

    <!-- META OPEN GRAPH POUR PARTAGE SOCIAL -->
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="{{ site_name }}" />
    <meta property="og:locale" content="fr_FR" />

    {# Ces blocs doivent être surchargés par chaque page spécifique #}
    <meta
      property="og:title"
      content="{% block ogtitle %}{% block title_og_override %}{{ block('title') }}{% endblock %}{% endblock %}"
    />
    <meta
      property="og:description"
      content="{% block ogdescription %}{{ block('metadescription') }}{% endblock %}"
    />
    <meta
      property="og:url"
      content="{% block ogurl %}{{ base_url ~ app.request.uri.path }}{% endblock %}"
    />
    {# Adaptez 'app.request.uri.path' à la manière dont Slim/Twig vous donne
    l'URL actuelle #}
    <meta
      property="og:image"
      content="{% block ogimage %}{{ default_og_image }}{% endblock %}"
    />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta
      property="og:image:alt"
      content="{% block ogimagealt %}Bannière promotionnelle pour {{ site_name }}{% endblock %}"
    />

    <!-- Meta Twitter -->
    <meta name="twitter:card" content="summary_large_image" />
    {#
    <meta name="twitter:site" content="@VotreCompteTwitter" />
    Si vous en avez un #} {#
    <meta name="twitter:creator" content="@AuteurSiDifferent" />
    Si pertinent #}
    <meta
      name="twitter:title"
      content="{% block twtitle %}{{ block('ogtitle') }}{% endblock %}"
    />
    <meta
      name="twitter:description"
      content="{% block twdescription %}{{ block('ogdescription') }}{% endblock %}"
    />
    <meta
      name="twitter:image"
      content="{% block twimage %}{{ block('ogimage') }}{% endblock %}"
    />
    <meta
      name="twitter:image:alt"
      content="{% block twimagealt %}{{ block('ogimagealt') }}{% endblock %}"
    />

    <!-- Preloading critical assets -->
    {# Assurez-vous que la fonction asset() génère les bons chemins #}
    <link rel="preload" href="{{ asset('css/style.css') }}" as="style" />
    {# Soyez plus spécifique si possible #}
    <link
      rel="preload"
      href="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"
      as="script"
    />
    <link rel="preload" href="{{ asset('js/app.js') }}?v=1.1" as="script" />
    {# Soyez plus spécifique #}

    <!-- Preload logo for faster paint -->
    <link
      rel="preload"
      href="{{ asset('images/logo-camydia.svg') }}"
      as="image"
    />
    {# Mettez le vrai chemin vers votre logo #}

    <!-- Favicons (générez-les avec realfavicongenerator.net) -->
    <link
      rel="apple-touch-icon"
      sizes="180x180"
      href="{{ asset('favicons/apple-touch-icon.png') }}"
    />
    <link
      rel="icon"
      type="image/png"
      sizes="32x32"
      href="{{ asset('favicons/favicon-32x32.png') }}"
    />
    <link
      rel="icon"
      type="image/png"
      sizes="16x16"
      href="{{ asset('favicons/favicon-16x16.png') }}"
    />
    <link rel="manifest" href="{{ asset('favicons/site.webmanifest') }}" />
    <link
      rel="mask-icon"
      href="{{ asset('favicons/safari-pinned-tab.svg') }}"
      color="#ed1e79"
    />
    <meta name="msapplication-TileColor" content="#da532c" />
    {# theme-color est déjà défini plus haut #}

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
    {# Chemin vers votre CSS compilé #} {# Google Fonts : Bodoni Moda est une
    police élégante mais peut être lourde. Inter est un bon choix pour le corps.
    #}
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,opsz,wght@0,6..96,400..900;1,6..96,400..900&family=Inter:wght@300;400;500;600;700&display=swap"
      rel="stylesheet"
    />

    <!-- Scripts -->
    <script
      src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"
      defer
    ></script>
    <script src="{{ asset('js/app.js') }}?v=1.1" defer></script>
    {# Ajoutez defer pour les scripts non critiques #}

    <!-- Page-specific scripts -->
    {% block scripts %}{% endblock %}

    <!-- Mobile-specific meta tags -->
    <meta name="format-detection" content="telephone=no" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="default" />

    <!-- Alpine.js cloak style to prevent flicker on load -->
    <style>
      [x-cloak] {
        display: none !important;
      }
    </style>
  </head>
  <body class="bg-white text-gray-900 antialiased font-sans">
    {# J'ai ajouté font-sans comme fallback pour Inter #} {% block header %}
    <div x-data="mobileMenu()">{% include 'partials/header.twig' %}</div>
    {% endblock %}

    <main id="main-content" role="main">
      {# Ajout d'ID et rôle pour accessibilité #} {% block content %}{% endblock
      %}
    </main>

    {% block footer %} {% include 'partials/footer.twig' %} {% endblock %}
  </body>
</html>
```

**Explications des améliorations :**

1.  **Variables Twig pour les valeurs par défaut** (`site_name`, `default_description`, etc.) : Rend le code plus lisible et facile à maintenir.
2.  **Bloc `title_og_override`** : Parfois, on veut un titre légèrement différent pour l'OG que pour la balise `<title>`. Ce bloc imbriqué permet cela.
3.  **`og:url` dynamique** : `{{ base_url ~ app.request.uri.path }}`. Vous devrez adapter `app.request.uri.path` à la manière dont votre instance Slim et Twig exposent l'URL actuelle de la requête. L'URL OG doit être l'URL canonique absolue de la page.
4.  **`og:image:alt` et `twitter:image:alt`** : Important pour l'accessibilité.
5.  **Chemins `asset()` plus spécifiques** : Au lieu de `asset('css')`, utilisez `asset('css/style.css')` (ou le nom de votre fichier CSS principal). Idem pour JS. Cela aide aussi au versioning/cache busting si votre fonction `asset()` le gère.
6.  **Favicons complets** : Utilisez un générateur comme [https://realfavicongenerator.net/](https://realfavicongenerator.net/) pour obtenir toutes les icônes nécessaires.
7.  **Google Fonts `<link rel="preconnect">`** : Améliore les performances de chargement des polices.
8.  **`defer` sur les scripts** : `defer` est généralement préférable pour les scripts qui ne sont pas critiques pour le premier rendu, car ils ne bloquent pas l'analyse HTML.
9.  **Accessibilité** : Ajout de `role="main"` et `id="main-content"` sur la balise `<main>`.
10. **`font-sans`** : Ajouté à `body` comme classe Tailwind pour appliquer `Inter` (qui est une police sans-serif) comme police par défaut. `Bodoni Moda` peut ensuite être appliquée spécifiquement aux titres.

**Comment surcharger dans une page spécifique (par exemple `services.twig`) :**

```twig
{% extends 'layout.twig' %}

{% block title %}Nos Services d'Excellence - {{ parent() }}{% endblock %}

{% block metadescription %}
Découvrez notre gamme complète de services professionnels d'hôtesses pour vos événements d'entreprise et occasions spéciales à Abidjan et en Côte d'Ivoire. Camydia Agency assure un accueil VIP, la gestion des inscriptions, l'animation de stands et plus.
{% endblock %}

{# og:title utilisera le block title par défaut, ce qui est souvent bien #}

{% block ogdescription %}
{{ block('metadescription') }} {# Réutilise la meta description optimisée #}
{% endblock %}

{# Si la page des services a une image spécifique pour le partage : #}
{# {% block ogimage %}https://camydia-agency.site/public/images/services-meta-banner.jpg{% endblock %} #}
{# {% block ogimagealt %}Hôtesses professionnelles de Camydia Agency en service lors d'un événement.{% endblock %} #}

{# og:url sera généré automatiquement si la configuration dans layout.twig est correcte #}

{% block content %}
  {# ... contenu de votre page services.twig ... #}
{% endblock %}
```

**Après avoir effectué les modifications :**

1.  **Videz le cache de votre navigateur.**
2.  **Utilisez les outils de débogage des plateformes sociales** pour forcer une nouvelle analyse de votre page :
    - Facebook Sharing Debugger : [https://developers.facebook.com/tools/debug/](https://developers.facebook.com/tools/debug/)
    - Twitter Card Validator : [https://cards-dev.twitter.com/validator](https://cards-dev.twitter.com/validator)
    - LinkedIn Post Inspector : [https://www.linkedin.com/post-inspector/](https://www.linkedin.com/post-inspector/)

Cela devrait résoudre le problème des caractères incorrects et améliorer la façon dont vos pages sont présentées lors du partage.
