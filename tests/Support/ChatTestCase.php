<?php

namespace Tests\Support;

use App\Models\Permission;
use App\Models\PermissionCategory;
use Spatie\Permission\PermissionRegistrar;

/**
 * Shared slim schema (which already includes the chat tables) plus chat permissions.
 */
abstract class ChatTestCase extends TicketTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $category = PermissionCategory::create(['name' => 'Chat']);
        foreach (['view-chat-analytics', 'manage-chat', 'view-staff'] as $name) {
            Permission::create(['name' => $name, 'guard_name' => 'web', 'category_id' => $category->id]);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
