<?php

namespace Database\Seeders;

use App\Models\FollowUpSequence;
use App\Models\Inquiry;
use App\Models\MaintenanceRequest;
use App\Models\Opportunity;
use App\Models\Property;
use App\Models\Resident;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorWorkOrder;
use App\Services\FollowUpWorkflowService;
use App\Services\ModuleManager;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app(ModuleManager::class)->syncFromConfig();

        FollowUpSequence::query()->updateOrCreate(
            ['slug' => 'residential-standard-cadence'],
            [
                'name' => 'Residential Leasing Cadence',
                'channel_preference' => 'email',
                'is_active' => true,
                'description' => 'Day-0 thank you, Day-2 tour nudge, Day-5 final follow-up for residential property leads.',
                'steps' => [
                    [
                        'day' => 0,
                        'channel' => 'email',
                        'template' => 'Thanks for contacting our property management team — we received your inquiry and a leasing coordinator will confirm next steps.',
                    ],
                    [
                        'day' => 2,
                        'channel' => 'email',
                        'template' => 'Quick nudge: we still have tour / unit openings this week. Reply YES to lock a showing or maintenance window.',
                    ],
                    [
                        'day' => 5,
                        'channel' => 'sms',
                        'template' => 'Final check-in from our residential desk — want a morning or afternoon slot?',
                    ],
                ],
            ]
        );

        $users = [
            ['name' => 'Admin Demo', 'email' => 'admin@residentialproperfollowflow.test', 'role' => 'admin', 'phone' => '555-0100'],
            ['name' => 'Owner Demo', 'email' => 'owner@residentialproperfollowflow.test', 'role' => 'owner', 'phone' => '555-0101'],
            ['name' => 'Staff Demo', 'email' => 'staff@residentialproperfollowflow.test', 'role' => 'staff', 'phone' => '555-0102'],
            ['name' => 'Tech Demo', 'email' => 'tech@residentialproperfollowflow.test', 'role' => 'technician', 'phone' => '555-0103'],
            ['name' => 'Customer Demo', 'email' => 'customer@residentialproperfollowflow.test', 'role' => 'customer', 'phone' => '555-0104'],
        ];

        $created = [];
        foreach ($users as $user) {
            $created[$user['role']] = User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'phone' => $user['phone'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }

        $oakridge = Property::query()->updateOrCreate(
            ['code' => 'OAK-01'],
            [
                'name' => 'Oakridge Gardens',
                'address' => '1200 Oak Ridge Dr',
                'city' => 'Plano',
                'state' => 'TX',
                'zip' => '75024',
                'unit_count' => 86,
                'property_type' => 'multifamily',
                'manager_name' => 'Owner Demo',
                'notes' => 'Flagship community — leasing + maintenance follow-up pilot',
                'is_active' => true,
            ]
        );

        Property::query()->updateOrCreate(
            ['code' => 'RIV-02'],
            [
                'name' => 'Riverbend Flats',
                'address' => '440 Riverbend Pkwy',
                'city' => 'Austin',
                'state' => 'TX',
                'zip' => '78701',
                'unit_count' => 124,
                'property_type' => 'multifamily',
                'manager_name' => 'Staff Demo',
                'is_active' => true,
            ]
        );

        $techUser = $created['technician'];
        $vendor = Vendor::query()->updateOrCreate(
            ['user_id' => $techUser->id],
            [
                'name' => 'Marcus Facilities Co',
                'license_number' => 'TX-VND-44821',
                'specialties' => ['plumbing', 'hvac', 'emergency_after_hours'],
                'availability_status' => 'available',
                'service_area' => 'Dallas–Fort Worth',
                'phone' => '555-0103',
                'is_active' => true,
            ]
        );

        Vendor::query()->updateOrCreate(
            ['license_number' => 'TX-VND-55210'],
            [
                'name' => 'Elena Unit Turn Pros',
                'specialties' => ['make_ready', 'painting', 'appliance'],
                'availability_status' => 'on_job',
                'service_area' => 'Austin Metro',
                'phone' => '555-0118',
                'is_active' => true,
            ]
        );

        Resident::query()->updateOrCreate(
            ['email' => 'prospect@example.com'],
            [
                'property_id' => $oakridge->id,
                'name' => 'Riley Prospect',
                'phone' => '555-2001',
                'unit_number' => '2B',
                'address' => '1200 Oak Ridge Dr #2B',
                'city' => 'Plano',
                'state' => 'TX',
                'zip' => '75024',
                'resident_type' => 'prospect',
                'notes' => 'Prefers weekend tours; 2-bed interest',
            ]
        );

        if (Inquiry::query()->count() === 0) {
            $workflow = app(FollowUpWorkflowService::class);
            $staff = $created['staff'];
            $owner = $created['owner'];

            $samples = [
                [
                    'source' => 'website',
                    'contact_name' => 'Chris Martinez',
                    'contact_email' => 'chris.m@example.com',
                    'contact_phone' => '555-3001',
                    'subject' => '2-bed tour this week',
                    'service_interest' => 'Leasing tour',
                    'body' => 'Looking for a 2-bedroom at Oakridge Gardens — can we schedule a tour ASAP? Phone 555-3001. Ready to apply if the unit fits.',
                    'consent_email' => true,
                    'consent_sms' => true,
                ],
                [
                    'source' => 'phone',
                    'contact_name' => 'Dana Lee',
                    'contact_email' => 'dana.lee@example.com',
                    'contact_phone' => '555-3002',
                    'subject' => 'Unit 4A kitchen leak — urgent',
                    'service_interest' => 'Emergency maintenance',
                    'body' => 'Urgent: kitchen sink leak flooding unit 4A. Need emergency maintenance and vendor dispatch this week.',
                    'consent_email' => true,
                    'consent_sms' => false,
                ],
                [
                    'source' => 'email',
                    'contact_name' => 'Sam Patel',
                    'contact_email' => 'sam.patel@example.com',
                    'contact_phone' => '555-3003',
                    'subject' => 'Lease renewal question',
                    'service_interest' => 'Renewal',
                    'body' => 'Want to discuss lease renewal options and parking amenities for our current unit. Prefer email follow-up.',
                    'consent_email' => false,
                    'consent_sms' => false,
                ],
            ];

            foreach ($samples as $i => $sample) {
                $inquiry = $workflow->intake($sample, $staff);
                if ($i === 0) {
                    $draft = $workflow->approveAndStartFollowUp($inquiry, $owner);
                    if ($draft->status !== 'blocked_no_consent') {
                        $workflow->approveSend($draft, $owner);
                        $workflow->ingestReply($inquiry->fresh(), [
                            'body' => 'Yes please schedule a tour tomorrow — we are interested and ready to apply.',
                            'channel' => 'email',
                        ], $staff);
                    }
                } elseif ($i === 1) {
                    $workflow->approveAndStartFollowUp($inquiry, $owner);
                }
            }
        }

        $mr = MaintenanceRequest::query()->first();
        if (! $mr) {
            $resident = Resident::query()->first();
            $mr = MaintenanceRequest::query()->create([
                'resident_id' => $resident?->id,
                'property_id' => $oakridge->id,
                'resident_name' => $resident?->name ?? 'Demo Resident',
                'address' => '1200 Oak Ridge Dr #4A',
                'service_type' => 'maintenance_emergency',
                'priority' => 'urgent',
                'status' => 'scheduled',
                'scheduled_at' => now()->addDay()->setTime(10, 0),
                'notes' => 'Seeded demo maintenance request for vendor work order.',
            ]);
        }

        VendorWorkOrder::query()->updateOrCreate(
            ['work_order_number' => 'VWO-RPFF0001'],
            [
                'maintenance_request_id' => $mr->id,
                'vendor_id' => $vendor->id,
                'status' => 'dispatched',
                'scheduled_start' => now()->addDay()->setTime(9, 0),
                'scheduled_end' => now()->addDay()->setTime(12, 0),
                'safety_checklist' => ['PPE on site', 'Water shutoff verified', 'Resident notice posted'],
            ]
        );

        if (Opportunity::query()->count() === 0) {
            Opportunity::query()->create([
                'title' => 'Demo residential follow-up pipeline opportunity',
                'stage' => 'qualified',
                'value_estimate' => 1800,
                'probability' => 60,
                'status' => 'open',
                'owner_id' => $created['owner']->id,
                'resident_id' => Resident::query()->value('id'),
            ]);
        }
    }
}
