@extends('layouts.app')

@section('title', $jadwal->nama_siswa.' - Ruang Cerita')

@section('content')
<style>
    .detail-grid { display: grid; grid-template-columns: 1.2fr 1fr; gap: 28px; }
    .foto-grid { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 12px; }
    .foto-grid img { width: 120px; height: 120px; object-fit: cover; border-radius: var(--radius-sm); }
    @media (max-width: 800px) { .detail-grid { grid-template-columns: 1fr; } }
</style>

<div class="detail-grid reveal reveal-1">
    <div class="card">
        <h2 style="font-size:24px;">{{ $jadwal->nama_siswa }}</h2>
        <p style="color:#6B5A85; margin-top:4px;">
            Kelas {{ $jadwal->kelas }} {{ $jadwal->jurusan }} ·
            {{ $jadwal->tanggal }} · {{ $jadwal->jam_mulai }}-{{ $jadwal->jam_selesai }} ·
            {{ ucfirst($jadwal->jenis_kedatangan) }} · {{ $jadwal->kategoriMasalah->nama }}
        </p>

        @if ($jadwal->jenis_kedatangan === 'kelompok')
            <p><strong>Anggota kelompok:</strong> {{ $jadwal->anggota_kelompok }}</p>
        @endif

        <form method="POST" action="{{ route('guru.konseling.catatan', $jadwal) }}" style="margin-top:20px;">
            @csrf
            <label>Catatan / Poin Penting</label>
            <textarea name="catatan" rows="8" style="margin-top:6px; margin-bottom:16px;">{{ $jadwal->catatan->catatan ?? '' }}</textarea>

            <button type="submit" class="btn btn-primary">Simpan Catatan</button>
        </form>

        <a href="{{ route('guru.konseling.pdf', $jadwal) }}" target="_blank" class="btn btn-ghost" style="margin-top:14px; display:inline-block;">
            Export PDF (A4)
        </a>
    </div>

    <div class="card">
        <h3 style="font-size:17px; margin-bottom:12px;">Foto Sesi Konseling</h3>
        <form method="POST" action="{{ route('guru.konseling.foto', $jadwal) }}" enctype="multipart/form-data">
            @csrf
            <input type="file" name="foto" accept="image/*" capture="environment" required>
            <button type="submit" style="margin-top:10px;">Tambah Foto</button>
        </form>

        <div class="foto-grid">
            @forelse ($jadwal->catatan->foto ?? [] as $f)
                <div style="position:relative;">
                    <a href="{{ route('guru.galeri.foto', $f) }}" target="_blank">
                        <img src="{{ route('guru.galeri.foto', $f) }}" alt="Foto konseling">
                    </a>
                    <form method="POST" action="{{ route('guru.foto.hapus', $f) }}"
                          onsubmit="return confirm('Hapus foto ini?')"
                          style="position:absolute; top:4px; right:4px;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="background:#A23B4C; color:#fff; border:none; border-radius:50%; width:24px; height:24px; font-size:14px; line-height:1; cursor:pointer; padding:0;">×</button>
                    </form>
                </div>
            @empty
                <p style="color:#6B5A85; font-size:14px;">Belum ada foto.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
