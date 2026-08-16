<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kode_obat')->unique();
            $table->string('nama_obat');
            $table->string('kategori');
            $table->string('satuan');
            $table->decimal('harga_beli', 15, 2);
            $table->decimal('harga_jual', 15, 2);
            $table->unsignedInteger('stok')->default(0);
            $table->unsignedInteger('stok_minimum')->default(10);
            $table->date('tanggal_expired');
            $table->unsignedTinyInteger('prioritas_owner')->default(3);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
