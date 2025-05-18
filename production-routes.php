<?php

namespace App\App\Config;

/**
 * Class Routes
 * 
 * Centralise toutes les routes de l'application pour éviter les liens codés en dur
 * et faciliter la maintenance du code.
 */
class Routes
{
    /**
     * Routes principales du site
     */
    const HOME = '/';
    const ABOUT = '/about';
    const SERVICES = '/services';
    const CONTACT = '/contact';
    const TEAM = '/team';
    
    /**
     * Routes d'assets
     * Modifiées pour production - préfixe /public ajouté
     */
    const ASSETS = [
        'CSS' => '/public/css/main.css',
        'JS' => '/public/js/main.js',
        'LOGO' => '/public/images/logo/logo-camydia.jpg',
    ];

    /**
     * Routes d'images par section
     * Modifiées pour production - préfixe /public ajouté
     */
    const IMAGES = [
        'HOME' => [
            'HERO' => '/public/images/content/home/hero-min.png',
            'TESTIMONIALS_BKG' => '/public/images/content/home/testimonials-bkg.jpg',
        ],
        'ABOUT' => [
            'MAP' => '/public/images/content/about/others/82407.png',
        ],
        'PARTENAIRES' => '/public/images/content/partenaires/',
        'SERVICES' => '/public/images/content/services/',
    ];

    /**
     * Liens externes
     */
    const EXTERNAL = [
        'FACEBOOK' => 'https://www.facebook.com/profile.php?id=100069320362729',
        'INSTAGRAM' => '#', // À compléter
        'LINKEDIN' => '#',  // À compléter
        'TWITTER' => '#',   // À compléter
    ];

    /**
     * Retourne l'URL complète pour une route donnée
     *
     * @param string $route La route à convertir en URL
     * @return string L'URL complète
     */
    public static function url(string $route): string
    {
        // Si l'URL commence déjà par http ou https, on la retourne telle quelle
        if (strpos($route, 'http') === 0) {
            return $route;
        }

        // En production, on utilise la constante définie dans app.php
        $baseUrl = defined('APP_URL') ? APP_URL : self::getBaseUrl();
        
        // On s'assure que la route commence par un slash
        if (strpos($route, '/') !== 0) {
            $route = '/' . $route;
        }
        
        return $baseUrl . $route;
    }

    /**
     * Récupère l'URL de base du site
     * 
     * @return string
     */
    private static function getBaseUrl(): string
    {
        // En production, on utilise https://www.camydia-agency.site par défaut
        return 'https://www.camydia-agency.site';
    }
}