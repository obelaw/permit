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
        Schema::table($this->prefix . 'permit_users', function (Blueprint $table) {
            if (!Schema::hasColumn($this->prefix . 'permit_users', 'is_suspend')) {
                $table->timestamp('is_suspend')->after('is_active')->nullable();
            }

            if (!Schema::hasColumn($this->prefix . 'permit_users', 'last_active_at')) {
                $table->timestamp('last_active_at')->after('is_suspend')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table($this->prefix . 'permit_users', function (Blueprint $table) {
            if (Schema::hasColumn($this->prefix . 'permit_users', 'last_active_at')) {
                $table->dropColumn('last_active_at');
            }

            if (Schema::hasColumn($this->prefix . 'permit_users', 'is_suspend')) {
                $table->dropColumn('is_suspend');
            }
        });
    }
};
