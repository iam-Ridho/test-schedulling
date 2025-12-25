@include('layout.dosen')
<h3>Daftar Dosen</h3>
<a href="{{ route('dosen.create') }}" class="tombol">Tambah</a>
<table>
    <thead>
        <tr>
            <th>No.</th>
            <th>Nama Dosen</th>
            <th>NIP</th>
            <th>No.hp</th>
            <th>Bidang Keahlian</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($allDosen as $key => $r)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $r->nama }}</td>
                <td>{{ $r->nip }}</td>
                <td>{{ $r->telepon }}</td>
                <td>
                    @if($r->keahlians->count() > 0)
                        {{ $r->keahlians->pluck('nama')->join(', ') }}
                    @else
                        <span class="text-muted">Tidak ada keahlian</span>
                    @endif
                </td>
                <td>
                    <form action="{{ route('dosen.destroy', $r->id) }}" method="POST">
                        <a href="{{ route('dosen.show', $r->id) }}" class="tombol">Detail</a>
                        <a href="{{ route('dosen.edit', $r->id) }}" class="tombol">Edit</a>
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