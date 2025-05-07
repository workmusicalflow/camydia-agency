// Script de test spécifique pour la validation du numéro de téléphone
document.addEventListener('DOMContentLoaded', function() {
    // Vérification de la présence du formulaire de contact et du champ téléphone
    const contactForm = document.querySelector('form[action="/contact"]');
    const phoneInput = document.getElementById('phone');
    
    if (!contactForm || !phoneInput) {
        console.warn('Formulaire de contact ou champ téléphone non trouvé sur cette page');
        return;
    }
    
    console.log('Champ téléphone détecté, ajout des tests de validation');
    
    // Exemples de numéros de téléphone à tester
    const phoneTestCases = [
        // Numéros ivoiriens valides
        { value: '+225 07 12 34 56 78', valid: true, description: 'Ivoirien - Format international avec espaces' },
        { value: '+2250712345678', valid: true, description: 'Ivoirien - Format international sans espaces' },
        { value: '00225 07 12 34 56 78', valid: true, description: 'Ivoirien - Format international avec 00 et espaces' },
        { value: '002250712345678', valid: true, description: 'Ivoirien - Format international avec 00 sans espaces' },
        { value: '07 12 34 56 78', valid: true, description: 'Ivoirien - Format local avec espaces' },
        { value: '0712345678', valid: true, description: 'Ivoirien - Format local sans espaces' },
        { value: '712345678', valid: true, description: 'Ivoirien - Format court sans 0 initial' },
        
        // Numéros internationaux valides
        { value: '+33 6 12 34 56 78', valid: true, description: 'International - France mobile' },
        { value: '+1 202 555 1234', valid: true, description: 'International - USA' },
        { value: '+44 7911 123456', valid: true, description: 'International - UK mobile' },
        { value: '0033612345678', valid: true, description: 'International - France avec 00' },
        
        // Formats invalides
        { value: '+225', valid: false, description: 'Invalide - Indicatif seul, trop court' },
        { value: '+abc123', valid: false, description: 'Invalide - Format incorrect avec lettres' },
        { value: '12345', valid: false, description: 'Invalide - Numéro trop court' },
        { value: '+225abc123456', valid: false, description: 'Invalide - Numéro avec lettres' }
    ];
    
    // Ajouter un bouton de test du champ téléphone
    const testPhoneButton = document.createElement('button');
    testPhoneButton.type = 'button';
    testPhoneButton.textContent = 'Tester la validation téléphone';
    testPhoneButton.style.position = 'fixed';
    testPhoneButton.style.bottom = '70px'; // Au-dessus du bouton de test du formulaire
    testPhoneButton.style.right = '20px';
    testPhoneButton.style.zIndex = '9999';
    testPhoneButton.style.padding = '10px 20px';
    testPhoneButton.style.background = '#ed1e79'; // Couleur primaire du site
    testPhoneButton.style.color = 'white';
    testPhoneButton.style.border = 'none';
    testPhoneButton.style.borderRadius = '5px';
    testPhoneButton.style.cursor = 'pointer';
    testPhoneButton.style.boxShadow = '0 2px 5px rgba(0,0,0,0.2)';
    
    // Fonction pour tester tous les cas de téléphone
    testPhoneButton.addEventListener('click', function() {
        // Récupérer l'instance Alpine.js
        const alpineData = Alpine.$data(contactForm);
        
        if (!alpineData || !alpineData.validatePhone) {
            alert('Impossible de trouver la fonction de validation du téléphone dans Alpine.js');
            return;
        }
        
        // Créer un tableau pour les résultats des tests
        let testResults = [];
        
        // Tester chaque cas de numéro de téléphone
        phoneTestCases.forEach(testCase => {
            const isValid = alpineData.validatePhone(testCase.value);
            const passed = isValid === testCase.valid;
            
            testResults.push({
                value: testCase.value,
                expectedValid: testCase.valid,
                actualValid: isValid,
                passed: passed,
                description: testCase.description
            });
        });
        
        // Afficher les résultats dans une boîte de dialogue stylisée
        displayTestResults(testResults);
    });
    
    document.body.appendChild(testPhoneButton);
    
    // Fonction pour afficher les résultats des tests
    function displayTestResults(results) {
        // Créer un élément modal pour afficher les résultats
        const modal = document.createElement('div');
        modal.style.position = 'fixed';
        modal.style.top = '0';
        modal.style.left = '0';
        modal.style.width = '100%';
        modal.style.height = '100%';
        modal.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
        modal.style.display = 'flex';
        modal.style.justifyContent = 'center';
        modal.style.alignItems = 'center';
        modal.style.zIndex = '10000';
        
        // Contenu du modal
        const modalContent = document.createElement('div');
        modalContent.style.width = '90%';
        modalContent.style.maxWidth = '800px';
        modalContent.style.maxHeight = '90%';
        modalContent.style.backgroundColor = 'white';
        modalContent.style.borderRadius = '5px';
        modalContent.style.padding = '20px';
        modalContent.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.3)';
        modalContent.style.overflowY = 'auto';
        
        // Titre du modal
        const title = document.createElement('h2');
        title.textContent = 'Résultats des tests de validation du téléphone';
        title.style.color = '#ed1e79';
        title.style.marginBottom = '15px';
        
        // Résumé des tests
        const summary = document.createElement('div');
        summary.style.marginBottom = '20px';
        summary.style.padding = '10px';
        summary.style.backgroundColor = '#f5f5f5';
        summary.style.borderRadius = '5px';
        
        // Compter les tests réussis
        const passedTests = results.filter(r => r.passed).length;
        const totalTests = results.length;
        const passRate = Math.round((passedTests / totalTests) * 100);
        
        summary.innerHTML = `
            <p style="font-weight: bold; margin-bottom: 5px;">Résumé des tests:</p>
            <ul style="margin: 0; padding-left: 20px;">
                <li>Tests passés: ${passedTests} / ${totalTests} (${passRate}%)</li>
                <li>Numéros ivoiriens testés: ${results.filter(r => r.description.includes('Ivoirien')).length}</li>
                <li>Numéros internationaux testés: ${results.filter(r => r.description.includes('International')).length}</li>
                <li>Formats invalides testés: ${results.filter(r => r.description.includes('Invalide')).length}</li>
            </ul>
            <p style="margin-top: 10px; font-style: italic;">
                Note: Les numéros avec la mention "Ivoirien" sont éligibles à recevoir des SMS via notre API Orange.
            </p>
        `;
        
        // Tableau des résultats
        const table = document.createElement('table');
        table.style.width = '100%';
        table.style.borderCollapse = 'collapse';
        
        // En-tête du tableau
        const thead = document.createElement('thead');
        thead.innerHTML = `
            <tr>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: left; background-color: #f2f2f2;">Numéro</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: left; background-color: #f2f2f2;">Description</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: center; background-color: #f2f2f2;">Attendu</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: center; background-color: #f2f2f2;">Résultat</th>
                <th style="border: 1px solid #ddd; padding: 8px; text-align: center; background-color: #f2f2f2;">Statut</th>
            </tr>
        `;
        
        // Corps du tableau
        const tbody = document.createElement('tbody');
        results.forEach(result => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td style="border: 1px solid #ddd; padding: 8px;">${result.value}</td>
                <td style="border: 1px solid #ddd; padding: 8px;">${result.description}</td>
                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">${result.expectedValid ? 'Valide' : 'Invalide'}</td>
                <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">${result.actualValid ? 'Valide' : 'Invalide'}</td>
                <td style="border: 1px solid #ddd; padding: 8px; text-align: center; color: ${result.passed ? 'green' : 'red'};">
                    ${result.passed ? '✓' : '✗'}
                </td>
            `;
            tbody.appendChild(row);
        });
        
        table.appendChild(thead);
        table.appendChild(tbody);
        
        // Bouton de fermeture
        const closeButton = document.createElement('button');
        closeButton.textContent = 'Fermer';
        closeButton.style.marginTop = '20px';
        closeButton.style.padding = '8px 16px';
        closeButton.style.backgroundColor = '#ed1e79';
        closeButton.style.color = 'white';
        closeButton.style.border = 'none';
        closeButton.style.borderRadius = '4px';
        closeButton.style.cursor = 'pointer';
        
        closeButton.addEventListener('click', function() {
            document.body.removeChild(modal);
        });
        
        // Assembler le modal
        modalContent.appendChild(title);
        modalContent.appendChild(summary);
        modalContent.appendChild(table);
        modalContent.appendChild(closeButton);
        modal.appendChild(modalContent);
        
        // Ajouter le modal au document
        document.body.appendChild(modal);
    }
    
    console.log('Script de test du téléphone chargé avec succès');
});