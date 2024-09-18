<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Obelaw\Twist\Base\BaseMigration;

return new class extends BaseMigration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create($this->prefix . 'permit_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('has_all_permissions')->default(false);
            $table->json('resource_permissions')->nullable();
            $table->json('page_permissions')->nullable();
            $table->json('widget_permissions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->prefix . 'permit_rules');
    }
};
