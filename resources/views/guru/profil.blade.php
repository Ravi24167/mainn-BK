@extends('layouts.app')

@section('title', 'Profil Saya - Ruang Cerita')

@section('content')
<style>
    .profil-shell { max-width: 520px; background: var(--putih); padding: 32px; border:1px solid #F0E9FA; border-radius: 24px; text-align: center; box-shadow:0 18px 50px -44px rgba(74,56,104,.5); }
    .foto-preview {
        width: 152px; height: 152px; border-radius: 50%; object-fit: cover;
        border: 5px solid var(--putih); box-shadow: 0 12px 30px -12px rgba(74,56,104,.35);
        margin-bottom: 18px;
    }
    .foto-placeholder {
        display:flex; align-items:center; justify-content:center;
        background:linear-gradient(145deg, #F5EFFF, #E6DDF3);
        color:var(--ungu-gelap); font-family:'Fraunces', serif;
        font-size:52px; font-weight:600; margin-left:auto; margin-right:auto;
    }
    .profil-subtitle { color:#786d88; margin:7px 0 24px; font-size:14px; }
    .foto-input { margin-bottom:14px; padding:10px; background:#FCFAFE; }
    .profil-note { color:#8A7A9E; font-size:12px; margin-top:12px; }
    @media(max-width:520px) { .profil-shell { padding:24px 18px; } }
</style>

<p class="reveal reveal-1" style="color:#8A7A9E; font-size:12px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; margin:0 0 8px;">Akun guru</p>
<h1 class="reveal reveal-1" style="font-size:30px; margin-bottom:20px;">Profil saya</h1>

<div class="profil-shell reveal reveal-2">
    @if ($fotoProfil)
        <img class="foto-preview" src="{{ asset('storage/'.$fotoProfil) }}" alt="Foto profil {{ auth()->user()->name }}">
    @else
        <div class="foto-preview foto-placeholder" aria-label="Inisial {{ auth()->user()->name }}">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth()->user()->name, 0, 1)) }}</div>
    @endif

    <h3 style="margin-bottom:4px;">{{ auth()->user()->name }}</h3>
    <p class="profil-subtitle">Perbarui foto yang ditampilkan kepada siswa di halaman depan dan pilihan guru.</p>

    <form method="POST" action="{{ route('guru.profil.simpan') }}" enctype="multipart/form-data">
        @csrf
        <label for="foto" style="display:block; text-align:left; margin-bottom:7px;">Pilih foto baru</label>
        <input id="foto" type="file" name="foto" accept="image/*" required class="foto-input">
        <button type="submit" class="btn btn-primary" style="width:100%;">Simpan foto profil</button>
        <p class="profil-note">Format gambar umum, ukuran maksimal 5 MB.</p>
    </form>
</div>
@endsection
