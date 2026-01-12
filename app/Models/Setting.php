<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'value',
        'type',
    ];

    /**
     * Get a setting value
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = static::find($key);
            if (!$setting) {
                return $default;
            }

            return match ($setting->type) {
                'boolean' => (bool) $setting->value,
                'integer' => (int) $setting->value,
                'array' => json_decode($setting->value, true),
                default => $setting->value,
            };
        });
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, mixed $value, string $type = 'string'): void
    {
        $storeValue = match ($type) {
            'boolean' => $value ? '1' : '0',
            'array' => json_encode($value),
            default => (string) $value,
        };

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $storeValue, 'type' => $type]
        );

        Cache::forget("setting.{$key}");
    }

    /**
     * Default settings
     */
    public static function getDefaults(): array
    {
        return [
            'auto_responder_enabled' => true,
            'welcome_email_subject' => 'Thanks for reaching out, {name}!',
            'welcome_email_body' => "Hi {name},\n\nThank you for your interest in Genie InfoTech. We've received your inquiry and our team will get back to you within 24 hours.\n\nWhile you wait, here's what you can expect:\n- Free consultation call\n- Project assessment & roadmap\n- Cost estimation with 70% savings breakdown\n\nBest regards,\nGenie InfoTech Team",
            'notification_emails' => 'contact@genieinfo.tech,genie.projectmanager@gmail.com',
        ];
    }
}
