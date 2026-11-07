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
            <label> <span>Claim date <span class="required">*</span></span><input required type="date" name="claim_date" value="{{ old('claim_date', $claim->claim_date?->format('Y-m-d')) }}"></label>

            <label><span>Area <span class="required">*</span></span>
                <select required name="area_id" data-area-select>
                    <option value="">Choose an area first</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->id }}" @selected((string) $selectedAreaId === (string) $area->id)>{{ $area->name }}</option>
                    @endforeach
                </select>
                <small>Choose an area to limit the store list to that area.</small>
            </label>

            <label><span>Store name <span class="required">*</span></span> 
                <select required name="store_id" data-store-select data-area-filter-required>
                    <option value="">Choose an area first</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" data-area-id="{{ $store->area_id }}" @selected((string) $selectedStoreId === (string) $store->id)>{{ $store->name }}</option>
                    @endforeach
                </select>
                <small>Only stores assigned to the selected area can be chosen.</small>
            </label>

            <label><span>PIC name <span class="required">*</span></span><input required name="pic_name" value="{{ old('pic_name', $claim->pic_name) }}" placeholder="Person in charge"></label>
            <label><span>PIC phone number <span class="required">*</span></span><input required name="pic_phone" value="{{ old('pic_phone', $claim->pic_phone) }}" placeholder="e.g. 081234567890"></label>
            {{-- <label>Total claimed (IDR) <span class="required">*</span><div class="input-prefix"><span>Rp</span><input required type="number" min="0.01" step="0.01" name="total_claimed" value="{{ old('total_claimed', $claim->total_claimed) }}" placeholder="500000"></div></label> --}}
            <label>
            <span>
            Total claimed (IDR)
            <span class="required">*</span></span>

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
                Automatically calculated: A–D are Rp50,000 each; E is Rp100,000.
            </small>
        </label>
            <label><span>Transaction date <span class="required">*</span></span><input required type="date" name="transaction_date" value="{{ old('transaction_date', $claim->transaction_date?->format('Y-m-d')) }}"></label>
            <label><span>Mandiri account number <span class="required">*</span></span><input required name="mandiri_account_number" value="{{ old('mandiri_account_number', $claim->mandiri_account_number) }}" placeholder="Account number"></label>
            <label><span>Account holder name <span class="required">*</span></span><input required name="account_holder_name" value="{{ old('account_holder_name', $claim->account_holder_name) }}" placeholder="Name as registered with bank"></label>
        </div>
    </section>

    <section class="form-section">
        <div class="section-title"><span class="section-number">02</span><div><h3>Voucher proof</h3><p>Upload a clear receipt or transaction proof image.</p></div></div>
        <label class="upload-zone"><input type="file" name="proof_image" accept="image/*,.jpg,.jpeg,.png,.webp,.gif,.bmp,.tif,.tiff,.avif,.heic,.heif,.jp2,.j2k" {{ $claim->exists ? '' : 'required' }} data-file-input><span class="upload-icon">↑</span><strong data-file-name>{{ $claim->exists ? 'Choose a new image to replace the current proof' : 'Click to upload or drag your image here' }}</strong><small>JPG, PNG, WEBP, GIF, BMP, TIFF, AVIF or HEIC/HEIF · Maximum 15 MB</small></label>
        @if($claim->exists && $claim->proof_image_path)<p class="current-proof">Current proof: <a target="_blank" rel="noopener" href="{{ Storage::disk('public')->url($claim->proof_image_path) }}">View uploaded image ↗</a></p>@endif
    </section>

    <section class="form-section">
        <div class="section-title"><span class="section-number">03</span><div><h3>Voucher codes</h3><p>Paste codes separated by commas or new lines. Every code can only be claimed once.</p></div></div>
        <div class="voucher-input-grid">
            @foreach(['A', 'B', 'C', 'D', 'E'] as $type)
                @php($existingCodes = $claim->exists ? $claim->vouchers->where('voucher_type', $type)->pluck('voucher_code')->implode("\n") : '')
                @php($voucherInput = old('voucher_codes.'.$type, $existingCodes))
                @php($voucherCount = count(array_filter(preg_split('/[\r\n,;]+/', $voucherInput))))
                <div class="voucher-input-card voucher-border-{{ strtolower($type) }}">
                    <div class="voucher-card-head"><span class="type-letter type-{{ strtolower($type) }}">{{ $type }}</span><div><strong>Voucher {{ $type }}</strong><small>Enter voucher codes below</small></div><span class="code-count" data-count-for="{{ $type }}">{{ $voucherCount }} codes</span></div>
                    <textarea name="voucher_codes[{{ $type }}]" rows="4" placeholder="e.g. {{ $type }}001, {{ $type }}002" data-voucher-textarea>{{ $voucherInput }}</textarea>
                    <div class="voucher-card-foot"><span>One code per line or comma-separated</span><strong>Count: <span data-count-number>{{ $voucherCount }}</span></strong></div>
                </div>
            @endforeach
            <p class="voucher-duplicate-warning" data-voucher-duplicate-warning hidden></p>
        </div>
    </section>

    {{-- <section class="form-section">
        <div class="section-title"><span class="section-number">04</span><div><h3>Transfer tracking</h3><p>Keep payment status up to date for reconciliation.</p></div></div>
        <div class="form-grid">
            <!-- <label>Transfer status
                <select name="transfer_status"><option value="pending" @selected(old('transfer_status', $claim->transfer_status ?? 'pending') === 'pending')>Pending transfer</option></select>
            </label> -->
            <input type="hidden" name="transfer_status" value="{{ old('transfer_status', $claim->transfer_status ?? 'pending') }}">
            <label>Transfer date<input type="date" name="transfer_date" value="{{ old('transfer_date', $claim->transfer_date?->format('Y-m-d')) }}"></label>
            <label class="full-width">Internal notes <span class="optional">Optional</span><textarea name="notes" rows="3" placeholder="Add any notes for the operations team...">{{ old('notes', $claim->notes) }}</textarea></label>
        </div>
    </section> --}}

    <div class="form-actions">
        <a href="{{ route('dashboard') }}" class="button button-outline">Cancel</a>
        <button
            type="submit"
            class="button button-primary claim-submit-button"
            data-claim-submit
            data-loading-label="{{ $claim->exists ? 'Saving changes...' : 'Submitting claim...' }}"
        >
            <span data-submit-label>{{ $claim->exists ? 'Save changes' : 'Save claim' }}</span>
            <span data-submit-arrow aria-hidden="true">→</span>
            <span class="claim-submit-button-spinner" data-submit-spinner aria-hidden="true" hidden></span>
        </button>
    </div>
