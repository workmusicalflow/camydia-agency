# Correction du problème d'encodage des balises meta

## Problème identifié

Les caractères spéciaux (accents, etc.) ne s'affichent pas correctement lors du partage du site sur WhatsApp. Les symboles `�` indiquent un problème d'encodage de caractères.

## Causes possibles et solutions

1. **Problème d'encodage du fichier `.twig`** - C'est la cause la plus probable
2. **Problème d'en-tête HTTP Content-Type** - Moins probable avec un framework moderne
3. **Problème d'interprétation par les plateformes de partage**

## Solution recommandée

### 1. Ré-enregistrer les fichiers Twig en UTF-8

Assurez-vous que tous vos fichiers Twig, en particulier `layout.twig`, sont enregistrés en UTF-8:

- Ouvrez le fichier dans votre éditeur
- Utilisez l'option "Enregistrer avec encodage" ou "Réouvrir avec encodage"
- Sélectionnez **UTF-8** (sans BOM)
- Ré-enregistrez le fichier

### 2. Assurer un en-tête HTTP correct

Ajoutez ou vérifiez cette ligne dans votre `index.php` principal:

```php
// Définir l'encodage UTF-8 dans les en-têtes HTTP
header('Content-Type: text/html; charset=UTF-8');
```

### 3. Template layout.twig optimisé

```twig
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  
  {% set site_name = "Camydia Agency" %}
  {% set default_description = "Camydia Agency transforme vos événements en expériences mémorables grâce à une gamme complète de services professionnels pour hôtesses en Côte d'Ivoire." %}
  {% set default_og_image = "https://camydia-agency.site/public/images/camydia-meta-banner.jpg" %}
  {% set base_url = "https://camydia-agency.site" %}
  
  <title>{% block title %}{{ site_name }}{% endblock %}</title>
  <meta name="description" content="{% block metadescription %}{{ default_description }}{% endblock %}">
  <meta name="theme-color" content="#ed1e79">
  
  <!-- META OPEN GRAPH POUR PARTAGE SOCIAL -->
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="{{ site_name }}">
  <meta property="og:locale" content="fr_FR">
  <meta property="og:title" content="{% block ogtitle %}{{ block('title') }}{% endblock %}">
  <meta property="og:description" content="{% block ogdescription %}{{ block('metadescription') }}{% endblock %}">
  <meta property="og:url" content="{% block ogurl %}{{ base_url }}{% if app.request.uri.path is defined %}{{ app.request.uri.path }}{% endif %}{% endblock %}">
  <meta property="og:image" content="{% block ogimage %}{{ default_og_image }}{% endblock %}">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:image:alt" content="{% block ogimagealt %}Bannière promotionnelle pour {{ site_name }}{% endblock %}">
  
  <!-- Meta Twitter -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="{% block twtitle %}{{ block('ogtitle') }}{% endblock %}">
  <meta name="twitter:description" content="{% block twdescription %}{{ block('ogdescription') }}{% endblock %}">
  <meta name="twitter:image" content="{% block twimage %}{{ block('ogimage') }}{% endblock %}">
  <meta name="twitter:image:alt" content="{% block twimagealt %}{{ block('ogimagealt') }}{% endblock %}">
  
  <!-- Preloading critical assets -->
  <link rel="preload" href="{{ asset('css') }}" as="style">
  <link rel="preload" href="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" as="script">
  <link rel="preload" href="{{ asset('js') }}?v=1.1" as="script">
  
  <!-- Preload logo for faster paint -->
  <link rel="preload" href="{{ asset('logo') }}" as="image">
  
  <!-- Styles -->
  <link rel="stylesheet" href="{{ asset('css') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  
  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  <script src="{{ asset('js') }}?v=1.1"></script>
  
  <!-- Page-specific scripts -->
  {% block scripts %}{% endblock %}
  
  <!-- Mobile-specific meta tags -->
  <meta name="format-detection" content="telephone=no">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  
  <!-- Alpine.js cloak style to prevent flicker on load -->
  <style>
    [x-cloak] { display: none !important; }
  </style>
</head>
<body class="bg-white text-gray-900 antialiased font-sans">
  {% block header %}
    <div x-data="mobileMenu()">
      {% include 'partials/header.twig' %}
    </div>
  {% endblock %}

  <main id="main-content" role="main">
    {% block content %}{% endblock %}
  </main>

  {% block footer %}
    {% include 'partials/footer.twig' %}
  {% endblock %}
</body>
</html>
```

### 4. Personnalisation par page (exemple pour services.twig)

```twig
{% extends 'layout.twig' %}

{% block title %}Nos Services d'Excellence - {{ parent() }}{% endblock %}

{% block metadescription %}
Découvrez notre gamme complète de services professionnels d'hôtesses pour vos événements d'entreprise et occasions spéciales à Abidjan et en Côte d'Ivoire.
{% endblock %}

{% block ogimage %}https://camydia-agency.site/public/images/camydia-meta-banner.jpg{% endblock %}
{% block ogimagealt %}Hôtesses professionnelles de Camydia Agency en service lors d'un événement.{% endblock %}

{% block content %}
  <!-- Contenu de la page -->
{% endblock %}
```

## Vérification après mise à jour

1. Videz le cache Twig:
   ```bash
   rm -rf ~/public_html/private/cache/twig/*
   ```

2. Utilisez les outils de validation des plateformes sociales:
   - [Facebook Sharing Debugger](https://developers.facebook.com/tools/debug/)
   - [Twitter Card Validator](https://cards-dev.twitter.com/validator)
   - [LinkedIn Post Inspector](https://www.linkedin.com/post-inspector/)

## Autres bonnes pratiques

1. Ajouter un fichier `.htaccess` à la racine qui force l'encodage UTF-8:
   ```apache
   # Forcer l'encodage UTF-8
   AddDefaultCharset UTF-8
   ```

2. Vérifier que votre éditeur de code est configuré pour enregistrer tous les nouveaux fichiers en UTF-8 par défaut.

3. Si vous utilisez Git, configurez `.gitattributes` pour assurer la cohérence des encodages:
   ```
   # Auto detect text files and perform LF normalization
   * text=auto
   *.php text eol=lf
   *.twig text eol=lf
   *.html text eol=lf
   *.css text eol=lf
   *.js text eol=lf
   ```