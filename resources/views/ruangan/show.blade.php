@include('layout.header')
<h3>Detail Ruangan</h3>
<table>
    <tbody>
        <tr>
            <td width="150px">Nama Ruangan</td>
            <td width="2px">:</td>
            <td>{{ $ruangan->nama }}</td>
        </tr>
    </tbody>
                
</table>
@include('layout.footer')