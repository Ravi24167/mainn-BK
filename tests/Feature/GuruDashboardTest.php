<?php

namespace Tests\Feature;

use App\Models\CatatanKonseling;
use App\Models\FotoKonseling;
use App\Models\JadwalKonseling;
use App\Models\KategoriMasalah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GuruDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_login_and_dashboard_access_are_restricted_by_role(): void
    {
        $this->travelTo(Carbon::parse('2026-10-26 08:00:00'));
        $maya = $this->createUser('Bu Maya', 'maya@example.test', 'guru');
        $noya = $this->createUser('Bu Noya', 'noya@example.test', 'guru');
        $admin = $this->createUser('Admin BK', 'admin@example.test', 'admin');

        $this->get(route('guru.dashboard'))
            ->assertRedirect(route('login'));

        foreach ([$maya, $noya] as $guru) {
            $this->post(route('login'), [
                'email' => $guru->email,
                'password' => 'password',
            ])->assertRedirect(route('guru.dashboard'));

            $this->assertAuthenticatedAs($guru);
            $this->get(route('guru.dashboard'))
                ->assertOk()
                ->assertSee($guru->name);

            $this->post(route('logout'))->assertRedirect(route('siswa.dashboard'));
        }

        $this->actingAs($admin)
            ->get(route('guru.dashboard'))
            ->assertForbidden();
    }

    public function test_teacher_cannot_read_another_teachers_session_or_pdf(): void
    {
        $maya = $this->createUser('Bu Maya', 'maya@example.test', 'guru');
        $noya = $this->createUser('Bu Noya', 'noya@example.test', 'guru');
        $jadwal = $this->createJadwal($noya, 'Alya', now()->toDateString(), 'Akademik');

        $this->actingAs($maya)
            ->get(route('guru.konseling.show', $jadwal))
            ->assertForbidden();

        $this->get(route('guru.konseling.pdf', $jadwal))
            ->assertForbidden();
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login'), [
                'email' => 'maya@example.test',
                'password' => 'incorrect',
            ])->assertSessionHasErrors('email');
        }

        $this->post(route('login'), [
            'email' => 'maya@example.test',
            'password' => 'incorrect',
        ])->assertTooManyRequests();
    }

    public function test_teacher_notes_persist_and_export_as_pdf(): void
    {
        $this->travelTo(Carbon::parse('2026-10-26 08:00:00'));
        $guru = $this->createUser('Bu Maya', 'maya@example.test', 'guru');
        $jadwal = $this->createJadwal($guru, 'Alya', '2026-10-20', 'Akademik');

        $this->actingAs($guru)
            ->post(route('guru.konseling.catatan', $jadwal), [
                'catatan' => 'Catatan yang tersimpan.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('catatan_konselings', [
            'jadwal_konseling_id' => $jadwal->id,
            'catatan' => 'Catatan yang tersimpan.',
            'tahun' => 2026,
        ]);

        $this->get(route('guru.konseling.show', $jadwal))
            ->assertOk()
            ->assertSee('Catatan yang tersimpan.');

        $this->get(route('guru.konseling.pdf', $jadwal))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertSee('%PDF', false);
    }

    public function test_gallery_shows_session_date_time_and_link_to_session_details(): void
    {
        $guru = $this->createUser('Bu Maya', 'maya@example.test', 'guru');
        $jadwal = $this->createJadwal($guru, 'Alya', '2026-10-20', 'Akademik');
        $catatan = CatatanKonseling::create([
            'jadwal_konseling_id' => $jadwal->id,
            'tahun' => 2026,
        ]);
        FotoKonseling::create([
            'catatan_konseling_id' => $catatan->id,
            'path' => 'galeri-konseling/2026/foto.jpg',
            'tahun' => 2026,
        ]);

        $this->actingAs($guru)
            ->get(route('guru.galeri', ['tahun' => 2026, 'bulan' => 10]))
            ->assertSee('Alya')
            ->assertSee('Kelas 11 RPL')
            ->assertSee('Tanggal: 20/10/2026')
            ->assertSee('Jam: 08:00 - 08:40')
            ->assertSee(route('guru.konseling.show', $jadwal));
    }

    public function test_dashboard_chart_data_matches_the_selected_month_and_refreshes_from_database(): void
    {
        $this->travelTo(Carbon::parse('2026-10-26 08:00:00'));
        $guru = $this->createUser('Bu Maya', 'maya@example.test', 'guru');
        $this->createJadwal($guru, 'Alya', '2026-10-05', 'Akademik');
        $this->createJadwal($guru, 'Bima', '2026-10-06', 'Akademik');
        $this->createJadwal($guru, 'Citra', '2026-09-05', 'Sosial');

        $response = $this->actingAs($guru)
            ->get(route('guru.dashboard', ['tahun' => 2026, 'bulan' => 10]))
            ->assertOk()
            ->assertSee('chartMasalah')
            ->assertSee('data: [2]', false)
            ->assertSee('Akademik')
            ->assertDontSee('Citra');

        $this->assertDatabaseCount('jadwal_konselings', 3);

        $this->get(route('guru.dashboard', ['tahun' => 2026, 'bulan' => 9]))
            ->assertOk()
            ->assertSee('data: [1]', false)
            ->assertSee('Sosial')
            ->assertDontSee('Alya');
    }

    public function test_dashboard_escapes_student_names_and_session_notes(): void
    {
        $this->travelTo(Carbon::parse('2026-10-26 08:00:00'));
        $guru = $this->createUser('Bu Maya', 'maya@example.test', 'guru');
        $namaSiswa = '<svg onload=alert(1)>';
        $jadwal = $this->createJadwal($guru, $namaSiswa, '2026-10-05', 'Akademik');

        CatatanKonseling::create([
            'jadwal_konseling_id' => $jadwal->id,
            'catatan' => '<script>catatan()</script>',
            'tahun' => 2026,
        ]);

        $this->actingAs($guru)
            ->get(route('guru.siswa.riwayat', ['nama' => $namaSiswa]))
            ->assertOk()
            ->assertSee('&lt;svg onload=alert(1)&gt;', false)
            ->assertSee('&lt;script&gt;catatan()&lt;/script&gt;', false)
            ->assertDontSee('<svg onload=alert(1)>', false)
            ->assertDontSee('<script>catatan()</script>', false);
    }

    public function test_student_status_lookup_requires_the_exact_name(): void
    {
        $guru = $this->createUser('Bu Maya', 'maya@example.test', 'guru');
        $this->createJadwal($guru, 'Alya Putri', '2026-10-05', 'Akademik');

        $this->get(route('siswa.jadwal.cek', ['nama' => 'Alya']))
            ->assertOk()
            ->assertSee('Gak ketemu jadwal atas nama itu.')
            ->assertDontSee('Alya Putri');

        $this->get(route('siswa.jadwal.cek', ['nama' => 'Alya Putri']))
            ->assertOk()
            ->assertSee('Alya Putri');
    }

    private function createUser(string $name, string $email, string $role): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }

    private function createJadwal(User $guru, string $namaSiswa, string $tanggal, string $namaKategori): JadwalKonseling
    {
        $kategori = KategoriMasalah::firstOrCreate(['nama' => $namaKategori]);

        return JadwalKonseling::create([
            'nama_siswa' => $namaSiswa,
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
    }
}
