@extends('layouts.app')

@section('title', 'Cek Status - Ruang Cerita')

@section('content')
<style>
    .cari-shell { max-width: 560px; margin: 0 auto 40px; text-align: center; }
    .kartu-riwayat {
        background: var(--putih); border: 1px solid #F0E9FA; border-radius: var(--radius-md);
        padding: 18px 22px; margin-bottom: 12px; text-align: left;
        display: flex; justify-content: space-between; align-items: center; gap: 12px;
    }
    .badge { padding: 5px 14px; border-radius: 999px; font-size: 12.5px; font-weight: 600; white-space: nowrap; }
    .badge-menunggu { background: #FDF3D9; color: #8A6D1F; }
    .badge-disetujui { background: #E4F5EA; color: #2A6B49; }
    .badge-ditolak { background: #FBE9EC; color: #A23B4C; }
</style>

<div class="cari-shell reveal reveal-1">
    <h1 style="font-size:28px;">Cek Status Pengajuan</h1>
    <p style="color:#6B5A85; margin-bottom:22px;">Ketik nama kamu persis seperti waktu ngisi form.</p>
    <form method="GET" action="{{ route('siswa.jadwal.cek') }}" style="display:flex; gap:10px;">
        <input type="text" name="nama" placeholder="Nama lengkap kamu" value="{{ $nama }}" required>
        <button type="submit" class="btn btn-primary">Cari</button>
    </form>
</div>

@if ($nama)
    <div class="reveal reveal-2" style="max-width:640px; margin:0 auto;">
        @forelse ($jadwal as $j)
            <div class="kartu-riwayat">
                <div>
                    <strong>{{ \Carbon\Carbon::parse($j->tanggal)->translatedFormat('d F Y') }}</strong>
                    · {{ $j->jam_mulai }} - {{ $j->jam_selesai }}
                    <br>
                    <small style="color:#6B5A85;">Dengan {{ $j->guru->name }} · {{ $j->kategoriMasalah->nama }}</small>
                    @if ($j->status === 'ditolak' && $j->alasan_ditolak)
                        <div style="margin-top:8px; background:#FBE9EC; color:#A23B4C; padding:10px 14px; border-radius:10px; font-size:13.5px;">
                            <strong>Alasan ditolak:</strong> {{ $j->alasan_ditolak }}
                        </div>
                    @endif
                </div>
                <span class="badge badge-{{ $j->status }}">{{ ucfirst($j->status) }}</span>
            </div>
        @empty
            <p style="text-align:center; color:#6B5A85;">Gak ketemu jadwal atas nama itu. Coba cek ejaannya lagi ya.</p>
        @endforelse
    </div>
@endif
@endsection
