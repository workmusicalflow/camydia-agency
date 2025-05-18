# Personnalisation des meta tags pour chaque page

J'ai ajouté des balises meta OpenGraph et Twitter optimisées au template `layout.twig`. Ces balises génériques s'appliqueront à toutes les pages par défaut.

Pour personnaliser les balises meta pour des pages spécifiques, vous pouvez ajouter les blocs suivants dans chaque template individuel:

## Pour la page d'accueil (index.twig)

```twig
{% extends 'layout.twig' %}

{% block title %}Accueil - Camydia Agency{% endblock %}

{% block ogtitle %}Camydia Agency - Agence d'hôtesses professionnelles{% endblock %}
{% block ogdescription %}Camydia Agency propose des services d'hôtesses et hôtes professionnels pour tous vos événements: salons, conférences, soirées VIP en Côte d'Ivoire{% endblock %}
{% block ogurl %}https://camydia-agency.site/{% endblock %}

{% block twtitle %}Camydia Agency - Agence d'hôtesses professionnelles{% endblock %}
{% block twdescription %}Camydia Agency propose des services d'hôtesses et hôtes professionnels pour tous vos événements: salons, conférences, soirées VIP en Côte d'Ivoire{% endblock %}

{% block content %}
  <!-- Contenu de la page d'accueil -->
{% endblock %}
```

## Pour la page À propos (about.twig)

```twig
{% extends 'layout.twig' %}

{% block title %}Notre Histoire & Équipe - Camydia Agency{% endblock %}

{% block ogtitle %}Notre Histoire & Équipe - Camydia Agency{% endblock %}
{% block ogdescription %}Découvrez l'histoire de Camydia Agency, notre équipe d'élite et notre processus de sélection rigoureux pour garantir l'excellence de nos services{% endblock %}
{% block ogurl %}https://camydia-agency.site/about{% endblock %}

{% block twtitle %}Notre Histoire & Équipe - Camydia Agency{% endblock %}
{% block twdescription %}Découvrez l'histoire de Camydia Agency, notre équipe d'élite et notre processus de sélection rigoureux pour garantir l'excellence de nos services{% endblock %}

{% block content %}
  <!-- Contenu de la page À propos -->
{% endblock %}
```

## Pour la page Services (services.twig)

```twig
{% extends 'layout.twig' %}

{% block title %}Nos Services - Camydia Agency{% endblock %}

{% block ogtitle %}Nos Services - Camydia Agency{% endblock %}
{% block ogdescription %}Camydia Agency propose une gamme complète de services professionnels: hôtesses d'accueil, event staffing, animations commerciales, VIP Service{% endblock %}
{% block ogurl %}https://camydia-agency.site/services{% endblock %}

{% block twtitle %}Nos Services - Camydia Agency{% endblock %}
{% block twdescription %}Camydia Agency propose une gamme complète de services professionnels: hôtesses d'accueil, event staffing, animations commerciales, VIP Service{% endblock %}

{% block content %}
  <!-- Contenu de la page Services -->
{% endblock %}
```

## Pour la page Contact (contact.twig)

```twig
{% extends 'layout.twig' %}

{% block title %}Contact - Camydia Agency{% endblock %}

{% block ogtitle %}Contactez-nous - Camydia Agency{% endblock %}
{% block ogdescription %}Contactez Camydia Agency pour tous vos besoins en services d'hôtesses professionnelles. Devis personnalisés pour vos événements.{% endblock %}
{% block ogurl %}https://camydia-agency.site/contact{% endblock %}

{% block twtitle %}Contactez-nous - Camydia Agency{% endblock %}
{% block twdescription %}Contactez Camydia Agency pour tous vos besoins en services d'hôtesses professionnelles. Devis personnalisés pour vos événements.{% endblock %}

{% block content %}
  <!-- Contenu de la page Contact -->
{% endblock %}
```

## Remarques importantes:

1. **Vérifier l'image de partage**: Assurez-vous que l'image `camydia-meta-banner.jpg` existe dans le dossier `/public/images/` sur votre serveur. Cette image doit être au format 1200x630 pixels pour un affichage optimal sur les réseaux sociaux.

2. **Tester le partage**: Utilisez des outils comme [Facebook Sharing Debugger](https://developers.facebook.com/tools/debug/) ou [Twitter Card Validator](https://cards-dev.twitter.com/validator) pour vérifier que vos balises meta sont correctement interprétées.

3. **Cache à vider**: N'oubliez pas de vider le cache Twig après avoir modifié ces templates:
   ```bash
   rm -rf ~/public_html/private/cache/twig/*
   ```

4. **Formats d'image recommandés**: 
   - Facebook/OpenGraph: 1200x630 pixels
   - Twitter: 1200x600 pixels
   - Taille maximale recommandée: 5MB

5. **Personnalisation avancée**: Pour une personnalisation plus poussée par page, vous pouvez créer un système dynamique qui utilise les données du contrôleur pour définir les meta tags.