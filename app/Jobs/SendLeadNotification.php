<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendLeadNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public Lead $lead
    ) {}

    public function handle(): void
    {
        $recipients = Setting::get('notification_emails', env('NOTIFICATION_EMAILS', 'contact@genieinfo.tech'));
        $emails = array_map('trim', explode(',', $recipients));

        try {
            Mail::send([], [], function ($message) use ($emails) {
                $message->to($emails)
                    ->replyTo($this->lead->email, $this->lead->name)
                    ->subject("🎯 New Lead: {$this->lead->name} - " . ucfirst($this->lead->source))
                    ->html($this->buildEmailHtml());
            });

            Log::info('Lead notification sent', [
                'lead_id' => $this->lead->id,
                'recipients' => $emails,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send lead notification', [
                'lead_id' => $this->lead->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function buildEmailHtml(): string
    {
        $lead = $this->lead;
        $sourceLabel = Lead::getSources()[$lead->source] ?? $lead->source;

        return <<<HTML
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <div style="background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%); padding: 20px; border-radius: 8px 8px 0 0;">
                <h1 style="color: white; margin: 0; font-size: 24px;">🎯 New Lead Captured!</h1>
                <p style="color: rgba(255,255,255,0.9); margin: 8px 0 0 0;">via {$sourceLabel}</p>
            </div>

            <div style="background: #f9fafb; padding: 24px; border: 1px solid #e5e7eb; border-top: none;">
                <h2 style="color: #1f2937; margin: 0 0 16px 0; font-size: 18px;">Contact Information</h2>

                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280; width: 120px;">Name:</td>
                        <td style="padding: 8px 0; color: #1f2937; font-weight: 600;">{$lead->name}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #6b7280;">Email:</td>
                        <td style="padding: 8px 0;">
                            <a href="mailto:{$lead->email}" style="color: #3b82f6;">{$lead->email}</a>
                        </td>
                    </tr>
HTML
        . ($lead->phone ? "<tr><td style='padding: 8px 0; color: #6b7280;'>Phone:</td><td style='padding: 8px 0;'><a href='tel:{$lead->phone}' style='color: #3b82f6;'>{$lead->phone}</a></td></tr>" : '')
        . ($lead->company ? "<tr><td style='padding: 8px 0; color: #6b7280;'>Company:</td><td style='padding: 8px 0; color: #1f2937;'>{$lead->company}</td></tr>" : '')
        . ($lead->service_interest ? "<tr><td style='padding: 8px 0; color: #6b7280;'>Service:</td><td style='padding: 8px 0; color: #1f2937;'>{$lead->service_interest}</td></tr>" : '')
        . <<<HTML
                </table>

                {$this->getMessageSection()}

                <div style="margin-top: 24px; padding-top: 24px; border-top: 1px solid #e5e7eb;">
                    <a href="{$this->getAdminUrl()}" style="display: inline-block; background: #3b82f6; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 600;">
                        View in Admin Panel →
                    </a>
                </div>
            </div>

            <div style="padding: 16px; text-align: center; color: #9ca3af; font-size: 12px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 8px 8px;">
                Genie InfoTech Lead Management System
            </div>
        </div>
        HTML;
    }

    protected function getMessageSection(): string
    {
        if (!$this->lead->description) {
            return '';
        }

        $message = nl2br(htmlspecialchars($this->lead->description));

        return <<<HTML
        <div style="margin-top: 20px;">
            <h3 style="color: #1f2937; margin: 0 0 12px 0; font-size: 16px;">Message</h3>
            <div style="background: white; padding: 16px; border-radius: 6px; border: 1px solid #e5e7eb; color: #374151;">
                {$message}
            </div>
        </div>
        HTML;
    }

    protected function getAdminUrl(): string
    {
        return config('app.url') . '/admin/leads/' . $this->lead->id;
    }
}
