@include('layout.header')
    <h3>Edit Ruangan</h3>
    <form action="{{ route('ruangan.update', $ruangan->id) }}" method="post">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="">Nama Ruangan</label>
            <input type="text" name="nama" value="{{ $ruangan->nama }}">
        </div>
        <button type="submit" class="tombol">Update</button>
    </form>
@include('layout.footer')