<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('snaps', function (Blueprint $table) {
            $table->string('caption', 120)->nullable()->after('thumbnail_path');
        });
    }

    public function down(): void
    {
        Schema::table('snaps', function (Blueprint $table) {
            $table->dropColumn('caption');
        });
    }
};
