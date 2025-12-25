@include('layout.header')
<style>
    input,
    select,
    textarea,
    button {
        width: 100%;
        padding: 10px;
        margin: 8px 0;
        border: 1px solid #ccc;
        border-radius: 5px;
    }
</style>
<h3>Data Mahasiswa</h3>
<a href="{{ route('mahasiswa.create') }}" class="tombol">Tambah</a>
<table border="1">
    <thead>
        <tr>
            <th>No</th>
            <th>NIM</th>
            <th>Nama</th>
            <th>Skripsi</th>
            <th>Keahlian</th>
            <th>Dosen Pembimbing</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse($allMahasiswa as $key => $m)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $m->nim }}</td>
                <td>{{ $m->nama }}</td>
                <td>{{ $m->skripsi }}</td>
                <td>{{ $m->keahlian->nama ?? '-' }}</td>
                <td>
                    @foreach($m->dosens as $dosen)
                        {{ $dosen->nama }} - {{ $dosen->nip }}<br>
                    @endforeach
                </td>
                <td>
                    <div style="display: flex; gap: 5px;">
                    <a href="{{ route('mahasiswa.edit', $m->id) }}" class="tombol" style="flex: 1; text-align: center;">Edit</a>
                    </div>
                    <form action="{{ route('mahasiswa.destroy', $m->id) }}" method="POST" style="flex: 1; text-align: center;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="tombol" onclick="return confirm('Yakin hapus?')">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7">Tidak ada data</td>
            </tr>
        @endforelse
    </tbody>
</table>

@include('layout.footer')