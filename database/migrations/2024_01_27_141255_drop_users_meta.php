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
            $indexes = Arr::pluck(Schema::getIndexes('users'), 'name');

            if (in_array('users_email_unique', $indexes)) {
                $table->dropUnique('users_email_unique');
            }

            $table->dropColumn([
                'name',
                'email',
                'password',
                'email_verified_at',
                'remember_token',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
        });
    }
};
