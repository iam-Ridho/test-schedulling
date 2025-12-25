@include('layout.header')
<h3>Detail Sesi</h3>
<table>
    <tbody>
        <tr>
            <td width="150px">Hari</td>
            <td width="2px">:</td>
            <td>{{ $sesi->hari }}</td>
        </tr>
        <tr>
            <td width="150px">Sesi</td>
            <td width="2px">:</td>
            <td>{{ $sesi->jam_sesi }}</td>
        </tr>
    </tbody>
                
</table>
@include('layout.footer')