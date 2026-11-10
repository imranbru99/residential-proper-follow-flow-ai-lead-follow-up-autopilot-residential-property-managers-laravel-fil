<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('staff')->after('email');
            $table->string('phone')->nullable()->after('role');
            $table->boolean('is_active')->default(true)->after('phone');
        });

        Schema::create('module_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->default('core');
            $table->boolean('enabled')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            $table->string('zip', 20)->nullable();
            $table->unsignedInteger('unit_count')->default(1);
            $table->string('property_type')->default('multifamily');
            $table->string('manager_name')->nullable();
            $table->text('notes')->nullable();
            $table->string('slug')->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('residents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('unit_number')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            $table->string('zip', 20)->nullable();
            $table->string('resident_type')->default('prospect');
            $table->text('notes')->nullable();
            $table->string('slug')->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->enum('source', ['website', 'phone', 'email', 'sms', 'referral'])->default('website');
            $table->string('contact_name');
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('subject')->nullable();
            $table->text('body');
            $table->string('service_interest')->nullable();
            $table->string('ai_category')->nullable();
            $table->string('ai_priority')->nullable();
            $table->unsignedTinyInteger('ai_score')->nullable();
            $table->text('ai_summary')->nullable();
            $table->json('ai_tags')->nullable();
            $table->enum('status', [
                'new',
                'classified',
                'pending_approval',
                'in_follow_up',
                'awaiting_reply',
                'escalated',
                'approved',
                'rejected',
                'converted',
                'closed',
            ])->default('new');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('resident_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('consent_sms')->default(false);
            $table->boolean('consent_email')->default(false);
            $table->timestamp('consent_captured_at')->nullable();
            $table->string('consent_source')->nullable();
            $table->unsignedTinyInteger('follow_up_step')->default(0);
            $table->timestamp('next_follow_up_at')->nullable();
            $table->timestamp('last_contacted_at')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('classified_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('follow_up_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('channel_preference')->default('email');
            $table->json('steps');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inquiry_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('resident_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('stage')->default('qualified');
            $table->decimal('value_estimate', 12, 2)->nullable();
            $table->unsignedTinyInteger('probability')->default(50);
            $table->enum('status', ['open', 'won', 'lost', 'on_hold'])->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('opportunity_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('resident_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->string('resident_name');
            $table->string('address')->nullable();
            $table->string('service_type')->nullable();
            $table->string('priority')->default('normal');
            $table->enum('status', ['intake', 'scheduled', 'in_progress', 'completed', 'cancelled'])->default('intake');
            $table->timestamp('scheduled_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('license_number')->nullable();
            $table->json('specialties')->nullable();
            $table->string('availability_status')->default('available');
            $table->string('service_area')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('vendor_work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('work_order_number')->unique();
            $table->foreignId('maintenance_request_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['draft', 'dispatched', 'on_site', 'completed', 'cancelled'])->default('draft');
            $table->timestamp('scheduled_start')->nullable();
            $table->timestamp('scheduled_end')->nullable();
            $table->text('completion_notes')->nullable();
            $table->json('safety_checklist')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_classifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inquiry_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->default('local-rules');
            $table->string('model')->default('residential-followup-v1');
            $table->text('prompt')->nullable();
            $table->json('response')->nullable();
            $table->unsignedTinyInteger('confidence')->nullable();
            $table->timestamps();
        });

        Schema::create('follow_up_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inquiry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sequence_id')->nullable()->constrained('follow_up_sequences')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('step_number')->default(1);
            $table->string('channel')->default('email');
            $table->string('subject')->nullable();
            $table->text('body');
            $table->boolean('consent_verified')->default(false);
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'sent', 'blocked_no_consent', 'rejected'])->default('draft');
            $table->timestamp('scheduled_for')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('lead_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inquiry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('follow_up_draft_id')->nullable()->constrained('follow_up_drafts')->nullOnDelete();
            $table->string('channel')->default('email');
            $table->text('body');
            $table->boolean('is_qualified')->default(false);
            $table->unsignedTinyInteger('ai_interest_score')->nullable();
            $table->json('ai_signals')->nullable();
            $table->enum('status', ['received', 'analyzed', 'escalated', 'dismissed'])->default('received');
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });

        Schema::create('escalations', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('inquiry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_reply_id')->nullable()->constrained('lead_replies')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason');
            $table->string('priority')->default('high');
            $table->enum('status', ['open', 'acknowledged', 'resolved', 'cancelled'])->default('open');
            $table->timestamp('sla_due_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escalations');
        Schema::dropIfExists('lead_replies');
        Schema::dropIfExists('follow_up_drafts');
        Schema::dropIfExists('ai_classifications');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('vendor_work_orders');
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('maintenance_requests');
        Schema::dropIfExists('opportunities');
        Schema::dropIfExists('follow_up_sequences');
        Schema::dropIfExists('inquiries');
        Schema::dropIfExists('residents');
        Schema::dropIfExists('properties');
        Schema::dropIfExists('module_settings');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'is_active']);
        });
    }
};
