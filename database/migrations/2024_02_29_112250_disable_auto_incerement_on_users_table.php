<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * supaapps/supaapps-guard package
 */

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::table('users')->count() > 0) {
            throw new Exception('Table users is not empty');
        }
        Schema::table('users', function (Blueprint $table) {
            $table->dropPrimary();
            $table->unsignedBigInteger('id')->primary()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropPrimary();
            $table->id();
        });
    }
};
