@include('layout.header')
    <h3>Buat Sesi</h3>
    <form action="{{ route('sesi.store') }}" method="post">
        @csrf
        <div class="form-group">
            <label for="">Hari</label>
            <input type="text" name="hari" placeholder="">
        </div>
        <div class="form-group">
            <label for="">Jam Sesi</label>
            <input type="time" name="jam_sesi" placeholder="">
        </div>
        <button type="submit" class="tombol">Tambah</button>

    </form>
@include('layout.footer')