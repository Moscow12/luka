<?php

use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(TestCase::class);

// Several migrations are MySQL-only, so migrate only what Spatie's gate check reads
beforeEach(function () {
    $this->artisan('migrate', ['--path' => 'database/migrations/2025_06_20_223844_create_permission_tables.php']);
});

// Unsaved user: a recent last_seen_at keeps the UpdateLastSeen middleware from writing
function logViewerUser(bool $superAdmin): User
{
    return (new User)->forceFill([
        'id' => (string) Str::uuid(),
        'is_super_admin' => $superAdmin,
        'last_seen_at' => now(),
    ]);
}

it('blocks guests from the log viewer', function () {
    $this->get('/log-viewer')->assertForbidden();
    $this->getJson('/log-viewer/api/files')->assertForbidden();
});

it('blocks regular users from the log viewer', function () {
    $user = logViewerUser(superAdmin: false);

    $this->actingAs($user)->get('/log-viewer')->assertForbidden();
    $this->actingAs($user)->getJson('/log-viewer/api/files')->assertForbidden();
});

it('lets super admins open the log viewer', function () {
    $admin = logViewerUser(superAdmin: true);

    $this->actingAs($admin)->get('/log-viewer')->assertOk();
    $this->actingAs($admin)->getJson('/log-viewer/api/files')->assertOk();
});
