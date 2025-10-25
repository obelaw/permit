<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Twist\Base\BaseMigration;

return new class extends BaseMigration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table($this->prefix . 'permit_users', function (Blueprint $table) {
            $table->foreignId('created_by')->after('id')->nullable()->constrained($this->prefix . 'permit_users')->cascadeOnDelete();
            $table->boolean('can_create')->after('lang')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table($this->prefix . 'permit_users', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
            $table->dropColumn('can_create');
        });
    }
};
