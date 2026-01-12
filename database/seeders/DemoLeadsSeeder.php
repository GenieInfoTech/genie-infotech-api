<?php

namespace Database\Seeders;

use App\Models\Lead;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoLeadsSeeder extends Seeder
{
    /**
     * Create demo leads for dashboard demonstration.
     */
    public function run(): void
    {
        $leads = [
            // Recent leads (today and yesterday)
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah.johnson@techcorp.com',
                'phone' => '+1 (555) 123-4567',
                'company' => 'TechCorp Solutions',
                'service_interest' => 'Mobile App Development',
                'description' => 'Looking for a Flutter development team to build our e-commerce app. Budget around $50k.',
                'source' => 'contact_form',
                'status' => 'new',
                'priority' => 'high',
                'utm_campaign' => 'google_ads_mobile',
                'created_at' => now()->subHours(2),
            ],
            [
                'name' => 'Michael Chen',
                'email' => 'mchen@startupxyz.io',
                'phone' => '+1 (555) 234-5678',
                'company' => 'StartupXYZ',
                'service_interest' => 'Web Development',
                'description' => 'Need a React/Next.js frontend team for our SaaS platform.',
                'source' => 'exit_intent',
                'status' => 'new',
                'priority' => 'urgent',
                'created_at' => now()->subHours(5),
            ],
            [
                'name' => 'Emma Williams',
                'email' => 'emma@designstudio.dk',
                'phone' => '+45 20 123 456',
                'company' => 'Design Studio Copenhagen',
                'service_interest' => 'UI/UX Design',
                'description' => 'Looking for dedicated UI/UX team for long-term engagement.',
                'source' => 'referral',
                'status' => 'contacted',
                'priority' => 'high',
                'utm_campaign' => 'partner_referral',
                'last_contacted_at' => now()->subHours(1),
                'created_at' => now()->subDay(),
            ],

            // This week leads
            [
                'name' => 'James Wilson',
                'email' => 'j.wilson@enterprise.com',
                'phone' => '+1 (555) 345-6789',
                'company' => 'Enterprise Solutions Inc.',
                'service_interest' => 'Enterprise Software',
                'description' => 'Custom ERP development project. Looking for Laravel expertise.',
                'source' => 'organic',
                'status' => 'qualified',
                'priority' => 'normal',
                'last_contacted_at' => now()->subDays(1),
                'created_at' => now()->subDays(2),
            ],
            [
                'name' => 'Lisa Anderson',
                'email' => 'lisa@healthcare.ae',
                'phone' => '+971 50 123 4567',
                'company' => 'Healthcare Plus UAE',
                'service_interest' => 'Mobile App Development',
                'description' => 'Healthcare app for patient management. HIPAA compliance required.',
                'source' => 'paid',
                'status' => 'proposal',
                'priority' => 'high',
                'utm_campaign' => 'linkedin_healthcare',
                'last_contacted_at' => now()->subDays(2),
                'created_at' => now()->subDays(3),
            ],
            [
                'name' => 'Robert Taylor',
                'email' => 'rtaylor@fintech.uk',
                'phone' => '+44 20 7123 4567',
                'company' => 'FinTech London',
                'service_interest' => 'Web Development',
                'description' => 'Fintech platform development. Security is top priority.',
                'source' => 'contact_form',
                'status' => 'negotiation',
                'priority' => 'urgent',
                'utm_campaign' => 'google_ads_fintech',
                'last_contacted_at' => now()->subDays(1),
                'created_at' => now()->subDays(5),
            ],

            // Last week leads
            [
                'name' => 'David Miller',
                'email' => 'david@retailco.au',
                'phone' => '+61 2 1234 5678',
                'company' => 'RetailCo Australia',
                'service_interest' => 'E-commerce Development',
                'description' => 'Shopify Plus customization and app development.',
                'source' => 'social',
                'status' => 'converted',
                'priority' => 'normal',
                'last_contacted_at' => now()->subDays(5),
                'converted_at' => now()->subDays(3),
                'created_at' => now()->subDays(8),
            ],
            [
                'name' => 'Jennifer Brown',
                'email' => 'jennifer@edtech.de',
                'phone' => '+49 30 12345678',
                'company' => 'EdTech Germany',
                'service_interest' => 'Web Development',
                'description' => 'Learning management system development.',
                'source' => 'contact_form',
                'status' => 'converted',
                'priority' => 'normal',
                'utm_campaign' => 'facebook_education',
                'last_contacted_at' => now()->subDays(7),
                'converted_at' => now()->subDays(4),
                'created_at' => now()->subDays(10),
            ],
            [
                'name' => 'Mark Thompson',
                'email' => 'mark@logistics.us',
                'phone' => '+1 (555) 456-7890',
                'company' => 'Logistics Pro USA',
                'service_interest' => 'Enterprise Software',
                'description' => 'Fleet management system needed.',
                'source' => 'exit_intent',
                'status' => 'lost',
                'priority' => 'normal',
                'lost_reason' => 'Budget constraints',
                'last_contacted_at' => now()->subDays(8),
                'created_at' => now()->subDays(12),
            ],

            // Older leads for historical data
            [
                'name' => 'Amanda Davis',
                'email' => 'amanda@travel.sg',
                'phone' => '+65 6123 4567',
                'company' => 'Travel Singapore',
                'service_interest' => 'Mobile App Development',
                'description' => 'Travel booking app development.',
                'source' => 'referral',
                'status' => 'converted',
                'priority' => 'normal',
                'converted_at' => now()->subDays(15),
                'created_at' => now()->subDays(20),
            ],
            [
                'name' => 'Chris Martinez',
                'email' => 'chris@realestate.ca',
                'phone' => '+1 (416) 123-4567',
                'company' => 'Real Estate Canada',
                'service_interest' => 'Web Development',
                'description' => 'Property listing website.',
                'source' => 'organic',
                'status' => 'converted',
                'priority' => 'normal',
                'utm_campaign' => 'seo_realestate',
                'converted_at' => now()->subDays(18),
                'created_at' => now()->subDays(25),
            ],
            [
                'name' => 'Nicole Lee',
                'email' => 'nicole@fashion.jp',
                'phone' => '+81 3 1234 5678',
                'company' => 'Fashion Tokyo',
                'service_interest' => 'E-commerce Development',
                'description' => 'Fashion e-commerce platform.',
                'source' => 'paid',
                'status' => 'lost',
                'priority' => 'normal',
                'lost_reason' => 'Chose competitor',
                'utm_campaign' => 'instagram_fashion',
                'created_at' => now()->subDays(28),
            ],

            // More variety for stats
            [
                'name' => 'Peter Jackson',
                'email' => 'peter@food.nz',
                'company' => 'FoodDelivery NZ',
                'service_interest' => 'Mobile App Development',
                'description' => 'Food delivery app similar to UberEats.',
                'source' => 'contact_form',
                'status' => 'contacted',
                'priority' => 'normal',
                'created_at' => now()->subDays(4),
            ],
            [
                'name' => 'Rachel Green',
                'email' => 'rachel@beauty.fr',
                'company' => 'Beauty Paris',
                'service_interest' => 'UI/UX Design',
                'description' => 'Rebranding and app redesign.',
                'source' => 'social',
                'status' => 'qualified',
                'priority' => 'high',
                'utm_campaign' => 'pinterest_beauty',
                'created_at' => now()->subDays(6),
            ],
            [
                'name' => 'Tom Harris',
                'email' => 'tom@sports.us',
                'company' => 'SportsTech USA',
                'service_interest' => 'Web Development',
                'description' => 'Sports analytics dashboard.',
                'source' => 'exit_intent',
                'status' => 'new',
                'priority' => 'normal',
                'created_at' => now()->subHours(8),
            ],
        ];

        foreach ($leads as $leadData) {
            Lead::create($leadData);
        }

        $this->command->info('Created ' . count($leads) . ' demo leads successfully!');
    }
}
