<?php

namespace App\Enums;

enum PhpVersion: string
{
    case PHP_74 = '7.4';
    case PHP_80 = '8.0';
    case PHP_81 = '8.1';
    case PHP_82 = '8.2';
    case PHP_83 = '8.3';

    public function label(): string
    {
        return "PHP {$this->value}";
    }

    public function fpmService(): string
    {
        return "php{$this->value}-fpm";
    }
}
