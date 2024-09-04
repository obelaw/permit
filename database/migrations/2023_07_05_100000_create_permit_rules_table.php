<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('permit_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('has_all_permissions')->default(false);
            $table->json('resource_permissions')->nullable();
            $table->json('widget_permissions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permit_rules');
    }
};
