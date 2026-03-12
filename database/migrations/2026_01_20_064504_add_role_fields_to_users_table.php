<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->after('id')->constrained('roles')->onDelete('cascade');
            $table->string('nis')->nullable()->unique()->after('email'); // Nomor Induk Siswa
            $table->string('nip')->nullable()->unique()->after('nis'); // Nomor Induk Pegawai (Guru)
            $table->string('phone')->nullable()->after('nip');
            $table->text('address')->nullable()->after('phone');
            $table->string('photo')->nullable()->after('address');
            $table->boolean('is_active')->default(true)->after('photo');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn(['role_id', 'nis', 'nip', 'phone', 'address', 'photo', 'is_active']);
        });
    }
};
