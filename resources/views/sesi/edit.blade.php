@include('layout.header')
    <h3>Edit Sesi</h3>
    <form action="{{ route('sesi.update', $sesi->id) }}" method="post">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="">Sesi</label>
            <input type="text" name="hari" value="{{ $sesi->hari }}">
            <input type="time" name="jam_sesi" value="{{ $sesi->jam_sesi }}">
        </div>
        <button type="submit" class="tombol">Update</button>
    </form>
@include('layout.footer')