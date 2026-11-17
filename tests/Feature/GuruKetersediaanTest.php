<?php

namespace Tests\Feature;

use App\Models\JadwalTersedia;
use App\Models\JadwalKonseling;
use App\Models\KategoriMasalah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GuruKetersediaanTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_can_open_a_one_hour_window_as_one_forty_minute_booking_slot(): void
    {
        $this->travelTo(now()->startOfDay());
        $guru = $this->createGuru();
        $tanggal = now()->addDay()->toDateString();

        $this->actingAs($guru)->post(route('guru.ketersediaan.store'), [
            'tanggal' => $tanggal,
            'jam_buka' => '08:00',
            'jam_tutup' => '09:00',
        ])->assertRedirect();

        $this->getJson(route('siswa.jadwal.slot', [
            'guru_id' => $guru->id,
            'tanggal' => $tanggal,
        ]))
            ->assertOk()
            ->assertJsonPath('buka', true)
            ->assertJsonPath('slot', ['08:00']);
    }

    public function test_both_teachers_can_open_independent_slots_that_students_can_book(): void
    {
        $this->travelTo(Carbon::parse('2026-10-26 08:00:00'));
        $maya = $this->createGuru();
        $noya = User::create([
            'name' => 'Bu Noya',
            'email' => 'noya@example.test',
            'password' => Hash::make('password'),
            'role' => 'guru',
        ]);
        $kategori = KategoriMasalah::create(['nama' => 'Akademik']);
        $tanggal = '2026-10-28';

        foreach ([[$maya, 'Alya'], [$noya, 'Bima']] as [$guru, $namaSiswa]) {
            $this->actingAs($guru)
                ->post(route('guru.ketersediaan.store'), [
                    'tanggal' => $tanggal,
                    'jam_buka' => '08:00',
                    'jam_tutup' => '09:00',
                ])
                ->assertRedirect();

            $this->assertDatabaseHas('jadwal_tersedias', [
                'guru_id' => $guru->id,
                'tanggal' => $tanggal,
                'jam_buka' => '08:00',
                'jam_tutup' => '09:00',
            ]);

            $this->getJson(route('siswa.jadwal.slot', [
                'guru_id' => $guru->id,
                'tanggal' => $tanggal,
            ]))
                ->assertOk()
                ->assertJsonPath('slot', ['08:00']);

            $this->post(route('siswa.jadwal.store'), [
                'nama_siswa' => $namaSiswa,
                'kelas' => '11',
                'jurusan' => 'RPL',
                'guru_id' => $guru->id,
                'kategori_masalah_id' => $kategori->id,
                'tanggal' => $tanggal,
                'jam_mulai' => '08:00',
                'jenis_kedatangan' => 'individu',
            ])->assertRedirect(route('siswa.jadwal.cek', ['nama' => $namaSiswa]));

            $this->assertDatabaseHas('jadwal_konselings', [
                'nama_siswa' => $namaSiswa,
                'guru_id' => $guru->id,
                'tanggal' => $tanggal,
                'jam_mulai' => '08:00:00',
            ]);

            $this->get(route('siswa.jadwal.cek', ['nama' => $namaSiswa]))
                ->assertOk()
                ->assertSee($namaSiswa)
                ->assertSee($guru->name);
        }
    }

    public function test_home_and_teacher_management_pages_render(): void
    {
        $guru = $this->createGuru();

        $this->get(route('siswa.dashboard'))
            ->assertOk()
            ->assertSee('Ruang konseling sekolah')
            ->assertSee('Buat janji konseling');

        $this->actingAs($guru)
            ->get(route('guru.ketersediaan.index'))
            ->assertOk()
            ->assertSee('Atur jam konseling');

        $this->get(route('guru.profil'))
            ->assertOk()
            ->assertSee('Perbarui foto');
    }

    public function test_missing_profile_photo_uses_a_local_initial_instead_of_requesting_a_broken_image(): void
    {
        $guru = $this->createGuru();
        $guru->update(['foto_profil' => 'foto-guru/tidak-ada.jpg']);

        $this->get(route('siswa.dashboard'))
            ->assertOk()
            ->assertSee('Maya')
            ->assertDontSee('/storage/foto-guru/tidak-ada.jpg');

        $this->get(route('siswa.jadwal.create'))
            ->assertOk()
            ->assertSee('Maya')
            ->assertDontSee('/storage/foto-guru/tidak-ada.jpg');

        $this->actingAs($guru)
            ->get(route('guru.profil'))
            ->assertOk()
            ->assertSee('Maya')
            ->assertDontSee('/storage/foto-guru/tidak-ada.jpg');
    }

    public function test_teacher_can_replace_their_own_profile_photo(): void
    {
        Storage::fake('public');
        $maya = $this->createGuru();
        $noya = User::create([
            'name' => 'Bu Noya',
            'email' => 'noya@example.test',
            'password' => Hash::make('password'),
            'role' => 'guru',
        ]);
        $fotoMayaLama = UploadedFile::fake()->image('foto-maya-lama.jpg')->store('foto-guru', 'public');
        $fotoNoyaLama = UploadedFile::fake()->image('foto-noya-lama.jpg')->store('foto-guru', 'public');
        $maya->update(['foto_profil' => $fotoMayaLama]);
        $noya->update(['foto_profil' => $fotoNoyaLama]);

        foreach ([[$maya, $fotoMayaLama], [$noya, $fotoNoyaLama]] as [$guru, $fotoLama]) {
            $this->actingAs($guru)
                ->post(route('guru.profil.simpan'), [
                    'foto' => UploadedFile::fake()->image('foto-baru.jpg'),
                ])
                ->assertRedirect();

            $guru->refresh();
            Storage::disk('public')->assertExists($guru->foto_profil);
            Storage::disk('public')->assertMissing($fotoLama);
        }

        $this->assertNotSame($maya->refresh()->foto_profil, $noya->refresh()->foto_profil);
    }

    public function test_guru_cannot_open_overlapping_time_windows_on_the_same_date(): void
    {
        $this->travelTo(now()->startOfDay());
        $guru = $this->createGuru();
        $tanggal = now()->addDay()->toDateString();

        JadwalTersedia::create([
            'guru_id' => $guru->id,
            'tanggal' => $tanggal,
            'jam_buka' => '08:00',
            'jam_tutup' => '09:00',
        ]);

        $this->actingAs($guru)
            ->from(route('guru.ketersediaan.index'))
            ->post(route('guru.ketersediaan.store'), [
                'tanggal' => $tanggal,
                'jam_buka' => '08:30',
                'jam_tutup' => '09:30',
            ])
            ->assertRedirect(route('guru.ketersediaan.index'))
            ->assertSessionHasErrors([
                'jam_buka' => 'Rentang jam ini bertumpang tindih dengan jadwal buka yang sudah dibuat.',
            ]);

        $this->assertDatabaseCount('jadwal_tersedias', 1);
    }

    public function test_guru_cannot_open_a_window_shorter_than_one_session(): void
    {
        $this->travelTo(now()->startOfDay());
        $guru = $this->createGuru();

        $this->actingAs($guru)
            ->from(route('guru.ketersediaan.index'))
            ->post(route('guru.ketersediaan.store'), [
                'tanggal' => now()->addDay()->toDateString(),
                'jam_buka' => '08:00',
                'jam_tutup' => '08:30',
            ])
            ->assertRedirect(route('guru.ketersediaan.index'))
            ->assertSessionHasErrors([
                'jam_tutup' => 'Rentang jadwal harus cukup untuk satu sesi konseling selama 40 menit.',
            ]);
    }

    public function test_booking_slots_that_overlap_an_existing_session_are_blocked(): void
    {
        $this->travelTo(now()->startOfDay());
        $guru = $this->createGuru();
        $kategori = KategoriMasalah::create(['nama' => 'Akademik']);
        $tanggal = now()->addDay()->toDateString();

        JadwalKonseling::create([
            'nama_siswa' => 'Bima',
            'kelas' => '11',
            'jurusan' => 'RPL',
            'guru_id' => $guru->id,
            'kategori_masalah_id' => $kategori->id,
            'tanggal' => $tanggal,
            'jam_mulai' => '08:00',
            'jam_selesai' => '08:40',
            'jenis_kedatangan' => 'individu',
            'status' => 'disetujui',
        ]);

        JadwalTersedia::create([
            'guru_id' => $guru->id,
            'tanggal' => $tanggal,
            'jam_buka' => '08:20',
            'jam_tutup' => '09:00',
        ]);

        $this->getJson(route('siswa.jadwal.slot', [
            'guru_id' => $guru->id,
            'tanggal' => $tanggal,
        ]))
            ->assertOk()
            ->assertJsonPath('slot', ['08:20'])
            ->assertJsonPath('terisi', ['08:20']);

        $this->from(route('siswa.jadwal.create'))
            ->post(route('siswa.jadwal.store'), [
                'nama_siswa' => 'Citra',
                'kelas' => '11',
                'jurusan' => 'RPL',
                'guru_id' => $guru->id,
                'kategori_masalah_id' => $kategori->id,
                'tanggal' => $tanggal,
                'jam_mulai' => '08:20',
                'jenis_kedatangan' => 'individu',
            ])
            ->assertRedirect(route('siswa.jadwal.create'))
            ->assertSessionHasErrors([
                'jam_mulai' => 'Waktu ini bertumpang tindih dengan sesi konseling lain. Silakan pilih jam lain.',
            ]);

        $this->assertDatabaseCount('jadwal_konselings', 1);
    }

    public function test_guru_dashboard_keeps_the_problem_category_chart(): void
    {
        $this->travelTo(now()->startOfDay());
        $guru = $this->createGuru();
        $kategori = KategoriMasalah::create(['nama' => 'Akademik']);

        JadwalKonseling::create([
            'nama_siswa' => 'Alya',
            'kelas' => '11',
            'jurusan' => 'RPL',
            'guru_id' => $guru->id,
            'kategori_masalah_id' => $kategori->id,
            'tanggal' => now()->toDateString(),
            'jam_mulai' => '08:00',
            'jam_selesai' => '08:40',
            'jenis_kedatangan' => 'individu',
            'status' => 'menunggu',
        ]);

        $this->actingAs($guru)
            ->get(route('guru.dashboard'))
            ->assertOk()
            ->assertSee('chartMasalah')
            ->assertSee('Rekap persoalan siswa')
            ->assertSee('Menunggu konfirmasi');
    }

    public function test_student_history_is_grouped_by_month_and_year(): void
    {
        $this->travelTo(now()->startOfDay());
        $guru = $this->createGuru();
        $kategori = KategoriMasalah::create(['nama' => 'Akademik']);
        $dates = [
            now()->startOfMonth()->toDateString(),
            now()->subMonth()->startOfMonth()->toDateString(),
        ];

        foreach ($dates as $date) {
            JadwalKonseling::create([
                'nama_siswa' => 'Alya',
                'kelas' => '11',
                'jurusan' => 'RPL',
                'guru_id' => $guru->id,
                'kategori_masalah_id' => $kategori->id,
                'tanggal' => $date,
                'jam_mulai' => '08:00',
                'jam_selesai' => '08:40',
                'jenis_kedatangan' => 'individu',
                'status' => 'disetujui',
            ]);
        }

        $response = $this->actingAs($guru)
            ->get(route('guru.siswa.riwayat', ['nama' => 'Alya']))
            ->assertOk()
            ->assertSee('tersusun menurut tahun dan bulan');

        foreach ($dates as $date) {
            $response->assertSee(Carbon::parse($date)->translatedFormat('F Y'));
        }
    }

    private function createGuru(): User
    {
        return User::create([
            'name' => 'Bu Maya',
            'email' => 'maya@example.test',
            'password' => Hash::make('password'),
            'role' => 'guru',
        ]);
    }
}
