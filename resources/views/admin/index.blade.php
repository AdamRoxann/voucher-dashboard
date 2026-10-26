@extends('layouts.app')
@section('title', 'Admin settings')
@section('content')
<div class="page-heading"><div><span class="eyebrow blue">ADMINISTRATION</span><h2>Campaign control centre</h2><p class="muted">Manage event dates, merchant directory and claim transfer statuses.</p></div><a class="button button-outline" href="{{ route('dashboard') }}">← Back to dashboard</a></div>
<div class="stats-grid admin-stats">
    <article class="stat-card"><span>Total claims</span><strong>{{ number_format($counts['claims']) }}</strong><small>All submitted forms</small></article>
    <article class="stat-card"><span>Pending transfer</span><strong>{{ number_format($counts['pending']) }}</strong><small>Require follow-up</small></article>
    <article class="stat-card"><span>Transferred</span><strong>{{ number_format($counts['transferred']) }}</strong><small>Marked as paid</small></article>
    <article class="stat-card"><span>Active stores</span><strong>{{ number_format($counts['stores']) }}</strong><small>Can submit claims</small></article>
</div>
<section class="panel admin-panel" id="event"><div class="panel-heading"><div><h3>Event &amp; campaign period</h3><p class="muted">These details are saved in the database for future admin edits.</p></div></div>
<form method="POST" action="{{ route('admin.settings.update') }}" class="admin-form">@csrf @method('PUT')
<div class="field"><label for="event_name">Event name</label><input id="event_name" name="event_name" value="{{ old('event_name', $settings['event_name'] ?? 'Livin’ by Mandiri & Visa') }}" required></div>
<div class="field"><label for="event_subtitle">Subtitle</label><input id="event_subtitle" name="event_subtitle" value="{{ old('event_subtitle', $settings['event_subtitle'] ?? 'Discover · Connect · Collect') }}"></div>
<div class="form-grid"><div class="field"><label for="event_start">Start date</label><input id="event_start" type="date" name="event_start" value="{{ old('event_start', $settings['event_start'] ?? '2026-09-25') }}" required></div><div class="field"><label for="event_end">End date</label><input id="event_end" type="date" name="event_end" value="{{ old('event_end', $settings['event_end'] ?? '2026-09-27') }}" required></div></div>
<div class="form-actions"><button class="button button-primary">Save campaign settings</button></div></form></section>
<section class="panel admin-panel" id="stores">
    <div class="panel-heading">
        <div><h3>Merchant directory</h3><p class="muted">{{ $stores->count() }} stores · Deactivate stores instead of deleting those with claim history.</p></div>
        <a href="{{ route('areas.index') }}" class="button button-outline">Manage areas</a>
    </div>
    <form method="POST" action="{{ route('admin.stores.store') }}" class="admin-add-store">
        @csrf
        <input name="name" placeholder="Enter a new store name" maxlength="150" required>
        <select name="area_id" required aria-label="Store area">
            <option value="">Choose area</option>
            @foreach($areas as $area)<option value="{{ $area->id }}" @selected((string) old('area_id') === (string) $area->id)>{{ $area->name }}</option>@endforeach
        </select>
        <button class="button button-primary">＋ Add store</button>
    </form>
    <div class="admin-store-list">
        @foreach($stores as $store)
            <div class="admin-store-row"><div><strong>{{ $store->name }}</strong><small>{{ $store->area?->name ?? 'Unassigned area' }} · {{ $store->claims_count }} claims · {{ $store->is_active ? 'Active' : 'Inactive' }}</small></div><div class="admin-store-actions"><a class="text-link" href="{{ route('stores.edit', $store) }}">Edit</a><form method="POST" action="{{ route('admin.stores.toggle', $store) }}">@csrf @method('PATCH')<button class="button {{ $store->is_active ? 'button-outline' : 'button-primary' }}">{{ $store->is_active ? 'Deactivate' : 'Activate' }}</button></form></div></div>
        @endforeach
    </div>
