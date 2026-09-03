<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuruAgendaFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;
    protected Classes $class;
    protected Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->teacher = User::where('email', 'guru@school.com')->first();
        $this->class = Classes::first();
        $this->subject = Subject::first();
    }

    public function test_guru_agenda_all_filter_shows_published_and_draft(): void
    {
        $published = $this->createAgenda('Jurnal Published', 'published');
        $draft = $this->createAgenda('Jurnal Draft', 'draft');

        $response = $this->actingAs($this->teacher)->get(route('guru.agenda.index'));

        $response->assertOk();
        $response->assertSee($published->title);
        $response->assertSee($draft->title);
    }

    public function test_guru_agenda_status_filter_only_shows_selected_status(): void
    {
        $published = $this->createAgenda('Jurnal Published', 'published');
        $draft = $this->createAgenda('Jurnal Draft', 'draft');

        $publishedResponse = $this->actingAs($this->teacher)->get(route('guru.agenda.index', ['status' => 'published']));
        $publishedResponse->assertOk();
        $publishedResponse->assertSee($published->title);
        $publishedResponse->assertDontSee($draft->title);

        $draftResponse = $this->actingAs($this->teacher)->get(route('guru.agenda.index', ['status' => 'draft']));
        $draftResponse->assertOk();
        $draftResponse->assertSee($draft->title);
        $draftResponse->assertDontSee($published->title);
    }

    public function test_guru_agenda_cannot_be_deleted(): void
    {
        $agenda = $this->createAgenda('Jurnal Tetap Arsip', 'published');

        $response = $this->actingAs($this->teacher)->delete(route('guru.agenda.destroy', $agenda));

        $response->assertRedirect(route('guru.agenda.index'));
        $response->assertSessionHas('error', 'Jurnal tidak dapat dihapus karena akan tetap disimpan sebagai arsip.');
        $this->assertDatabaseHas('agendas', ['id' => $agenda->id]);
    }

    public function test_guru_agenda_uses_selected_period_from_avatar_session(): void
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $oldYear = AcademicYear::create([
            'name' => '2022/2023',
            'semester' => 'Ganjil',
            'start_date' => '2022-07-01',
            'end_date' => '2022-12-31',
            'is_active' => false,
            'institution_id' => $this->teacher->institution_id,
        ]);

        $activeAgenda = $this->createAgenda('Jurnal Periode Aktif', 'published', $activeYear->id);
        $oldAgenda = $this->createAgenda('Jurnal Periode Lama', 'published', $oldYear->id);

        $response = $this->actingAs($this->teacher)
            ->withSession(['academic_year_id' => $oldYear->id])
            ->followingRedirects()
            ->get(route('guru.agenda.index'));

        $response->assertOk();
        $response->assertSee($oldAgenda->title);
        $response->assertDontSee($activeAgenda->title);
    }

    public function test_guru_agenda_can_switch_back_to_active_period_from_query(): void
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $oldYear = AcademicYear::create([
            'name' => '2022/2023',
            'semester' => 'Ganjil',
            'start_date' => '2022-07-01',
            'end_date' => '2022-12-31',
            'is_active' => false,
            'institution_id' => $this->teacher->institution_id,
        ]);

        $activeAgenda = $this->createAgenda('Jurnal Aktif Setelah Switch', 'published', $activeYear->id);
        $oldAgenda = $this->createAgenda('Jurnal Lama Sebelum Switch', 'published', $oldYear->id);

        $response = $this->actingAs($this->teacher)
            ->withSession(['academic_year_id' => $oldYear->id])
            ->get(route('guru.agenda.index', ['academic_year_id' => $activeYear->id]));

        $response->assertOk();
        $response->assertSee($activeAgenda->title);
        $response->assertDontSee($oldAgenda->title);
    }

    private function createAgenda(string $title, string $status, ?int $academicYearId = null): Agenda
    {
        return Agenda::create([
            'teacher_id' => $this->teacher->id,
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'date' => '2026-07-23',
            'room' => 'Ruang Test',
            'title' => $title,
            'description' => 'Konten jurnal test',
            'status' => $status,
            'academic_year_id' => $academicYearId,
        ]);
    }
}
