<?php

namespace App\Enums;

enum NodeVersion: string
{
    case NODE_14 = '14.x';
    case NODE_16 = '16.x';
    case NODE_18 = '18.x';
    case NODE_20 = '20.x';
    case NODE_21 = '21.x';

    public function label(): string
    {
        return "Node.js {$this->value}";
    }

    public function nvmInstall(): string
    {
        return "nvm install {$this->value} && nvm use {$this->value}";
    }
}
