<?php $__env->startSection('title', 'Workflow — ResidentialProperFollowFlow MVP Autopilot'); ?>

<?php $__env->startSection('content'); ?>
<section class="wrap section">
    <p class="eyebrow">Sales operations workflow</p>
    <h1>1 intake → draft → human approval → audit</h1>
    <p class="lead">MVP scope for residential property managers: generate consent-aware follow-ups, escalate qualified replies, operate on properties, residents, maintenance requests and vendor work orders.</p>

    <div class="grid" style="margin-top:1.5rem; gap:1rem">
        <div class="card"><h3>1. Intake trigger</h3><p class="muted">Website, phone, email, SMS, or referral lead lands with consent flags and raw payload.</p></div>
        <div class="card"><h3>2. AI classify + draft</h3><p class="muted">Category, priority, score — then Day-0 follow-up draft on the consented channel.</p></div>
        <div class="card"><h3>3. Human approval</h3><p class="muted">Owner/staff reviews draft; consent guard and quiet hours can block or defer send.</p></div>
        <div class="card"><h3>4. Send + await reply</h3><p class="muted">Approved messages mark sent; lead moves to awaiting_reply with next cadence stamp.</p></div>
        <div class="card"><h3>5. Escalate qualified replies</h3><p class="muted">Buying signals open an escalation with SLA; convert to CRM opportunity + maintenance request.</p></div>
        <div class="card"><h3>6. Vendor dispatch</h3><p class="muted">Vendors and work orders carry safety checklists after escalation conversion.</p></div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.marketing', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/marketing/workflow.blade.php ENDPATH**/ ?>