<?php

namespace App\Enums;

enum DatabaseType: string
{
    case MYSQL = 'mysql';
    case POSTGRESQL = 'postgresql';
    case MONGODB = 'mongodb';
    case MARIADB = 'mariadb';

    public function label(): string
    {
        return match($this) {
            self::MYSQL => 'MySQL',
            self::POSTGRESQL => 'PostgreSQL',
            self::MONGODB => 'MongoDB',
            self::MARIADB => 'MariaDB',
        };
    }

    public function defaultPort(): int
    {
        return match($this) {
            self::MYSQL => 3306,
            self::POSTGRESQL => 5432,
            self::MONGODB => 27017,
            self::MARIADB => 3306,
        };
    }
}
