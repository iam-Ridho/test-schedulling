@include('layout.header')

<div class="container">

    {{-- Alert Messages --}}
    @if(session('success') || session('warning'))
        <div class="{{ session('success') ? 'alert-success' : 'alert-warning' }}">
            <i class="bi {{ session('success') ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' }}"></i>
            <strong>{{ session('success') ? 'Berhasil!' : 'Peringatan!' }}</strong>
            {{ session('success') ?? session('warning') }}

            @if(session('jadwal_stats'))
                <div class="alert-stats">
                    <strong>📊 Statistik:</strong>
                    <ul>
                        <li>Total mahasiswa input: {{ session('jadwal_stats')['total'] }}</li>
                        <li>✅ Berhasil dijadwalkan: {{ session('jadwal_stats')['terjadwal'] }}</li>
                        @if(session('jadwal_stats')['tidak_terjadwal_count'] > 0)
                            <li style="color: #d9534f;">
                                ❌ Tidak dapat dijadwalkan: {{ session('jadwal_stats')['tidak_terjadwal_count'] }}
                            </li>
                        @endif

                        @if(session('jadwal_stats')['has_mismatch'])
                            <li style="color: #ff6b6b; font-weight: bold;">
                                ⚠️ PERINGATAN: Ada ketidakcocokan data! Periksa log untuk detail.
                            </li>
                        @endif
                    </ul>

                    @php
                        $total = session('jadwal_stats')['total'];
                        $terjadwal = session('jadwal_stats')['terjadwal'];
                        $tidakTerjadwal = session('jadwal_stats')['tidak_terjadwal_count'];
                        $sum = $terjadwal + $tidakTerjadwal;
                    @endphp

                    @if($sum !== $total)
                        <li class="alert-debug">
                            <strong>🔍 Debug Info:</strong><br>
                            Total Input: {{ $total }} |
                            Terjadwal: {{ $terjadwal }} |
                            Tidak Terjadwal: {{ $tidakTerjadwal }} |
                            <strong>Hilang: {{ $total - $sum }}</strong>
                        </li>
                    @endif
                </div>
            @endif
        </div>
    @endif

    @if(session('error'))
        <div class="alert-error">
            <i class="bi bi-exclamation-triangle-fill"></i> <strong>Error!</strong> {{ session('error') }}
        </div>
    @endif

    {{-- Form Generate Jadwal --}}
    <h3></i> Generate Jadwal</h3>

    <form action="{{ route('mahasiswa.jadwal.generate') }}" method="POST" id="generateForm">
        @csrf

        <div class="form-group">
            <label for="max_time_seconds">
                <strong>Timeout (detik)</strong>
                <small style="color: #999;">- Waktu maksimal untuk mencari solusi terbaik</small>
            </label>
            <input type="number" name="max_time_seconds" id="max_time_seconds" value="60" min="30" max="300" required>
            <small style="color: #999; display: block; margin-top: 5px;">
                Rekomendasi: 60-120 detik untuk hasil optimal. Semakin lama, semakin baik hasilnya.
            </small>
        </div>

        <div class="info-box">
            <i class="bi bi-info-circle-fill"></i> <strong>Informasi:</strong>
            <ul>
                <li>API akan memilih dosen penguji yang memiliki keahlian sesuai dengan bidang skripsi</li>
                <li>Penguji dipilih yang <strong>bukan pembimbing</strong> mahasiswa tersebut</li>
                <li>Jadwal akan dipilih berdasarkan ketersediaan semua dosen (pembimbing + penguji)</li>
                <li>Ruangan akan dialokasikan secara optimal</li>
            </ul>
        </div>

        <button type="submit" class="tombol" id="btnGenerate" style="padding: 10px 20px; font-size: 16px;">
            <i class="bi bi-calendar-check"></i> Generate Jadwal Sekarang
        </button>
    </form>

    @if(session('jadwal_result'))
        <div class="button-actions">
            <form action="{{ route('mahasiswa.jadwal.clear') }}" method="POST" style="display: inline;"
                onsubmit="return confirm('Yakin ingin menghapus semua jadwal yang tersimpan?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="tombol btn-delete">
                    <i class="bi bi-trash"></i> Hapus Jadwal
                </button>
            </form>
        </div>
    @endif

    {{-- Hasil Jadwal --}}
    @if(session('jadwal_result'))
        <h3><i class="bi bi-clipboard-check"></i> Hasil Jadwal ({{ count(session('jadwal_result')) }} Mahasiswa)</h3>

        {{-- Toggle View --}}
        <div class="view-toggle">
            <button class="tombol active" id="btnCalendarView" onclick="toggleView('calendar')">
                <i class="bi bi-calendar3"></i> Lihat Kalender
            </button>
            <button class="tombol" id="btnTableView" onclick="toggleView('table')">
                <i class="bi bi-table"></i> Lihat Tabel
            </button>
        </div>

        {{-- Notifikasi Mahasiswa Tidak Terjadwal --}}
        @if(session('unscheduled_students') && count(session('unscheduled_students')) > 0)
            <div class="unscheduled-warning">
                <div class="unscheduled-header">
                    <h3>
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span>Mahasiswa Tidak Terjadwal ({{ count(session('unscheduled_students')) }})</span>
                    </h3>
                    <button class="tombol btn-toggle-detail" onclick="toggleUnscheduledDetail()" id="btnToggleUnscheduled">
                        <i class="bi bi-chevron-down" id="iconToggleUnscheduled"></i> Detail
                    </button>
                </div>

                <p class="unscheduled-intro">
                    <strong>{{ count(session('unscheduled_students')) }} mahasiswa</strong> tidak dapat dijadwalkan karena
                    kendala berikut. Silakan perbaiki masalah dan generate ulang.
                </p>

                {{-- Collapsible Detail Section --}}
                <div id="unscheduledDetailSection" style="display: none;">
                    <div class="detail-scroll-container">
                        @foreach(session('unscheduled_students') as $index => $student)
                            <div class="student-card">
                                <div class="student-header">
                                    <div>
                                        <strong class="student-name">
                                            <i class="bi bi-person-circle"></i> {{ $index + 1 }}. {{ $student['nama'] }}
                                        </strong>
                                        @if(isset($student['nim']) && $student['nim'] !== '-')
                                            <span class="student-nim">NIM: {{ $student['nim'] }}</span>
                                        @endif
                                    </div>
                                    @if(isset($student['bidang']) && $student['bidang'] !== '-')
                                        <span class="student-badge">{{ $student['bidang'] }}</span>
                                    @endif
                                </div>

                                @if(isset($student['judul']) && $student['judul'] !== '-')
                                    <div class="student-title">
                                        <i class="bi bi-journal-text"></i>
                                        <small>{{ $student['judul'] }}</small>
                                    </div>
                                @endif

                                @if((isset($student['pembimbing1']) && $student['pembimbing1'] !== '-') ||
                                    (isset($student['pembimbing2']) && $student['pembimbing2'] !== '-'))
                                    <div class="student-supervisors">
                                        <i class="bi bi-people-fill"></i> <strong>Pembimbing:</strong>
                                        @if(isset($student['pembimbing1']) && $student['pembimbing1'] !== '-')
                                            {{ $student['pembimbing1'] }}
                                        @endif
                                        @if(isset($student['pembimbing2']) && $student['pembimbing2'] !== '-')
                                            {{ isset($student['pembimbing1']) && $student['pembimbing1'] !== '-' ? ', ' : '' }}
                                            {{ $student['pembimbing2'] }}
                                        @endif
                                    </div>
                                @endif

                                <div class="student-reason">
                                    <div class="student-reason-content">
                                        <i class="bi bi-info-circle-fill"></i>
                                        <div class="student-reason-text">
                                            <strong>Alasan:</strong>
                                            <p>{{ $student['reason'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="suggestions-box">
                        <strong>
                            <i class="bi bi-lightbulb-fill"></i> Saran Penyelesaian:
                        </strong>
                        <ul>
                            <li>Pastikan setiap mahasiswa memiliki 2 pembimbing yang valid</li>
                            <li>Pastikan ada minimal 2 dosen dengan keahlian sesuai bidang skripsi untuk menjadi penguji</li>
                            <li>Periksa ketersediaan dosen - pastikan ada cukup slot waktu tersedia</li>
                            <li>Tambahkan lebih banyak ruangan atau slot waktu jika diperlukan</li>
                            <li>Coba tingkatkan timeout (60-120 detik) untuk hasil lebih optimal</li>
                            <li>Periksa apakah ada konflik jadwal pada dosen pembimbing</li>
                        </ul>
                    </div>
                </div>

                <div class="quick-summary">
                    <strong>📊 Ringkasan Masalah Umum:</strong>
                    <div class="summary-grid">
                        @php
                            $reasonCounts = [];
                            foreach (session('unscheduled_students') as $student) {
                                $reason = $student['reason'] ?? 'Unknown';
                                if (str_contains($reason, 'penguji') || str_contains($reason, 'keahlian')) {
                                    $key = 'Tidak ada dosen penguji yang sesuai';
                                } elseif (str_contains($reason, 'waktu') || str_contains($reason, 'slot')) {
                                    $key = 'Tidak ada slot waktu tersedia';
                                } elseif (str_contains($reason, 'ruangan') || str_contains($reason, 'room')) {
                                    $key = 'Tidak ada ruangan tersedia';
                                } elseif (str_contains($reason, 'pembimbing')) {
                                    $key = 'Masalah data pembimbing';
                                } else {
                                    $key = 'Konflik resource / constraint';
                                }
                                $reasonCounts[$key] = ($reasonCounts[$key] ?? 0) + 1;
                            }
                            arsort($reasonCounts);
                        @endphp

                        @foreach($reasonCounts as $reason => $count)
                            <div class="summary-item">
                                <strong>{{ $count }}x</strong> {{ $reason }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>  
        @endif

        {{-- Calendar View --}}
        <div id="calendarView">
            <div id='calendar'></div>
        </div>

        {{-- Table View --}}
        <div id="tableView" style="display: none;">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Mahasiswa</th>
                        <th>Judul Skripsi</th>
                        <th>Bidang</th>
                        <th>Pembimbing</th>
                        <th>Penguji</th>
                        <th>Jadwal</th>
                        <th>Ruang</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(session('jadwal_result') as $index => $jadwal)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $jadwal['nama'] }}</strong></td>
                            <td><small>{{ $jadwal['judul'] }}</small></td>
                            <td><span class="student-badge">{{ $jadwal['bidang'] }}</span></td>
                            <td>
                                <small>
                                    <i class="bi bi-person-fill"></i> {{ $jadwal['pembimbing1'] }}<br>
                                    <i class="bi bi-person-fill"></i> {{ $jadwal['pembimbing2'] }}
                                </small>
                            </td>
                            <td>
                                <small>
                                    <i class="bi bi-person-badge"></i> {{ $jadwal['penguji1'] }}<br>
                                    <i class="bi bi-person-badge"></i> {{ $jadwal['penguji2'] }}
                                </small>
                            </td>
                            <td><i class="bi bi-clock"></i> {{ $jadwal['sesi'] }}</td>
                            <td><i class="bi bi-door-open"></i> {{ $jadwal['ruang'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            <i class="bi bi-calendar-x"></i>
            <h3>Belum Ada Jadwal</h3>
            <p>Klik tombol "Generate Jadwal" untuk membuat jadwal ujian otomatis.</p>
        </div>
    @endif
</div>

{{-- Modal for Event Detail (unchanged) --}}
<div id="eventModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="bi bi-calendar-check"></i> Detail Ujian Skripsi</h3>
        </div>
        <div class="modal-body" id="modalBody"></div>
        <div class="modal-footer">
            <button class="tombol close-modal" onclick="hideModal('eventModal')">
                <i class="bi bi-x-circle"></i> Tutup
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize FullCalendar if jadwal exists
        @if(session('jadwal_result'))
            initializeCalendar();
        @endif

        // Form submit handler
        const form = document.getElementById('generateForm');
        const btnGenerate = document.getElementById('btnGenerate');

        if (form && btnGenerate) {
            form.addEventListener('submit', function (e) {
                btnGenerate.disabled = true;
                btnGenerate.innerHTML = '<i class="bi bi-hourglass-split"></i> Generating...';
            });
        }
    });

    function initializeCalendar() {
        const jadwalData = @json(session('jadwal_result', []));

        if (!jadwalData || jadwalData.length === 0) {
            console.error('No jadwal data available');
            return;
        }

        const events = jadwalData.map((jadwal, index) => {
            try {
                // Parse sesi format: "Senin 08.00-10.00"
                const sesiMatch = jadwal.sesi.match(/(\w+)\s+(\d{2}\.\d{2})-(\d{2}\.\d{2})/);

                if (!sesiMatch) {
                    console.error('Invalid sesi format:', jadwal.sesi);
                    return null;
                }

                const hari = sesiMatch[1];
                const startTime = sesiMatch[2].replace('.', ':');
                const endTime = sesiMatch[3].replace('.', ':');

                console.log('📅 Parsing:', { hari, startTime, endTime });

                const today = new Date();

                const currentDay = today.getDay();
                const daysUntilMonday = currentDay === 0 ? 1 : (8 - currentDay);

                const monday = new Date(today);
                monday.setDate(today.getDate() + daysUntilMonday);
                monday.setHours(0, 0, 0, 0);

                console.log('📆 Base Monday:', monday.toLocaleDateString('id-ID'));

                const hariOffset = {
                    'Senin': 0,
                    'Selasa': 1,
                    'Rabu': 2,
                    'Kamis': 3,
                    'Jumat': 4,
                    'Sabtu': 5,
                    'Minggu': 6
                };

                const offset = hariOffset[hari];
                if (offset === undefined) {
                    console.error('❌ Unknown day:', hari);
                    return null;
                }

                // Hitung tanggal event
                const eventDate = new Date(monday);
                eventDate.setDate(monday.getDate() + offset);

                console.log('📅 Event date for', hari, ':', eventDate.toLocaleDateString('id-ID'));

                // Parse waktu
                const [startHour, startMin] = startTime.split(':').map(Number);
                const [endHour, endMin] = endTime.split(':').map(Number);

                const startDateTime = new Date(eventDate);
                startDateTime.setHours(startHour, startMin, 0, 0);

                const endDateTime = new Date(eventDate);
                endDateTime.setHours(endHour, endMin, 0, 0);

                console.log('🕒 Event time:', {
                    start: startDateTime.toLocaleString('id-ID'),
                    end: endDateTime.toLocaleString('id-ID')
                });

                // Determine color based on bidang
                let className = 'event-default';
                const bidang = jadwal.bidang.toLowerCase();

                if (bidang.includes('ai') || bidang.includes('intelligence') || bidang.includes('kecerdasan')) {
                    className = 'event-ai';
                } else if (bidang.includes('web')) {
                    className = 'event-web';
                } else if (bidang.includes('mobile') || bidang.includes('android')) {
                    className = 'event-mobile';
                } else if (bidang.includes('game')) {
                    className = 'event-game';
                } else if (bidang.includes('network') || bidang.includes('jaringan')) {
                    className = 'event-network';
                }

                return {
                    id: index,
                    title: jadwal.nama,
                    start: startDateTime.toISOString(),
                    end: endDateTime.toISOString(),
                    className: className,
                    extendedProps: {
                        mahasiswa: jadwal.nama,
                        judul: jadwal.judul,
                        bidang: jadwal.bidang,
                        pembimbing1: jadwal.pembimbing1,
                        pembimbing2: jadwal.pembimbing2,
                        penguji1: jadwal.penguji1,
                        penguji2: jadwal.penguji2,
                        ruang: jadwal.ruang,
                        sesi: jadwal.sesi
                    }
                };
            } catch (error) {
                console.error('❌ Error parsing jadwal:', jadwal, error);
                return null;
            }
        }).filter(event => event !== null);

        console.log('✅ Total events:', events.length);
        if (events.length > 0) {
            console.log('📋 Sample event:', events[0]);
        }

        const calendarEl = document.getElementById('calendar');

        if (!calendarEl) {
            console.error('❌ Calendar element not found!');
            return;
        }

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',

            initialDate: function () {
                const today = new Date();
                const currentDay = today.getDay();
                const daysUntilMonday = currentDay === 0 ? 1 : (8 - currentDay);
                const monday = new Date(today);
                monday.setDate(today.getDate() + daysUntilMonday);
                return monday;
            }(),

            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            slotMinTime: '07:00:00',
            slotMaxTime: '18:00:00',
            slotDuration: '00:30:00',
            weekends: false,
            locale: 'id',
            buttonText: {
                today: 'Hari Ini',
                month: 'Bulan',
                week: 'Minggu',
                day: 'Hari',
                list: 'Daftar'
            },
            allDaySlot: false,
            height: 'auto',
            displayEventTime: true,
            displayEventEnd: true,

            // Custom event rendering
            eventContent: function (arg) {
                const sesiMatch = arg.event.extendedProps.sesi.match(/(\w+)\s+(\d{2}\.\d{2})-(\d{2}\.\d{2})/);
                const timeDisplay = sesiMatch ? `${sesiMatch[2]}-${sesiMatch[3]}` : '';

                return {
                    html: `
                        <div style="padding: 4px 6px; overflow: hidden; line-height: 1.3;">
                            <div style="font-weight: bold; font-size: 12px; white-space: normal; margin-bottom: 2px;">
                                ${arg.event.title}
                            </div>
                            <div style="font-size: 11px; opacity: 0.95; display: flex; align-items: center; gap: 3px;">
                                <i class="bi bi-clock" style="font-size: 10px;"></i>
                                <span>${timeDisplay}</span>
                            </div>
                            <div style="font-size: 11px; opacity: 0.9; display: flex; align-items: center; gap: 3px; margin-top: 1px;">
                                <i class="bi bi-door-open" style="font-size: 10px;"></i>
                                <span>${arg.event.extendedProps.ruang}</span>
                            </div>
                        </div>
                    `
                };
            },

            events: events,

            eventClick: function (info) {
                showEventDetail(info.event);
            },

            eventDidMount: function (info) {
                info.el.setAttribute('title',
                    info.event.extendedProps.mahasiswa + ' - ' +
                    info.event.extendedProps.ruang
                );
                info.el.style.cursor = 'pointer';

                info.el.style.minHeight = '60px';
            }
        });

        calendar.render();
        console.log('✅ Calendar rendered successfully');
    }

    function showEventDetail(event) {
        const props = event.extendedProps;

        const tableHTML = `
            <table style="margin-top: 0;">
                <tbody>
                    <tr>
                        <th width="35%"><i class="bi bi-person-circle"></i> Mahasiswa</th>
                        <td><strong>${props.mahasiswa}</strong></td>
                    </tr>
                    <tr>
                        <th><i class="bi bi-journal-text"></i> Judul Skripsi</th>
                        <td>${props.judul}</td>
                    </tr>
                    <tr>
                        <th><i class="bi bi-tag"></i> Bidang</th>
                        <td><span style="background: #4e73df; color: white; padding: 3px 8px; border-radius: 3px;">${props.bidang}</span></td>
                    </tr>
                    <tr>
                        <th><i class="bi bi-person-fill"></i> Pembimbing 1</th>
                        <td>${props.pembimbing1}</td>
                    </tr>
                    <tr>
                        <th><i class="bi bi-person-fill"></i> Pembimbing 2</th>
                        <td>${props.pembimbing2}</td>
                    </tr>
                    <tr>
                        <th><i class="bi bi-person-badge"></i> Penguji 1</th>
                        <td>${props.penguji1}</td>
                    </tr>
                    <tr>
                        <th><i class="bi bi-person-badge"></i> Penguji 2</th>
                        <td>${props.penguji2}</td>
                    </tr>
                    <tr>
                        <th><i class="bi bi-clock"></i> Jadwal</th>
                        <td>${props.sesi}</td>
                    </tr>
                    <tr>
                        <th><i class="bi bi-door-open"></i> Ruangan</th>
                        <td>${props.ruang}</td>
                    </tr>
                </tbody>
            </table>
        `;

        document.getElementById('modalBody').innerHTML = tableHTML;
        showModal('eventModal');
    }

    function toggleView(view) {
        const calendarView = document.getElementById('calendarView');
        const tableView = document.getElementById('tableView');
        const btnCalendar = document.getElementById('btnCalendarView');
        const btnTable = document.getElementById('btnTableView');

        if (view === 'calendar') {
            calendarView.style.display = 'block';
            tableView.style.display = 'none';
            btnCalendar.classList.add('active');
            btnTable.classList.remove('active');
        } else {
            calendarView.style.display = 'none';
            tableView.style.display = 'block';
            btnTable.classList.add('active');
            btnCalendar.classList.remove('active');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('generateForm');
        const btnGenerate = document.getElementById('btnGenerate');

        if (form && btnGenerate) {
            console.log('✅ Form dan button ditemukan');

            // Debug: Log saat form akan di-submit
            form.addEventListener('submit', function (e) {
                console.log('🚀 Form sedang disubmit...');
                console.log('📝 Form action:', form.action);
                console.log('📝 Form method:', form.method);
                console.log('📝 CSRF token:', form.querySelector('[name="_token"]')?.value);
                console.log('📝 Timeout value:', document.getElementById('max_time_seconds').value);

                // Disable button
                btnGenerate.disabled = true;
                btnGenerate.innerHTML = '<i class="bi bi-hourglass-split"></i> Generating...';


                // Safety timeout: re-enable button setelah 30 detik jika tidak ada response
                setTimeout(function () {
                    if (btnGenerate.disabled) {
                        console.log('⚠️ Timeout - Re-enabling button');
                        btnGenerate.disabled = false;
                        btnGenerate.innerHTML = '<i class="bi bi-calendar-check"></i> Generate Jadwal Sekarang';
                        alert('Request timeout. Silakan coba lagi atau periksa koneksi.');
                    }
                }, 30000);
            });

            // Debug: Log jika ada error pada form
            form.addEventListener('error', function (e) {
                console.error('❌ Form error:', e);
            });
        } else {
            console.error('❌ Form atau button tidak ditemukan!');
            if (!form) console.error('   - Form dengan ID "generateForm" tidak ada');
            if (!btnGenerate) console.error('   - Button dengan ID "btnGenerate" tidak ada');
        }
    });

    // Debug: Monitor network requests
    if (window.PerformanceObserver) {
        const observer = new PerformanceObserver((list) => {
            for (const entry of list.getEntries()) {
                if (entry.name.includes('jadwal.generate')) {
                    console.log('📡 Network request detected:', {
                        url: entry.name,
                        duration: entry.duration,
                        startTime: entry.startTime
                    });
                }
            }
        });
        observer.observe({ entryTypes: ['resource', 'navigation'] });
    }

    function toggleUnscheduledDetail() {
        const section = document.getElementById('unscheduledDetailSection');
        const icon = document.getElementById('iconToggleUnscheduled');
        const btn = document.getElementById('btnToggleUnscheduled');

        if (section.style.display === 'none') {
            section.style.display = 'block';
            icon.classList.remove('bi-chevron-down');
            icon.classList.add('bi-chevron-up');
            btn.innerHTML = '<i class="bi bi-chevron-up" id="iconToggleUnscheduled"></i> Sembunyikan';
        } else {
            section.style.display = 'none';
            icon.classList.remove('bi-chevron-up');
            icon.classList.add('bi-chevron-down');
            btn.innerHTML = '<i class="bi bi-chevron-down" id="iconToggleUnscheduled"></i> Detail';
        }
    }
</script>

@include('layout.footer')