@extends('layouts.app')
@section('title', 'Areas')
@section('content')
<div class="page-heading">
    <div><span class="eyebrow blue">CONFIGURATION</span><h2>Area directory</h2><p class="muted">Create parent areas such as Hall 1, then assign stores to each area.</p></div>
    <div class="page-heading-actions"><a href="{{ route('stores.index') }}" class="button button-outline">View stores</a><a href="{{ route('areas.create') }}" class="button button-primary">＋ Add area</a></div>
</div>
<section class="panel table-panel">
    <div class="panel-heading"><div><h3>All areas</h3><p class="muted">{{ $areas->total() }} registered areas</p></div></div>
    <div class="table-scroll"><table><thead><tr><th>Area name</th><th>Stores assigned</th><th>Actions</th></tr></thead><tbody>
        @forelse($areas as $area)
            <tr><td><strong>{{ $area->name }}</strong></td><td>{{ $area->stores_count }}</td><td><div class="row-actions"><a href="{{ route('areas.edit', $area) }}">Edit</a><form method="POST" action="{{ route('areas.destroy', $area) }}" data-confirm="Delete this area? It must not have any stores assigned.">@csrf @method('DELETE')<button type="submit">Delete</button></form></div></td></tr>
        @empty
            <tr><td colspan="3"><div class="empty-state">No areas created yet. Add an area first, then add or edit stores to assign them.</div></td></tr>
        @endforelse
    </tbody></table></div>
    <div class="pagination-wrap">{{ $areas->links() }}</div>
</section>
@endsection
