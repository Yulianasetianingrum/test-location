let currentStep = 1;
let formData = {};

function nextStep(step) {
    if (step === 2) {
        if (!document.getElementById('nama').value || !document.getElementById('nomor_hp').value) {
            alert("Harap lengkapi Nama Lengkap dan Nomor HP.");
            return;
        }
    }

    currentStep = step;
    const container = document.getElementById('stepContainer');
    container.style.transform = `translateX(-${(step - 1) * 100}%)`;

    // Update stepper UI
    document.querySelectorAll('.step-item').forEach((el, index) => {
        if (index < step) {
            el.classList.add('active');
        } else {
            el.classList.remove('active');
        }
    });
}

function showNextField(fieldId) {
    const el = document.getElementById(`fg-${fieldId}`);
    if(el) {
        el.classList.remove('hidden-field');
    }
}

function wilayahSelesai() {
    if (document.getElementById('desa').value) {
        document.getElementById('fb-wilayah').classList.remove('hidden-element');
        setTimeout(() => {
            document.getElementById('cardDetail').classList.remove('hidden-element');
            // small delay to allow display:block to apply before animating opacity
            setTimeout(() => {
                document.getElementById('cardDetail').classList.add('show');
            }, 50);
        }, 500);
    }
}

function checkDetailDone() {
    if (document.getElementById('rt').value && document.getElementById('rw').value) {
        document.getElementById('fb-rtrw').classList.remove('hidden-element');
    }
}

function checkAlamatDone() {
    if (document.getElementById('detail_alamat').value.length > 5) {
        document.getElementById('fb-alamat').classList.remove('hidden-element');
        document.getElementById('alamat-ready-text').classList.remove('hidden-element');
        document.getElementById('btn-lanjut-alamat').classList.remove('hidden-element');
    }
}

async function prepareVerification() {
    const addressFields = ['provinsi', 'kabupaten', 'kecamatan', 'desa'];
    for (let field of addressFields) {
        if (!document.getElementById(field).value) {
            alert(`Harap lengkapi pilihan ${field}.`);
            return;
        }
    }

    // Hide cards and show loading
    document.getElementById('cardWilayah').classList.add('hidden-element');
    document.getElementById('cardDetail').classList.add('hidden-element');
    document.getElementById('loadingAlamat').classList.remove('hidden-element');
    document.querySelector('#step2 .step-header').classList.add('hidden-element');
    try {
        formData.provinsi = document.getElementById('provinsi').value;
        formData.kabupaten = document.getElementById('kabupaten').value;
        formData.kecamatan = document.getElementById('kecamatan').value;
        formData.desa = document.getElementById('desa').value;
        formData.dusun = document.getElementById('dusun').value;
        formData.rt = document.getElementById('rt').value;
        formData.rw = document.getElementById('rw').value;
        formData.nama_jalan = document.getElementById('nama_jalan').value;
        formData.nomor_rumah = document.getElementById('nomor_rumah').value;
        formData.detail_alamat = document.getElementById('detail_alamat').value;

        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const response = await fetch('/api/respondents/prepare', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                provinsi: formData.provinsi,
                kabupaten: formData.kabupaten,
                kecamatan: formData.kecamatan,
                desa: formData.desa,
                dusun: formData.dusun,
                rt: formData.rt,
                rw: formData.rw,
                nama_jalan: formData.nama_jalan,
                nomor_rumah: formData.nomor_rumah,
                detail_alamat: formData.detail_alamat
            })
        });
        
        const result = await response.json();

        if (result.success) {
            formData.latitude_referensi = result.data.latitude_referensi;
            formData.longitude_referensi = result.data.longitude_referensi;
            formData.display_name_referensi = result.data.display_name_referensi;
            
            setTimeout(() => {
                nextStep(3);
            }, 1000); 
        } else {
            throw new Error(result.message || "Terjadi kesalahan");
        }
    } catch (error) {
        let msg = error.message;
        if (msg === "Failed to fetch" || msg.includes("NetworkError")) {
            msg = "Gagal terhubung ke server. Pastikan koneksi internet stabil.";
        }
        alert(msg);
        
        // Revert UI
        document.getElementById('cardWilayah').classList.remove('hidden-element');
        document.getElementById('cardDetail').classList.remove('hidden-element');
        document.getElementById('loadingAlamat').classList.add('hidden-element');
        document.querySelector('#step2 .step-header').classList.remove('hidden-element');
    }
}

