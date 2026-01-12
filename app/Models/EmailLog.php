<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'lead_id',
        'type',
        'subject',
        'body',
        'sent_at',
        'status',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    /**
     * Email types
     */
    public static function getTypes(): array
    {
        return [
            'welcome' => 'Welcome Email',
            'follow_up' => 'Follow-up',
            'proposal' => 'Proposal',
            'custom' => 'Custom',
        ];
    }

    /**
     * Get the lead
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Scope for successful emails
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope for failed emails
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
