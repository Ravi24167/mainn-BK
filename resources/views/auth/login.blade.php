@extends('layouts.app')

@section('title', 'Login Guru/Admin - Ruang Cerita')

@section('content')
<div class="reveal reveal-1" style="max-width:400px; margin:20px auto; background:var(--ungu-muda); padding:36px; border-radius:var(--radius-lg);">
    <h2 style="text-align:center; margin-bottom:26px;">Login Guru & Admin</h2>

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div style="margin-bottom:16px;">
            <label>Email</label><br>
            <input type="email" name="email" required autofocus style="margin-top:6px;">
        </div>
        <div style="margin-bottom:16px;">
            <label>Password</label><br>
            <input type="password" name="password" required style="margin-top:6px;">
        </div>
        <label style="font-size:13px; display:flex; align-items:center; gap:6px; font-weight:400; margin-bottom:20px;">
            <input type="checkbox" name="ingat" style="width:auto;"> Ingat saya
        </label>
        <button type="submit" class="btn btn-primary" style="width:100%;">Masuk</button>
    </form>

    <p style="text-align:center; margin-top:18px; font-size:13px; color:#6B5A85;">
        Halaman ini khusus untuk guru BK dan admin sekolah.
    </p>
</div>
@endsection
