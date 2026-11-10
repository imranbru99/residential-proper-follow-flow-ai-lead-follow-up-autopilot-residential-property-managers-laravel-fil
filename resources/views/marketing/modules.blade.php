@extends('layouts.marketing')

@section('title', 'Modules — ResidentialProperFollowFlow ON/OFF Feature Flags')

@section('content')
<section class="wrap section">
    <p class="eyebrow">Modular architecture</p>
    <h1>Every module — toggle what your shop needs</h1>
    <p class="lead">ResidentialProperFollowFlow ships with a full module catalog. Core follow-up autopilot modules start ON; niche and integration modules start OFF.</p>

    <div class="grid grid-2" style="margin-top:1.5rem">
        @foreach($modules as $module)
            <div class="card">
                <span class="pill {{ $module['enabled'] ? '' : 'off' }}">{{ $module['enabled'] ? 'ON' : 'OFF' }}</span>
                <h3 style="margin-top:.55rem">{{ $module['name'] }}</h3>
                <p class="muted">{{ $module['description'] }}</p>
                <p class="muted" style="font-size:.85rem;margin-top:.4rem">Category: {{ $module['category'] }} · key: {{ $module['key'] }}</p>
            </div>
        @endforeach
    </div>
</section>
@endsection
