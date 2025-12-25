@include('layout.header')
<h3>Keahlian</h3>
<a href="{{ route('keahlian.create') }}" class="tombol">Tambah</a>
<table>
    <thead>
        <tr>
            <th>No.</th>
            <th>Nama Keahlian</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($allKeahlian as $key => $r)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $r->nama }}</td>
                <td>
                    <form action="{{ route('keahlian.destroy', $r->id) }}" method="POST">
                        <a href="{{ route('keahlian.edit', $r->id) }}" class="tombol">Edit</a>
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