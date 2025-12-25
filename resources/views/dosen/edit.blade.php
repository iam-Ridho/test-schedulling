@include('layout.header')
    <h3>Edit Dosen</h3>
    <form action="{{ route('dosen.update', $dosen->id) }}" method="post">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="">Nama Dosen</label>
            <input type="text" name="nama" value="{{ $dosen->nama }}">
        </div>
        <div class="form-group">
            <label for="">NIP</label>
            <input type="text" name="nip" value="{{ $dosen->nip }}">
        </div>
        <div class="form-group">
            <label for="">No.hp</label>
            <input type="text" name="telepon" value="{{ $dosen->telepon }}">
        </div>
        <div class="form-group">
        <label>Bidang Keahlian</label>
        <div>
            @foreach ($keahlian as $k)
                <div class="form-check">
                    <input 
                        class="form-check-input" 
                        type="checkbox" 
                        name="keahlian[]" 
                        value="{{ $k->id }}" 
                        id="keahlian{{ $k->id }}"
                        {{ in_array($k->id, old('keahlian', $selectedKeahlian)) ? 'checked' : '' }}
                    >
                    <label class="form-check-label" for="keahlian{{ $k->id }}">
                        {{ $k->nama }}
                    </label>
                </div>
            @endforeach
        </div>

        <div class="form-group">
        <label>Sesi</label>
        <div class="checkbox-grid">
            @foreach ($sesi as $s)
                <div class="checkbox-item">
                    <input type="checkbox" 
                           name="sesi[]" 
                           value="{{ $s->id }}" 
                           id="sesi{{ $s->id }}"
                           {{ in_array($s->id, old('sesi', $selectedSesi)) ? 'checked' : '' }}
                           >
                    <label for="sesi{{ $s->id }}">{{ $s->hari }} - {{ \Carbon\Carbon::parse($s->jam_sesi)->format('h:i A') }}</label>
                </div>
            @endforeach
        </div>
    </div>

    </div>
        
        <button type="submit" class="tombol">Update</button>
    </form>
@include('layout.footer')    
    