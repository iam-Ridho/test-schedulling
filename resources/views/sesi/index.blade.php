@include('layout.header')
<h3>Sesi</h3>
<a href="{{ route('sesi.create') }}" class="tombol">Tambah</a>
<table>
    <thead>
        <tr>
            <th>No.</th>
            <th>Hari</th>
            <th>Jam Sesi</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($allSesi as $key => $r)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $r->hari }}</td>
                <td>{{ \Carbon\Carbon::parse($r->jam_sesi)->format('h:i A') }}</td>
                <td>
                    <form action="{{ route('sesi.destroy', $r->id) }}" method="POST">
                        <a href="{{ route('sesi.edit', $r->id) }}" class="tombol">Edit</a>
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="tombol">Hapus</button>

                    </form>
                </td>
            </tr>

        @endforeach
    </tbody>
</table>
@include('layout.footer')