</section>
<!-- <section class="panel admin-panel" id="transfers"><div class="panel-heading"><div><h3>Claim transfer management</h3><p class="muted">Update transfer status and transfer date. One row represents one submitted claim.</p></div></div><div class="table-scroll"><table><thead><tr><th>Merchant / claim</th><th>Claim date</th><th>Vouchers</th><th>Claim total</th><th>Transfer status</th><th>Transfer date</th><th>Action</th></tr></thead><tbody>@forelse($claims as $claim)<tr><td><strong>{{ $claim->store->name }}</strong><small class="table-subline">{{ $claim->pic_name }} · #{{ $claim->id }}</small></td><td>{{ $claim->claim_date?->format('d M Y') }}</td><td>{{ $claim->vouchers->count() }}</td><td>Rp {{ number_format((float)$claim->total_claimed, 0, ',', '.') }}</td><td><span class="status-badge {{ $claim->transfer_status === 'transferred' ? 'status-done' : 'status-pending' }}">{{ $claim->transfer_status === 'transferred' ? 'Transferred' : 'Pending' }}</span></td><td colspan="2"><form class="admin-transfer-form" method="POST" action="{{ route('admin.claims.transfer', $claim) }}">@csrf @method('PATCH')<select name="transfer_status"><option value="pending" @selected($claim->transfer_status === 'pending')>Pending</option><option value="transferred" @selected($claim->transfer_status === 'transferred')>Transferred</option></select><input type="date" name="transfer_date" value="{{ $claim->transfer_date?->format('Y-m-d') }}"><button class="button button-primary button-small">Save</button><a class="text-link" href="{{ route('claims.show', $claim) }}">Details</a></form></td></tr>@empty<tr><td colspan="7">No claims found.</td></tr>@endforelse</tbody></table></div><div class="pagination-wrap">{{ $claims->fragment('transfers')->links() }}</div></section> -->

