<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->string('nomor_batch')->nullable();
            $table->unsignedInteger('qty');
            $table->decimal('harga_beli', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->date('tanggal_expired');
            $table->unsignedInteger('qty_retur')->default(0);
            $table->string('status_retur')->default('belum_retur');
            $table->date('tanggal_retur')->nullable();
            $table->text('catatan_retur')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_details');
    }
};
