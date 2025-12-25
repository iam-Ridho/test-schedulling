@include('layout.header')
<h3>Ruangan</h3>
<a href="{{ route('ruangan.create') }}" class="tombol">Tambah</a>
<table>
    <thead>
        <tr>
            <th>No.</th>
            <th>Nama Ruangan</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($allRuangan as $key => $r)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $r->nama }}</td>
                <td>
                    <form action="{{ route('ruangan.destroy', $r->id) }}" method="POST">
                        <a href="{{ route('ruangan.edit', $r->id) }}" class="tombol">Edit</a>
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