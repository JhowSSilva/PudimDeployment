<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class RolePermission extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'category',
        'description',
        'is_dangerous',
        'sort_order',
    ];

    protected $casts = [
        'is_dangerous' => 'boolean',
    ];

    /**
     * Permission categories.
     */
    public const CATEGORY_SERVERS = 'servers';
    public const CATEGORY_SITES = 'sites';
    public const CATEGORY_DEPLOYMENTS = 'deployments';
    public const CATEGORY_DATABASES = 'databases';
    public const CATEGORY_SSL = 'ssl';
    public const CATEGORY_WORKERS = 'workers';
    public const CATEGORY_MONITORING = 'monitoring';
    public const CATEGORY_BILLING = 'billing';
    public const CATEGORY_TEAM = 'team';

    /**
     * Scope to get permissions by category.
     */
    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category)
                     ->orderBy('sort_order');
    }

    /**
     * Scope to get dangerous permissions.
     */
    public function scopeDangerous(Builder $query): Builder
    {
        return $query->where('is_dangerous', true);
    }

    /**
     * Get all permissions grouped by category.
     */
    public static function getAllGrouped(): array
    {
        return self::orderBy('category')
                   ->orderBy('sort_order')
                   ->get()
                   ->groupBy('category')
                   ->toArray();
    }

    /**
     * Get permission by slug.
     */
    public static function findBySlug(string $slug): ?self
    {
        return self::where('slug', $slug)->first();
    }

    /**
     * Create default permissions.
     */
    public static function createDefaults(): void
    {
        $permissions = [
            // Servers
            ['name' => 'View Servers', 'slug' => 'view-servers', 'category' => self::CATEGORY_SERVERS, 'sort_order' => 1],
            ['name' => 'Create Servers', 'slug' => 'create-servers', 'category' => self::CATEGORY_SERVERS, 'sort_order' => 2],
            ['name' => 'Edit Servers', 'slug' => 'edit-servers', 'category' => self::CATEGORY_SERVERS, 'sort_order' => 3],
            ['name' => 'Delete Servers', 'slug' => 'delete-servers', 'category' => self::CATEGORY_SERVERS, 'is_dangerous' => true, 'sort_order' => 4],
            ['name' => 'Manage Server Services', 'slug' => 'manage-server-services', 'category' => self::CATEGORY_SERVERS, 'sort_order' => 5],
            
            // Sites
            ['name' => 'View Sites', 'slug' => 'view-sites', 'category' => self::CATEGORY_SITES, 'sort_order' => 10],
            ['name' => 'Create Sites', 'slug' => 'create-sites', 'category' => self::CATEGORY_SITES, 'sort_order' => 11],
            ['name' => 'Edit Sites', 'slug' => 'edit-sites', 'category' => self::CATEGORY_SITES, 'sort_order' => 12],
            ['name' => 'Delete Sites', 'slug' => 'delete-sites', 'category' => self::CATEGORY_SITES, 'is_dangerous' => true, 'sort_order' => 13],
            
            // Deployments
            ['name' => 'View Deployments', 'slug' => 'view-deployments', 'category' => self::CATEGORY_DEPLOYMENTS, 'sort_order' => 20],
            ['name' => 'Trigger Deployments', 'slug' => 'trigger-deployments', 'category' => self::CATEGORY_DEPLOYMENTS, 'sort_order' => 21],
            ['name' => 'Rollback Deployments', 'slug' => 'rollback-deployments', 'category' => self::CATEGORY_DEPLOYMENTS, 'is_dangerous' => true, 'sort_order' => 22],
            
            // Databases
            ['name' => 'View Databases', 'slug' => 'view-databases', 'category' => self::CATEGORY_DATABASES, 'sort_order' => 30],
            ['name' => 'Create Databases', 'slug' => 'create-databases', 'category' => self::CATEGORY_DATABASES, 'sort_order' => 31],
            ['name' => 'Delete Databases', 'slug' => 'delete-databases', 'category' => self::CATEGORY_DATABASES, 'is_dangerous' => true, 'sort_order' => 32],
            
            // SSL
            ['name' => 'View SSL Certificates', 'slug' => 'view-ssl', 'category' => self::CATEGORY_SSL, 'sort_order' => 40],
            ['name' => 'Manage SSL Certificates', 'slug' => 'manage-ssl', 'category' => self::CATEGORY_SSL, 'sort_order' => 41],
            
            // Workers
            ['name' => 'View Workers', 'slug' => 'view-workers', 'category' => self::CATEGORY_WORKERS, 'sort_order' => 50],
            ['name' => 'Manage Workers', 'slug' => 'manage-workers', 'category' => self::CATEGORY_WORKERS, 'sort_order' => 51],
            
            // Monitoring
            ['name' => 'View Monitoring', 'slug' => 'view-monitoring', 'category' => self::CATEGORY_MONITORING, 'sort_order' => 60],
            ['name' => 'Manage Alerts', 'slug' => 'manage-alerts', 'category' => self::CATEGORY_MONITORING, 'sort_order' => 61],
            
            // Billing
            ['name' => 'View Billing', 'slug' => 'view-billing', 'category' => self::CATEGORY_BILLING, 'sort_order' => 70],
            ['name' => 'Manage Subscription', 'slug' => 'manage-subscription', 'category' => self::CATEGORY_BILLING, 'sort_order' => 71],
            
            // Team
            ['name' => 'View Team Members', 'slug' => 'view-team', 'category' => self::CATEGORY_TEAM, 'sort_order' => 80],
            ['name' => 'Invite Members', 'slug' => 'invite-members', 'category' => self::CATEGORY_TEAM, 'sort_order' => 81],
            ['name' => 'Manage Roles', 'slug' => 'manage-roles', 'category' => self::CATEGORY_TEAM, 'sort_order' => 82],
            ['name' => 'Remove Members', 'slug' => 'remove-members', 'category' => self::CATEGORY_TEAM, 'is_dangerous' => true, 'sort_order' => 83],
        ];

        foreach ($permissions as $permission) {
            self::firstOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }
    }
}
