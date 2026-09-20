<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Lengkapi Data Survei</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

<div class="container">
    <div class="stepper">
        <div class="step-item active" id="stepIndicator1">① Data Diri</div>
        <div class="step-arrow">→</div>
        <div class="step-item" id="stepIndicator2">② Alamat</div>
        <div class="step-arrow">→</div>
        <div class="step-item" id="stepIndicator3">③ Lokasi</div>
    </div>

    <div class="step-container" id="stepContainer">
        
        <!-- Step 1: Data Diri -->
        <div class="step" id="step1">
            <div class="step-header">
                <h1 class="step-title">Data Diri</h1>
                <p class="step-subtitle">Yuk, mulai dengan beberapa data dasar.</p>
            </div>
            
            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" placeholder="Contoh: Budi Santoso" required>
            </div>
            <div class="form-group">
                <label for="nomor_hp">Nomor HP</label>
                <input type="tel" id="nomor_hp" placeholder="Contoh: 081234567890" pattern="^08[0-9]{8,11}$" required>
            </div>
            
            <button class="btn btn-primary" onclick="nextStep(2)">Lanjutkan</button>
        </div>

        <!-- Step 2: Alamat Rumah -->
        <div class="step" id="step2">
            <div class="step-header">
                <h1 class="step-title">Alamat Tempat Tinggal</h1>
                <p class="step-subtitle">Lengkapi alamat tempat tinggal Anda agar data dapat tercatat dengan tepat.</p>
            </div>

            <!-- Bagian A: Wilayah -->
            <div class="address-card show" id="cardWilayah">
                <div class="form-group" id="fg-provinsi">
                    <label for="provinsi">Provinsi</label>
                    <select id="provinsi" onchange="showNextField('kabupaten')">
                        <option value="">Pilih Provinsi...</option>
                        <option value="Jawa Tengah">Jawa Tengah</option>
                    </select>
                </div>
                
                <div class="form-group hidden-field" id="fg-kabupaten">
                    <label for="kabupaten">Kabupaten/Kota</label>
                    <select id="kabupaten" onchange="showNextField('kecamatan')">
                        <option value="">Pilih Kabupaten/Kota...</option>
                        <option value="Kudus">Kudus</option>
                    </select>
                </div>

                <div class="form-group hidden-field" id="fg-kecamatan">
                    <label for="kecamatan">Kecamatan</label>
                    <select id="kecamatan" onchange="showNextField('desa')">
                        <option value="">Pilih Kecamatan...</option>
                        <option value="Dawe">Dawe</option>
                        <option value="Bae">Bae</option>
                    </select>
                </div>

                <div class="form-group hidden-field" id="fg-desa">
                    <label for="desa">Desa/Kelurahan</label>
                    <select id="desa" onchange="wilayahSelesai()">
                        <option value="">Pilih Desa/Kelurahan...</option>
                        <option value="Gondangmanis">Gondangmanis</option>
                        <option value="Japan">Japan</option>
                        <option value="Rejosari">Rejosari</option>
                        <option value="Lainnya">Lainnya (Demo)</option>
                    </select>
                </div>

                <div class="feedback-text hidden-element" id="fb-wilayah">
                    ✓ Wilayah sudah lengkap
                </div>
            </div>

            <!-- Bagian B: Detail Alamat -->
            <div class="address-card hidden-element" id="cardDetail">
                <h3 style="font-size: 1rem; color: var(--text-main); margin-bottom: 8px;">Sedikit lagi, lengkapi alamatnya</h3>
                
                <div class="form-group">
                    <label for="dusun">Dusun/Dukuh</label>
                    <input type="text" id="dusun" placeholder="Contoh: Kayuapu Kulon">
                    <div class="helper-text">Isi jika wilayah Anda menggunakan Dusun/Dukuh.</div>
                </div>
                
                <div class="row">
                    <div class="form-group">
                        <label for="rt">RT</label>
                        <input type="text" id="rt" placeholder="01" oninput="checkDetailDone()">
                    </div>
                    <div class="form-group">
                        <label for="rw">RW</label>
                        <input type="text" id="rw" placeholder="02" oninput="checkDetailDone()">
                    </div>
                </div>

                <div class="feedback-text hidden-element" id="fb-rtrw">
                    ✓ Detail wilayah sudah lengkap
                </div>

                <div class="form-group" style="margin-top: 8px;">
                    <label for="nama_jalan">Nama Jalan (Opsional)</label>
                    <input type="text" id="nama_jalan" placeholder="Contoh: Jl. Lingkar Utara UMK">
                    <div class="helper-text">Isi jika mengetahui nama jalannya.</div>
                </div>

                <div class="form-group">
                    <label for="nomor_rumah">Nomor Rumah (Opsional)</label>
                    <input type="text" id="nomor_rumah" placeholder="Contoh: 2">
                    <div class="helper-text">Isi jika ada atau jika mengetahui nomor rumah.</div>
                </div>

                <div class="form-group">
                    <label for="detail_alamat">Detail Alamat / Patokan</label>
                    <textarea id="detail_alamat" rows="2" placeholder="Contoh: dekat masjid, sebelah balai desa, rumah warna putih, Gang 3..." oninput="checkAlamatDone()"></textarea>
                    <div class="helper-text">Tulis patokan yang membantu menunjukkan tempat tinggal Anda.</div>
                </div>

                <div class="feedback-text hidden-element" id="fb-alamat" style="margin-top: 8px;">
                    ✓ Alamat sudah dicatat
                </div>
                <p id="alamat-ready-text" class="hidden-element" style="font-size: 0.875rem; color: var(--text-muted); text-align: center; margin-top: 8px;">Tinggal satu langkah lagi untuk melengkapi data tempat tinggal Anda.</p>

                <button class="btn btn-primary hidden-element" id="btn-lanjut-alamat" onclick="prepareVerification()">Lanjutkan</button>
            </div>
            
            <div class="loading-state hidden-element" id="loadingAlamat">
                <div class="spinner"></div>
                <p style="font-weight: 500; font-size: 1rem;">Menyiapkan data alamat...</p>
            </div>
        </div>

        <!-- Step 3: Verifikasi Lokasi -->
        <div class="step" id="step3">
            <div class="step-header" style="text-align: center; margin-bottom: 16px;">
                <h1 class="step-title">Lengkapi Lokasi</h1>
                <p class="step-subtitle" style="font-weight: 500; color: var(--text-main);">Satu langkah terakhir 📍</p>
            </div>

            <div id="verifyIntro">
                <p style="text-align: center; font-size: 0.95rem; margin-bottom: 24px;">Untuk melengkapi data tempat tinggal, gunakan lokasi perangkat Anda saat ini. Pastikan Anda berada di lokasi tempat tinggal yang didaftarkan agar data lokasi dapat tercatat sesuai dengan alamat yang diberikan.</p>
                
                <button class="btn btn-primary" onclick="verifyLocation()">Gunakan Lokasi Saya</button>

                <div class="privacy-note">
                    <span style="font-size: 1rem;">🔒</span> Lokasi hanya digunakan saat proses ini dan tidak digunakan untuk pelacakan terus-menerus.
                </div>
            </div>

            <div class="loading-state hidden-element" id="loadingLokasi">
                <div class="spinner"></div>
                <p id="loadingLokasiText" style="font-weight: 600; font-size: 1.1rem; color: var(--text-main);">📍 Mengambil lokasi...</p>
                <p id="loadingLokasiSub" style="font-size: 0.9rem; color: var(--text-muted); margin-top: 4px;">Mohon tunggu sebentar.</p>
            </div>

            <div class="result-box hidden-element" id="resultSuccess">
                <div class="icon-large" style="color: var(--success);">✓</div>
                <h2 style="color: var(--text-main); font-size: 1.25rem;">Data lokasi berhasil dicatat</h2>
                <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 16px;">Data tempat tinggal Anda sudah lengkap.</p>
                <button class="btn btn-primary" onclick="window.location.reload()">Lanjutkan Survei</button>
            </div>

            <div class="result-box hidden-element" id="resultError">
                <div class="icon-large" style="color: var(--error);">✕</div>
                <h2 style="color: var(--text-main); font-size: 1.25rem;">Lokasi belum dapat dicatat</h2>
                <p class="dynamic-message" style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 16px;">Lokasi perangkat belum memenuhi kondisi yang diperlukan untuk melengkapi data tempat tinggal. Silakan pastikan layanan lokasi perangkat aktif dan coba kembali saat berada di lokasi tempat tinggal.</p>
                
                <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 24px;">
                    <button class="btn btn-primary" onclick="retryVerification()">Coba Lagi</button>
                    <button class="btn" style="background-color: var(--surface-hover); color: var(--text-main);" onclick="lanjutkanTanpaLokasi(null)">Lanjutkan Survei</button>
                </div>
            </div>

            <div class="result-box hidden-element" id="resultOutside">
                <div class="icon-large" style="color: #F59E0B;">ℹ️</div>
                <h2 style="color: var(--text-main); font-size: 1.25rem;">Pemberitahuan Lokasi</h2>
                <p class="dynamic-message" style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 16px;">Lokasi perangkat berada di luar wilayah tempat tinggal yang dipilih. Anda tetap dapat melanjutkan pengisian survei. Jika ingin mencatat lokasi tempat tinggal, Anda dapat mencoba kembali saat berada di wilayah tersebut.</p>
                
                <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 24px;">
                    <button class="btn btn-primary" onclick="retryVerification()">Coba Lagi</button>
                    <button class="btn" style="background-color: var(--surface-hover); color: var(--text-main);" onclick="lanjutkanTanpaLokasi('DI_LUAR_WILAYAH')">Lanjutkan Survei</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/verification.js') }}"></script>
</body>
</html>
