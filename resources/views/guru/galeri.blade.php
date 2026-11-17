@extends('layouts.app')

@section('title', 'Galeri Konseling - Ruang Cerita')

@section('content')
<style>
    .tab-pill {
        display: inline-block; padding: 8px 16px; border-radius: 999px; margin-right: 6px; margin-bottom: 6px;
        font-weight: 600; font-size: 13.5px; text-decoration: none;
        border: 1.5px solid #E4D9F5; transition: all .15s ease;
    }
    .tab-pill.aktif { background: var(--ungu); color: #fff; border-color: var(--ungu); }
    .tab-row { margin-bottom: 10px; }
    .galeri-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px; margin-top: 20px; }
    .galeri-item {
        background: var(--putih); border-radius: var(--radius-md); overflow: hidden;
        border: 1px solid #F0E9FA; transition: transform .18s ease, box-shadow .18s ease;
    }
    .galeri-item:hover { transform: translateY(-4px); box-shadow: 0 14px 30px -14px rgba(74,56,104,.25); }
    .galeri-item img { width: 100%; height: 160px; object-fit: cover; display: block; cursor: pointer; }
    .galeri-caption { padding: 12px; font-size: 13px; color: var(--ungu-gelap); }
    .galeri-caption small { color: #9A8CB0; }
    .galeri-meta { display: grid; gap: 4px; margin-top: 8px; color: #766b85; font-size: 12px; }
    .galeri-detail {
        display: inline-flex; margin-top: 12px; padding: 7px 12px; border-radius: 999px;
        background: var(--ungu-muda); color: var(--ungu-gelap); font-weight: 600;
        text-decoration: none; transition: background .15s ease;
    }
    .galeri-detail:hover { background: #DCCBF2; }
    .empty-state {
        text-align: center; padding: 60px 20px; color: #9A8CB0;
        background: var(--ungu-muda); border-radius: var(--radius-lg); margin-top: 24px;
    }
    .empty-state .icon { font-size: 40px; margin-bottom: 10px; }
</style>

<h1 class="reveal reveal-1" style="font-size:28px; margin-bottom:14px;">Galeri Konseling</h1>

<div class="reveal reveal-2 tab-row">
    @foreach ($tahunTersedia as $t)
        <a href="{{ route('guru.galeri', ['tahun' => $t, 'bulan' => $bulan]) }}" class="tab-pill {{ $t == $tahun ? 'aktif' : '' }}">{{ $t }}</a>
    @endforeach
</div>

<div class="reveal reveal-2 tab-row">
    @foreach ($namaBulan as $angka => $label)
        <a href="{{ route('guru.galeri', ['tahun' => $tahun, 'bulan' => $angka]) }}" class="tab-pill {{ $angka == $bulan ? 'aktif' : '' }}">{{ $label }}</a>
    @endforeach
</div>

@if ($foto->isEmpty())
    <div class="empty-state reveal reveal-3">
        <div class="icon">🗂️</div>
        <p>Belum ada foto konseling di bulan ini.</p>
    </div>
@else
    <div class="galeri-grid reveal reveal-3">
        @foreach ($foto as $f)
            <div class="galeri-item">
                <a href="{{ route('guru.galeri.foto', $f) }}" target="_blank">
                    <img src="{{ route('guru.galeri.foto', $f) }}" alt="Foto konseling">
                </a>
                <div class="galeri-caption">
                    @php($jadwal = $f->catatan->jadwal)
                    {{ $jadwal->nama_siswa ?? '-' }}
                    @if ($jadwal)
                        <br><small>Kelas {{ $jadwal->kelas }} {{ $jadwal->jurusan }}</small>
                        <div class="galeri-meta">
                            <span>Tanggal: {{ \Illuminate\Support\Carbon::parse($jadwal->tanggal)->format('d/m/Y') }}</span>
                            <span>Jam: {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}</span>
                        </div>
                        <a href="{{ route('guru.konseling.show', $jadwal) }}" class="galeri-detail">Selengkapnya</a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
