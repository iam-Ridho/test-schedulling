@include('layout.header')
<h3>Detail Dosen</h3>
<a href="{{ route('dosen.index') }}" class="tombol">Kembali</a>
<table>
    <tbody>
        <tr>
            <td width="150px">Nama Dosen</td>
            <td width="2px">:</td>
            <td>{{ $dosen->nama }}</td>
        </tr>
        <tr>
            <td width="150px">NIP</td>
            <td width="2px">:</td>
            <td>{{ $dosen->nip }}</td>
        </tr>
        <tr>
            <td width="150px">No.hp</td>
            <td width="2px">:</td>
            <td>{{ $dosen->telepon }}</td>
        </tr>
        <tr>
            <td width="150px">Keahlian</td>
            <td width="2px">:</td>
            <td>
                @if($dosen->keahlians->count() > 0)
                    {{ $dosen->keahlians->pluck('nama')->join(', ') }}
                @else
                    <span class="text-muted">Tidak ada keahlian</span>
                @endif
            </td>
        </tr>

        <tr>
            <td width="150px">Sesi</td>
            <td width="2px">:</td>
            <td>
                @if($dosen->sesis->count() > 0)
                    {{ $dosen->sesis->map(function ($sesi) {
                    $jam = \Carbon\Carbon::parse($sesi->jam)->format('h:i A');
                        return $sesi->hari . ' (' . $sesi->jam_sesi . ')';
                    })->join(', ') }}
                @else
                    <span class="text-muted">Tidak ada sesi</span>
                @endif
            </td>
        </tr>
    </tbody>

</table>
@include('layout.footer')