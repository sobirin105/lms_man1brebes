<!DOCTYPE html>
<html>
<head>
    <title>Export Data {{ ucfirst($role ?? 'Pengguna') }}</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: right; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>DATA {{ strtoupper($role ?? 'PENGGUNA') }}</h2>
        <h3>LMS MAN 1 BREBES</h3>
        <p>Tanggal Cetak: {{ date('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Alamat</th>
                <th>Telepon</th>
                @if($role === 'siswa') <th>NIS</th> @endif
                @if($role === 'guru') <th>NIP</th> @endif
            </tr>
        </thead>
        <tbody>
            @foreach($users as $index => $user)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->role->display_name ?? $user->role->name }}</td>
                <td>{{ $user->is_active ? 'Aktif' : 'Non-Aktif' }}</td>
                <td>{{ $user->address }}</td>
                <td>{{ $user->phone }}</td>
                @if($role === 'siswa') <td>{{ $user->nis }}</td> @endif
                @if($role === 'guru') <td>{{ $user->nip }}</td> @endif
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Halaman: {PAGE_NUM}
    </div>
</body>
</html>
