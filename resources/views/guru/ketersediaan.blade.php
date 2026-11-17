@extends('layouts.app')

@section('title', 'Atur Jam Buka - Ruang Cerita')

@section('content')
<style>
    .availability-layout { display: grid; grid-template-columns: minmax(270px, .85fr) 1.15fr; gap: 24px; align-items: start; }
    .form-shell { background: #fff; padding: 24px; border: 1px solid #F0E9FA; border-radius: 20px; }
    .availability-intro { color:#786d88; margin:8px 0 22px; max-width:62ch; }
    .form-hint { color:#786d88; font-size:12px; margin:12px 0 0; line-height:1.6; }
    .availability-list { background:#fff; border:1px solid #F0E9FA; border-radius:20px; padding:22px; }
    .daftar-item {
        background: #FCFAFE; border: 1px solid #F0E9FA; border-radius: 14px;
        padding: 14px 16px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; gap:12px;
    }
    .time-range { display:block; margin-top:3px; color:#786d88; font-size:13px; }
    .tab-pill {
        display: inline-block; padding: 8px 16px; border-radius: 999px; margin-right: 6px; margin-bottom: 6px;
        font-weight: 600; font-size: 13.5px; text-decoration: none;
        border: 1.5px solid #E4D9F5; transition: all .15s ease;
    }
    .tab-pill.aktif { background: var(--ungu); color: #fff; border-color: var(--ungu); }
    @media(max-width:760px) { .availability-layout { grid-template-columns:1fr; } }
</style>

<div class="reveal reveal-1">
    <p style="color:#8A7A9E; font-size:12px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; margin:0 0 8px;">Jadwal layanan</p>
    <h1 style="font-size:30px;">Atur jam konseling</h1>
    <p class="availability-intro">Tentukan tanggal dan rentang waktu saat Ibu tersedia. Siswa hanya bisa memilih waktu yang sudah dibuka.</p>
</div>

<div class="availability-layout">
<form method="POST" action="{{ route('guru.ketersediaan.store') }}" class="form-shell reveal reveal-2">
    @csrf
    <h3 style="font-size:20px; margin-bottom:18px;">Buka waktu baru</h3>
    <label for="tanggal">Tanggal</label>
    <input id="tanggal" type="date" name="tanggal" value="{{ old('tanggal') }}" min="{{ now()->toDateString() }}" required style="margin-top:6px; margin-bottom:14px;">

    <div style="display:flex; gap:10px; margin-bottom:16px;">
        <div style="flex:1;">
            <label for="jam_buka">Mulai</label>
            <input id="jam_buka" type="time" name="jam_buka" value="{{ old('jam_buka') }}" required style="margin-top:6px;">
        </div>
        <div style="flex:1;">
            <label for="jam_tutup">Selesai</label>
            <input id="jam_tutup" type="time" name="jam_tutup" value="{{ old('jam_tutup') }}" required style="margin-top:6px;">
        </div>
    </div>

    <button type="submit" class="btn btn-primary" style="width:100%;">Simpan waktu tersedia</button>
    <p class="form-hint">Contoh: 08.00–09.00 akan menyediakan slot sesi 40 menit. Rentang pada tanggal yang sama tidak boleh bertumpang tindih.</p>
</form>

<section class="availability-list reveal reveal-3">
<h3 style="font-size:20px; margin-bottom:16px;">Waktu yang sudah dibuka</h3>

<div style="margin-bottom:16px;">
    @foreach ($tahunTersedia as $t)
        <a href="{{ route('guru.ketersediaan.index', ['tahun' => $t]) }}" class="tab-pill {{ $t == $tahun ? 'aktif' : '' }}">{{ $t }}</a>
    @endforeach
</div>

<div>
    @forelse ($daftar as $d)
        <div class="daftar-item">
            <span><strong>{{ \Carbon\Carbon::parse($d->tanggal)->translatedFormat('l, d F Y') }}</strong><span class="time-range">{{ substr($d->jam_buka, 0, 5) }} – {{ substr($d->jam_tutup, 0, 5) }}</span></span>
            <form method="POST" action="{{ route('guru.ketersediaan.hapus', $d) }}" onsubmit="return confirm('Hapus jam buka ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" style="background:#A23B4C; color:#fff; border:none; border-radius:8px; padding:6px 14px; font-size:13px; cursor:pointer;">Hapus</button>
            </form>
        </div>
    @empty
        <p style="color:#786d88;">Belum ada jadwal yang dibuka di tahun {{ $tahun }}.</p>
    @endforelse
</div>
</section>
</div>
@endsection
