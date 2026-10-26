@extends('layouts.app')
@section('title', $area->exists ? 'Edit area' : 'Add area')
@section('content')
<div class="page-heading"><div><span class="eyebrow blue">CONFIGURATION</span><h2>{{ $area->exists ? 'Edit area' : 'Add an area' }}</h2><p class="muted">An area is a parent group; stores are assigned to it separately.</p></div><a href="{{ route('areas.index') }}" class="button button-outline">← Back to areas</a></div>
<form method="POST" action="{{ $area->exists ? route('areas.update', $area) : route('areas.store') }}" class="claim-form compact-form">
    @csrf
    @if($area->exists) @method('PUT') @endif
    <section class="form-section">
        <label>Area name <span class="required">*</span><input name="name" value="{{ old('name', $area->name) }}" maxlength="150" placeholder="e.g. Hall 1" required autofocus></label>
        <div class="form-actions"><a href="{{ route('areas.index') }}" class="button button-outline">Cancel</a><button class="button button-primary">Save area →</button></div>
    </section>
</form>
@endsection
