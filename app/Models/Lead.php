<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Lead extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'project_type',
        'budget',
        'timeline',
        'description',
        'service_interest',
        'team_package',
        'source',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'landing_page',
        'status',
        'priority',
        'notes',
        'assigned_to',
        'last_contacted_at',
        'converted_at',
        'lost_reason',
    ];

    protected function casts(): array
    {
        return [
            'last_contacted_at' => 'datetime',
            'converted_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Status options
     */
    public static function getStatuses(): array
    {
        return [
            'new' => 'New',
            'contacted' => 'Contacted',
            'qualified' => 'Qualified',
            'proposal' => 'Proposal Sent',
            'negotiation' => 'Negotiation',
            'converted' => 'Converted',
            'lost' => 'Lost',
        ];
    }

    /**
     * Priority options
     */
    public static function getPriorities(): array
    {
        return [
            'low' => 'Low',
            'normal' => 'Normal',
            'high' => 'High',
            'urgent' => 'Urgent',
        ];
    }

    /**
     * Source options
     */
    public static function getSources(): array
    {
        return [
            'contact_form' => 'Contact Form',
            'exit_intent' => 'Exit Intent Popup',
            'chat' => 'Live Chat',
            'referral' => 'Referral',
            'organic' => 'Organic Search',
            'paid' => 'Paid Ads',
            'social' => 'Social Media',
            'other' => 'Other',
        ];
    }

    /**
     * Get email logs for this lead
     */
    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class);
    }

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'priority', 'notes', 'assigned_to'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Scope for new leads
     */
    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    /**
     * Scope for today's leads
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope by source
     */
    public function scopeBySource($query, string $source)
    {
        return $query->where('source', $source);
    }
}
