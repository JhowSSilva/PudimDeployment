<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServerInstallationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'language',
        'version',
        'description',
        'dependencies',
        'install_script',
        'configure_script',
        'default_config',
        'is_active',
    ];

    protected $casts = [
        'dependencies' => 'array',
        'default_config' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Scope for active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific language
     */
    public function scopeForLanguage($query, string $language)
    {
        return $query->where('language', $language);
    }

    /**
     * Get formatted dependencies as string
     */
    public function getFormattedDependenciesAttribute(): string
    {
        return implode(' ', $this->dependencies ?? []);
    }

    /**
     * Check if template has configuration script
     */
    public function hasConfigureScript(): bool
    {
        return !empty($this->configure_script);
    }

    /**
     * Get default config value by key
     */
    public function getDefaultConfigValue(string $key, $default = null)
    {
        return $this->default_config[$key] ?? $default;
    }

    /**
     * Get all templates grouped by language
     */
    public static function getGroupedByLanguage(): array
    {
        return self::active()
            ->orderBy('language')
            ->orderBy('version', 'desc')
            ->get()
            ->groupBy('language')
            ->toArray();
    }

    /**
     * Get available languages
     */
    public static function getAvailableLanguages(): array
    {
        return self::active()
            ->distinct('language')
            ->pluck('language')
            ->toArray();
    }

    /**
     * Get versions for specific language
     */
    public static function getVersionsForLanguage(string $language): array
    {
        return self::active()
            ->forLanguage($language)
            ->orderBy('version', 'desc')
            ->pluck('version', 'id')
            ->toArray();
    }
}