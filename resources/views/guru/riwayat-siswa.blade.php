@extends('layouts.app')

@section('title', $nama.' - Riwayat Konseling')

@section('content')
<style>
    .kunjungan {
        background: var(--putih); border: 1px solid #F0E9FA; border-left: 3px solid var(--ungu);
        border-radius: 16px; padding: 18px 20px; margin-bottom: 10px;
    }
    .tahun-heading { margin:28px 0 14px; font-size:23px; }
    .bulan-heading { color:#786d88; font-size:15px; margin:18px 0 10px; font-weight:700; }
    .kunjungan-nomor { color: var(--ungu); font-size: 11px; text-transform:uppercase; letter-spacing:.08em; font-weight:700; }
    .badge { padding: 5px 14px; border-radius: 999px; font-size: 12.5px; font-weight: 600; }
    .badge-menunggu { background: #FDF3D9; color: #8A6D1F; }
    .badge-disetujui { background: #E4F5EA; color: #2A6B49; }
    .badge-ditolak { background: #FBE9EC; color: #A23B4C; }
</style>

<p class="reveal reveal-1" style="color:#8A7A9E; font-size:12px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; margin:0 0 8px;">Arsip konseling</p>
<h1 class="reveal reveal-1" style="font-size:30px;">Riwayat {{ $nama }}</h1>
<p class="reveal reveal-1" style="color:#786d88; margin:8px 0 24px;">{{ $riwayat->count() }} sesi, tersusun menurut tahun dan bulan.</p>

<div class="reveal reveal-2">
    @foreach ($riwayatPerTahun as $tahun => $daftarBulan)
        <h2 class="tahun-heading">{{ $tahun }}</h2>
        @foreach ($daftarBulan as $bulan => $daftarKunjungan)
            <h3 class="bulan-heading">{{ \Carbon\Carbon::createFromDate((int) $tahun, (int) $bulan, 1)->translatedFormat('F Y') }}</h3>
            @foreach ($daftarKunjungan as $r)
        <div class="kunjungan">
            <div class="kunjungan-nomor">{{ \Carbon\Carbon::parse($r->tanggal)->translatedFormat('l, d F Y') }}</div>
            <p style="margin:4px 0 10px;">
                <strong>{{ substr($r->jam_mulai, 0, 5) }}–{{ substr($r->jam_selesai, 0, 5) }}</strong>
                ·
                Kelas {{ $r->kelas }} {{ $r->jurusan }} ·
                {{ $r->kategoriMasalah->nama }} ·
                <span class="badge badge-{{ $r->status }}">{{ ucfirst($r->status) }}</span>
            </p>
            @if ($r->catatan && $r->catatan->catatan)
                <p style="font-size:14px; color:#6B5A85; margin-bottom:10px;">{{ $r->catatan->catatan }}</p>
            @endif
            <a href="{{ route('guru.konseling.show', $r) }}" class="btn btn-ghost" style="padding:8px 16px; font-size:13px;">Buka Detail</a>
        </div>
            @endforeach
        @endforeach
    @endforeach
</div>
@endsection
