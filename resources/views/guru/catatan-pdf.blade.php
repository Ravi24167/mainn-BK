<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 13px; color: #333; }
        h2 { color: #7C5CBF; margin-bottom: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        td { padding: 4px 0; vertical-align: top; }
        .label { width: 160px; font-weight: bold; }
        .kotak { border: 1px solid #ccc; border-radius: 6px; padding: 10px; margin-top: 6px; }
    </style>
</head>
<body>
    <h2>Catatan Sesi Konseling</h2>
    <p>Ruang Cerita Sekolah</p>

    <table>
        <tr><td class="label">Nama Siswa</td><td>: {{ $jadwal->nama_siswa }}</td></tr>
        <tr><td class="label">Kelas</td><td>: {{ $jadwal->kelas }}</td></tr>
        <tr><td class="label">Jurusan</td><td>: {{ $jadwal->jurusan }}</td></tr>
        <tr><td class="label">Tanggal</td><td>: {{ $jadwal->tanggal }}</td></tr>
        <tr><td class="label">Jam</td><td>: {{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }}</td></tr>
        <tr><td class="label">Jenis Kedatangan</td><td>: {{ ucfirst($jadwal->jenis_kedatangan) }}</td></tr>
        <tr><td class="label">Kategori Persoalan</td><td>: {{ $jadwal->kategoriMasalah->nama }}</td></tr>
        <tr><td class="label">Guru BK</td><td>: {{ $jadwal->guru->name }}</td></tr>
    </table>

    <p><strong>Catatan / Poin Penting:</strong></p>
    <div class="kotak">{{ $jadwal->catatan->catatan ?? '-' }}</div>

    <br><br>
    <p style="text-align:right;">{{ now()->format('d F Y') }}<br>{{ $jadwal->guru->name }}</p>
</body>
</html>