<section class="panel table-panel admin-panel" id="transfers">
    <div class="panel-heading">
        <div>
            <h3>Claim records</h3>
            <p class="muted">
                {{ number_format($claims->total()) }} matching records · newest first
            </p>
        </div>

        <div>
            <a href="{{ route('claims.export', request()->query()) }}"
            class="button button-outline">
                ↓ Download Excel
            </a>

            <a
                href="{{ route('claims.proofs.download', request()->query()) }}"
                class="button button-primary"
                >
                ↓ Download All Proofs
            </a>
        </div>
    </div>

    <form class="filter-card" method="GET"
          action="{{ route('admin.index') }}#transfers">
        <div class="filter-title">
            <div>
                <h3>Find a claim</h3>
                <p class="muted">
                    Narrow down claims by date, area, store, voucher type or keyword.
                </p>
            </div>

            <a href="{{ route('admin.index') }}#transfers"
               class="text-link">
                Reset filters ↺
            </a>
        </div>

        <div class="filter-grid">
            <label>
                From date
                <input type="date" name="from"
                       value="{{ request('from') }}">
            </label>

            <label>
                To date
                <input type="date" name="to"
                       value="{{ request('to') }}">
            </label>

            <label>
                Area
                <select name="area_id" data-area-select>
                    <option value="">All areas</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->id }}" @selected((string) request('area_id') === (string) $area->id)>{{ $area->name }}</option>
                    @endforeach
                </select>
            </label>

            <label>
                Store
                <select name="store_id" data-store-select>
                    <option value="">All stores</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" data-area-id="{{ $store->area_id }}" @selected((string) request('store_id') === (string) $store->id)>
                            {{ $store->name }}{{ $store->area ? ' — '.$store->area->name : ' — Unassigned' }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label>
                Voucher type
                <select name="voucher_type">
                    <option value="">All types</option>
                    @foreach(['A', 'B'] as $type)
                        <option value="{{ $type }}"
                            @selected(request('voucher_type') === $type)>
                            Voucher {{ $type }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="search-field">
                Search store, PIC or code
                <input name="search"
                       value="{{ request('search') }}"
                       placeholder="e.g. BEIGE STUDIO or A006">
            </label>

            <button class="button button-dark" type="submit">
                Apply filters
            </button>
        </div>
    </form>

    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Claim date</th>
                    <th>Store / PIC</th>
                    <th>Claim amount</th>
                    <th>Vouchers</th>
                    <th>Transfer</th>
                    <th>Transfer date</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($claims as $claim)
                    <tr>
                        <td>
                            <strong>{{ $claim->claim_date?->format('d M Y') }}</strong>
                            <small>#{{ $claim->id }}</small>
                        </td>

                        <td>
                            <strong>{{ $claim->store->name }}</strong>
                            <small>Area: {{ $claim->store->area?->name ?? 'Unassigned' }}</small>
                            <small>{{ $claim->pic_name }} · {{ $claim->pic_phone }}</small>
                        </td>

                        <td class="amount">
                            Rp {{ number_format((float) $claim->total_claimed, 0, ',', '.') }}
                        </td>

                        <td>
                            <div class="voucher-pills">
                                @foreach(['A', 'B'] as $type)
                                    @php
                                        $count = $claim->vouchers
                                            ->where('voucher_type', $type)
                                            ->count();
                                    @endphp

                                    @if($count)
                                        <span class="pill pill-{{ strtolower($type) }}">
                                            {{ $type }} · {{ $count }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                            <small>{{ $claim->vouchers->count() }} codes</small>
                        </td>

                        <td>
                            {{-- <span class="status-badge {{ $claim->transfer_status === 'transferred' ? 'status-done' : 'status-pending' }}">
                                {{ $claim->transfer_status === 'transferred' ? '✓ Transferred' : '○ Pending' }}
                            </span> --}}
                            <span class="status-badge {{
                                $claim->transfer_status === 'transferred'
                                    ? 'status-done'
                                    : ($claim->transfer_status === 'rejected'
                                        ? 'status-rejected'
                                        : 'status-pending')
                            }}">
                                {{
                                    $claim->transfer_status === 'transferred'
                                        ? '✓ Transferred'
                                        : ($claim->transfer_status === 'rejected'
                                            ? '✕ Rejected'
                                            : '○ Pending')
                                }}
                            </span>
                        </td>

                        <td>
                            {{ $claim->transfer_date?->format('d M Y') ?? '—' }}
                        </td>

                        <td style="display: flex; align-items: center;">
                            {{-- <form class="admin-transfer-form"
                                  method="POST"
                                  action="{{ route('admin.claims.transfer', $claim) }}">
                                @csrf
                                @method('PATCH')

                                <select name="transfer_status" aria-label="Transfer status">
                                    <option value="pending"
                                        @selected($claim->transfer_status === 'pending')>
                                        Pending
                                    </option>
                                    <option value="transferred"
                                        @selected($claim->transfer_status === 'transferred')>
                                        Transferred
                                    </option>
                                </select>

                                <input type="date" name="transfer_date"
                                       aria-label="Transfer date"
                                       value="{{ $claim->transfer_date?->format('Y-m-d') }}">

                                <button class="button button-primary button-small">
                                    Save
                                </button>
                            </form> --}}
                            <form
                                class="admin-transfer-form"
                                method="POST"
                                action="{{ route('admin.claims.transfer', $claim) }}"
                            >
                                @csrf
                                @method('PATCH')

                                <select
                                    name="transfer_status"
                                    aria-label="Transfer status"
                                    required
                                >
                                    <option
                                        value="pending"
                                        @selected($claim->transfer_status === 'pending')
                                    >
                                        Pending transfer
                                    </option>

                                    <option
                                        value="transferred"
                                        @selected($claim->transfer_status === 'transferred')
                                    >
                                        Transferred
                                    </option>

                                    <option
                                        value="rejected"
                                        @selected($claim->transfer_status === 'rejected')
                                    >
                                        Rejected
                                    </option>
                                </select>

                                <label class="admin-notes-field">
                                    <span>Notes (optional)</span>

                                    <textarea
                                        name="notes"
                                        rows="2"
                                        maxlength="2000"
                                        placeholder="Reason for rejection or internal notes..."
                                    >{{ $claim->notes }}</textarea>
                                </label>

                                <input
                                    type="date"
                                    name="transfer_date"
                                    aria-label="Transfer date"
                                    value="{{ $claim->transfer_date?->format('Y-m-d') ?? now()->format('Y-m-d') }}"
                                >

                                <button
                                    type="submit"
                                    class="button button-primary button-small"
                                >
                                    Save
                                </button>
                            </form>

                             <a class="text-link"
                               href="{{ route('claims.show', $claim) }}">
                                Details
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                No claims found. Try changing your filters.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">
        {{ $claims->fragment('transfers')->links() }}
    </div>
</section>
@endsection
