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
        Schema::create($this->prefix . 'permit_giver_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained($this->prefix . 'permit_users')->cascadeOnDelete();
            $table->foreignId('rule_id')->constrained($this->prefix . 'permit_rules')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->prefix . 'permit_giver_rules');
    }
};
