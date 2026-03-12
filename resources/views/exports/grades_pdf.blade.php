<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rekap Nilai Siswa</title>
    <style>
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 11px; 
            color: #333;
            margin: 0;
            padding: 0;
        }
        .kop-surat {
            border-bottom: 3px double #000;
            margin-bottom: 20px;
            padding-bottom: 10px;
            text-align: center;
        }
        .kop-surat h1 { margin: 0; font-size: 20px; text-transform: uppercase; }
        .kop-surat h2 { margin: 5px 0; font-size: 16px; }
        .kop-surat p { margin: 2px 0; font-style: italic; font-size: 10px; }

        .title { text-align: center; margin-bottom: 20px; }
        .title h3 { margin: 0; font-size: 14px; text-decoration: underline; text-transform: uppercase; }

        .info { margin-bottom: 15px; }
        .info table { width: 100%; border: none; }
        .info td { padding: 2px 0; border: none; font-weight: bold; }

        .grade-table { 
            width: 100%; 
            border-collapse: collapse; 
            table-layout: fixed; /* Ensures even column distribution */
        }
        .grade-table th, .grade-table td { 
            border: 1px solid #000; 
            padding: 6px 4px; 
            text-align: center; 
            word-wrap: break-word; /* Prevents overflow */
        }
        .grade-table th { 
            background-color: #f0f4f8; 
            font-size: 9px;
            text-transform: uppercase;
        }
        .grade-table td { font-size: 10px; }
        .text-left { text-align: left !important; padding-left: 8px !important; }
        
        .name-col { width: 25%; }
        .no-col { width: 30px; }
        .final-col { width: 60px; background-color: #fff9c4 !important; font-weight: bold; }

        .footer { 
            margin-top: 30px; 
            width: 100%;
        }
        .footer table { width: 100%; }
        .footer .signature-box { 
            width: 250px; 
            text-align: center; 
            float: right;
        }
        .signature-space { height: 70px; }
        
        @page {
            margin: 1cm;
        }
    </style>
</head>
<body>
    <div class="kop-surat">
        <h1>{{ $school_name }}</h1>
        <p>{{ $school_address }} | Telp: {{ $school_phone }}</p>
        <p>Website: {{ $school_website }}</p>
    </div>

    <div class="title">
        <h3>REKAPITULASI NILAI {{ strtoupper($subject->name) }}</h3>
    </div>

    <div class="info">
        <table cellspacing="0" cellpadding="0">
            <tr>
                <td width="15%">KELAS</td>
                <td width="2%">:</td>
                <td width="33%">{{ strtoupper($class->name) }}</td>
                <td width="15%">TAHUN PELAJARAN</td>
                <td width="2%">:</td>
                <td width="33%">{{ date('Y') }}/{{ date('Y') + 1 }}</td>
            </tr>
            <tr>
                <td>GURU PENGAMPU</td>
                <td>:</td>
                <td>{{ strtoupper($teacher->name) }}</td>
                <td>SEMESTER</td>
                <td>:</td>
                <td>-</td>
            </tr>
        </table>
    </div>

    <table class="grade-table">
        <thead>
            <tr>
                <th class="no-col">NO</th>
                <th class="name-col text-left">NAMA SISWA</th>
                @foreach($categories as $cat)
                    <th>{{ $cat }}</th>
                @endforeach
                <th class="final-col">RATA-RATA</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $student)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left">{{ strtoupper($student->name) }}</td>
                    @php $total = 0; $count = 0; @endphp
                    @foreach($categories as $cat)
                        @php 
                            $grade = $grades->where('student_id', $student->id)->where('grade_type', $cat)->first();
                            $score = $grade ? $grade->score : 0;
                            if($grade) { $total += $score; $count++; }
                        @endphp
                        <td>{{ $grade ? number_format($score, 0) : '-' }}</td>
                    @endforeach
                    <td class="final-col">
                        {{ $count > 0 ? number_format($total / $count, 1) : '0' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="signature-box">
            <p>{{ $location }}, {{ date('d') }} {{ ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][date('n')-1] }} {{ date('Y') }}</p>
            <p>Guru Mata Pelajaran,</p>
            <div class="signature-space"></div>
            <p><strong>{{ strtoupper($teacher->name) }}</strong></p>
            <p>NIP. {{ $teacher->nip ?? '-' }}</p>
        </div>
        <div style="clear: both;"></div>
    </div>
</body>
</html>
