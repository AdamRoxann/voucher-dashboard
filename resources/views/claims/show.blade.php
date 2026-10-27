@extends('layouts.app')
@section('title', 'Claim details · '.$claim->store->name)
@section('content')
<div class="page-heading">
  <div><span class="eyebrow blue">CLAIM #{{ str_pad($claim->id, 5, '0', STR_PAD_LEFT) }}</span><h2>Claim details</h2><p class="muted">Full submission details, voucher codes and transfer information.</p></div>
  <div style="display:flex;gap:10px;flex-wrap:wrap"><a class="button button-outline" href="{{ route('dashboard') }}">← Back to dashboard</a><a class="button button-primary" href="{{ route('claims.edit', $claim) }}">Edit claim</a></div>
</div>
<section class="panel" style="overflow:hidden;padding:0;margin-bottom:22px">
  <div style="padding:24px;background:linear-gradient(120deg,#174aa8,#2865c7);color:#fff;display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap">
    <div><div style="font-size:12px;letter-spacing:.12em;text-transform:uppercase;opacity:.8">Merchant / Store</div><h2 style="margin:7px 0;font-size:28px;color:#fff">{{ $claim->store->name }}</h2><div style="opacity:.88">Area: {{ $claim->store->area?->name ?? 'Unassigned' }} · Submitted {{ $claim->created_at->format('d M Y · H:i') }}</div></div>
    <span class="status-badge {{ $claim->transfer_status==='transferred'?'status-done':'status-pending' }}">{{ $claim->transfer_status==='transferred'?'✓ Sudah ditransfer':'○ Belum ditransfer' }}</span>
  </div>
  <div style="padding:24px">
    <div class="stat-grid" style="margin-bottom:22px">
      <article class="stat-card stat-primary"><div class="stat-label">TOTAL KLAIM</div><strong style="font-size:26px">Rp {{ number_format((float)$claim->total_claimed,0,',','.') }}</strong><small>Nominal pengajuan</small></article>
      <article class="stat-card"><div class="stat-label">TOTAL VOUCHER</div><strong>{{ $claim->vouchers->count() }}</strong><small>Kode voucher tercatat</small></article>
      <article class="stat-card"><div class="stat-label">TANGGAL KLAIM</div><strong style="font-size:22px">{{ $claim->claim_date->format('d M Y') }}</strong><small>Tanggal pengajuan</small></article>
      <article class="stat-card"><div class="stat-label">TANGGAL TRANSFER</div><strong style="font-size:22px">{{ $claim->transfer_date ? $claim->transfer_date->format('d M Y') : '—' }}</strong><small>Status pembayaran</small></article>
    </div>
    <div class="insight-grid" style="grid-template-columns:repeat(2,minmax(0,1fr));margin-bottom:22px">
      <section class="panel"><div class="panel-heading"><div><h3>Informasi merchant</h3><p class="muted">Detail kontak yang dikirim bersama form</p></div></div><dl class="detail-list">
        <div><dt>Nama PIC</dt><dd>{{ $claim->pic_name }}</dd></div><div><dt>Nomor HP PIC</dt><dd>{{ $claim->pic_phone }}</dd></div><div><dt>Tanggal transaksi</dt><dd>{{ $claim->transaction_date?->format('d M Y') ?? '—' }}</dd></div><div><dt>Waktu submit</dt><dd>{{ $claim->created_at->format('d M Y, H:i:s') }}</dd></div>
      </dl></section>
      <section class="panel"><div class="panel-heading"><div><h3>Informasi rekening</h3><p class="muted">Detail rekening Mandiri penerima</p></div></div><dl class="detail-list">
        <div><dt>Nama pemilik rekening</dt><dd>{{ $claim->account_holder_name }}</dd></div><div><dt>Nomor rekening Mandiri</dt><dd>{{ $claim->mandiri_account_number }}</dd></div><div><dt>Status transfer</dt><dd>{{ $claim->transfer_status==='transferred'?'Sudah ditransfer':'Belum ditransfer' }}</dd></div><div><dt>Tanggal transfer</dt><dd>{{ $claim->transfer_date?->format('d M Y') ?? 'Belum tersedia' }}</dd></div>
      </dl></section>
    </div>
    <section class="panel" style="margin-bottom:22px"><div class="panel-heading"><div><h3>Rincian voucher</h3><p class="muted">Kode voucher dikelompokkan berdasarkan jenis</p></div></div><div class="type-list">
      @foreach(['A','B'] as $type)@php($codes=$claim->vouchers->where('voucher_type',$type))<div class="type-row"><span class="type-letter type-{{ strtolower($type) }}">{{ $type }}</span><div class="type-info"><strong>Voucher {{ $type }}</strong><small>{{ $codes->count() }} kode</small><div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px">@forelse($codes as $voucher)<span class="pill pill-{{ strtolower($type) }}">{{ $voucher->voucher_code }}</span>@empty<span class="muted">Tidak ada kode</span>@endforelse</div></div><b>{{ $codes->count() }}</b></div>@endforeach
    </div></section>
    <section class="panel"><div class="panel-heading"><div><h3>Bukti foto voucher</h3><p class="muted">File bukti yang diunggah saat pengajuan</p></div>@if($claim->proof_image_path)<a class="button button-outline" href="{{ Storage::disk('public')->url($claim->proof_image_path) }}" target="_blank" rel="noopener">Buka gambar ↗</a>@endif</div>
      @if($claim->proof_image_path)<a href="{{ Storage::disk('public')->url($claim->proof_image_path) }}" target="_blank" rel="noopener"><img src="{{ Storage::disk('public')->url($claim->proof_image_path) }}" alt="Bukti voucher {{ $claim->store->name }}" style="display:block;max-width:100%;max-height:560px;object-fit:contain;border-radius:12px;background:#f4f5f8"></a>@else<p class="empty-state">Tidak ada gambar bukti untuk klaim ini.</p>@endif
    </section>
  </div>
</section>
@endsection
