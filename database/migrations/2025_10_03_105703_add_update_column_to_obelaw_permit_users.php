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
            // Check if columns exist before dropping them
            if (Schema::hasColumn($this->prefix . 'permit_users', 'name')) {
                $table->dropColumn('name');
            }
            if (Schema::hasColumn($this->prefix . 'permit_users', 'email')) {
                $table->dropColumn('email');
            }
            if (Schema::hasColumn($this->prefix . 'permit_users', 'password')) {
                $table->dropColumn('password');
            }
            if (Schema::hasColumn($this->prefix . 'permit_users', 'remember_token')) {
                $table->dropColumn('remember_token');
            }
            
            // Add morph columns if they don't exist
            if (!Schema::hasColumn($this->prefix . 'permit_users', 'authable_type')) {
                $table->string('authable_type')->after('id');
                $table->unsignedBigInteger('authable_id')->after('authable_type');
                $table->index(['authable_type', 'authable_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table($this->prefix . 'permit_users', function (Blueprint $table) {
            // Drop morph columns if they exist
            if (Schema::hasColumn($this->prefix . 'permit_users', 'authable_type')) {
                $table->dropIndex(['authable_type', 'authable_id']);
                $table->dropColumn(['authable_type', 'authable_id']);
            }
            
            // Add back original columns if they don't exist
            if (!Schema::hasColumn($this->prefix . 'permit_users', 'name')) {
                $table->string('name')->after('id');
            }
            if (!Schema::hasColumn($this->prefix . 'permit_users', 'email')) {
                $table->string('email')->unique()->after('name');
            }
            if (!Schema::hasColumn($this->prefix . 'permit_users', 'password')) {
                $table->string('password')->after('email');
            }
            if (!Schema::hasColumn($this->prefix . 'permit_users', 'remember_token')) {
                $table->rememberToken()->after('password');
            }
        });
    }
};
