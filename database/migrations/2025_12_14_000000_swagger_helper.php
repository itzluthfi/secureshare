<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Run this to generate comprehensive Swagger annotations
        // php artisan l5-swagger:generate
        
        echo "Swagger Annotations Helper\n";
        echo "===========================\n";
        echo "To generate/update Swagger documentation:\n";
        echo "php artisan l5-swagger:generate\n\n";
        echo "View documentation at:\n";
        echo "http://localhost:8000/api/documentation\n";
    }

    public function down(): void
    {
        //
    }
};
