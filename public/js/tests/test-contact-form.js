// Script de test pour le formulaire de contact
document.addEventListener('DOMContentLoaded', function() {
    // Vérification de la présence du formulaire de contact
    const contactForm = document.querySelector('form[action="/contact"]');
    if (!contactForm) {
        console.warn('Formulaire de contact non trouvé sur cette page');
        return;
    }

    console.log('Formulaire de contact détecté');

    // Fonction pour remplir automatiquement le formulaire avec des données de test
    window.fillContactForm = function() {
        // Récupération des champs du formulaire
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        const phoneInput = document.getElementById('phone');
        const subjectSelect = document.getElementById('subject');
        const messageTextarea = document.getElementById('message');
        const privacyCheckbox = document.getElementById('privacy');

        // Remplissage avec des données de test
        if (nameInput) nameInput.value = 'Test Utilisateur';
        if (emailInput) emailInput.value = 'test@example.com';
        if (phoneInput) phoneInput.value = '+225 07 12 34 56 78';
        if (subjectSelect) subjectSelect.value = 'information';
        if (messageTextarea) messageTextarea.value = 'Ceci est un message de test envoyé depuis le script de test automatique. Veuillez ignorer ce message.';
        if (privacyCheckbox) privacyCheckbox.checked = true;

        console.log('Formulaire rempli avec des données de test');
    };

    // Fonction pour soumettre automatiquement le formulaire
    window.submitContactForm = function() {
        // D'abord remplir le formulaire
        window.fillContactForm();

        // Simuler un clic sur le bouton d'envoi
        const submitButton = contactForm.querySelector('button[type="submit"]');
        if (submitButton) {
            console.log('Soumission du formulaire de test...');
            submitButton.click();
        } else {
            console.warn('Bouton de soumission non trouvé');
        }
    };

    // Ajouter un bouton de test dans l'interface
    const testButton = document.createElement('button');
    testButton.type = 'button';
    testButton.textContent = 'Tester le formulaire';
    testButton.style.position = 'fixed';
    testButton.style.bottom = '20px';
    testButton.style.right = '20px';
    testButton.style.zIndex = '9999';
    testButton.style.padding = '10px 20px';
    testButton.style.background = '#ff9b2e';
    testButton.style.color = 'white';
    testButton.style.border = 'none';
    testButton.style.borderRadius = '5px';
    testButton.style.cursor = 'pointer';
    testButton.style.boxShadow = '0 2px 5px rgba(0,0,0,0.2)';
    
    testButton.addEventListener('click', function() {
        window.fillContactForm();
        alert('Formulaire rempli avec des données de test. Cliquez sur "Envoyer le message" pour tester la soumission.');
    });
    
    document.body.appendChild(testButton);
    
    console.log('Script de test du formulaire de contact chargé avec succès');
});