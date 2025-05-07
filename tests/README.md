# Tests pour l'API Orange SMS

Ce dossier contient des scripts de test pour vérifier le bon fonctionnement de diverses fonctionnalités de l'application.

## Test de l'API Orange SMS

Le script `test-orange-sms-api.php` permet de tester directement l'API Orange SMS en envoyant des messages à des numéros spécifiés.

### Fonctionnalités

- Utilise directement l'API Orange SMS sans passer par les classes du projet
- Envoie des SMS de test à des numéros spécifiés (actuellement +2250141399354 et +2250768174439)
- Génère des logs détaillés pour faciliter le diagnostic des problèmes
- Affiche toutes les étapes du processus d'envoi (demande de jeton, préparation des numéros, envoi, etc.)
- Capture les réponses HTTP complètes pour analyse

### Exécution du script

```bash
# Se placer à la racine du projet
cd /Users/ns2poportable/Documents/camydia-agency

# Exécuter le script de test
php tests/test-orange-sms-api.php
```

### Fichier de log

Le script génère un fichier de log détaillé à l'emplacement :
```
/Users/ns2poportable/Documents/camydia-agency/tests/sms-api-test-results.log
```

Ce fichier contient toutes les informations utiles pour diagnostiquer d'éventuels problèmes avec l'API.

### Codes de sortie

- `0` : Tous les SMS ont été envoyés avec succès
- `1` : Au moins un SMS n'a pas pu être envoyé ou une erreur s'est produite

## Dépannage

Si vous rencontrez des problèmes avec l'API Orange SMS, vérifiez les points suivants :

1. **Identifiants API** : Assurez-vous que les identifiants dans `src/App/Config/app.php` sont corrects
2. **Format des numéros** : Les numéros doivent être au format international (+225XXXXXXXXX)
3. **Crédit SMS** : Vérifiez que votre compte Orange dispose de crédits suffisants
4. **Disponibilité de l'API** : L'API Orange peut parfois être indisponible, réessayez plus tard

Pour plus d'informations, consultez la documentation officielle de l'API Orange SMS : https://developer.orange.com/apis/sms-ci/getting-started