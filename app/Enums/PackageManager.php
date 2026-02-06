<?php

namespace App\Enums;

enum PackageManager: string
{
    case NPM = 'npm';
    case YARN = 'yarn';
    case PNPM = 'pnpm';

    public function label(): string
    {
        return strtoupper($this->value);
    }

    public function installCommand(): string
    {
        return match($this) {
            self::NPM => 'npm install',
            self::YARN => 'yarn install',
            self::PNPM => 'pnpm install',
        };
    }

    public function buildCommand(): string
    {
        return match($this) {
            self::NPM => 'npm run build',
            self::YARN => 'yarn build',
            self::PNPM => 'pnpm build',
        };
    }
}
