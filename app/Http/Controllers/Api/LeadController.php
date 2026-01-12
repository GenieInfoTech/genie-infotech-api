<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Jobs\SendLeadNotification;
use App\Jobs\SendWelcomeEmail;
use App\Models\Lead;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeadController extends Controller
{
    /**
     * Store a new lead (public endpoint)
     */
    public function store(ContactRequest $request): JsonResponse
    {
        try {
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

            // Queue notification emails (don't fail if email sending fails)
            try {
                SendLeadNotification::dispatch($lead);

                // Queue welcome email to lead
                if (Setting::get('auto_responder_enabled', true)) {
                    SendWelcomeEmail::dispatch($lead);
                }
            } catch (\Exception $e) {
                // Log but don't fail - lead was already created
                \Illuminate\Support\Facades\Log::warning('Failed to queue notification emails', [
                    'lead_id' => $lead->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Thank you! We will contact you within 24 hours.',
            ], 201);

        } catch (\Exception $e) {
            Log::error('Lead creation failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
            ], 500);
        }
    }

    /**
     * List leads (admin only)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Lead::query();

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('source')) {
            $query->where('source', $request->source);
        }
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            });
        }

        $leads = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json($leads);
    }

    /**
     * Show a single lead (admin only)
     */
    public function show(int $id): JsonResponse
    {
        $lead = Lead::with('emailLogs')->findOrFail($id);
        return response()->json($lead);
    }

    /**
     * Update a lead (admin only)
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);

        $validated = $request->validate([
            'status' => 'sometimes|string|in:' . implode(',', array_keys(Lead::getStatuses())),
            'priority' => 'sometimes|string|in:' . implode(',', array_keys(Lead::getPriorities())),
            'notes' => 'sometimes|nullable|string|max:5000',
            'assigned_to' => 'sometimes|nullable|string|max:255',
        ]);

        // Track status changes
        if (isset($validated['status'])) {
            if ($validated['status'] === 'contacted') {
                $validated['last_contacted_at'] = now();
            }
            if ($validated['status'] === 'converted') {
                $validated['converted_at'] = now();
            }
        }

        $lead->update($validated);

        return response()->json([
            'success' => true,
            'lead' => $lead->fresh(),
        ]);
    }

    /**
     * Delete a lead (admin only)
     */
    public function destroy(int $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        $lead->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lead deleted successfully.',
        ]);
    }

    /**
     * Get lead analytics (admin only)
     */
    public function analytics(): JsonResponse
    {
        $stats = [
            'total' => Lead::count(),
            'new' => Lead::where('status', 'new')->count(),
            'today' => Lead::whereDate('created_at', today())->count(),
            'this_week' => Lead::whereBetween('created_at', [now()->startOfWeek(), now()])->count(),
            'this_month' => Lead::whereMonth('created_at', now()->month)->count(),
            'converted' => Lead::where('status', 'converted')->count(),
            'conversion_rate' => Lead::count() > 0
                ? round((Lead::where('status', 'converted')->count() / Lead::count()) * 100, 2)
                : 0,
            'by_source' => Lead::selectRaw('source, COUNT(*) as count')
                ->groupBy('source')
                ->pluck('count', 'source'),
            'by_status' => Lead::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status'),
        ];

        return response()->json($stats);
    }

    /**
     * Export leads as CSV (admin only)
     */
    public function export(Request $request)
    {
        $leads = Lead::orderBy('created_at', 'desc')->get();

        $filename = 'leads_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($leads) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'ID', 'Name', 'Email', 'Phone', 'Company',
                'Service', 'Status', 'Priority', 'Source',
                'Created At', 'Last Contacted', 'Converted At',
            ]);

            foreach ($leads as $lead) {
                fputcsv($file, [
                    $lead->id,
                    $lead->name,
                    $lead->email,
                    $lead->phone,
                    $lead->company,
                    $lead->service_interest,
                    $lead->status,
                    $lead->priority,
                    $lead->source,
                    $lead->created_at?->toDateTimeString(),
                    $lead->last_contacted_at?->toDateTimeString(),
                    $lead->converted_at?->toDateTimeString(),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
