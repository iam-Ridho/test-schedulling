@include('layout.header')
    <h3>Buat Ruangan</h3>
    <form action="{{ route('ruangan.store') }}" method="post">
        @csrf
        <div class="form-group">
            <label for="">Nama Ruangan</label>
            <input type="text" name="nama" placeholder="">
        </div>
        <button type="submit" class="tombol">Tambah</button>

    </form>
@include('layout.footer')