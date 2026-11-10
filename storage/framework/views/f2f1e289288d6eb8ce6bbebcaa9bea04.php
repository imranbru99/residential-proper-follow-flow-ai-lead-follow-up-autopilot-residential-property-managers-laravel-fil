<?php $__env->startSection('title', 'Modules — ResidentialProperFollowFlow ON/OFF Feature Flags'); ?>

<?php $__env->startSection('content'); ?>
<section class="wrap section">
    <p class="eyebrow">Modular architecture</p>
    <h1>Every module — toggle what your shop needs</h1>
    <p class="lead">ResidentialProperFollowFlow ships with a full module catalog. Core follow-up autopilot modules start ON; niche and integration modules start OFF.</p>

    <div class="grid grid-2" style="margin-top:1.5rem">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="card">
                <span class="pill <?php echo e($module['enabled'] ? '' : 'off'); ?>"><?php echo e($module['enabled'] ? 'ON' : 'OFF'); ?></span>
                <h3 style="margin-top:.55rem"><?php echo e($module['name']); ?></h3>
                <p class="muted"><?php echo e($module['description']); ?></p>
                <p class="muted" style="font-size:.85rem;margin-top:.4rem">Category: <?php echo e($module['category']); ?> · key: <?php echo e($module['key']); ?></p>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.marketing', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/html/resources/views/marketing/modules.blade.php ENDPATH**/ ?>