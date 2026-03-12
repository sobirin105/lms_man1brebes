<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $role;

    public function __construct($role = null)
    {
        $this->role = $role;
    }

    public function collection()
    {
        $query = User::with('role');
        if ($this->role) {
            $query->whereHas('role', function($q) {
                $q->where('name', $this->role);
            });
        }
        return $query->get();
    }

    public function headings(): array
    {
        $headings = ['Nama', 'Email', 'Role', 'Status', 'Alamat', 'Telepon'];
        if ($this->role === 'siswa') $headings[] = 'NIS';
        if ($this->role === 'guru') $headings[] = 'NIP';
        return $headings;
    }

    public function map($user): array
    {
        $map = [
            $user->name,
            $user->email,
            $user->role->display_name ?? $user->role->name,
            $user->is_active ? 'Aktif' : 'Non-Aktif',
            $user->address,
            $user->phone,
        ];
        if ($this->role === 'siswa') $map[] = $user->nis;
        if ($this->role === 'guru') $map[] = $user->nip;
        return $map;
    }
}