</form>

<div class="claim-submit-overlay" data-claim-submit-overlay hidden aria-hidden="true">
    <div class="claim-submit-dialog" role="status" aria-live="polite" aria-atomic="true">
        <span class="claim-submit-spinner" aria-hidden="true"></span>
        <strong>{{ $claim->exists ? 'Saving your changes...' : 'Submitting your claim...' }}</strong>
        <p>Please keep this page open while we save your claim. Do not click submit again.</p>
    </div>
</div>

<style>
    .claim-submit-button:disabled {
        cursor: wait;
        opacity: .78;
        transform: none;
    }

    .claim-submit-button-spinner,
    .claim-submit-spinner {
        display: inline-block;
        width: 18px;
        height: 18px;
        border: 2px solid rgba(255, 255, 255, .38);
        border-top-color: #fff;
        border-radius: 50%;
        animation: claim-submit-spin .75s linear infinite;
        flex: 0 0 auto;
    }

    .claim-submit-button-spinner[hidden] {
        display: none !important;
    }

    .claim-submit-spinner {
        width: 42px;
        height: 42px;
        border: 4px solid #e7edf5;
        border-top-color: #0758DD;
        margin-bottom: 16px;
    }

    .claim-submit-overlay[hidden] {
        display: none !important;
    }

    .claim-submit-overlay:not([hidden]) {
        position: fixed;
        inset: 0;
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: rgba(11, 26, 48, .58);
        backdrop-filter: blur(3px);
    }

    .claim-submit-dialog {
        width: min(100%, 390px);
        padding: 30px 26px;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 20px 70px rgba(0, 0, 0, .22);
        text-align: center;
        color: #17324f;
    }

    .claim-submit-dialog strong {
        display: block;
        font-size: 16px;
        margin-bottom: 7px;
    }

    .claim-submit-dialog p {
        margin: 0;
        color: #718198;
        font-size: 12px;
        line-height: 1.6;
    }

    @keyframes claim-submit-spin {
        to { transform: rotate(360deg); }
    }

    @media (prefers-reduced-motion: reduce) {
        .claim-submit-button-spinner,
        .claim-submit-spinner {
            animation-duration: 1.8s;
        }
    }
</style>

@push('scripts')
<script>
(() => {
    const form = document.querySelector('.claim-form');
    if (!form) return;

    const submitButton = form.querySelector('[data-claim-submit]');
    const submitLabel = submitButton?.querySelector('[data-submit-label]');
    const submitArrow = submitButton?.querySelector('[data-submit-arrow]');
    const submitSpinner = submitButton?.querySelector('[data-submit-spinner]');
    const loadingOverlay = document.querySelector('[data-claim-submit-overlay]');
    const originalSubmitLabel = submitLabel?.textContent ?? 'Save claim';
    let submissionStarted = false;

    form.addEventListener('submit', (event) => {
        // Ignore any additional click, Enter key, or scripted submit while the
        // first request is still being processed by the server.
        if (submissionStarted) {
            event.preventDefault();
            return;
        }

        submissionStarted = true;
        form.setAttribute('aria-busy', 'true');

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.setAttribute('aria-disabled', 'true');
            submitButton.classList.add('is-loading');

            if (submitLabel) {
                submitLabel.textContent = submitButton.dataset.loadingLabel || 'Submitting...';
            }
            if (submitArrow) submitArrow.hidden = true;
            if (submitSpinner) submitSpinner.hidden = false;
        }

        if (loadingOverlay) {
            loadingOverlay.hidden = false;
            loadingOverlay.setAttribute('aria-hidden', 'false');
        }
    });

    // Restore the form if the browser brings it back from its back-forward cache.
    window.addEventListener('pageshow', (event) => {
        if (!event.persisted) return;

        submissionStarted = false;
        form.removeAttribute('aria-busy');

        if (submitButton) {
            submitButton.disabled = false;
            submitButton.removeAttribute('aria-disabled');
            submitButton.classList.remove('is-loading');
        }
        if (submitLabel) submitLabel.textContent = originalSubmitLabel;
        if (submitArrow) submitArrow.hidden = false;
        if (submitSpinner) submitSpinner.hidden = true;

        if (loadingOverlay) {
            loadingOverlay.hidden = true;
            loadingOverlay.setAttribute('aria-hidden', 'true');
        }
    });
})();
</script>
@endpush

@endsection
