<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Voucher Claims') · Mandiri</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body>
<header class="hero">
    <div class="hero-inner">
        <img class="campaign-banner" src="{{ asset('images/banner-mandiri.png') }}" alt="Campaign banner placeholder">
        <div class="hero-topline"><span class="eyebrow">CAMPAIGN OPERATIONS</span><span class="report-chip">VOUCHER REPORT <span>↗</span></span></div>
        <h1>Voucher claims, <span>made simple.</span></h1>
        <p>Livin’ by Mandiri &amp; Visa 4-8 September 2026</p>
        <div class="hero-tags"><span>DISCOVER</span><i>·</i><span>CONNECT</span><i>·</i><span>COLLECT</span></div>
    </div>
</header>
<nav class="nav-wrap"><a class="brand" href="{{ route('dashboard') }}">
    <img class="logo" style="width: 42px; height: 42px;" src="{{ asset('images/logo.png') }}" alt="Campaign banner placeholder">
    <span>Mandiri <small>CLAIM PORTAL</small></span></a><div class="nav-links"><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Overview</a><a href="{{ route('claims.create') }}" class="{{ request()->routeIs('claims.create') ? 'active' : '' }}">New claim</a><a href="{{ route('stores.index') }}" class="{{ request()->routeIs('stores.*') ? 'active' : '' }}">Stores</a></div><a class="button button-dark nav-action" href="{{ route('claims.export', request()->query()) }}">↓ Export Excel</a></nav>
<main class="container">
    @if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert error"><strong>Please check the form:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @yield('content')
</main>
<footer class="footer"><span>Voucher Claim Portal</span><span>Internal campaign operations · {{ now()->format('Y') }}</span></footer>
<script src="{{ asset('js/dashboard.js') }}"></script>
@stack('scripts')
</body></html>
