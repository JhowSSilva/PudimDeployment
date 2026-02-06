<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServerSoftwareCatalog extends Model
{
    protected $table = 'server_software_catalog';

    protected $fillable = [
        'key',
        'name',
        'category',
        'description',
        'is_default',
        'dependencies',
        'install_commands',
        'verify_commands',
        'install_order',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'dependencies' => 'array',
        'install_commands' => 'array',
        'verify_commands' => 'array',
        'install_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('install_order')->orderBy('name');
    }
}
