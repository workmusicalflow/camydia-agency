<?php

namespace App\App\Twig;

use App\App\Config\Routes;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Extension Twig pour les fonctions liées aux routes
 */
class RouteExtension extends AbstractExtension
{
    /**
     * Renvoie la liste des fonctions Twig.
     *
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('route', [$this, 'getRoute']),
            new TwigFunction('asset', [$this, 'getAsset']),
            new TwigFunction('image', [$this, 'getImage']),
            new TwigFunction('external', [$this, 'getExternal']),
        ];
    }

    /**
     * Récupère une route principale
     *
     * @param string $name Nom de la route (HOME, ABOUT, etc.)
     * @return string La route
     */
    public function getRoute(string $name): string
    {
        $constant = 'App\App\Config\Routes::' . strtoupper($name);
        if (defined($constant)) {
            return constant($constant);
        }
        
        return '/';
    }

    /**
     * Récupère le chemin d'un asset
     *
     * @param string $name Nom de l'asset (CSS, JS, LOGO, etc.)
     * @return string Le chemin de l'asset
     */
    public function getAsset(string $name): string
    {
        if (isset(Routes::ASSETS[strtoupper($name)])) {
            return Routes::ASSETS[strtoupper($name)];
        }
        
        return '';
    }

    /**
     * Récupère le chemin d'une image
     *
     * @param string $section La section (HOME, ABOUT, etc.)
     * @param string|null $name Nom spécifique de l'image (optionnel)
     * @return string Le chemin de l'image
     */
    public function getImage(string $section, ?string $name = null): string
    {
        $section = strtoupper($section);
        
        if ($name === null) {
            return Routes::IMAGES[$section] ?? '';
        }
        
        $name = strtoupper($name);
        return Routes::IMAGES[$section][$name] ?? '';
    }

    /**
     * Récupère un lien externe
     *
     * @param string $name Nom du lien (FACEBOOK, INSTAGRAM, etc.)
     * @return string L'URL externe
     */
    public function getExternal(string $name): string
    {
        return Routes::EXTERNAL[strtoupper($name)] ?? '#';
    }
}