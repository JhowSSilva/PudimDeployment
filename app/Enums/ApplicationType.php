<?php

namespace App\Enums;

enum ApplicationType: string
{
    case LARAVEL = 'laravel';
    case WORDPRESS = 'wordpress';
    case STATIC_HTML = 'static_html';
    case NODEJS_EXPRESS = 'nodejs_express';
    case REACT_SPA = 'react_spa';
    case VUE_SPA = 'vue_spa';
    case NEXTJS = 'nextjs';
    case NUXTJS = 'nuxtjs';
    case ANGULAR = 'angular';
    case NESTJS = 'nestjs';
    case DJANGO = 'django';
    case FLASK = 'flask';
    case RUBY_RAILS = 'ruby_rails';
    case PHP_PURE = 'php_pure';
    case SYMFONY = 'symfony';
    case CODEIGNITER = 'codeigniter';
    case CUSTOM = 'custom';

    public function label(): string
    {
        return match($this) {
            self::LARAVEL => 'Laravel (PHP)',
            self::WORDPRESS => 'WordPress (PHP)',
            self::STATIC_HTML => 'HTML/CSS/JS Estático',
            self::NODEJS_EXPRESS => 'Node.js/Express',
            self::REACT_SPA => 'React (SPA)',
            self::VUE_SPA => 'Vue.js (SPA)',
            self::NEXTJS => 'Next.js (SSR)',
            self::NUXTJS => 'Nuxt.js (SSR)',
            self::ANGULAR => 'Angular',
            self::NESTJS => 'Nest.js',
            self::DJANGO => 'Python/Django',
            self::FLASK => 'Python/Flask',
            self::RUBY_RAILS => 'Ruby on Rails',
            self::PHP_PURE => 'PHP Puro',
            self::SYMFONY => 'Symfony',
            self::CODEIGNITER => 'CodeIgniter',
            self::CUSTOM => 'Customizado',
        };
    }

    public function requiresPhp(): bool
    {
        return in_array($this, [
            self::LARAVEL,
            self::WORDPRESS,
            self::PHP_PURE,
            self::SYMFONY,
            self::CODEIGNITER,
        ]);
    }

    public function requiresNode(): bool
    {
        return in_array($this, [
            self::NODEJS_EXPRESS,
            self::REACT_SPA,
            self::VUE_SPA,
            self::NEXTJS,
            self::NUXTJS,
            self::ANGULAR,
            self::NESTJS,
        ]);
    }

    public function requiresDatabase(): bool
    {
        return in_array($this, [
            self::LARAVEL,
            self::WORDPRESS,
            self::DJANGO,
            self::FLASK,
            self::RUBY_RAILS,
            self::SYMFONY,
            self::CODEIGNITER,
        ]);
    }

    public function defaultRootDirectory(): string
    {
        return match($this) {
            self::LARAVEL => '/public',
            self::WORDPRESS => '',
            self::STATIC_HTML => '',
            self::REACT_SPA, self::VUE_SPA, self::ANGULAR => '/dist',
            self::NEXTJS, self::NUXTJS => '/.next',
            self::SYMFONY => '/public',
            default => '/public',
        };
    }

    public function nginxTemplate(): string
    {
        return match($this) {
            self::LARAVEL => 'laravel',
            self::WORDPRESS => 'wordpress',
            self::STATIC_HTML => 'static',
            self::REACT_SPA, self::VUE_SPA, self::ANGULAR => 'spa',
            self::NODEJS_EXPRESS, self::NEXTJS, self::NESTJS => 'node_proxy',
            self::NUXTJS => 'nuxt',
            self::SYMFONY => 'symfony',
            default => 'generic_php',
        };
    }
}
