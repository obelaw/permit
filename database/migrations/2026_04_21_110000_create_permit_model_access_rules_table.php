<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Obelaw\Twist\Base\BaseMigration;

return new class extends BaseMigration
{
    public function up(): void
    {
        Schema::create($this->prefix . 'permit_model_access_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')
                ->constrained($this->prefix . 'permit_rules')
                ->cascadeOnDelete();
            $table->string('model_path');   // e.g. App\Models\Warehouse
            $table->string('field');
            $table->string('operator');     // =, !=, in, not_in, >, <, like
            $table->text('value');          // comma-separated for in/not_in
            $table->string('boolean')->default('and'); // and | or
            $table->timestamps();

            $table->index(['rule_id', 'model_path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix . 'permit_model_access_rules');
    }
};
