<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DeploymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_release_has_a_valid_version_and_message(): void
    {
        $this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+$/', config('release.version'));
        $this->assertNotSame('', trim(config('release.message')));
    }

    public function test_public_probes_are_available_without_authentication(): void
    {
        $this->getJson('/health')->assertOk()->assertJsonPath('status', 'ok');
        $this->getJson('/ready')->assertOk()->assertJsonPath('status', 'ready');
    }

    public function test_version_reports_environment_and_artifact_identity(): void
    {
        config(['release.branch' => 'master', 'release.commit' => 'abc123', 'release.build' => '42']);

        $this->getJson('/version')->assertOk()
            ->assertJsonPath('environment', 'testing')
            ->assertJsonPath('branch_target', 'production')
            ->assertJsonPath('commit', 'abc123')
            ->assertJsonPath('build', '42')
            ->assertHeader('Cache-Control', 'no-store, private');

        config(['release.branch' => 'qa']);
        $this->getJson('/version')->assertJsonPath('branch_target', 'qa');
        config(['release.branch' => 'feature/release-banner']);
        $this->getJson('/version')->assertJsonPath('branch_target', 'preview');
    }

    public function test_bad_release_fails_readiness_but_keeps_liveness_available(): void
    {
        config(['release.simulate_failure' => true]);

        $this->getJson('/ready')->assertStatus(503)->assertJsonPath('checks.release', false);
        $this->getJson('/health')->assertOk();

        config(['release.simulate_failure' => false]);
        $this->getJson('/ready')->assertOk();
    }

    public function test_readiness_detects_missing_migrations(): void
    {
        Schema::drop('deployment_notes');

        $this->getJson('/ready')->assertStatus(503)->assertJsonPath('checks.database', false);
    }

    public function test_readiness_does_not_expose_database_errors(): void
    {
        DB::shouldReceive('select')->once()->with('SELECT 1')->andThrow(new \RuntimeException('secret-database-password'));

        $this->getJson('/ready')->assertStatus(503)
            ->assertJsonPath('checks.database', false)
            ->assertDontSee('secret-database-password');
    }

    public function test_guests_cannot_access_or_modify_notes(): void
    {
        $this->get('/deployment')->assertRedirect('/login');
        $this->post('/deployment/notes', ['body' => 'test'])->assertRedirect('/login');
        $this->assertDatabaseCount('deployment_notes', 0);
    }

    public function test_notes_survive_release_changes_and_are_private(): void
    {
        $this->withoutVite();
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($owner)->post('/deployment/notes', ['body' => 'Data sebelum deployment'])
            ->assertRedirect('/deployment');

        $id = DB::table('deployment_notes')->value('id');
        config(['release.version' => '1.1.0']);
        $this->get('/deployment')->assertOk()->assertSee('1.1.0')->assertSee('Data sebelum deployment');
        config(['release.version' => '1.0.0']);
        $this->get('/deployment')->assertOk()->assertSee('Data sebelum deployment');

        $this->actingAs($other)->get('/deployment')->assertDontSee('Data sebelum deployment');
        $this->delete('/deployment/notes/'.$id)->assertForbidden();
        $this->actingAs($owner)->delete('/deployment/notes/'.$id)->assertRedirect('/deployment');
        $this->assertDatabaseCount('deployment_notes', 0);
    }

    public function test_notes_validate_length_and_escape_html(): void
    {
        $this->withoutVite();
        $this->actingAs(User::factory()->create());
        $this->post('/deployment/notes', ['body' => str_repeat('a', 256)])->assertSessionHasErrors('body');
        $this->post('/deployment/notes', ['body' => '<script>alert(1)</script>'])->assertRedirect('/deployment');
        $this->get('/deployment')->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }
}