function verifyLocation() {
    const intro = document.getElementById('verifyIntro');
    const loading = document.getElementById('loadingLokasi');
    const loadText = document.getElementById('loadingLokasiText');
    const loadSub = document.getElementById('loadingLokasiSub');

    intro.classList.add('hidden-element');
    loading.classList.remove('hidden-element');

    loadText.innerText = "📍 Mengambil lokasi...";
    loadSub.innerText = "Mohon tunggu sebentar.";

    if (!navigator.geolocation) {
        showResult(false, "Silakan izinkan akses lokasi pada browser untuk melanjutkan.", "Lokasi belum dapat dicatat");
        return;
    }

    const successCallback = (position) => {
        loadText.innerText = "Menyesuaikan data...";
        loadSub.innerText = "";
        formData.latitude_rumah = position.coords.latitude;
        formData.longitude_rumah = position.coords.longitude;
        formData.accuracy = position.coords.accuracy;
        
        setTimeout(() => {
            loadText.innerText = "Menyelesaikan data tempat tinggal...";
            submitData();
        }, 1000);
    };

    const errorCallback = (error) => {
        if (error.code === error.PERMISSION_DENIED) {
            let msg = "Akses lokasi ditolak. Silakan izinkan akses lokasi (Location) pada pengaturan browser Anda untuk melanjutkan.";
            showResult('error', msg, "Lokasi belum dapat dicatat");
        } else {
            // Fallback: Timeout ATAU Position Unavailable
            navigator.geolocation.getCurrentPosition(
                successCallback,
                (fallbackError) => {
                    let finalMsg = "Pencarian lokasi gagal. Pastikan fitur Lokasi/GPS di HP Anda MENYALA (Aktif) dan coba muat ulang (refresh) halaman ini.";
                    showResult('error', finalMsg, "Lokasi belum dapat dicatat");
                },
                { enableHighAccuracy: false, timeout: 30000, maximumAge: 0 }
            );
        }
    };

    navigator.geolocation.getCurrentPosition(
        successCallback,
        errorCallback,
        { enableHighAccuracy: true, timeout: 20000, maximumAge: 0 }
    );
}

async function submitData() {
    formData.nama = document.getElementById('nama').value;
    formData.nomor_hp = document.getElementById('nomor_hp').value;
    formData.provinsi = document.getElementById('provinsi').value;
    formData.kabupaten = document.getElementById('kabupaten').value;
    formData.kecamatan = document.getElementById('kecamatan').value;
    formData.desa = document.getElementById('desa').value;
    formData.dusun = document.getElementById('dusun').value;
    formData.rt = document.getElementById('rt').value;
    formData.rw = document.getElementById('rw').value;
    formData.nama_jalan = document.getElementById('nama_jalan').value;
    formData.nomor_rumah = document.getElementById('nomor_rumah').value;
    formData.detail_alamat = document.getElementById('detail_alamat').value;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const response = await fetch('/api/respondents/verify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json();
        
        if (result.success) {
            showResult('success', "Data tempat tinggal Anda sudah lengkap.", "Lokasi sesuai wilayah tempat tinggal");
        } else if (result.is_outside) {
            showResult('outside', result.message, "Pemberitahuan Lokasi");
        } else {
            // General error
            showResult('error', result.message, "Lokasi belum dapat dicatat");
        }

    } catch (error) {
        showResult('error', "Terjadi kesalahan sistem saat menyimpan data. Silakan coba lagi.", "Lokasi belum dapat dicatat");
    }
}

function showResult(type, message, titleText) {
    document.getElementById('loadingLokasi').classList.add('hidden-element');
    
    // Hide all result boxes first
    document.getElementById('resultSuccess').classList.add('hidden-element');
    document.getElementById('resultError').classList.add('hidden-element');
    document.getElementById('resultOutside').classList.add('hidden-element');

    let targetBox;
    if (type === 'success') {
        targetBox = document.getElementById('resultSuccess');
    } else if (type === 'outside') {
        targetBox = document.getElementById('resultOutside');
    } else {
        targetBox = document.getElementById('resultError');
    }

    targetBox.classList.remove('hidden-element');
    
    // Only outside and error have dynamic text
    if (type !== 'success') {
        targetBox.querySelector('h2').innerText = titleText;
        targetBox.querySelector('.dynamic-message').innerText = message;
    }
}

function retryVerification() {
    // Reset to location verify intro
    document.getElementById('resultError').classList.add('hidden-element');
    document.getElementById('resultOutside').classList.add('hidden-element');
    document.getElementById('verifyIntro').classList.remove('hidden-element');
}

function lanjutkanTanpaLokasi(status = null) {
    // Kosongkan koordinat dan kirim ulang dengan tanda skip_location
    formData.latitude_rumah = null;
    formData.longitude_rumah = null;
    formData.accuracy = null;
    formData.skip_location = true;
    formData.skip_status = status;
    
    document.getElementById('resultOutside').classList.add('hidden-element');
    document.getElementById('resultError').classList.add('hidden-element');
    document.getElementById('loadingLokasi').classList.remove('hidden-element');
    document.getElementById('loadingLokasiText').innerText = "Menyimpan data...";
    document.getElementById('loadingLokasiSub').innerText = "Melanjutkan tanpa verifikasi lokasi.";
    
    submitData();
}
