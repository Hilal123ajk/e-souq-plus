<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('material')->nullable()->after('description');
            $table->string('finish')->nullable()->after('material');
            $table->boolean('has_dimensions')->default(false)->after('finish');
            $table->decimal('thickness', 10, 2)->nullable()->after('has_dimensions');
            $table->decimal('height', 10, 2)->nullable()->after('thickness');
            $table->decimal('width', 10, 2)->nullable()->after('height');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'material',
                'finish',
                'has_dimensions',
                'thickness',
                'height',
                'width',
            ]);
        });
    }
};
