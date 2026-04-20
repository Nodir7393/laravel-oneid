<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('pinfl', 20)->nullable()->unique()->index();
            $table->string('passport', 20)->nullable()->index();
            $table->string('egov_login')->nullable()->index();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('full_name')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->enum('status', ['pending', 'active', 'blocked'])->default('pending')->index();
            $table->string('password');
            $table->string('org_name')->nullable();
            $table->string('org_stir', 20)->nullable();
            $table->string('position')->nullable();
            $table->string('region')->nullable();
            $table->string('district')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
