@extends('layouts.app')

@section('title', $store->exists ? 'Edit store' : 'Add store')

@section('content')
<div class="page-heading">
    <div><span class="eyebrow blue">CONFIGURATION</span><h2>{{ $store->exists ? 'Edit store' : 'Add a store' }}</h2><p class="muted">Assign each store to an area so the claim form can filter stores correctly.</p></div>
    <a href="{{ route('stores.index') }}" class="button button-outline">← Back to stores</a>
</div>

<form method="POST" action="{{ $store->exists ? route('stores.update', $store) : route('stores.store') }}" class="claim-form compact-form">
    @csrf
    @if($store->exists) @method('PUT') @endif
    <section class="form-section">
        <label>Store name <span class="required">*</span><input required name="name" value="{{ old('name', $store->name) }}" placeholder="e.g. Merchant Mombee" maxlength="150"></label>
        <label>Area <span class="required">*</span>
            <select required name="area_id">
                <option value="">Choose an area</option>
                @foreach($areas as $area)
                    <option value="{{ $area->id }}" @selected((string) old('area_id', $store->area_id) === (string) $area->id)>{{ $area->name }}</option>
                @endforeach
            </select>
            @if($areas->isEmpty())<small>Create an area first on the Areas page.</small>@else<small>Choose the area this store belongs to.</small>@endif
        </label>
        <label class="checkbox-label"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $store->is_active ?? true))> Store is active and available in claim forms</label>
        <div class="form-actions"><a href="{{ route('stores.index') }}" class="button button-outline">Cancel</a><button class="button button-primary">Save store →</button></div>
    </section>
</form>
@endsection
