@extends('layouts.app')
@section('title', 'Stores')
@section('content')
<div class="page-heading">
    <div><span class="eyebrow blue">CONFIGURATION</span><h2>Store directory</h2><p class="muted">Manage stores and assign each one to an area.</p></div>
    <div class="page-heading-actions"><a href="{{ route('areas.index') }}" class="button button-outline">Manage areas</a><a href="{{ route('stores.create') }}" class="button button-primary">＋ Add store</a></div>
</div>
<section class="panel table-panel">
    <div class="panel-heading"><div><h3>All stores</h3><p class="muted">{{ $stores->total() }} registered stores</p></div></div>
    <div class="table-scroll"><table><thead><tr><th>Area</th><th>Store name</th><th>Claims</th><th>Status</th><th>Actions</th></tr></thead><tbody>
        @forelse($stores as $store)
            <tr><td>{{ $store->area?->name ?? 'Unassigned' }}</td><td><strong>{{ $store->name }}</strong></td><td>{{ $store->claims_count }}</td><td><span class="status-badge {{ $store->is_active ? 'status-done' : 'status-pending' }}">{{ $store->is_active ? 'Active' : 'Inactive' }}</span></td><td><div class="row-actions"><a href="{{ route('stores.edit', $store) }}">Edit</a><form method="POST" action="{{ route('stores.destroy', $store) }}" data-confirm="Delete this store? This is only possible if it has no claims.">@csrf @method('DELETE')<button type="submit">Delete</button></form></div></td></tr>
        @empty
            <tr><td colspan="5"><div class="empty-state">No stores added yet.</div></td></tr>
        @endforelse
    </tbody></table></div>
    <div class="pagination-wrap">{{ $stores->links() }}</div>
</section>
@endsection
