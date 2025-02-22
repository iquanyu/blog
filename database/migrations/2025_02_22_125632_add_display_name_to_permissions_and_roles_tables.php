<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('display_name')->nullable()->after('name');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->string('display_name')->nullable()->after('name');
        });
    }

    public function down()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn('display_name');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('display_name');
        });
    }
}; 