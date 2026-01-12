<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Jobs\SendLeadNotification;
use App\Jobs\SendWelcomeEmail;
use App\Models\Lead;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    /**
     * Handle contact form submission
     *
     * SECURITY:
     * - Honeypot middleware applied in routes
     * - Request validation via ContactRequest
     * - Rate limiting via throttle middleware
     */
    public function submit(ContactRequest $request): JsonResponse
    {
        try {
            // Create lead record
            $lead = Lead::create([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'phone' => $request->validated('phone'),
                'company' => $request->validated('company'),
                'service_interest' => $request->validated('service'),
                'description' => $request->validated('message'),
                'source' => $request->validated('source', 'contact_form'),
                'utm_source' => $request->validated('utm_source'),
                'utm_medium' => $request->validated('utm_medium'),
                'utm_campaign' => $request->validated('utm_campaign'),
                'landing_page' => $request->validated('landing_page'),
                'status' => 'new',
                'priority' => 'normal',
            ]);

            Log::info('New lead captured', [
                'lead_id' => $lead->id,
                'source' => $lead->source,
                'email' => $lead->email,
            ]);

            // Queue notification emails (don't fail if email sending fails)
            try {
                SendLeadNotification::dispatch($lead);

                // Queue welcome email to lead (if auto-responder enabled)
                if (Setting::get('auto_responder_enabled', true)) {
                    SendWelcomeEmail::dispatch($lead);
                }
            } catch (\Exception $e) {
                // Log but don't fail - lead was already created
                Log::warning('Failed to queue notification emails', [
                    'lead_id' => $lead->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Thank you for contacting us. We will get back to you within 24 hours.',
            ], 201);

        } catch (\Exception $e) {
            Log::error('Contact form submission failed', [
                'error' => $e->getMessage(),
                'request' => $request->except(['password', 'token']),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again later.',
            ], 500);
        }
    }
}
