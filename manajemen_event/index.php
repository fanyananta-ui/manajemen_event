<?php
session_start();
include 'koneksi.php';

// 1. CRUD: Logika AJAX - Kunci Status Login ke Session
if (isset($_POST['action']) && $_POST['action'] == 'set_login_session') {
    $_SESSION['is_logged_in'] = true;
    echo 'success';
    exit;
}

// 2. CRUD: Logika AJAX - Hapus Session saat Logout
if (isset($_POST['action']) && $_POST['action'] == 'clear_login_session') {
    session_destroy();
    echo 'success';
    exit;
}

// 3. CRUD: Logika AJAX - Tambah Data (Create)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nama_event']) && !isset($_POST['action'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_event']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal_event']);
    $lokasi = mysqli_real_escape_string($conn, $_POST['lokasi']);
    $signature_base64 = mysqli_real_escape_string($conn, $_POST['signature_data']);

    $namaFile = 'Tidak Ada File';
    if (isset($_FILES['file_event']) && $_FILES['file_event']['error'] == 0) {
        $namaFile = time() . '_' . $_FILES['file_event']['name'];
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }
        move_uploaded_file($_FILES['file_event']['tmp_name'], 'uploads/' . $namaFile);
    }

    $query = "INSERT INTO event (nama_event, tanggal_event, lokasi, file_event, signature) 
              VALUES ('$nama', '$tanggal', '$lokasi', '$namaFile', '$signature_base64')";
    if (mysqli_query($conn, $query)) {
        echo 'sukses';
    } else {
        echo 'gagal';
    }
    exit;
}

// 4. CRUD: Logika AJAX - Update Data (Update)
if (isset($_POST['action']) && $_POST['action'] == 'edit_event') {
    $id = intval($_POST['id']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama_event']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal_event']);
    $lokasi = mysqli_real_escape_string($conn, $_POST['lokasi']);

    // Handle upload file baru jika ada berkas yang dimasukkan
    $file_update_sql = "";
    if (isset($_FILES['file_event']) && $_FILES['file_event']['error'] == 0) {
        $namaFile = time() . '_' . $_FILES['file_event']['name'];
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }
        if (move_uploaded_file($_FILES['file_event']['tmp_name'], 'uploads/' . $namaFile)) {
            // Hapus file lama secara fisik dari server agar hemat penyimpanan
            $old_file_query = mysqli_query($conn, "SELECT file_event FROM event WHERE id = $id");
            if ($old_row = mysqli_fetch_assoc($old_file_query)) {
                $old_file = 'uploads/' . $old_row['file_event'];
                if ($old_row['file_event'] != 'Tidak Ada File' && file_exists($old_file)) {
                    @unlink($old_file);
                }
            }
            $file_update_sql = ", file_event='$namaFile'";
        }
    }

    // Handle signature baru jika user melakukan gambar ulang
    $signature_update_sql = "";
    if (isset($_POST['has_new_signature']) && $_POST['has_new_signature'] == 'yes') {
        $signature_base64 = mysqli_real_escape_string($conn, $_POST['signature_data']);
        $signature_update_sql = ", signature='$signature_base64'";
    }

    $query = "UPDATE event SET nama_event='$nama', tanggal_event='$tanggal', lokasi='$lokasi' $file_update_sql $signature_update_sql WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        echo 'sukses';
    } else {
        echo 'gagal';
    }
    exit;
}

// 5. CRUD: Logika AJAX - Hapus Data (Delete)
if (isset($_POST['action']) && $_POST['action'] == 'delete_event') {
    $id = intval($_POST['id']);
    
    // Ambil info nama berkas untuk dihapus dari penyimpanan lokal sebelum row dihilangkan
    $file_query = mysqli_query($conn, "SELECT file_event FROM event WHERE id = $id");
    if ($row = mysqli_fetch_assoc($file_query)) {
        $file = "uploads/" . $row['file_event'];
        if ($row['file_event'] != 'Tidak Ada File' && file_exists($file)) {
            @unlink($file);
        }
    }

    $query = "DELETE FROM event WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        echo 'sukses';
    } else {
        echo 'gagal';
    }
    exit;
}

// QUERY UTK DATA KARTU STATISTIK DASHBOARD REALTIME
$q_total = mysqli_query($conn, "SELECT COUNT(*) as total FROM event");
$r_total = mysqli_fetch_assoc($q_total)['total'] ?? 0;

