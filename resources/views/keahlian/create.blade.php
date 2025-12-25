@include('layout.header')
    <h3>Buat Keahlian</h3>
    <form action="{{ route('keahlian.store') }}" method="post">
        @csrf
        <div class="form-group">
            <label for="">Nama Keahlian</label>
            <input type="text" name="nama" placeholder="Keahlian">
        </div>
        <button type="submit" class="tombol">Tambah</button>

    </form>
@include('layout.footer')