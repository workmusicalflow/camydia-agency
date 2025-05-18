# Correction du problème d'affichage des partenaires

## Diagnostic du problème

Le message "Nos partenaires seront bientôt affichés ici" s'affiche lorsque le tableau `partenaires` est vide, ce qui signifie qu'aucune image de partenaire n'a été trouvée.

Dans le HomeController.php, le code suivant est utilisé pour chercher les images:
```php
$partenairesDir = __DIR__ . '/../../../public/images/content/partenaires';
```

Ceci peut causer des problèmes si:
1. Le dossier `public/images/content/partenaires` n'existe pas
2. Le dossier existe mais est vide
3. Le chemin relatif `__DIR__ . '/../../../public'` ne pointe pas au bon endroit sur le serveur

## Solution 1: Vérifier et créer le dossier des partenaires

```bash
# Se connecter au serveur
ssh utilisateur@camydia-agency.site

# Vérifier si le dossier existe
ls -la ~/public_html/public/images/content/partenaires

# Si le dossier n'existe pas, le créer
mkdir -p ~/public_html/public/images/content/partenaires

# Vérifier les permissions
chmod 755 ~/public_html/public/images/content/partenaires
```

## Solution 2: Ajouter des images de partenaires

Si le dossier existe mais est vide, téléchargez quelques images de partenaires:

```bash
# Se connecter au serveur
ssh utilisateur@camydia-agency.site

# Ajouter quelques images de test (si vous avez des images locales)
cd ~/public_html/public/images/content/partenaires
touch partenaire1.jpg partenaire2.jpg partenaire3.jpg
```

Ou utilisez FTP pour télécharger des images de partenaires dans le dossier.

## Solution 3: Corriger le chemin dans HomeController.php

Si le problème est lié au chemin relatif, modifiez le HomeController.php pour utiliser un chemin absolu:

```php
// Version avec chemin absolu
$partenairesDir = $_SERVER['DOCUMENT_ROOT'] . '/public/images/content/partenaires';

// Ou spécifier le chemin complet
$partenairesDir = '/home/username/public_html/public/images/content/partenaires';
```

## Solution 4: Afficher les messages de débogage

Pour comprendre ce qui se passe, modifiez temporairement HomeController.php pour afficher des informations de débogage:

```php
public function index(Request $request, Response $response): Response
{
    // Récupérer la liste des logos des partenaires
    $partenairesDir = __DIR__ . '/../../../public/images/content/partenaires';
    $partenaires = [];
    
    // Ajouter des informations de débogage
    $debug = [
        'chemin_absolu' => realpath($partenairesDir),
        'existe' => is_dir($partenairesDir) ? 'Oui' : 'Non',
        'contenu' => is_dir($partenairesDir) ? scandir($partenairesDir) : 'Dossier inexistant'
    ];
    
    if (is_dir($partenairesDir)) {
        $files = scandir($partenairesDir);
        
        foreach ($files as $file) {
            // Ignorer les dossiers . et .. et s'assurer que c'est une image
            if ($file !== '.' && $file !== '..' && !is_dir($partenairesDir . '/' . $file)) {
                // Vérifier l'extension du fichier pour s'assurer que c'est une image
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    // Extraire le nom sans extension pour l'utiliser comme alt text
                    $name = pathinfo($file, PATHINFO_FILENAME);
                    $partenaires[] = [
                        'file' => $file,
                        'path' => '/public/images/content/partenaires/' . $file,
                        'name' => $name
                    ];
                }
            }
        }
    }
    
    return $this->view->render($response, 'index.twig', [
        'title' => 'Camydia Agency - Accueil',
        'partenaires' => $partenaires,
        'debug' => $debug  // Ajouter les informations de débogage
    ]);
}
```

Puis ajoutez ce code dans index.twig juste après la ligne 153:

```twig
{% if debug is defined %}
<div class="text-left bg-gray-100 p-4 my-4 rounded overflow-auto max-h-60">
    <h3 class="font-bold mb-2">Informations de débogage:</h3>
    <p><strong>Chemin absolu:</strong> {{ debug.chemin_absolu }}</p>
    <p><strong>Le dossier existe:</strong> {{ debug.existe }}</p>
    <p><strong>Contenu du dossier:</strong> 
        {% if debug.contenu is iterable %}
            <ul class="list-disc pl-5">
            {% for item in debug.contenu %}
                <li>{{ item }}</li>
            {% endfor %}
            </ul>
        {% else %}
            {{ debug.contenu }}
        {% endif %}
    </p>
</div>
{% endif %}
```

Cela affichera des informations qui vous aideront à comprendre pourquoi aucune image n'est trouvée.

## Vérification finale

Pour vérifier que les chemins sont correctement résolus, créez un simple fichier PHP de test:

```php
<?php
// Créer un fichier test-path.php dans public_html
$dir = __DIR__ . '/public/images/content/partenaires';
echo "Chemin testé: " . $dir . "<br>";
echo "Chemin absolu: " . realpath($dir) . "<br>";
echo "Existe: " . (is_dir($dir) ? 'Oui' : 'Non') . "<br>";
echo "Contenu:<br>";
if (is_dir($dir)) {
    $files = scandir($dir);
    echo "<pre>";
    print_r($files);
    echo "</pre>";
} else {
    echo "Dossier inexistant";
}
```

Accédez à `https://camydia-agency.site/test-path.php` pour voir les résultats.