@include('layout.header')
<h3>Dosen</h3>
<form action="{{ route('dosen.store') }}" method="post">
    @csrf
    <div class="form-group">
        <label for="">Nama Dosen</label>
        <input type="text" name="nama" placeholder="">
    </div>
    <div class="form-group">
        <label for="">NIP</label>
        <input type="text" name="nip" placeholder="">
    </div>
    <div class="form-group">
        <label for="">No.hp</label>
        <input type="text" name="telepon" placeholder="">
    </div>
    <div class="form-group">
        <label for="">Bidang Keahlian</label>
        <div>
            @foreach ($keahlian as $k)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="keahlian[]" value="{{ $k->id }}"
                        id="keahlian{{ $k->id }}">
                    <label class="form-check-label" for="keahlian{{ $k->id }}">
                        {{ $k->nama }}
                    </label>
                </div>
            @endforeach
        </div>
    </div>

    <div class="form-group">
        <label>Sesi</label>
        <div class="checkbox-grid">
            @foreach ($sesi as $s)
                <div class="checkbox-item">
                    <input type="checkbox" 
                           name="sesi[]" 
                           value="{{ $s->id }}" 
                           id="sesi_{{ $s->id }}">
                    <label for="sesi_{{ $s->id }}">{{ $s->hari }} - {{ \Carbon\Carbon::parse($s->jam_sesi)->format('h:i A') }}</label>
                </div>
            @endforeach
        </div>
    </div>

    <button type="submit" class="tombol">Tambah</button>

</form>
@include('layout.footer')