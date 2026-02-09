<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class InstanceRegistrationToken extends Model
{
    use HasFactory;

    protected $fillable = ['token', 'user_id', 'team_id', 'expires_at', 'used_at', 'meta'];

    protected $casts = [
        'meta' => 'array',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public static function generateForUser($user = null, $team = null, $ttlMinutes = 60 * 24)
    {
        // Accept either model or relation
        if (is_object($team) && method_exists($team, 'first')) {
            $team = $team->first();
        }

        if (is_object($user) && method_exists($user, 'first')) {
            $user = $user->first();
        }

        return static::create([
            'token' => Str::random(40),
            'user_id' => $user?->id,
            'team_id' => $team?->id,
            'expires_at' => now()->addMinutes($ttlMinutes),
        ]);
    }

    public function isValid(): bool
    {
        return !$this->used_at && (!$this->expires_at || $this->expires_at->isFuture());
    }

    public function markUsed(): void
    {
        $this->used_at = now();
        $this->save();
    }
}
