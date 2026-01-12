<?php

namespace App\Jobs;

use App\Models\EmailLog;
use App\Models\Lead;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public Lead $lead
    ) {}

    public function handle(): void
    {
        $subject = $this->parseTemplate(
            Setting::get('welcome_email_subject', 'Thanks for reaching out, {name}!')
        );

        $body = $this->parseTemplate(
            Setting::get('welcome_email_body', $this->getDefaultBody())
        );

        try {
            Mail::send([], [], function ($message) use ($subject, $body) {
                $message->to($this->lead->email, $this->lead->name)
                    ->subject($subject)
                    ->html($this->buildEmailHtml($body));
            });

            // Log successful email
            EmailLog::create([
                'lead_id' => $this->lead->id,
                'type' => 'welcome',
                'subject' => $subject,
                'body' => $body,
                'status' => 'sent',
            ]);

            Log::info('Welcome email sent', [
                'lead_id' => $this->lead->id,
                'email' => $this->lead->email,
            ]);
        } catch (\Exception $e) {
            // Log failed email
            EmailLog::create([
                'lead_id' => $this->lead->id,
                'type' => 'welcome',
                'subject' => $subject,
                'body' => $body,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error('Failed to send welcome email', [
                'lead_id' => $this->lead->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function parseTemplate(string $template): string
    {
        $replacements = [
            '{name}' => $this->lead->name,
            '{email}' => $this->lead->email,
            '{company}' => $this->lead->company ?? '',
            '{service}' => $this->lead->service_interest ?? '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    protected function getDefaultBody(): string
    {
        return <<<TEXT
Hi {name},

Thank you for your interest in Genie InfoTech! We've received your inquiry and our team will get back to you within 24 hours.

While you wait, here's what you can expect:
• Free consultation call to understand your project
• Project assessment & roadmap
• Cost estimation with 70% savings breakdown compared to US/EU rates

We look forward to discussing how we can help bring your project to life.

Best regards,
The Genie InfoTech Team

---
Expert Engineers. Exceptional Results.
https://genieinfo.tech
TEXT;
    }

    protected function buildEmailHtml(string $body): string
    {
        $bodyHtml = nl2br(htmlspecialchars($body));

        return <<<HTML
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background: #ffffff;">
            <div style="background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%); padding: 32px; text-align: center;">
                <h1 style="color: white; margin: 0; font-size: 28px;">Genie InfoTech</h1>
                <p style="color: rgba(255,255,255,0.9); margin: 8px 0 0 0; font-size: 14px;">Expert Engineers. Exceptional Results.</p>
            </div>

            <div style="padding: 32px; color: #374151; line-height: 1.6;">
                {$bodyHtml}
            </div>

            <div style="background: #f3f4f6; padding: 24px; text-align: center; border-top: 1px solid #e5e7eb;">
                <p style="margin: 0 0 16px 0; color: #6b7280; font-size: 14px;">
                    Have questions? Reply to this email or reach us at:
                </p>
                <a href="mailto:contact@genieinfo.tech" style="color: #3b82f6; text-decoration: none;">contact@genieinfo.tech</a>
                <span style="color: #9ca3af; margin: 0 8px;">|</span>
                <a href="https://wa.me/8801976445888" style="color: #22c55e; text-decoration: none;">WhatsApp</a>
            </div>

            <div style="padding: 16px; text-align: center; color: #9ca3af; font-size: 12px;">
                © 2024 Genie InfoTech. All rights reserved.
            </div>
        </div>
        HTML;
    }
}
