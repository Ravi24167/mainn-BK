<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        h2 { color: #7C5CBF; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #EDE4F8; color: #7C5CBF; }
    </style>
</head>
<body>
    <h2>Rekap Konseling - Ruang Cerita</h2>
    <table>
        <thead>
            <tr>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Tanggal</th>
                <th>Jam</th>
                <th>Guru</th>
                <th>Ulasan / Catatan Guru</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($jadwal as $j)
                <tr>
                    <td>{{ $j->nama_siswa }}</td>
                    <td>{{ $j->kelas }} {{ $j->jurusan }}</td>
                    <td>{{ $j->tanggal }}</td>
                    <td>{{ $j->jam_mulai }} - {{ $j->jam_selesai }}</td>
                    <td>{{ $j->guru->name }}</td>
                    <td>{{ $j->catatan->catatan ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
