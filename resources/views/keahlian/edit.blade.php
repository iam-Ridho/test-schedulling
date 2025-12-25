@include('layout.header')
    <h3>Edit Keahlian</h3>
    <form action="{{ route('keahlian.update', $keahlian->id) }}" method="post">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="">Nama Keahlian</label>
            <input type="text" name="nama" value="{{ $keahlian->nama }}">
        </div>
        <button type="submit" class="tombol">Update</button>
    </form>
@include('layout.footer')