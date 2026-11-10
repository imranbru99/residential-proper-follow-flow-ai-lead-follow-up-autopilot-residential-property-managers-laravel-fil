<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ResidentialProperFollowFlow Feature Modules
    |--------------------------------------------------------------------------
    | Toggle modules per residential property management needs. Core follow-up
    | autopilot modules default ON; niche / integration modules default OFF.
    */

    'definitions' => [
        'lead_follow_up_autopilot' => [
            'name' => 'Lead Follow-Up Autopilot',
            'description' => 'Generate and schedule multi-touch follow-ups for leasing and resident leads with cadence control.',
            'category' => 'core',
            'enabled' => true,
        ],
        'messaging_consent' => [
            'name' => 'Messaging Consent Guard',
            'description' => 'Block SMS/email sends unless TCPA-aware consent flags are present and audited (fair-housing safe messaging).',
            'category' => 'compliance',
            'enabled' => true,
        ],
        'ai_follow_up_writer' => [
            'name' => 'AI Follow-Up Writer',
            'description' => 'Draft consent-aware email/SMS follow-ups tailored to tours, applications, renewals, and maintenance.',
            'category' => 'ai',
            'enabled' => true,
        ],
        'human_approval_workflow' => [
            'name' => 'Human Approval Workflow',
            'description' => 'Require staff approval before any automated follow-up is marked sent.',
            'category' => 'core',
            'enabled' => true,
        ],
        'reply_escalation' => [
            'name' => 'Qualified Reply Escalation',
            'description' => 'Detect leasing/maintenance buying-signal replies and escalate to owners/staff with SLA.',
            'category' => 'sales',
            'enabled' => true,
        ],
        'follow_up_sequences' => [
            'name' => 'Follow-Up Sequences',
            'description' => 'Day-0 / Day-2 / Day-5 cadence templates for tours, applications, and stalled prospects.',
            'category' => 'sales',
            'enabled' => true,
        ],
        'inbound_lead_intake' => [
            'name' => 'Inbound Lead Intake',
            'description' => 'Capture website, phone, email, and referral inquiries into the follow-up queue.',
            'category' => 'core',
            'enabled' => true,
        ],
        'ai_classification' => [
            'name' => 'AI Lead Classification',
            'description' => 'Classify leads by leasing vs maintenance category, urgency, and qualification score.',
            'category' => 'ai',
            'enabled' => true,
        ],
        'crm_opportunities' => [
            'name' => 'CRM Opportunities',
            'description' => 'Promote escalated qualified leads into open leasing/ops opportunities.',
            'category' => 'sales',
            'enabled' => true,
        ],
        'properties' => [
            'name' => 'Properties',
            'description' => 'Manage multifamily and residential communities linked to residents and work orders.',
            'category' => 'operations',
            'enabled' => true,
        ],
        'residents' => [
            'name' => 'Residents & Prospects',
            'description' => 'Track prospects, applicants, and residents with unit and consent context.',
            'category' => 'operations',
            'enabled' => true,
        ],
        'maintenance_requests' => [
            'name' => 'Maintenance Requests',
            'description' => 'Convert hot replies into resident maintenance requests.',
            'category' => 'operations',
            'enabled' => true,
        ],
        'vendors' => [
            'name' => 'Vendors',
            'description' => 'Manage licensed vendors, specialties, and availability for property work.',
            'category' => 'operations',
            'enabled' => true,
        ],
        'vendor_work_orders' => [
            'name' => 'Vendor Work Orders',
            'description' => 'Dispatch vendor work orders with safety checklists after escalation.',
            'category' => 'operations',
            'enabled' => true,
        ],
        'audit_log' => [
            'name' => 'Audit Log',
            'description' => 'Immutable trail of draft, approve, send, escalate, and consent events.',
            'category' => 'compliance',
            'enabled' => true,
        ],
        'slug_engine' => [
            'name' => 'Universal Slug Engine',
            'description' => 'Generate URL-safe slugs for properties, residents, and public lead pages (local laravel-universal-slug equivalent).',
            'category' => 'platform',
            'enabled' => true,
        ],
        'customer_portal' => [
            'name' => 'Resident Portal',
            'description' => 'Let residents review consent preferences and open maintenance tickets (optional).',
            'category' => 'portal',
            'enabled' => false,
        ],
        'attendance_hub' => [
            'name' => 'Attendance Hub',
            'description' => 'Staff / vendor clock-in attendance machine integration (local laravel-attendance-hub equivalent).',
            'category' => 'hr',
            'enabled' => false,
        ],
        'unicode_pdf_exports' => [
            'name' => 'Unicode PDF Exports',
            'description' => 'Export follow-up and escalation reports as PDF (local laravel-unicode-pdf equivalent).',
            'category' => 'documents',
            'enabled' => false,
        ],
        'ai_catalog_scrape' => [
            'name' => 'AI Catalog / Lead Enrichment',
            'description' => 'Enrich leads from online listing sources (local laravel-ai-catalog equivalent).',
            'category' => 'ai',
            'enabled' => false,
        ],
        'ai_hub' => [
            'name' => 'AI Hub Provider Switch',
            'description' => 'Route follow-up drafting through pluggable AI providers (local laravel-ai-hub equivalent).',
            'category' => 'ai',
            'enabled' => true,
        ],
        'drutogoti_speed' => [
            'name' => 'Drutogoti Speed Layer',
            'description' => 'Caching and queue acceleration helpers (local drutogoti equivalent).',
            'category' => 'platform',
            'enabled' => true,
        ],
        'admin_panel_extras' => [
            'name' => 'Admin Panel Extras',
            'description' => 'Extended Filament admin dashboard widgets (local laravel-admin-panel equivalent).',
            'category' => 'platform',
            'enabled' => true,
        ],
        'integrations_appfolio' => [
            'name' => 'AppFolio Integration',
            'description' => 'Sync residents, work orders, and follow-ups with AppFolio.',
            'category' => 'integrations',
            'enabled' => false,
        ],
        'integrations_buildium' => [
            'name' => 'Buildium Integration',
            'description' => 'Push qualified follow-ups and maintenance into Buildium.',
            'category' => 'integrations',
            'enabled' => false,
        ],
        'integrations_email' => [
            'name' => 'Email Integration',
            'description' => 'Connect property management email inboxes for reply capture.',
            'category' => 'integrations',
            'enabled' => false,
        ],
        'integrations_accounting' => [
            'name' => 'Accounting Integration',
            'description' => 'Link won leasing opportunities to accounting customers.',
            'category' => 'integrations',
            'enabled' => false,
        ],
        'multi_location' => [
            'name' => 'Multi-Property Portfolio',
            'description' => 'Route follow-ups and escalations by community / portfolio.',
            'category' => 'operations',
            'enabled' => false,
        ],
        'quiet_hours' => [
            'name' => 'Quiet Hours Enforcement',
            'description' => 'Suppress outbound SMS/email outside property-manager-defined quiet windows.',
            'category' => 'compliance',
            'enabled' => true,
        ],
        'fair_housing_guard' => [
            'name' => 'Fair Housing Guard',
            'description' => 'Flag follow-up copy that may raise fair-housing or tenant-privacy risk for human review.',
            'category' => 'compliance',
            'enabled' => true,
        ],
    ],
];
