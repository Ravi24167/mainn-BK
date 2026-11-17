@extends('layouts.app')

@section('title', 'Dashboard Guru - Ruang Cerita')

@section('content')
<style>
    .tab-pill {
        display: inline-block; padding: 8px 16px; border-radius: 999px; margin-right: 6px; margin-bottom: 6px;
        font-weight: 600; font-size: 13.5px; text-decoration: none;
        border: 1.5px solid #E4D9F5; transition: all .15s ease;
    }
    .tab-pill.aktif { background: var(--ungu); color: #fff; border-color: var(--ungu); }
    .tab-row { margin-bottom: 10px; }
    .dashboard-header {
        display: flex; justify-content: space-between; align-items: flex-end; gap: 18px;
        margin-bottom: 24px;
    }
    .dashboard-kicker { color: #8A7A9E; font-size: 12px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; margin-bottom: 8px; }
    .dashboard-subtitle { color: #786d88; margin-top: 8px; font-size: 14px; }
    .dashboard-actions { display: flex; flex-wrap: wrap; gap: 8px; }
    .dashboard-actions .btn { padding: 10px 16px; font-size: 13px; }
    .summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 26px; }
    .summary-card { padding: 18px 20px; background: #fff; border: 1px solid #F0E9FA; border-radius: 18px; }
    .summary-label { color: #786d88; font-size: 13px; }
    .summary-value { font-size: 26px; font-weight: 700; color: var(--ungu-gelap); margin-top: 6px; }
    .filter-panel { padding: 16px 18px 8px; margin-bottom: 16px; background: #fff; border: 1px solid #F0E9FA; border-radius: 18px; }
    .tanggal-section { margin-top: 28px; }
    .tanggal-title {
        font-family: 'Fraunces', serif; font-size: 17px; color: var(--ungu-gelap);
        margin-bottom: 12px; display: flex; align-items: center; gap: 10px;
    }
    .tanggal-title::after { content: ""; flex: 1; height: 1px; background: #F0E9FA; }
    .kartu-siswa {
        background: var(--putih); border: 1px solid #F0E9FA; border-left: 3px solid var(--ungu);
        border-radius: 18px; padding: 18px 20px; margin-bottom: 10px;
        display: flex; justify-content: space-between; align-items: center; gap: 16px;
        transition: transform .15s ease;
    }
    .kartu-siswa:hover { transform: translateY(-2px); }
    .badge { padding: 5px 14px; border-radius: 999px; font-size: 12.5px; font-weight: 600; }
    .badge-menunggu { background: #FDF3D9; color: #8A6D1F; }
    .badge-disetujui { background: #E4F5EA; color: #2A6B49; }
    .badge-ditolak { background: #FBE9EC; color: #A23B4C; }
    .aksi-kecil { padding: 8px 16px; font-size: 13px; border-radius: 999px; border: none; cursor: pointer; font-weight: 600; }
    .aksi-terima { background: var(--ungu); color: #fff; }
    .aksi-tolak { background: transparent; border: 1.5px solid #E4D9F5; color: var(--ungu-gelap); }
    .rekap-card { background: var(--putih); border: 1px solid #F0E9FA; border-radius: 20px; padding: 24px; margin-top: 32px; }
    .rekap-header { display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
    .rekap-header p { color: #786d88; font-size: 13px; margin: 5px 0 0; }
    .empty-state {
        text-align: center; padding: 50px 20px; color: #9A8CB0;
        background: var(--ungu-muda); border-radius: var(--radius-lg); margin-top: 20px;
    }
    .empty-state .icon { font-size: 36px; margin-bottom: 8px; }
    @media (max-width: 720px) {
        .dashboard-header { align-items: flex-start; flex-direction: column; }
        .summary-grid { grid-template-columns: 1fr; gap: 9px; }
        .summary-card { padding: 14px 16px; }
        .summary-value { font-size: 22px; }
        .kartu-siswa { flex-direction: column; align-items: flex-start; }
        .kartu-siswa > div:last-child { width: 100%; flex-wrap: wrap; }
        .rekap-header { align-items: flex-start; flex-direction: column; }
    }
</style>

<div class="dashboard-header reveal reveal-1">
    <div>
        <p class="dashboard-kicker">Ruang kerja guru BK</p>
        <h1 style="font-size:30px;">Halo, {{ auth()->user()->name }}</h1>
        <p class="dashboard-subtitle">Kelola jadwal dan catatan konseling dalam satu tempat.</p>
    </div>
    <div class="dashboard-actions">
        <a href="{{ route('guru.ketersediaan.index') }}" class="btn btn-ghost">Atur jam konseling</a>
        <a href="{{ route('guru.profil') }}" class="btn btn-ghost">Profil saya</a>
    </div>
</div>

<div class="summary-grid reveal reveal-1">
    <div class="summary-card"><div class="summary-label">Konseling bulan ini</div><div class="summary-value">{{ $jadwalPerTanggal->flatten(1)->count() }}</div></div>
    <div class="summary-card"><div class="summary-label">Menunggu konfirmasi</div><div class="summary-value">{{ $jumlahMenunggu }}</div></div>
    <div class="summary-card"><div class="summary-label">Jadwal disetujui</div><div class="summary-value">{{ $jumlahDisetujui }}</div></div>
</div>

<div class="filter-panel reveal reveal-1">
<div class="tab-row" aria-label="Pilih tahun">
    @foreach ($tahunTersedia as $t)
        <a href="{{ route('guru.dashboard', ['tahun' => $t, 'bulan' => $bulan]) }}" class="tab-pill {{ $t == $tahun ? 'aktif' : '' }}">{{ $t }}</a>
    @endforeach
</div>

<div class="tab-row" aria-label="Pilih bulan">
    @foreach ($namaBulan as $angka => $label)
        <a href="{{ route('guru.dashboard', ['tahun' => $tahun, 'bulan' => $angka]) }}" class="tab-pill {{ $angka == $bulan ? 'aktif' : '' }}">{{ $label }}</a>
    @endforeach
</div>
</div>

@if ($jadwalPerTanggal->isEmpty())
    <div class="empty-state reveal reveal-2">
        <div class="icon">📅</div>
        <p>Belum ada jadwal konseling di bulan ini.</p>
    </div>
@else
    @foreach ($jadwalPerTanggal as $tanggal => $daftarJadwal)
        <div class="tanggal-section reveal reveal-2">
            <div class="tanggal-title">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}</div>

            @foreach ($daftarJadwal as $j)
                <div class="kartu-siswa">
                    <div>
                        <strong>{{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }}</strong> —
                        <a href="{{ route('guru.siswa.riwayat', $j->nama_siswa) }}" style="color:var(--ungu-gelap); font-weight:600; text-decoration:none;">{{ $j->nama_siswa }}</a>
                        <span class="badge badge-{{ $j->status }}">{{ ucfirst($j->status) }}</span>
                        <br>
                        <small style="color:#6B5A85;">{{ ucfirst($j->jenis_kedatangan) }} · {{ $j->kategoriMasalah->nama }} · Kelas {{ $j->kelas }} {{ $j->jurusan }}</small>
                    </div>
                    <div style="display:flex; gap:8px;">
                        @if ($j->status === 'menunggu')
                            <form method="POST" action="{{ route('guru.jadwal.approve', $j) }}">
                                @csrf
                                <button type="submit" class="aksi-kecil aksi-terima">Terima</button>
                            </form>
                            <form method="POST" action="{{ route('guru.jadwal.reject', $j) }}" style="display:flex; gap:6px; align-items:center;"
                                  onsubmit="return isiAlasan(this)">
                                @csrf
                                <input type="hidden" name="alasan">
                                <button type="submit" class="aksi-kecil aksi-tolak">Tolak</button>
                            </form>
                        @else
                            <a href="{{ route('guru.konseling.show', $j) }}" class="btn btn-ghost" style="padding:8px 16px; font-size:13px;">Buka Detail</a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
@endif

<div class="rekap-card reveal reveal-3">
    <div class="rekap-header">
        <div><h3>Rekap persoalan siswa</h3><p>Ringkasan kategori pada {{ $namaBulan[$bulan] }} {{ $tahun }}</p></div>
    </div>
    <canvas id="chartMasalah" height="90" aria-label="Diagram rekap kategori persoalan"></canvas>
</div>

<div class="dashboard-actions reveal reveal-4" style="margin-top:18px;">
    <a href="{{ route('guru.galeri') }}" class="btn btn-ghost">Buka galeri konseling</a>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
    new Chart(document.getElementById('chartMasalah'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($rekapKategori->keys()) !!},
            datasets: [{
                label: 'Jumlah Siswa',
                data: {!! json_encode($rekapKategori->values()) !!},
                backgroundColor: '#9B7FD4',
                borderRadius: 8,
            }]
        },
        options: { plugins: { legend: { display: false } } }
    });
</script>
@endsection
