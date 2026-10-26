@extends('layouts.app')

@section('title', $claim->exists ? 'Edit claim' : 'New claim')

@section('content')
@php
    $selectedAreaId = old('area_id', $claim->store?->area_id);
    $selectedStoreId = old('store_id', $claim->store_id);
@endphp

<div class="page-heading">
    <div>
        <span class="eyebrow blue">CLAIM MANAGEMENT</span>
        <h2>{{ $claim->exists ? 'Update claim details' : 'Create a voucher claim' }}</h2>
        <p class="muted">Enter the claim information and add each voucher code once. Commas and new lines are supported.</p>
    </div>
    <a href="{{ route('dashboard') }}" class="button button-outline">← Back to dashboard</a>
</div>

<form method="POST" enctype="multipart/form-data" action="{{ $claim->exists ? route('claims.update', $claim) : route('claims.store') }}" class="claim-form">
    @csrf
    @if($claim->exists) @method('PUT') @endif

    <section class="form-section">
        <div class="section-title"><span class="section-number">01</span><div><h3>Claim information</h3><p>Who submitted this claim, and for how much?</p></div></div>
        <div class="form-grid">
            <label>Claim date <span class="required">*</span><input required type="date" name="claim_date" value="{{ old('claim_date', $claim->claim_date?->format('Y-m-d')) }}"></label>

            <label>Area <span class="required">*</span>
                <select required name="area_id" data-area-select>
                    <option value="">Choose an area first</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->id }}" @selected((string) $selectedAreaId === (string) $area->id)>{{ $area->name }}</option>
                    @endforeach
                </select>
                <small>Choose an area to limit the store list to that area.</small>
            </label>

            <label>Store name <span class="required">*</span>
                <select required name="store_id" data-store-select data-area-filter-required>
                    <option value="">Choose an area first</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" data-area-id="{{ $store->area_id }}" @selected((string) $selectedStoreId === (string) $store->id)>{{ $store->name }}</option>
                    @endforeach
                </select>
                <small>Only stores assigned to the selected area can be chosen.</small>
            </label>

            <label>PIC name <span class="required">*</span><input required name="pic_name" value="{{ old('pic_name', $claim->pic_name) }}" placeholder="Person in charge"></label>
            <label>PIC phone number <span class="required">*</span><input required name="pic_phone" value="{{ old('pic_phone', $claim->pic_phone) }}" placeholder="e.g. 081234567890"></label>
            {{-- <label>Total claimed (IDR) <span class="required">*</span><div class="input-prefix"><span>Rp</span><input required type="number" min="0.01" step="0.01" name="total_claimed" value="{{ old('total_claimed', $claim->total_claimed) }}" placeholder="500000"></div></label> --}}
            <label>
            Total claimed (IDR)
            <span class="required">*</span>

            <div class="input-prefix">
                <span>Rp</span>
                <input
                    type="number"
                    name="total_claimed"
                    value="{{ old('total_claimed', $claim->total_claimed ?? 0) }}"
                    min="0"
                    step="50000"
                    readonly
                    data-total-claimed
                >
            </div>

            <small>
                Automatically calculated from Voucher A and B.
                Each voucher is worth Rp50,000.
            </small>
        </label>
            <label>Transaction date <span class="required">*</span><input required type="date" name="transaction_date" value="{{ old('transaction_date', $claim->transaction_date?->format('Y-m-d')) }}"></label>
            <label>Mandiri account number <span class="required">*</span><input required name="mandiri_account_number" value="{{ old('mandiri_account_number', $claim->mandiri_account_number) }}" placeholder="Account number"></label>
            <label>Account holder name <span class="required">*</span><input required name="account_holder_name" value="{{ old('account_holder_name', $claim->account_holder_name) }}" placeholder="Name as registered with bank"></label>
        </div>
    </section>

    <section class="form-section">
        <div class="section-title"><span class="section-number">02</span><div><h3>Voucher proof</h3><p>Upload a clear receipt or transaction proof image.</p></div></div>
        <label class="upload-zone"><input type="file" name="proof_image" accept=".jpg,.jpeg,.png,.webp,.heic,image/*" {{ $claim->exists ? '' : 'required' }} data-file-input><span class="upload-icon">↑</span><strong data-file-name>{{ $claim->exists ? 'Choose a new image to replace the current proof' : 'Click to upload or drag your image here' }}</strong><small>JPG, PNG, WEBP or HEIC · Maximum 5 MB</small></label>
        @if($claim->exists && $claim->proof_image_path)<p class="current-proof">Current proof: <a target="_blank" rel="noopener" href="{{ Storage::disk('public')->url($claim->proof_image_path) }}">View uploaded image ↗</a></p>@endif
    </section>

    <section class="form-section">
        <div class="section-title"><span class="section-number">03</span><div><h3>Voucher codes</h3><p>Paste codes separated by commas or new lines. Counts are calculated automatically.</p></div></div>
        <div class="voucher-input-grid">
            @foreach(['A', 'B'] as $type)
                @php($existingCodes = $claim->exists ? $claim->vouchers->where('voucher_type', $type)->pluck('voucher_code')->implode("\n") : '')
                @php($voucherInput = old('voucher_codes.'.$type, $existingCodes))
                @php($voucherCount = count(array_filter(preg_split('/[\r\n,;]+/', $voucherInput))))
                <div class="voucher-input-card voucher-border-{{ strtolower($type) }}">
                    <div class="voucher-card-head"><span class="type-letter type-{{ strtolower($type) }}">{{ $type }}</span><div><strong>Voucher {{ $type }}</strong><small>Enter voucher codes below</small></div><span class="code-count" data-count-for="{{ $type }}">{{ $voucherCount }} codes</span></div>
                    <textarea name="voucher_codes[{{ $type }}]" rows="4" placeholder="e.g. {{ $type }}001, {{ $type }}002" data-voucher-textarea>{{ $voucherInput }}</textarea>
                    <div class="voucher-card-foot"><span>One code per line or comma-separated</span><strong>Count: <span data-count-number>{{ $voucherCount }}</span></strong></div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="form-section">
        <div class="section-title"><span class="section-number">04</span><div><h3>Transfer tracking</h3><p>Keep payment status up to date for reconciliation.</p></div></div>
        <div class="form-grid">
            <!-- <label>Transfer status
                <select name="transfer_status"><option value="pending" @selected(old('transfer_status', $claim->transfer_status ?? 'pending') === 'pending')>Pending transfer</option></select>
            </label> -->
            <input type="hidden" name="transfer_status" value="{{ old('transfer_status', $claim->transfer_status ?? 'pending') }}">
            <label>Transfer date<input type="date" name="transfer_date" value="{{ old('transfer_date', $claim->transfer_date?->format('Y-m-d')) }}"></label>
            <label class="full-width">Internal notes <span class="optional">Optional</span><textarea name="notes" rows="3" placeholder="Add any notes for the operations team...">{{ old('notes', $claim->notes) }}</textarea></label>
        </div>
    </section>

    <div class="form-actions"><a href="{{ route('dashboard') }}" class="button button-outline">Cancel</a><button type="submit" class="button button-primary">{{ $claim->exists ? 'Save changes' : 'Save claim' }} <span>→</span></button></div>
</form>

<script>
(function () {
    const totalInput = document.querySelector('[data-total-claimed]');
    const voucherTextareas = document.querySelectorAll(
        '[data-voucher-textarea]'
    );

    if (!totalInput || voucherTextareas.length === 0) {
        return;
    }

    const VOUCHER_VALUE = 50000;

    function countUniqueCodes(text) {
        const codes = text
            .split(/[\r\n,;]+/)
            .map(code => code.trim())
            .filter(code => code !== '');

        return new Set(codes).size;
    }

    function updateTotal() {
        let totalCodes = 0;

        voucherTextareas.forEach(textarea => {
            totalCodes += countUniqueCodes(textarea.value);
        });

        totalInput.value = totalCodes * VOUCHER_VALUE;

        // Notify other scripts that the amount has changed.
        totalInput.dispatchEvent(new Event('input', { bubbles: true }));
    }

    voucherTextareas.forEach(textarea => {
        textarea.addEventListener('input', updateTotal);
    });

    // Calculate immediately when creating or editing a claim.
    updateTotal();
})();
</script>

@endsection
