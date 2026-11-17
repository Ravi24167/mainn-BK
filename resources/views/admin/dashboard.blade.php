@extends('layouts.app')

@section('title', 'Dashboard Admin - Ruang Cerita')

@section('content')
<style>
    table.rekap { width: 100%; border-collapse: collapse; margin-top: 20px; }
    table.rekap th {
        text-align: left; font-size: 12.5px; text-transform: uppercase; letter-spacing: .03em;
        color: #8A7A9E; padding: 10px 14px; border-bottom: 2px solid #F0E9FA;
    }
    table.rekap td { padding: 14px; border-bottom: 1px solid #F5F0FA; font-size: 14.5px; }
    table.rekap tr:hover { background: #FBF9FD; }
    .badge { padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 600; }
    .badge-menunggu { background: #FDF3D9; color: #8A6D1F; }
    .badge-disetujui { background: #E4F5EA; color: #2A6B49; }
    .badge-ditolak { background: #FBE9EC; color: #A23B4C; }
</style>

<div class="reveal reveal-1" style="display:flex; justify-content:space-between; align-items:center;">
    <h1 style="font-size:28px;">Rekap Konseling</h1>
    <a href="{{ route('admin.rekap.pdf') }}" target="_blank" class="btn btn-primary">Export PDF</a>
</div>

<div class="reveal reveal-2">
    <table class="rekap">
        <thead>
            <tr>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Tanggal</th>
                <th>Jam</th>
                <th>Guru</th>
                <th>Status</th>
                <th>Ulasan Guru</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($jadwal as $j)
                <tr>
                    <td>{{ $j->nama_siswa }}</td>
                    <td>{{ $j->kelas }} {{ $j->jurusan }}</td>
                    <td>{{ $j->tanggal }}</td>
                    <td>{{ $j->jam_mulai }} - {{ $j->jam_selesai }}</td>
                    <td>{{ $j->guru->name }}</td>
                    <td><span class="badge badge-{{ $j->status }}">{{ ucfirst($j->status) }}</span></td>
                    <td>{{ $j->catatan->catatan ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center; color:#6B5A85;">Belum ada data konseling.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:20px;">{{ $jadwal->links() }}</div>
</div>
@endsection
