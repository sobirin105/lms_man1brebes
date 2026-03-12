<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class UsersTemplateExport implements FromCollection, WithHeadings
{
    protected $role;

    public function __construct($role)
    {
        $this->role = $role;
    }

    public function collection()
    {
        // Return empty collection for template
        return new Collection();
    }

    public function headings(): array
    {
        // Column names MUST match the UserImport heading keys (lowercase)
        $headings = ['nama', 'email', 'alamat', 'telepon'];
        
        if ($this->role === 'siswa') {
            $headings[] = 'nis';
        } elseif ($this->role === 'guru') {
            $headings[] = 'nip';
        }

        return $headings;
    }
}
