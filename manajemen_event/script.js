// ================= LOGIKA OPERASIONAL MODAL =================
const modal = document.getElementById("eventModal");
const openModalBtn = document.getElementById("openModalBtn");
const closeBtn = document.querySelector(".close-btn");

openModalBtn.addEventListener("click", () => {
    modal.style.display = "block";
});

closeBtn.addEventListener("click", () => {
    modal.style.display = "none";
});

window.addEventListener("click", (event) => {
    if (event.target === modal) {
        modal.style.display = "none";
    }
});


// ================= LOGIKA INTERAKSI CANVAS TTD =================
const canvas = document.getElementById("signatureCanvas");
const ctx = canvas.getContext("2d");
let isDrawing = false;

// Konfigurasi tebal dan warna garis kuas canvas
ctx.strokeStyle = "#1a1a1a"; 
ctx.lineWidth = 3;
ctx.lineCap = "round";

// Deteksi mouse mulai menekan klik kiri
canvas.addEventListener("mousedown", (e) => {
    isDrawing = true;
    ctx.beginPath();
    ctx.moveTo(e.offsetX, e.offsetY);
});

// Deteksi pergerakan cursor mouse saat menggambar
canvas.addEventListener("mousemove", (e) => {
    if (isDrawing) {
        ctx.lineTo(e.offsetX, e.offsetY);
        ctx.stroke();
    }
});

// Hentikan proses gambar ketika klik kiri dilepas atau cursor keluar dari area canvas
canvas.addEventListener("mouseup", () => isDrawing = false);
canvas.addEventListener("mouseleave", () => isDrawing = false);

// Tombol reset/membersihkan coretan canvas
document.getElementById("clearCanvasBtn").addEventListener("click", () => {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
});


// ================= VALIDASI DAN SUBMIT DATA FORM =================
const eventForm = document.getElementById("eventForm");
eventForm.addEventListener("submit", function(e) {
    // Ekstrak coretan pada canvas menjadi bentuk string Base64 Data URL
    const dataURL = canvas.toDataURL();
    
    // Validasi sederhana memastikan canvas tidak kosong sebelum submit
    // (Bila canvas kosong, dataURL default-nya berukuran sangat pendek)
    if (canvas.toDataURL() === document.createElement('canvas').toDataURL()) {
        alert("Mohon isi tanda tangan digital panitia terlebih dahulu!");
        e.preventDefault(); // Gagalkan submit form jika kosong
        return false;
    }

    // Set nilai string base64 tersebut ke hidden input agar terbaca oleh PHP $_POST
    document.getElementById("ttd_data").value = dataURL;
});