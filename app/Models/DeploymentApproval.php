<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeploymentApproval extends Model
{
    protected $fillable = [
        'pipeline_run_id',
        'deployment_strategy_id',
        'status',
        'requested_by_user_id',
        'reviewed_by_user_id',
        'request_message',
        'review_comment',
        'requested_at',
        'expires_at',
        'reviewed_at',
        'required_approvers',
        'required_approvals',
        'approval_history',
    ];

    protected $casts = [
        'required_approvers' => 'array',
        'approval_history' => 'array',
        'required_approvals' => 'integer',
        'requested_at' => 'datetime',
        'expires_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    // Relationships
    public function pipelineRun(): BelongsTo
    {
        return $this->belongsTo(PipelineRun::class);
    }

    public function deploymentStrategy(): BelongsTo
    {
        return $this->belongsTo(DeploymentStrategy::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    // Business Logic
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || 
               ($this->expires_at && $this->expires_at->isPast());
    }

    public function canBeReviewed(): bool
    {
        return $this->isPending() && !$this->isExpired();
    }

    public function approve(User $user, string $comment = null): bool
    {
        if (!$this->canBeReviewed()) {
            return false;
        }

        if (!$this->canUserApprove($user)) {
            return false;
        }

        $this->addApproval($user, $comment);

        if ($this->hasEnoughApprovals()) {
            $this->update([
                'status' => 'approved',
                'reviewed_by_user_id' => $user->id,
                'review_comment' => $comment,
                'reviewed_at' => now(),
            ]);
        }

        return true;
    }

    public function reject(User $user, string $comment = null): bool
    {
        if (!$this->canBeReviewed()) {
            return false;
        }

        $this->update([
            'status' => 'rejected',
            'reviewed_by_user_id' => $user->id,
            'review_comment' => $comment,
            'reviewed_at' => now(),
        ]);

        return true;
    }

    public function expire(): void
    {
        if ($this->isPending()) {
            $this->update(['status' => 'expired']);
        }
    }

    protected function canUserApprove(User $user): bool
    {
        // Don't allow self-approval
        if ($user->id === $this->requested_by_user_id) {
            return false;
        }

        // Already approved by this user
        if ($this->hasUserApproved($user)) {
            return false;
        }

        // Check if user is in required approvers list
        $requiredApprovers = $this->required_approvers ?? [];
        if (empty($requiredApprovers)) {
            return true; // Anyone can approve if no specific users required
        }

        // Check if user ID is in the list or if user has required role
        foreach ($requiredApprovers as $approver) {
            if (is_numeric($approver) && $approver == $user->id) {
                return true;
            }
            // Could check roles here if implementing role-based approvals
        }

        return false;
    }

    protected function hasUserApproved(User $user): bool
    {
        $history = $this->approval_history ?? [];
        
        foreach ($history as $approval) {
            if ($approval['user_id'] === $user->id) {
                return true;
            }
        }

        return false;
    }

    protected function addApproval(User $user, ?string $comment): void
    {
        $history = $this->approval_history ?? [];
        
        $history[] = [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'comment' => $comment,
            'approved_at' => now()->toIso8601String(),
        ];

        $this->update(['approval_history' => $history]);
    }

    protected function hasEnoughApprovals(): bool
    {
        $history = $this->approval_history ?? [];
        return count($history) >= $this->required_approvals;
    }

    public function getApprovalCount(): int
    {
        return count($this->approval_history ?? []);
    }

    public function getRemainingApprovals(): int
    {
        return max(0, $this->required_approvals - $this->getApprovalCount());
    }

    public function getTimeRemaining(): ?string
    {
        if (!$this->expires_at) {
            return null;
        }

        if ($this->isExpired()) {
            return 'Expired';
        }

        return $this->expires_at->diffForHumans();
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending')
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'pending')
                    ->where('expires_at', '<=', now());
    }
}
