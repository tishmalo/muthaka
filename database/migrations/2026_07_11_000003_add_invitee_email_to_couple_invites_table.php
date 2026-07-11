<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('couple_invites', 'invitee_email')) {
            Schema::table('couple_invites', function (Blueprint $table) {
                $table->string('invitee_email')->nullable()->after('invitee_phone')->index();
            });
        }

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE couple_invites MODIFY invitee_phone VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('couple_invites', 'invitee_email')) {
            Schema::table('couple_invites', function (Blueprint $table) {
                $table->dropIndex(['invitee_email']);
                $table->dropColumn('invitee_email');
            });
        }
    }
};

