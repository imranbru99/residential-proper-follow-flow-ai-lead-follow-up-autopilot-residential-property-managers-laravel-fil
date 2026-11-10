<?php $__env->startSection('title', 'ResidentialProperFollowFlow — AI Lead Follow-Up Autopilot for Residential Property Managers'); ?>
<?php $__env->startSection('meta_description', 'Generate consent-aware follow-ups, escalate qualified replies to staff, and operate on properties, residents, maintenance requests and vendor work orders. Laravel Filament SaaS.'); ?>

<?php $__env->startSection('content'); ?>
<section class="wrap hero">
    <div>
        <p class="eyebrow">Residential Property Managers · Sales Operations</p>
        <h1>ResidentialProperFollowFlow</h1>
        <p class="lead">Consent-aware lead follow-up autopilot for residential property managers — draft, approve, and send multi-touch sequences, then escalate qualified replies to staff across properties, residents, maintenance requests, and vendor work orders.</p>
        <div class="cta-row">
            <a class="btn btn-accent" href="<?php echo e(route('demo')); ?>">Try live lead intake</a>
            <a class="btn btn-ghost" href="<?php echo e(url('/admin')); ?>">Open Filament admin</a>
            <a class="btn btn-ghost" href="<?php echo e(route('features')); ?>">Explore all features</a>
        </div>
    </div>
    <div class="panel">
        <small>Follow-up autopilot MVP</small>
        <div class="score">99</div>
        <p style="margin:0 0 1rem; opacity:.9">Opportunity score · Lead Follow-Up Autopilot</p>
        <div class="grid" style="gap:.55rem">
            <div>1) Intake trigger → lead + consent flags</div>
            <div>2) AI draft follow-up sequence</div>
            <div>3) Human approval before send</div>
            <div>4) Escalate qualified replies → audit log</div>
        </div>
    </div>
</section>

<section class="wrap section">
    <h2>Stop ghosting leasing & resident leads after the first touch</h2>
    <p class="muted" style="max-width:46rem">ResidentialProperFollowFlow keeps tours, applications, renewals, and maintenance emergencies in a consent-checked cadence. Quiet hours, fair-housing aware messaging guards, and escalation SLAs keep owners and ops leads in control.</p>
    <div class="grid grid-3" style="margin-top:1.25rem">
        <div class="card">
            <h3>Consent-first sends</h3>
            <p class="muted">Email/SMS blocked unless consent is verified — every attempt audited for tenant privacy.</p>
        </div>
        <div class="card">
            <h3>AI follow-up writer</h3>
            <p class="muted">Day-0 / Day-2 / Day-5 templates tailored to leasing vs maintenance category and urgency.</p>
        </div>
        <div class="card">
            <h3>Reply escalation</h3>
            <p class="muted">Qualified replies escalate to staff with SLA, CRM conversion, and vendor work orders.</p>
        </div>
    </div>
</section>

<section class="wrap section">
    <h2>Core modules enabled for demo</h2>
    <div class="grid grid-2">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = collect($modules)->where('enabled', true)->take(8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="card">
                <span class="pill">ON</span>
                <h3 style="margin-top:.55rem"><?php echo e($module['name']); ?></h3>
                <p class="muted"><?php echo e($module['description']); ?></p>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <div class="cta-row">
        <a class="btn btn-primary" href="<?php echo e(route('modules')); ?>">View every module (on & off)</a>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.marketing', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/marketing/home.blade.php ENDPATH**/ ?>