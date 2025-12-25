@include('layout.header')
<h3>Edit Kategori</h3>
<form action="{{ route('mahasiswa.update', $mahasiswa->id) }}" method="post">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label for="">Nama Mahasiswa</label>
        <input type="text" name="nama" value="{{ $mahasiswa->nama }}" placeholder="">
    </div>
    <div class="form-group">
        <label for="">NIM</label>
        <input type="text" name="nim" value="{{ $mahasiswa->nim }}" placeholder="">
    </div>
    <div class="form-group">
        <label for="">Judul Skripsi</label>
        <input type="text" name="skripsi" value="{{ $mahasiswa->skripsi }}" placeholder="">
    </div>
    <div class="form-group">
        <label for="">Bidang Keahlian</label>
        <div>
            @foreach ($keahlian as $k)
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="keahlian" value="{{ $k->id }}"
                        id="keahlian{{ $k->id }}" {{ old('keahlian', $selectedKeahlian) == $k->id ? 'checked' : '' }}
                        required>
                    <label class="form-check-label" for="keahlian{{ $k->id }}">
                        {{ $k->nama }}
                    </label>
                </div>
            @endforeach
        </div>
    </div>

    <div class="form-group">
        <label for="">Dosen Pembimbing (Pilih 2)</label>
        <div id="dosenContainer">
            <p class="text-muted">Pilih bidang keahlian terlebih dahulu</p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            // Data dosen yang sudah dipilih dari controller
            const selectedDosen = @json($selectedDosen ?? []);

            // Function untuk load dosen berdasarkan keahlian
            function loadDosen(keahlianId, preSelectedDosen = []) {
                const dosenContainer = $('#dosenContainer');
                dosenContainer.html('<p class="text-muted">Memuat data dosen...</p>');

                $.ajax({
                    url: `/dosen/keahlian/${keahlianId}`,
                    method: 'GET',
                    success: function (data) {
                        if (data.length > 0) {
                            let html = '';
                            data.forEach(function (dosen) {
                                // Check apakah dosen ini sudah dipilih sebelumnya
                                const isChecked = preSelectedDosen.includes(dosen.id) ? 'checked' : '';

                                html += `
                                <div class="form-check">
                                    <input class="form-check-input dospem-checkbox" 
                                           type="checkbox" 
                                           name="dosen_pembimbing[]"
                                           value="${dosen.id}" 
                                           id="dospem${dosen.id}"
                                           ${isChecked}>
                                    <label class="form-check-label" for="dospem${dosen.id}">
                                        ${dosen.nama} - ${dosen.nip}
                                    </label>
                                </div>
                            `;
                            });
                            dosenContainer.html(html);

                            // Update disabled state setelah load
                            updateCheckboxState();
                        } else {
                            dosenContainer.html('<p class="text-danger">Tidak ada dosen dengan keahlian ini</p>');
                        }
                    },
                    error: function (xhr) {
                        console.error('Error:', xhr);
                        dosenContainer.html('<p class="text-danger">Gagal memuat data dosen</p>');
                    }
                });
            }

            // Function untuk update state checkbox (disable jika sudah 2)
            function updateCheckboxState() {
                const checkedCount = $('.dospem-checkbox:checked').length;
                if (checkedCount >= 2) {
                    $('.dospem-checkbox:not(:checked)').prop('disabled', true);
                } else {
                    $('.dospem-checkbox').prop('disabled', false);
                }
            }

            // Load dosen saat halaman pertama kali dibuka (untuk edit)
            const selectedKeahlian = $('input[name="keahlian"]:checked').val();
            if (selectedKeahlian) {
                loadDosen(selectedKeahlian, selectedDosen);
            }

            // Load dosen saat keahlian dipilih/diubah
            $('input[name="keahlian"]').on('change', function () {
                const keahlianId = $(this).val();
                // Jika keahlian diubah, reset selected dosen (tidak pre-check)
                loadDosen(keahlianId, []);
            });

            // Validasi tepat 2 checkbox saat submit
            $('#formMahasiswa').on('submit', function (e) {
                const checked = $('.dospem-checkbox:checked').length;

                if (checked !== 2) {
                    e.preventDefault();
                    alert('Harus memilih tepat 2 dosen pembimbing!');
                    return false;
                }
            });

            // Disable checkbox jika sudah 2 terpilih
            $(document).on('change', '.dospem-checkbox', function () {
                updateCheckboxState();
            });
        });
    </script>
    <button type="submit" class="tombol">Update</button>
</form>
@include('layout.footer')