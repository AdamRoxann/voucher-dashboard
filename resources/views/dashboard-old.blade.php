
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    $isAdmin = auth()->check()
        && (bool) auth()->user()->is_admin;
@endphp

<div class="page-heading">
    <div>
        <span class="eyebrow blue">OVERVIEW</span>
        <h2>Your campaign at a glance</h2>
        <p class="muted">
            Track claims, voucher codes and transfer progress in one place.
        </p>
    </div>

    <a href="{{ route('claims.create') }}" class="button button-primary">
        ＋ Create a claim
    </a>
</div>

{{-- Filters --}}
<form class="filter-card" method="GET" action="{{ route('dashboard') }}">
    <div class="filter-title">
        <div>
            <h3>Find a claim</h3>
            <p class="muted">
                Narrow down the dashboard and export only what you need.
            </p>
        </div>

        <a href="{{ route('dashboard') }}" class="text-link">
            Reset filters ↺
        </a>
    </div>

    <div class="filter-grid">
        <label>
            From date
            <input
                type="date"
                name="from"
                value="{{ request('from') }}"
            >
        </label>

        <label>
            To date
            <input
                type="date"
                name="to"
                value="{{ request('to') }}"
            >
        </label>

        <label>
            Store
            <select name="store_id">
                <option value="">All stores</option>
                @foreach($stores as $store)
                    <option
                        value="{{ $store->id }}"
                        @selected((string) request('store_id') === (string) $store->id)
                    >
                        {{ $store->name }}
                    </option>
                @endforeach
            </select>
        </label>

        <label>
            Voucher type
            <select name="voucher_type">
                <option value="">All types</option>
                @foreach(['A', 'B'] as $type)
                    <option
                        value="{{ $type }}"
                        @selected(request('voucher_type') === $type)
                    >
                        Voucher {{ $type }}
                    </option>
                @endforeach
            </select>
        </label>

        <label class="search-field">
            Search store, PIC or code
            <input
                name="search"
                value="{{ request('search') }}"
                placeholder="e.g. BEIGE STUDIO or A006"
            >
        </label>

        <button class="button button-dark" type="submit">
            Apply filters
        </button>
    </div>
</form>

{{-- Statistics --}}
<div class="stat-grid">
    <article class="stat-card stat-primary">
        <div class="stat-label">
            TOTAL VOUCHER CODES <span>↗</span>
        </div>
        <strong>{{ number_format($stats['voucher_codes']) }}</strong>
        <small>Within current filters</small>
        <div class="stat-decoration">A</div>
    </article>

    <article class="stat-card">
        <div class="stat-label">
            ACTIVE STORES <span>⌂</span>
        </div>
        <strong>{{ number_format($stats['active_stores']) }}</strong>
        <small>Stores available for claims</small>
        <div class="stat-line orange"></div>
    </article>

    <article class="stat-card">
        <div class="stat-label">
            VOUCHER TYPES <span>◈</span>
        </div>
        <strong>
            {{ number_format($stats['voucher_types']) }}<em>/ 3</em>
        </strong>
        <small>Types represented in results</small>
        <div class="stat-line green"></div>
    </article>

    <article class="stat-card">
        <div class="stat-label">
            CLAIM DAYS <span>▦</span>
        </div>
        <strong>{{ number_format($stats['claim_days']) }}</strong>
        <small>Distinct dates with claims</small>
        <div class="stat-line yellow"></div>
    </article>
</div>

{{-- Charts --}}
<div class="insight-grid">
    <section class="panel">
        <div class="panel-heading">
            <div>
                <h3>Daily claim activity</h3>
                <p class="muted">Claim submissions by date</p>
            </div>
            <span class="panel-icon">↗</span>
        </div>

        @php
            $maxDaily = max(1, (int) $daily->max('total'));
        @endphp

        <div class="bar-chart">
            @forelse($daily as $day)
                <div class="bar-row">
                    <span class="bar-date">
                        {{ $day->claim_date->format('d M') }}
                    </span>

                    <div class="bar-track">
                        <div
                            class="bar-fill"
                            style="width: {{ max(4, ($day->total / $maxDaily) * 100) }}%"
                        ></div>
                    </div>

                    <strong>{{ $day->total }}</strong>
                </div>
            @empty
                <p class="empty-state">
                    No claims for the selected period.
                </p>
            @endforelse
        </div>
    </section>

    <section class="panel">
        <div class="panel-heading">
            <div>
                <h3>Voucher mix</h3>
                <p class="muted">Code distribution by type</p>
            </div>
            <span class="panel-icon">◉</span>
        </div>

        @php
            $typeTotal = max(1, (int) $typeCounts->sum());
        @endphp

        <div class="type-list">
            @foreach(['A', 'B'] as $type)
                @php
                    $count = (int) ($typeCounts[$type] ?? 0);
                    $percentage = ($count / $typeTotal) * 100;
                @endphp

                <div class="type-row">
                    <span class="type-letter type-{{ strtolower($type) }}">
                        {{ $type }}
                    </span>

                    <div class="type-info">
                        <strong>Voucher {{ $type }}</strong>

                        <small>
                            {{ round($percentage, 1) }}% of selected codes
                        </small>

                        <div class="mini-track">
                            <div
                                class="mini-fill fill-{{ strtolower($type) }}"
                                style="width: {{ $percentage }}%"
                            ></div>
                        </div>
                    </div>

                    <b>{{ $count }}</b>
                </div>
            @endforeach
        </div>
    </section>
