@extends('layouts.app')

@section('title', 'Ruang Cerita — Cerita Kamu Aman di Sini')

@push('styles')
    @vite('resources/css/lavender-theme.css')
@endpush

@section('content')
<style>
    .landing-home .reveal {
        animation-duration: .8s;
        animation-timing-function: cubic-bezier(.2, .75, .25, 1);
    }

    .hero {
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(280px, .85fr);
        gap: clamp(24px, 5vw, 56px);
        align-items: center;
        position: relative;
        isolation: isolate;
        padding: clamp(32px, 5vw, 58px);
        background: linear-gradient(130deg, #fff 0%, #f7f2fc 100%);
        border: 1px solid #F0E9FA;
        border-radius: 36px;
        box-shadow: 0 24px 70px -48px rgba(74,56,104,.42);
    }
    .hero::after {
        content: "";
        position: absolute;
        z-index: -1;
        width: 280px;
        height: 280px;
        top: -150px;
        right: 20%;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(237,228,248,.72), transparent 70%);
        pointer-events: none;
    }
    .hero h1 {
        max-width: 13ch;
        font-size: clamp(38px, 5.2vw, 58px);
        letter-spacing: -1.5px;
        line-height: 1.08;
    }
    .hero p.lead {
        font-size: clamp(15px, 1.5vw, 17px);
        color: #726584;
        max-width: 46ch;
        margin: 18px 0 0;
        line-height: 1.8;
    }
    .hero-actions { display: flex; gap: 12px; margin-top: 28px; flex-wrap: wrap; }
    .hero-actions .btn { min-height: 50px; }
    .hero-actions .btn:focus-visible {
        outline: 3px solid rgba(155,127,212,.45);
        outline-offset: 4px;
    }

    .foto-wrap {
        position: relative;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 290px;
    }
    .foto-wrap::before {
        content: "";
        position: absolute;
        width: min(290px, 90%);
        aspect-ratio: 1;
        background: radial-gradient(circle, rgba(237,228,248,.95) 0%, rgba(237,228,248,.35) 48%, transparent 72%);
        border-radius: 50%;
        z-index: 0;
    }
    .guru-foto {
        width: 154px; height: 154px;
        border-radius: 50%;
        object-fit: cover;
        border: 6px solid var(--putih);
        box-shadow: 0 18px 44px -14px rgba(74, 56, 104, 0.36);
        position: relative;
        z-index: 1;
        transition: transform .45s cubic-bezier(.2,.75,.25,1), box-shadow .45s ease;
    }
    .guru-avatar {
        width: 154px; height: 154px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(145deg, #F5EFFF, #E6DDF3);
        color: var(--ungu-gelap); font-family: 'Fraunces', serif;
        font-size: 54px; font-weight: 600;
        border: 6px solid var(--putih);
        box-shadow: 0 18px 44px -14px rgba(74, 56, 104, 0.28);
        position: relative; z-index: 1;
        transition: transform .45s cubic-bezier(.2,.75,.25,1), box-shadow .45s ease;
    }
    .guru-avatar.kiri { margin-right: -36px; z-index: 2; }
    .guru-avatar.kanan { margin-left: -36px; }
    .foto-wrap:hover .guru-avatar.kiri { transform: translateX(-5px) rotate(-3deg); }
    .foto-wrap:hover .guru-avatar.kanan { transform: translateX(5px) rotate(3deg); }
    .guru-avatar .guru-foto {
        position: absolute; inset: 0; width: 100%; height: 100%;
        border: 0; box-shadow: none;
    }
    .guru-label {
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        background: var(--putih);
        padding: 10px 20px;
        border-radius: 999px;
        font-size: 13.5px;
        font-weight: 600;
        box-shadow: 0 6px 16px -6px rgba(74,56,104,.25);
        white-space: nowrap;
        z-index: 3;
    }

    .langkah-section { padding: clamp(42px, 7vw, 68px) 0 12px; }
    .langkah-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
        margin-top: 28px;
        position: relative;
    }
    .langkah {
        background: var(--putih);
        border: 1px solid #F0E9FA;
        border-radius: 24px;
        min-height: 214px;
        padding: 26px 24px;
        transition: transform .35s cubic-bezier(.2,.75,.25,1), box-shadow .35s ease, border-color .35s ease;
    }
    .langkah:hover { transform: translateY(-5px); border-color: #DCCBF2; box-shadow: 0 18px 34px -18px rgba(74,56,104,.28); }
    .langkah .angka {
        width: 42px; height: 42px;
        border-radius: 50%;
        background: linear-gradient(145deg, #F5EFFF, #E6DDF3);
        color: var(--ungu-gelap);
        display: flex; align-items: center; justify-content: center;
        font-family: 'Fraunces', serif;
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
    }
    .langkah h3 { font-size: 18px; margin-bottom: 9px; }
    .langkah p { font-size: 14px; color: #6B5A85; line-height: 1.75; margin: 0; }
    .hero-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        color: var(--ungu);
        font-weight: 700;
        font-size: 12px;
        letter-spacing: .12em;
        text-transform: uppercase;
        margin: 0 0 16px;
    }
    .hero-eyebrow::before {
        content: "";
        width: 22px;
        height: 1px;
        background: currentColor;
    }
    .hero-note { display: inline-flex; align-items: center; gap: 8px; margin-top: 18px; color: #766b85; font-size: 13px; }
    .langkah-kicker { color: #8A7A9E; font-size: 12px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; margin-bottom: 8px; }
    .langkah-section > h2 { font-size: clamp(25px, 3vw, 32px); }
    .teacher-trail {
        position: relative;
        height: 110px;
        margin: 10px 0 0;
        overflow: hidden;
    }
    .teacher-route {
        position: absolute;
        top: 98px;
        right: 0;
        left: 0;
        border-top: 2px dashed rgba(142,107,214,.24);
    }
    .teacher-message {
        position: absolute;
        z-index: 1;
        top: 0;
        left: 50%;
        max-width: min(100%, 300px);
        transform: translateX(-50%);
        color: #4A3868;
        opacity: 0;
        animation: teacher-message-pop 10s ease-in-out infinite;
    }
    .teacher-message summary {
        list-style: none;
        position: relative;
        display: grid;
        gap: 3px;
        padding: 10px 16px;
        border: 1px solid #E8DDF5;
        border-radius: 20px 20px 20px 5px;
        background: #fff;
        box-shadow: 0 10px 22px -12px rgba(74,56,104,.42);
        text-align: center;
        cursor: pointer;
    }
    .teacher-message summary::-webkit-details-marker { display: none; }
    .teacher-message summary::before {
        content: "";
        position: absolute;
        bottom: -8px;
        left: 18px;
        width: 15px;
        height: 12px;
        background: #fff;
        border-right: 1px solid #E8DDF5;
        clip-path: polygon(0 0, 100% 0, 0 100%);
    }
    .teacher-message summary:focus-visible {
        outline: 3px solid rgba(155,127,212,.45);
        outline-offset: 3px;
    }
    .teacher-message[open] {
        opacity: 1;
        animation: none;
    }
    .teacher-message[open] ~ .teacher-pair {
        animation-play-state: paused;
    }
    .welcome-greeting { font-size: 13px; font-weight: 700; line-height: 1.4; }
    .welcome-hint { color: #88799A; font-size: 10px; font-weight: 600; }
    .welcome-greeting-open { display: none; }
    .teacher-message[open] .welcome-greeting-default,
    .teacher-message[open] .welcome-hint { display: none; }
    .teacher-message[open] .welcome-greeting-open { display: inline; }
    .teacher-message[open] ~ .teacher-pair .character-wave {
        animation: teacher-wave-reply .48s ease-in-out infinite alternate;
    }
    .teacher-pair {
        position: absolute;
        top: 48px;
        left: -76px;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        gap: 0;
        width: 76px;
        height: 52px;
        animation: teacher-pair-cross 10s linear infinite;
    }
    .teacher-person {
        display: block;
        width: 38px;
        height: 52px;
        flex: 0 0 38px;
    }
    .teacher-person + .teacher-person { margin-left: -7px; }
    .teacher-person svg {
        display: block;
        width: 38px;
        height: 52px;
        overflow: visible;
        filter: drop-shadow(0 3px 2px rgba(74,56,104,.12));
    }
    .teacher-person .character-wave {
        transform-box: view-box;
        transform-origin: 43px 164px;
        animation: teacher-wave 10s ease-in-out infinite;
    }
    .teacher-person:nth-child(2) .character-wave {
        transform-origin: 137px 164px;
        animation-delay: 0s;
    }
    .teacher-person .character-skin { fill: #F2C6A8; }
    .teacher-person .character-face-detail {
        fill: none;
        stroke: #59455C;
        stroke-linecap: round;
        stroke-width: 2.5;
    }
    .teacher-person .character-cheek { fill: #E99A9C; opacity: .55; }
    .teacher-person .character-shadow { fill: rgba(74,56,104,.12); }
    .teacher-person .character-sparkle { fill: #E7B45A; }
    .teacher-person .character-outfit-left { fill: #9B7FD4; }
    .teacher-person .character-outfit-right { fill: #E79DB4; }
    .teacher-person .character-cloth-detail { fill: rgba(255,255,255,.82); }
    .teacher-person .character-hijab-back { fill: #5D4A7B; }
    .teacher-person .character-hijab-front { fill: #B49ADE; }
    .teacher-person .character-hair { fill: #463A58; }
    .teacher-person .character-hair-highlight { fill: #665475; }
    .teacher-person .character-wave-sleeve-left { stroke: #9B7FD4; }
    .teacher-person .character-wave-sleeve-right { stroke: #E79DB4; }
    .teacher-person .character-hand {
        fill: #F2C6A8;
        stroke: #E9B595;
        stroke-width: 1.5;
    }
    .teacher-person .character-hand-detail {
        fill: none;
        stroke: #D99D85;
        stroke-linecap: round;
        stroke-width: 1.5;
    }
    .teacher-person .character-name { display: none; }
    @keyframes teacher-pair-cross {
        0% { left: -76px; }
        38%, 66% { left: calc(50% - 38px); }
        100% { left: 100%; }
    }
    @keyframes teacher-wave {
        0%, 38%, 66%, 100% { transform: rotate(0); }
        44%, 59% { transform: rotate(-12deg); }
        51%, 64% { transform: rotate(5deg); }
    }
    @keyframes teacher-wave-reply {
        from { transform: rotate(-12deg); }
        to { transform: rotate(5deg); }
    }
    @keyframes teacher-message-pop {
        0%, 42% { opacity: 0; transform: translate(-50%, 6px) scale(.94); }
        45%, 63% { opacity: 1; transform: translate(-50%, 0) scale(1); }
        66%, 100% { opacity: 0; transform: translate(-50%, 6px) scale(.94); }
    }
    @media (max-width: 820px) {
        .hero { grid-template-columns: 1fr; gap: 10px; padding: 34px 28px; }
        .hero h1 { max-width: 15ch; }
        .langkah-grid { grid-template-columns: 1fr; gap: 12px; }
        .langkah { min-height: 0; padding: 22px; }
        .foto-wrap { min-height: 230px; }
        .guru-avatar { width: 120px; height: 120px; font-size:44px; }
        .guru-label { bottom: 0; }
        .hero-actions .btn { width: 100%; }
    }
    @media (max-width: 480px) {
        .hero { padding: 30px 22px 26px; border-radius: 28px; }
        .hero h1 { font-size: clamp(36px, 10vw, 46px); }
        .hero-actions { margin-top: 22px; }
        .foto-wrap { min-height: 205px; }
        .guru-avatar { width: 108px; height: 108px; font-size: 40px; }
        .guru-avatar.kiri { margin-right: -28px; }
        .guru-avatar.kanan { margin-left: -28px; }
        .teacher-message { max-width: 95%; }
    }
    @media (prefers-reduced-motion: reduce) {
        .landing-home .reveal,
        .langkah,
        .guru-avatar,
        .teacher-pair,
        .teacher-pair *,
        .teacher-message {
            animation: none;
            transition: none;
        }
        .teacher-pair { left: calc(50% - 38px); }
        .teacher-message { opacity: 1; transform: translateX(-50%); }
        .foto-wrap:hover .guru-avatar.kiri,
        .foto-wrap:hover .guru-avatar.kanan,
        .langkah:hover {
            transform: none;
        }
    }
</style>

<main class="landing-home">
<div class="hero">
    <div class="reveal reveal-1">
        <p class="hero-eyebrow">Ruang konseling sekolah</p>
        <h1>Cerita Kamu Aman di Sini</h1>
        <p class="lead">
            Ruang BK bukan cuma untuk saat kamu punya masalah. Kamu boleh datang untuk bercerita,
            berbagi keluh kesah, mencari solusi, atau meminta arahan—bahkan saat semuanya terasa baik-baik saja.
        </p>
        <div class="hero-actions">
            <a href="{{ route('siswa.jadwal.create') }}" class="btn btn-primary">Buat janji konseling</a>
            <a href="{{ route('siswa.jadwal.cek') }}" class="btn btn-ghost">Lihat status janji</a>
        </div>
        <span class="hero-note"><span aria-hidden="true">✦</span> Ceritamu dijaga dengan penuh perhatian</span>
    </div>

    <div class="foto-wrap reveal reveal-2" aria-label="Guru konseling">
        @foreach ($guru as $i => $g)
            <div class="guru-avatar {{ $i === 0 ? 'kiri' : 'kanan' }}" aria-label="Foto {{ $g->name }}">
                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($g->name, 0, 1)) }}
                @if ($g->foto_profil)
                    <img class="guru-foto" src="{{ asset('storage/'.$g->foto_profil) }}" alt="{{ $g->name }}" onerror="this.style.display='none'">
                @endif
            </div>
        @endforeach
        @if ($guru->isNotEmpty())
            <span class="guru-label">{{ $guru->pluck('name')->join(' & ') }}</span>
        @endif
    </div>
</div>

<div class="teacher-trail" aria-label="Animasi dua guru BK menyambutmu">
        <span class="teacher-route" aria-hidden="true"></span>
        <details class="teacher-message">
            <summary>
                <span class="welcome-greeting welcome-greeting-default">Hai, {{ auth()->user()?->name ?? 'teman' }}! Kami siap dengerin 🤍</span>
                <span class="welcome-hint">Klik sapaan untuk membalas</span>
                <span class="welcome-greeting welcome-greeting-open">Cerita apa pun boleh dimulai di sini ✨</span>
            </summary>
        </details>
        <div class="teacher-pair" aria-hidden="true">
            <div class="teacher-person">
                <svg viewBox="0 0 180 230" aria-hidden="true" focusable="false">
                    <ellipse class="character-shadow" cx="91" cy="219" rx="54" ry="8" />
                    <path class="character-hijab-back" d="M47 95C37 31 63 14 95 15c34 1 54 28 42 79 16 35 25 73 28 124H15c4-48 12-91 32-123Z" />
                    <path class="character-outfit-left" d="M41 163c10-15 25-23 49-23s43 8 53 24l23 55H17l24-56Z" />
                    <path class="character-cloth-detail" d="m75 143 16 19 16-19-9 33H84l-9-33Z" />
                    <path class="character-hijab-front" d="M48 86c0-38 19-59 44-59 28 0 45 21 43 58-2 34 10 54 22 79-22-9-34-22-42-42-14 17-44 23-76 21 17-17 19-36 9-57Z" />
                    <ellipse class="character-skin" cx="91" cy="83" rx="31" ry="38" />
                    <path class="character-face-detail" d="M78 80h1m24 0h1" />
                    <path d="M83 96c5 7 12 7 17 0" fill="none" stroke="#B96F72" stroke-linecap="round" stroke-width="2.5" />
                    <ellipse class="character-cheek" cx="70" cy="91" rx="6" ry="3.5" />
                    <ellipse class="character-cheek" cx="112" cy="91" rx="6" ry="3.5" />
                    <g class="character-wave">
                        <path d="M45 164 25 129 31 103" fill="none" class="character-wave-sleeve-left" stroke-linecap="round" stroke-linejoin="round" stroke-width="17" />
                        <path class="character-hand" d="M27 107c-5-3-7-9-5-15l3-12c.5-2 3-2 4 0l1 9 4-12c.8-2 3.5-1.6 3.5.6l-1 11 4-8c1-2 3.5-.8 3 1.2l-2 10c-1 7-4 12-9 16Z" />
                        <path class="character-hand-detail" d="m25 94 3 4m5-5 2 5m5-2 1 4" />
                    </g>
                    <path class="character-wave-sleeve-left" d="m134 163 16 22" fill="none" stroke-linecap="round" stroke-width="16" />
                    <circle class="character-sparkle" cx="149" cy="189" r="5" />
                    <path d="M147 190c3-4 7-4 9 0-2 4-6 4-9 0Z" fill="#F7E7A8" />
                </svg>
            </div>
            <div class="teacher-person">
                <svg viewBox="0 0 180 230" aria-hidden="true" focusable="false">
                    <ellipse class="character-shadow" cx="89" cy="219" rx="54" ry="8" />
                    <path class="character-hair" d="M43 83c-7-43 13-67 48-67 39 0 54 30 43 72 16 29 20 70 19 135H24c-1-65 3-110 19-140Z" />
                    <path class="character-outfit-right" d="M40 163c11-15 27-23 50-23 25 0 43 8 52 24l23 55H15l25-56Z" />
                    <path class="character-cloth-detail" d="m76 142 14 19 14-19 9 86H67l9-86Z" />
                    <path d="M82 144h17v19H82z" fill="#F2C6A8" />
                    <ellipse class="character-skin" cx="90" cy="83" rx="31" ry="38" />
                    <path class="character-hair-highlight" d="M59 76c-1-31 16-50 37-50 22 0 36 14 39 37-12-7-19-17-23-26-8 16-26 27-53 31Z" />
                    <path class="character-face-detail" d="M77 81h1m24 0h1" />
                    <path d="M82 96c5 7 12 7 17 0" fill="none" stroke="#B96F72" stroke-linecap="round" stroke-width="2.5" />
                    <ellipse class="character-cheek" cx="69" cy="91" rx="6" ry="3.5" />
                    <ellipse class="character-cheek" cx="111" cy="91" rx="6" ry="3.5" />
                    <path class="character-hair" d="M60 106c-3 31-4 48-1 61l-19 19c-6-44 0-67 20-80Zm60 0c3 31 4 48 1 61l19 19c6-44 0-67-20-80Z" />
                    <path class="character-wave-sleeve-right" d="m135 164 20-35-6-25" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="17" />
                    <g class="character-wave">
                        <path d="m136 164 17-37-4-26" fill="none" class="character-wave-sleeve-right" stroke-linecap="round" stroke-linejoin="round" stroke-width="17" />
                        <path class="character-hand" d="M149 106c5-3 7-9 5-15l-3-12c-.5-2-3-2-4 0l-1 9-4-12c-.8-2-3.5-1.6-3.5.6l1 11-4-8c-1-2-3.5-.8-3 1.2l2 10c1 7 4 12 9 16Z" />
                        <path class="character-hand-detail" d="m151 93-3 4m-5-5-2 5m-5-2-1 4" />
                    </g>
                    <path d="M47 165c-9 7-14 15-17 24" fill="none" class="character-wave-sleeve-right" stroke-linecap="round" stroke-width="16" />
                    <circle class="character-sparkle" cx="34" cy="190" r="5" />
                    <path d="M32 191c3-4 7-4 9 0-2 4-6 4-9 0Z" fill="#F7E7A8" />
                </svg>
            </div>
        </div>
    </div>

<div class="langkah-section reveal reveal-3">
    <p class="langkah-kicker">Langkah sederhana</p>
    <h2 style="font-size:28px;">Mulai dari yang terasa nyaman</h2>
    <div class="langkah-grid">
        <div class="langkah">
            <div class="angka">1</div>
            <h3>Pilih guru & jadwal</h3>
            <p>Mau cerita ke Bu Maya atau Bu Noya, sesuaikan sama jam yang masih kosong.</p>
        </div>
        <div class="langkah">
            <div class="angka">2</div>
            <h3>Ceritakan persoalanmu</h3>
            <p>Pilih topik yang paling deket sama kondisimu, datang sendiri atau bareng teman.</p>
        </div>
        <div class="langkah">
            <div class="angka">3</div>
            <h3>Tunggu konfirmasi</h3>
            <p>Guru BK bakal setujui jadwalmu, tinggal cek status kapan aja.</p>
        </div>
    </div>
</div>
</main>
@endsection
