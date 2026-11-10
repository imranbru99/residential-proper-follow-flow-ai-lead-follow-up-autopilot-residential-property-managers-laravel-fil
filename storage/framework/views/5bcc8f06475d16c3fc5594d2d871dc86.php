<?php $__env->startSection('title', 'Live Demo — ResidentialProperFollowFlow Lead Intake'); ?>

<?php $__env->startSection('content'); ?>
<section class="wrap section">
    <p class="eyebrow">Interactive demo</p>
    <h1>Submit a residential property lead</h1>
    <p class="lead">Watch AI classify and queue a consent-aware follow-up draft. Then log into Filament to approve, send, and escalate replies.</p>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="flash"><?php echo e(session('success')); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="grid grid-2" style="margin-top:1rem">
        <form class="card" method="post" action="<?php echo e(route('demo.intake')); ?>">
            <?php echo csrf_field(); ?>
            <label>Name</label>
            <input name="contact_name" value="<?php echo e(old('contact_name')); ?>" required>
            <label>Email</label>
            <input type="email" name="contact_email" value="<?php echo e(old('contact_email')); ?>">
            <label>Phone</label>
            <input name="contact_phone" value="<?php echo e(old('contact_phone')); ?>">
            <label>Subject</label>
            <input name="subject" value="<?php echo e(old('subject', '2-bed tour this week')); ?>">
            <label>Interest</label>
            <input name="service_interest" value="<?php echo e(old('service_interest', 'Leasing tour')); ?>">
            <label>Message</label>
            <textarea name="body" rows="5" required><?php echo e(old('body', 'Looking for a 2-bedroom at Oakridge Gardens — can we schedule a tour ASAP? Ready to apply if the unit fits.')); ?></textarea>
            <label><input type="checkbox" name="consent_email" value="1" checked> Email consent</label>
            <label><input type="checkbox" name="consent_sms" value="1"> SMS consent</label>
            <div class="cta-row">
                <button class="btn btn-accent" type="submit">Start follow-up autopilot</button>
            </div>
        </form>
        <div class="card">
            <h3>Demo logins</h3>
            <p class="muted">Password for all users: <strong>password</strong></p>
            <ul class="muted">
                <li>admin@residentialproperfollowflow.test (admin)</li>
                <li>owner@residentialproperfollowflow.test (owner)</li>
                <li>staff@residentialproperfollowflow.test (staff)</li>
                <li>tech@residentialproperfollowflow.test (technician / vendor)</li>
                <li>customer@residentialproperfollowflow.test (customer — no admin)</li>
            </ul>
            <a class="btn btn-primary" href="<?php echo e(url('/admin/login')); ?>">Go to /admin</a>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.marketing', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/marketing/demo.blade.php ENDPATH**/ ?>