</div>

{{-- Store leaderboard --}}
<section class="panel top-panel">
    <div class="panel-heading">
        <div>
            <h3>Store leaderboard</h3>
            <p class="muted">Stores with the most recorded claims</p>
        </div>

        @if($isAdmin)
            <a href="{{ route('stores.index') }}" class="text-link">
                Manage stores →
            </a>
        @endif
    </div>

    <div class="leader-list">
        @php
            $maxStoreCount = max(1, (int) $topStores->max('voucher_count'));
        @endphp

        @forelse($topStores as $i => $store)
            <div class="leader-row">
                <span class="leader-rank">
                    {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}
                </span>

                <span class="leader-name">{{ $store->name }}</span>

                <div class="leader-track">
                    <div
                        style="width: {{ ($store->voucher_count / $maxStoreCount) * 100 }}%"
                    ></div>
                </div>

                <strong>{{ $store->voucher_count }}</strong>
            </div>
        @empty
            <p class="empty-state">
                Add stores and claims to see rankings.
            </p>
        @endforelse
    </div>
</section>

{{-- Claim records --}}
<section class="panel table-panel">
    <div class="panel-heading">
        <div>
            <h3>Claim records</h3>
            <p class="muted">
                {{ $claims->total() }} matching records · newest first
            </p>
        </div>

        @if($isAdmin)
            <a
                href="{{ route('claims.export', request()->query()) }}"
                class="button button-outline"
            >
                ↓ Download Excel
            </a>
        @endif
    </div>

    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Claim date</th>
                    <th>Store / PIC</th>
                    <th>Claim amount</th>
                    <th>Vouchers</th>
                    <th>Transfer</th>
                    <th>Proof</th>

                    @if($isAdmin)
                        <th>Actions</th>
                    @endif
                </tr>
            </thead>

            <tbody>
                @forelse($claims as $claim)
                    <tr>
                        <td>
                            <strong>
                                {{ $claim->claim_date->format('d M Y') }}
                            </strong>
                            <small>#{{ $claim->id }}</small>
                        </td>

                        <td>
                            <strong>{{ $claim->store->name }}</strong>
                            <small>
                                {{ $claim->pic_name }} · {{ $claim->pic_phone }}
                            </small>
                        </td>

                        <td class="amount">
                            Rp {{ number_format((float) $claim->total_claimed, 0, ',', '.') }}
                        </td>

                        <td>
                            <div class="voucher-pills">
                                @foreach(['A', 'B'] as $type)
                                    @php
                                        $voucherCount = $claim->vouchers
                                            ->where('voucher_type', $type)
                                            ->count();
                                    @endphp

                                    @if($voucherCount > 0)
                                        <span class="pill pill-{{ strtolower($type) }}">
                                            {{ $type }} · {{ $voucherCount }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>

                            <small>{{ $claim->vouchers->count() }} codes</small>
                        </td>

                        <td>
                            @if($isAdmin)
                                <form
                                    method="POST"
                                    action="{{ route('claims.transfer-status', $claim) }}"
                                    class="status-form"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <input
                                        type="hidden"
                                        name="transfer_status"
                                        value="{{ $claim->transfer_status === 'transferred' ? 'pending' : 'transferred' }}"
                                    >

                                    <button
                                        class="status-badge {{ $claim->transfer_status === 'transferred' ? 'status-done' : 'status-pending' }}"
                                        type="submit"
                                    >
                                        {{ $claim->transfer_status === 'transferred' ? '✓ Transferred' : '○ Pending' }}
                                    </button>

                                    @if($claim->transfer_date)
                                        <small>
                                            {{ $claim->transfer_date->format('d M Y') }}
                                        </small>
                                    @endif
                                </form>
                            @else
                                <span class="status-badge {{ $claim->transfer_status === 'transferred' ? 'status-done' : 'status-pending' }}">
                                    {{ $claim->transfer_status === 'transferred' ? '✓ Transferred' : '○ Pending' }}
                                </span>

                                @if($claim->transfer_date)
                                    <small>
                                        {{ $claim->transfer_date->format('d M Y') }}
                                    </small>
                                @endif
                            @endif
                        </td>

                        <td><a class="proof-link" href="{{ Storage::disk('public')->url($claim->proof_image_path) }}" target="_blank" rel="noopener">View proof ↗</a></td>

                        @if($isAdmin)
                            <td>
                                <div class="row-actions">
                                    <a
                                        href="{{ route('claims.show', $claim) }}"
                                        title="View claim details"
                                    >
                                        Details
                                    </a>

                                    <a
                                        href="{{ route('claims.edit', $claim) }}"
                                        title="Edit claim"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('claims.destroy', $claim) }}"
                                        data-confirm="Delete this claim and its voucher codes?"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isAdmin ? 7 : 6 }}">
                            <div class="empty-state">
                                No claims found. Try changing your filters
                                or create a new claim.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">
        {{ $claims->links() }}
    </div>
</section>

@endsection