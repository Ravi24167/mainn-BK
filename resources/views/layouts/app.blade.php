<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Ruang Cerita')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;1,9..144,500&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
    <nav>
        <a href="{{ route('siswa.dashboard') }}" class="logo-mark"><span class="dot"></span> Ruang Cerita</a>
        <div class="nav-links">
            @auth
                @if (auth()->user()->isGuru())
                    <a href="{{ route('guru.dashboard') }}">Dashboard</a>
                    <a href="{{ route('guru.ketersediaan.index') }}">Jam Buka</a>
                    <a href="{{ route('guru.galeri') }}">Galeri</a>
                    <a href="{{ route('guru.profil') }}">Profil</a>
                @elseif (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                    <a href="{{ route('admin.rekap.pdf') }}">Export Rekap</a>
                @endif
                <span style="opacity:.6; font-size:14px;">Hai, {{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="#" onclick="event.preventDefault(); this.closest('form').submit();">Keluar</a>
                </form>
            @else
                <a href="{{ route('siswa.dashboard') }}">Beranda</a>
                <a href="{{ route('siswa.jadwal.create') }}">Ajukan Konseling</a>
                <a href="{{ route('siswa.jadwal.cek') }}">Cek Status</a>
                <a href="{{ route('login') }}" class="btn btn-ghost" style="padding:9px 18px; font-size:13.5px;">Login Guru/Admin</a>
            @endauth
        </div>
    </nav>

    <div class="page-content">
        @if (session('sukses'))
            <div class="flash-sukses reveal">{{ session('sukses') }}</div>
        @endif
        @if ($errors->any())
            <div class="flash-error reveal">{{ $errors->first() }}</div>
        @endif

        @yield('content')
    </div>
</body>
</html>
