@include('layout.header')
<h3>Detail Keahlian</h3>
<table>
    <tbody>
        <tr>
            <td width="150px">Nama Keahlian</td>
            <td width="2px">:</td>
            <td>{{ $keahlian->nama }}</td>
        </tr>
    </tbody>
                
</table>
@include('layout.footer')