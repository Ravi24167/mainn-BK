@extends('layouts.app')

@section('title', 'Ajukan Konseling - Ruang Cerita')

@section('content')
<style>
    .form-shell { max-width: 640px; margin: 0 auto; }
    .step-label { font-size: 13px; font-weight: 700; color: var(--ungu); text-transform: uppercase; letter-spacing: .04em; margin-bottom: 8px; }
    .field { margin-bottom: 26px; }
    .kartu-guru-pilih {
        display: flex; align-items: center; gap: 12px;
        border: 1.5px solid #E4D9F5; border-radius: var(--radius-md);
        padding: 12px 16px; cursor: pointer;
        transition: border-color .15s ease, transform .15s ease;
        background: var(--putih);
    }
    .kartu-guru-pilih:hover { transform: translateY(-2px); }
    .kartu-guru-pilih.aktif { border-color: var(--ungu); background: var(--ungu-muda); }
    .avatar-guru-pilih {
        width: 48px; height: 48px; flex: 0 0 48px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        overflow: hidden; background: #F0E9FA; color: var(--ungu-gelap);
        font-family: 'Fraunces', serif; font-size: 21px; font-weight: 600; position:relative;
    }
    .avatar-guru-pilih img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
    .grid-guru { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

    .slot-grid { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; min-height: 44px; }
    .slot-jam {
        padding: 10px 16px; border-radius: 999px;
        border: 1.5px solid #E4D9F5; font-size: 14px; font-weight: 500;
        cursor: pointer; transition: all .15s ease; background: var(--putih);
    }
    .slot-jam:hover:not(.terisi) { border-color: var(--ungu); transform: translateY(-2px); }
    .slot-jam.terisi { background: #F4F2F6; color: #B4A9C2; border-color: #F4F2F6; cursor: not-allowed; text-decoration: line-through; }
    .slot-jam.dipilih { background: var(--ungu); color: var(--putih); border-color: var(--ungu); }
    .slot-info { color:#6B5A85; font-size:14px; }
    .slot-kosong { color:#A23B4C; font-size:14px; }

    .pilihan-ganda { display: flex; gap: 12px; margin-top: 8px; }
    .pilihan-ganda label {
        flex: 1; text-align: center; padding: 14px; border-radius: var(--radius-sm);
        border: 1.5px solid #E4D9F5; cursor: pointer; font-weight: 600; font-size: 14px;
        transition: all .15s ease;
    }
    .pilihan-ganda input { display: none; }
    .pilihan-ganda label:has(input:checked) { border-color: var(--ungu); background: var(--ungu-muda); }
</style>

<div class="form-shell reveal reveal-1">
    <h1 style="font-size:30px; margin-bottom:6px;">Ajukan Jadwal Konseling</h1>
    <p style="color:#6B5A85; margin-bottom:32px;">Isi form ini, tenang aja gak ada yang lihat kecuali guru BK yang kamu pilih.</p>

    <form method="POST" action="{{ route('siswa.jadwal.store') }}" id="formBooking">
        @csrf

        <div class="field">
            <div class="step-label">1. Data Diri</div>
            <input type="text" name="nama_siswa" placeholder="Nama lengkap kamu" value="{{ old('nama_siswa') }}" required style="margin-bottom:10px;">
            <div style="display:flex; gap:10px;">
                <select name="kelas" required style="flex:1;">
                    <option value="">Kelas</option>
                    @foreach (['10', '11', '12'] as $k)
                        <option value="{{ $k }}" {{ old('kelas') === $k ? 'selected' : '' }}>Kelas {{ $k }}</option>
                    @endforeach
                </select>
                <select name="jurusan" required style="flex:1;">
                    <option value="">Jurusan</option>
                    @foreach (['KKR', 'RPL', 'DKV', 'TKJ', 'PSPT'] as $j)
                        <option value="{{ $j }}" {{ old('jurusan') === $j ? 'selected' : '' }}>{{ $j }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="field">
            <div class="step-label">2. Pilih Guru BK</div>
            <div class="grid-guru" id="grid-guru">
                @foreach ($guru as $g)
                    <label class="kartu-guru-pilih" data-guru-id="{{ $g->id }}" onclick="pilihGuru({{ $g->id }}, this)">
                        <span class="avatar-guru-pilih">
                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($g->name, 0, 1)) }}
                            @if ($g->foto_profil)
                                <img src="{{ asset('storage/'.$g->foto_profil) }}" alt="" onerror="this.remove()">
                            @endif
                        </span>
                        <strong>{{ $g->name }}</strong>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="field">
            <div class="step-label">3. Tanggal & Jam (1 sesi = 40 menit)</div>
            <input type="date" id="inputTanggal" value="{{ old('tanggal', now()->toDateString()) }}" min="{{ now()->toDateString() }}"
                   onchange="refreshSlot()" style="max-width:220px;">
            <div class="slot-grid" id="slotGrid">
                <span class="slot-info">Pilih guru dulu buat lihat jam yang tersedia.</span>
            </div>
        </div>

        <div class="field">
            <div class="step-label">4. Kategori Persoalan</div>
            <select name="kategori_masalah_id" required>
                <option value="">Pilih topik yang paling dekat</option>
                @foreach ($kategori as $k)
                    <option value="{{ $k->id }}" {{ old('kategori_masalah_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <div class="step-label">5. Datang Dengan Siapa?</div>
            <div class="pilihan-ganda">
                <label><input type="radio" name="jenis_kedatangan" value="individu" {{ old('jenis_kedatangan', 'individu') === 'individu' ? 'checked' : '' }} onclick="toggleKelompok(false)"><span>Sendiri</span></label>
                <label><input type="radio" name="jenis_kedatangan" value="kelompok" {{ old('jenis_kedatangan') === 'kelompok' ? 'checked' : '' }} onclick="toggleKelompok(true)"><span>Bareng Teman</span></label>
            </div>
            <div id="inputKelompok" style="display:{{ old('jenis_kedatangan') === 'kelompok' ? 'block' : 'none' }}; margin-top:14px;">
                <textarea name="anggota_kelompok" rows="2" placeholder="Nama teman yang ikut, pisahkan dengan koma">{{ old('anggota_kelompok') }}</textarea>
            </div>
        </div>

        <input type="hidden" name="guru_id" id="inputGuruId" value="{{ old('guru_id') }}">
        <input type="hidden" name="tanggal" id="inputTanggalHidden" value="{{ old('tanggal', now()->toDateString()) }}">
        <input type="hidden" name="jam_mulai" id="inputJamMulai" value="{{ old('jam_mulai') }}">

        <button type="submit" class="btn btn-primary" style="width:100%;">Ajukan Jadwal</button>
    </form>
</div>

<script>
    const jamTerisiLama = {!! json_encode(old('jam_mulai')) !!};

    function toggleKelompok(tampil) {
        document.getElementById('inputKelompok').style.display = tampil ? 'block' : 'none';
    }

    function pilihGuru(id, el) {
        document.querySelectorAll('.kartu-guru-pilih').forEach(x => x.classList.remove('aktif'));
        el.classList.add('aktif');
        document.getElementById('inputGuruId').value = id;
        refreshSlot();
    }

    function refreshSlot() {
        const guruId = document.getElementById('inputGuruId').value;
        const tanggal = document.getElementById('inputTanggal').value;
        document.getElementById('inputTanggalHidden').value = tanggal;

        const grid = document.getElementById('slotGrid');

        if (!guruId) {
            grid.innerHTML = '<span class="slot-info">Pilih guru dulu buat lihat jam yang tersedia.</span>';
            return;
        }

        grid.innerHTML = '<span class="slot-info">Memuat jam kosong...</span>';

        fetch(`{{ route('siswa.jadwal.slot') }}?guru_id=${guruId}&tanggal=${tanggal}`)
            .then(res => {
                if (!res.ok) throw new Error('status ' + res.status);
                return res.json();
            })
            .then(data => {
                if (!data.buka) {
                    grid.innerHTML = '<span class="slot-kosong">Guru belum buka jadwal di tanggal ini, coba pilih tanggal lain ya.</span>';
                    return;
                }
                grid.innerHTML = '';
                data.slot.forEach(label => {
                    const terisi = data.terisi.includes(label);
                    const el = document.createElement('span');
                    el.className = 'slot-jam' + (terisi ? ' terisi' : '');
                    el.textContent = label;
                    if (!terisi) {
                        el.onclick = () => pilihJam(label, el);
                    }
                    grid.appendChild(el);
                });
            })
            .catch(err => {
                grid.innerHTML = '<span class="slot-kosong">Gagal memuat jam kosong (' + err.message + '). Coba refresh halaman.</span>';
            });
    }

    function pilihJam(label, el) {
        document.querySelectorAll('.slot-jam').forEach(x => x.classList.remove('dipilih'));
        el.classList.add('dipilih');
        document.getElementById('inputJamMulai').value = label;
    }

    // Pulihkan pilihan kalau baru saja submit gagal validasi
    window.addEventListener('DOMContentLoaded', () => {
        const guruIdLama = document.getElementById('inputGuruId').value;
        if (guruIdLama) {
            const kartu = document.querySelector(`.kartu-guru-pilih[data-guru-id="${guruIdLama}"]`);
            if (kartu) kartu.classList.add('aktif');
            refreshSlot();
        }
    });
</script>
@endsection