$q_aktif = mysqli_query($conn, "SELECT COUNT(*) as aktif FROM event WHERE tanggal_event >= CURDATE()");
$r_aktif = mysqli_fetch_assoc($q_aktif)['aktif'] ?? 0;

$q_selesai = mysqli_query($conn, "SELECT COUNT(*) as selesai FROM event WHERE tanggal_event < CURDATE()");
$r_selesai = mysqli_fetch_assoc($q_selesai)['selesai'] ?? 0;

// Cek apakah user sudah login atau belum untuk menentukan tampilan awal HTML
$sudah_login = (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

    <style>
        *{ font-family: 'Poppins', sans-serif; }
        body{ background: #f4f7fc; }
        .login-page{ height:100vh; display:flex; justify-content:center; align-items:center; background: linear-gradient(to right, #4e54c8, #8f94fb); }
        .login-box{ width:380px; background:white; padding:35px; border-radius:20px; box-shadow:0 10px 30px rgba(0,0,0,0.2); }
        .login-title{ text-align:center; margin-bottom:30px; font-weight:700; color:#4e54c8; }
        .btn-login{ background:#4e54c8; border:none; padding:10px; }
        .btn-login:hover{ background:#3d42a4; }
        .navbar{ background: linear-gradient(to right, #4e54c8, #8f94fb); }
        .navbar-brand{ color:white !important; font-weight:600; }
        .dashboard-card{ border:none; border-radius:18px; padding:20px; color:white; transition: all 0.3s ease; }
        .dashboard-card:hover{ transform: translateY(-10px); box-shadow: 0 15px 25px rgba(0,0,0,0.2); }
        .card-blue{ background:#4e54c8; }
        .card-green{ background:#16a34a; }
        .card-orange{ background:#f59e0b; }
        .card-red{ background:#dc2626; }
        .table-container{ background:white; padding:25px; border-radius:20px; box-shadow:0 5px 15px rgba(0,0,0,0.08); }
        .btn{ border-radius:10px; }
        canvas{ border:2px solid #ccc; border-radius:10px; background:white; cursor: crosshair; }
        footer{ text-align:center; margin-top:40px; padding:20px; color:#666; }
    </style>
</head>
<body>

<div class="login-page <?= $sudah_login ? 'd-none' : ''; ?>" id="loginPage">
    <div class="login-box">
        <h2 class="login-title"><i class="bi bi-calendar-event"></i> Event Management</h2>
        <form id="formLogin">
            <div class="mb-3">
                <label>Username</label>
                <input type="text" class="form-control" id="username" autocomplete="off" placeholder="Masukkan username...">
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" class="form-control" id="password" placeholder="Masukkan password...">
            </div>
            <button type="button" class="btn btn-login text-white w-100" onclick="showDashboard()">Login</button>
        </form>
    </div>
</div>

<div id="dashboard" class="<?= $sudah_login ? '' : 'd-none'; ?>">
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="bi bi-calendar-check"></i> Event Management System</a>
            <button class="btn btn-light btn-sm" onclick="logout()">Logout</button>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="dashboard-card card-blue" data-aos="zoom-in">
                    <h5>Total Event</h5>
                    <h2><?= $r_total; ?></h2>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="dashboard-card card-green" data-aos="fade-up">
                    <h5>Event Aktif</h5>
                    <h2><?= $r_aktif; ?></h2>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="dashboard-card card-red">
                    <h5>Event Selesai</h5>
                    <h2><?= $r_selesai; ?></h2>
                </div>
            </div>
        </div>

        <div class="table-container" data-aos="fade-up">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3><i class="bi bi-table"></i> Data Event</h3>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambah"><i class="bi bi-plus-circle"></i> Tambah Event</button>
            </div>

            <table id="example" class="table table-bordered display text-center align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama Event</th>
                        <th>Tanggal</th>
                        <th>Lokasi</th>
                        <th>File</th>
                        <th>Signature</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $no = 1;
                $data = mysqli_query($conn, "SELECT * FROM event ORDER BY id ASC");
                while($row = mysqli_fetch_assoc($data)){
                ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= htmlspecialchars($row['nama_event']); ?></td>
                    <td><?= $row['tanggal_event']; ?></td>
                    <td><?= htmlspecialchars($row['lokasi']); ?></td>
                    <td>
                        <?php if($row['file_event'] !== 'Tidak Ada File' && !empty($row['file_event'])): ?>
                            <a href="uploads/<?= $row['file_event']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-file-earmark-arrow-down"></i> Lihat File
                            </a>
                        <?php else: ?>
                            <span class="text-muted">Tidak Ada File</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if(!empty($row['signature']) && strlen($row['signature']) > 100): ?>
                            <img src="<?= $row['signature']; ?>" width="100" class="border rounded bg-white p-1">
                        <?php else: ?>
                            <span class="text-muted">Belum Ada TTD</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="btn btn-warning btn-sm" onclick="editData(this, <?= $row['id']; ?>)"
                                data-nama="<?= htmlspecialchars($row['nama_event']); ?>"
                                data-tanggal="<?= $row['tanggal_event']; ?>"
                                data-lokasi="<?= htmlspecialchars($row['lokasi']); ?>"
                                data-file="<?= htmlspecialchars($row['file_event']); ?>"
                                data-signature="<?= $row['signature']; ?>">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="hapusData(this, <?= $row['id']; ?>)"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="table-container mt-4" data-aos="fade-left">
            <h3 class="mb-3"><i class="bi bi-camera-video-fill"></i> Video</h3>
            <iframe width="500" height="280" src="https://www.youtube.com/embed/Q33KBiDriJY" title="Video" allowfullscreen></iframe>
        </div>

        <footer>© 2026 Event Management System</footer>
    </div>
</div>

<div class="modal fade" id="modalTambah">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Event</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formTambahEvent" enctype="multipart/form-data">
                    <div class="mb-3"><label>Nama Event</label><input type="text" class="form-control" id="namaEvent" name="nama_event"></div>
                    <div class="mb-3"><label>Tanggal Event</label><input type="date" class="form-control" id="tanggalEvent" name="tanggal_event"></div>
                    <div class="mb-3"><label>Lokasi Event</label><input type="text" class="form-control" id="lokasiEvent" name="lokasi"></div>
                    <div class="mb-3"><label>Upload File</label><input type="file" class="form-control" id="fileEvent" name="file_event"></div>
                    <div class="mb-3">
                        <label class="fw-bold">Canvas Signature</label><br>
                        <canvas id="signature-pad" width="400" height="200"></canvas>
                        <br>
                        <button type="button" class="btn btn-sm btn-secondary mt-2" id="clear-signature">Hapus Signature</button>
                        <br><br>
                        <h6>Hasil Live Preview TTD :</h6>
                        <img id="hasil-signature" width="200" class="border rounded p-2 bg-white" alt="Belum ada goresan">
                    </div>
                    <button type="button" class="btn btn-primary" onclick="tambahData()">Simpan Data</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdit">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Data Event</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditEvent" enctype="multipart/form-data">
                    <input type="hidden" id="editId" name="id">
                    <div class="mb-3"><label>Nama Event</label><input type="text" class="form-control" id="editNamaEvent" name="nama_event"></div>
                    <div class="mb-3"><label>Tanggal Event</label><input type="date" class="form-control" id="editTanggalEvent" name="tanggal_event"></div>
                    <div class="mb-3"><label>Lokasi Event</label><input type="text" class="form-control" id="editLokasiEvent" name="lokasi"></div>
                    <div class="mb-3">
                        <label>Upload File Baru *(Kosongkan jika tidak diganti)</label>
                        <input type="file" class="form-control" id="editFileEvent" name="file_event">
                        <div id="editFilePreview" class="mt-2"></div>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Canvas Signature Baru *(Kosongkan jika tidak diubah)</label><br>
                        <canvas id="edit-signature-pad" width="400" height="200"></canvas>
                        <br>
                        <button type="button" class="btn btn-sm btn-secondary mt-2" id="edit-clear-signature">Hapus Signature</button>
                        <br><br>
                        <h6>Hasil Live Preview TTD (Baru / Sebelumnya):</h6>
                        <img id="edit-hasil-signature" width="200" class="border rounded p-2 bg-white" alt="Belum ada tanda tangan">
                    </div>
                    <button type="button" class="btn btn-primary" onclick="updateData()">Update Data</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>

<script>
    // Validasi input login dan arahkan ke session
    function showDashboard(){
        let user = document.getElementById('username').value;
        let pass = document.getElementById('password').value;

        if(user === '' || pass === '') {
            Swal.fire('Gagal', 'Username dan Password wajib diisi!', 'error');
            return;
        }

        // Proses simpan status login ke session PHP lewat AJAX
        Swal.fire({ icon: 'success', title: 'Login Berhasil', confirmButtonText: 'Masuk' }).then(() => {
            $.post('', { action: 'set_login_session' }, function() {
                document.getElementById('loginPage').classList.add('d-none');
                document.getElementById('dashboard').classList.remove('d-none');
                
                // Reset form login agar bersih saat dicoba logout nanti
                document.getElementById('formLogin').reset();
                AOS.refreshHard();
                table.columns.adjust().draw(); // Set ulang responsive dataTables
            });
        });
    }

    function logout(){
        $.post('', { action: 'clear_login_session' }, function() {
            document.getElementById('dashboard').classList.add('d-none');
            document.getElementById('loginPage').classList.remove('d-none');
        });
    }

    let table;
    $(document).ready(function () {
        table = $('#example').DataTable({ dom: 'Bfrtip', buttons: ['copy', 'csv', 'excel', 'pdf', 'print'] });
    });

    // CRUD: Create
    function tambahData(){
        let nama = document.getElementById('namaEvent').value;
        let tanggal = document.getElementById('tanggalEvent').value;
        let lokasi = document.getElementById('lokasiEvent').value;

        if(nama === '' || tanggal === '' || lokasi === ''){
            Swal.fire('Gagal', 'Semua data wajib diisi!', 'error');
            return;
        }

        let hasilSignature = canvas.toDataURL();
        let formElement = document.getElementById('formTambahEvent');
        let formData = new FormData(formElement);
        formData.append('signature_data', hasilSignature);

        $.ajax({
            url: '',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(){
                Swal.fire('Berhasil', 'Data dan TTD sukses disimpan!', 'success').then(() => { location.reload(); });
            }
        });
    }

    // CRUD: Delete
    function hapusData(button, id){
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: "Data akan dihapus permanen dari database!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('', { action: 'delete_event', id: id }, function(response){
                    if(response.trim() === 'sukses') {
                        Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success').then(() => { location.reload(); });
                    }
                });
            }
        });
    }

    // Ambil data lama & muat ke form modal edit beserta review berkas/ttd yang tersimpan
    let selectedRow; 
    let hasEditDrawn = false;
    function editData(button, id){
        selectedRow = $(button).parents('tr');
        
        let nama = button.getAttribute('data-nama');
        let tanggal = button.getAttribute('data-tanggal');
        let lokasi = button.getAttribute('data-lokasi');
        let file = button.getAttribute('data-file');
        let signature = button.getAttribute('data-signature');

        document.getElementById('editId').value = id;
        document.getElementById('editNamaEvent').value = nama;
        document.getElementById('editTanggalEvent').value = tanggal;
        document.getElementById('editLokasiEvent').value = lokasi;
        document.getElementById('editFileEvent').value = ""; // Reset input file form edit

        // Cek file lama yang telah diupload
        let filePreview = document.getElementById('editFilePreview');
        if(file && file !== 'Tidak Ada File' && file !== ''){
            filePreview.innerHTML = `<a href="uploads/${file}" target="_blank" class="btn btn-sm btn-outline-primary d-inline-block"><i class="bi bi-eye"></i> Lihat File Sekarang (${file})</a>`;
        } else {
            filePreview.innerHTML = `<span class="text-muted">Tidak ada berkas yang diupload sebelumnya</span>`;
        }

        // Muat ttd lama ke preview gambar form edit
        const editCanvas = document.getElementById('edit-signature-pad');
        const editCtx = editCanvas.getContext('2d');
        editCtx.clearRect(0, 0, editCanvas.width, editCanvas.height);
        hasEditDrawn = false;

        let editHasil = document.getElementById('edit-hasil-signature');
        if(signature && signature.length > 100){
            editHasil.src = signature;
        } else {
            editHasil.src = '';
        }

        let modalEdit = new bootstrap.Modal(document.getElementById('modalEdit'));
        modalEdit.show();
    }

    // CRUD: Update Data menggunakan FormData (Mendukung upload file & ttd canvas)
    function updateData(){
        let nama = document.getElementById('editNamaEvent').value;
        let tanggal = document.getElementById('editTanggalEvent').value;
        let lokasi = document.getElementById('editLokasiEvent').value;

        if(nama === '' || tanggal === '' || lokasi === ''){
            Swal.fire('Gagal', 'Semua data wajib diisi!', 'error');
            return;
        }

        let formElement = document.getElementById('formEditEvent');
        let formData = new FormData(formElement);
        formData.append('action', 'edit_event');
        
        let editCanvas = document.getElementById('edit-signature-pad');
        formData.append('signature_data', editCanvas.toDataURL());
        formData.append('has_new_signature', hasEditDrawn ? 'yes' : 'no');

        $.ajax({
            url: '',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response){
                if(response.trim() === 'sukses') {
                    Swal.fire('Berhasil', 'Data berhasil diperbarui!', 'success').then(() => { location.reload(); });
                } else {
                    Swal.fire('Gagal', 'Gagal memperbarui data!', 'error');
                }
            }
        });
    }

    // CANVAS SIGNATURE ENGINE FOR CREATE FORM
    const canvas = document.getElementById('signature-pad');
    const ctx = canvas.getContext('2d');
    let drawing = false;

    canvas.addEventListener('mousedown', () => { drawing = true; });
    canvas.addEventListener('mouseup', () => { 
        drawing = false; 
        ctx.beginPath(); 
        document.getElementById('hasil-signature').src = canvas.toDataURL();
    });
    canvas.addEventListener('mousemove', draw);

    function draw(e){
        if(!drawing) return;
        ctx.lineWidth = 3;
        ctx.lineCap = 'round';
        ctx.strokeStyle = '#000000';
        ctx.lineTo(e.offsetX, e.offsetY);
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(e.offsetX, e.offsetY);
    }

    document.getElementById('clear-signature').addEventListener('click', () => {
        ctx.clearRect(0,0,canvas.width,canvas.height);
        document.getElementById('hasil-signature').src = '';
    });


    // CANVAS SIGNATURE ENGINE FOR EDIT FORM
    const editCanvas = document.getElementById('edit-signature-pad');
    const editCtx = editCanvas.getContext('2d');
    let editDrawing = false;

    editCanvas.addEventListener('mousedown', () => { editDrawing = true; hasEditDrawn = true; });
    editCanvas.addEventListener('mouseup', () => { 
        editDrawing = false; 
        editCtx.beginPath(); 
        document.getElementById('edit-hasil-signature').src = editCanvas.toDataURL();
    });
    editCanvas.addEventListener('mousemove', drawEdit);

    function drawEdit(e){
        if(!editDrawing) return;
        editCtx.lineWidth = 3;
        editCtx.lineCap = 'round';
        editCtx.strokeStyle = '#000000';
        editCtx.lineTo(e.offsetX, e.offsetY);
        editCtx.stroke();
        editCtx.beginPath();
        editCtx.moveTo(e.offsetX, e.offsetY);
    }

    document.getElementById('edit-clear-signature').addEventListener('click', () => {
        editCtx.clearRect(0,0,editCanvas.width,editCanvas.height);
        document.getElementById('edit-hasil-signature').src = '';
        hasEditDrawn = true;
    });

    // ================= FITUR SUARA KLIK (WEB AUDIO API - ANTI GAGAL) =================
    function bunyikanKlik() {
        try {
            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioCtx.createOscillator();
            const gainNode = audioCtx.createGain();

            oscillator.type = 'sine'; 
            oscillator.frequency.setValueAtTime(1000, audioCtx.currentTime); 
            
            gainNode.gain.setValueAtTime(0.1, audioCtx.currentTime); 
            gainNode.gain.exponentialRampToValueAtTime(0.00001, audioCtx.currentTime + 0.08); 

            oscillator.connect(gainNode);
            gainNode.connect(audioCtx.destination);

            oscillator.start();
            oscillator.stop(audioCtx.currentTime + 0.08);
        } catch (e) {
            console.log("Web Audio API tidak didukung browser ini: ", e);
        }
    }

    $(document).on('click', '.btn, button, .btn-close, .dt-button, table th, canvas, .page-link', function() {
        bunyikanKlik();
    });
    // ====================================================================

    AOS.init({ duration: 1000, once: true });
</script>
</body>
</html>