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
     */
    const ASSETS = [
        'CSS' => '/css/main.css',
        'JS' => '/js/main.js',
        'LOGO' => '/images/logo/logo-camydia.jpg',
    ];

    /**
     * Routes d'images par section
     */
    const IMAGES = [
        'HOME' => [
            'HERO' => '/images/content/home/hero-min.png',
            'TESTIMONIALS_BKG' => '/images/content/home/testimonials-bkg.jpg',
        ],
        'ABOUT' => [
            'MAP' => '/images/content/about/others/82407.png',
        ],
        'PARTENAIRES' => '/images/content/partenaires/',
        'SERVICES' => '/images/content/services/',
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

        // Construction de l'URL de base (dans un environnement de production, cela pourrait venir d'une variable d'environnement)
        $baseUrl = self::getBaseUrl();
        
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
        // En environnement de développement, on utilise simplement l'hôte actuel
        // En production, cela devrait idéalement provenir d'une config
        if (isset($_SERVER['HTTP_HOST'])) {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            return $protocol . '://' . $_SERVER['HTTP_HOST'];
        }
        
        // Fallback par défaut si $_SERVER n'est pas disponible
        return '';
    }
}