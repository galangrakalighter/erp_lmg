from reportlab.lib import colors
from reportlab.lib.enums import TA_CENTER, TA_LEFT
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import mm
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.platypus import (
    BaseDocTemplate, Frame, KeepTogether, PageBreak, PageTemplate,
    Paragraph, Spacer, Table, TableStyle,
)
from pathlib import Path


ROOT = Path(__file__).resolve().parents[2]
OUTPUT = ROOT / "output" / "pdf" / "dokumentasi-live-monitoring-erp-lmg.pdf"
FONT_REGULAR = Path(r"C:\Windows\Fonts\arial.ttf")
FONT_BOLD = Path(r"C:\Windows\Fonts\arialbd.ttf")

pdfmetrics.registerFont(TTFont("LMG-Regular", str(FONT_REGULAR)))
pdfmetrics.registerFont(TTFont("LMG-Bold", str(FONT_BOLD)))

NAVY = colors.HexColor("#111827")
INK = colors.HexColor("#27272A")
MUTED = colors.HexColor("#71717A")
LINE = colors.HexColor("#E4E4E7")
SOFT = colors.HexColor("#F4F4F5")
BLUE = colors.HexColor("#2563EB")
PALE_BLUE = colors.HexColor("#EFF6FF")
AMBER = colors.HexColor("#B45309")
PALE_AMBER = colors.HexColor("#FFFBEB")
GREEN = colors.HexColor("#047857")
PALE_GREEN = colors.HexColor("#ECFDF5")


def footer(canvas, doc):
    canvas.saveState()
    width, _ = A4
    canvas.setStrokeColor(LINE)
    canvas.line(18 * mm, 14 * mm, width - 18 * mm, 14 * mm)
    canvas.setFont("LMG-Regular", 8)
    canvas.setFillColor(MUTED)
    canvas.drawString(18 * mm, 9 * mm, "ERP Lighter Media Group • Live Monitoring")
    canvas.drawRightString(width - 18 * mm, 9 * mm, f"Halaman {doc.page}")
    canvas.restoreState()


doc = BaseDocTemplate(
    str(OUTPUT), pagesize=A4,
    leftMargin=18 * mm, rightMargin=18 * mm,
    topMargin=18 * mm, bottomMargin=21 * mm,
    title="Dokumentasi Live Monitoring ERP LMG",
    author="Lighter Media Group",
    subject="Panduan penggunaan, akses, privasi, dan troubleshooting Live Monitoring",
)
frame = Frame(doc.leftMargin, doc.bottomMargin, doc.width, doc.height, id="content")
doc.addPageTemplates(PageTemplate(id="main", frames=[frame], onPage=footer))

styles = getSampleStyleSheet()
styles.add(ParagraphStyle(
    name="TitleLMG", fontName="LMG-Bold", fontSize=27, leading=32,
    textColor=NAVY, alignment=TA_LEFT, spaceAfter=6 * mm,
))
styles.add(ParagraphStyle(
    name="SubtitleLMG", fontName="LMG-Regular", fontSize=12, leading=18,
    textColor=MUTED, spaceAfter=9 * mm,
))
styles.add(ParagraphStyle(
    name="H1LMG", fontName="LMG-Bold", fontSize=18, leading=22,
    textColor=NAVY, spaceBefore=3 * mm, spaceAfter=4 * mm,
))
styles.add(ParagraphStyle(
    name="H2LMG", fontName="LMG-Bold", fontSize=12.5, leading=16,
    textColor=BLUE, spaceBefore=3 * mm, spaceAfter=2 * mm,
))
styles.add(ParagraphStyle(
    name="BodyLMG", fontName="LMG-Regular", fontSize=9.5, leading=14,
    textColor=INK, spaceAfter=2.5 * mm,
))
styles.add(ParagraphStyle(
    name="SmallLMG", fontName="LMG-Regular", fontSize=8.2, leading=12,
    textColor=MUTED,
))
styles.add(ParagraphStyle(
    name="BulletLMG", fontName="LMG-Regular", fontSize=9.3, leading=13.5,
    textColor=INK, leftIndent=5 * mm, firstLineIndent=-3.5 * mm, spaceAfter=1.5 * mm,
))
styles.add(ParagraphStyle(
    name="CalloutLMG", fontName="LMG-Regular", fontSize=9.3, leading=14,
    textColor=INK, leftIndent=4 * mm, rightIndent=4 * mm, spaceBefore=2 * mm,
    spaceAfter=2 * mm,
))
styles.add(ParagraphStyle(
    name="StepNum", fontName="LMG-Bold", fontSize=12, leading=15,
    textColor=colors.white, alignment=TA_CENTER,
))


def p(text, style="BodyLMG"):
    return Paragraph(text, styles[style])


def bullet(text):
    return Paragraph("• " + text, styles["BulletLMG"])


def callout(title, body, bg=PALE_BLUE, edge=BLUE):
    content = Paragraph(f"<b>{title}</b><br/>{body}", styles["CalloutLMG"])
    table = Table([[content]], colWidths=[doc.width])
    table.setStyle(TableStyle([
        ("BACKGROUND", (0, 0), (-1, -1), bg),
        ("BOX", (0, 0), (-1, -1), 0.7, edge),
        ("LEFTPADDING", (0, 0), (-1, -1), 8),
        ("RIGHTPADDING", (0, 0), (-1, -1), 8),
        ("TOPPADDING", (0, 0), (-1, -1), 7),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 7),
    ]))
    return table


def step(number, title, body):
    badge = Table([[Paragraph(str(number), styles["StepNum"])]], colWidths=[10 * mm], rowHeights=[10 * mm])
    badge.setStyle(TableStyle([
        ("BACKGROUND", (0, 0), (-1, -1), BLUE),
        ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
        ("BOX", (0, 0), (-1, -1), 0, BLUE),
    ]))
    detail = Paragraph(f"<b>{title}</b><br/>{body}", styles["BodyLMG"])
    table = Table([[badge, detail]], colWidths=[13 * mm, doc.width - 13 * mm])
    table.setStyle(TableStyle([
        ("VALIGN", (0, 0), (-1, -1), "TOP"),
        ("LEFTPADDING", (0, 0), (-1, -1), 0),
        ("RIGHTPADDING", (0, 0), (-1, -1), 4),
        ("TOPPADDING", (0, 0), (-1, -1), 3),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 5),
    ]))
    return table


story = []

# Cover
story += [
    Spacer(1, 18 * mm),
    p("PANDUAN OPERASIONAL", "SmallLMG"),
    p("Live Monitoring<br/>ERP Lighter Media Group", "TitleLMG"),
    p("Panduan untuk pengguna target, pemantau, dan administrator — termasuk alur tanpa kamera fisik dan perlakuan terhadap kamera virtual.", "SubtitleLMG"),
    callout(
        "Prinsip utama",
        "Live Monitoring hanya menampilkan window kerja dan kamera opsional secara langsung. Fitur ini tidak merekam video dan tidak memberi pemantau kendali atas perangkat pengguna.",
        PALE_GREEN, GREEN,
    ),
    Spacer(1, 12 * mm),
]

summary_data = [
    [p("Dokumen", "SmallLMG"), p("Dokumentasi Live Monitoring", "BodyLMG")],
    [p("Versi", "SmallLMG"), p("1.0", "BodyLMG")],
    [p("Tanggal", "SmallLMG"), p("8 Oktober 2026", "BodyLMG")],
    [p("Cakupan", "SmallLMG"), p("Akses, penggunaan, privasi, konfigurasi, dan troubleshooting", "BodyLMG")],
]
summary = Table(summary_data, colWidths=[34 * mm, doc.width - 34 * mm])
summary.setStyle(TableStyle([
    ("BACKGROUND", (0, 0), (0, -1), SOFT),
    ("GRID", (0, 0), (-1, -1), 0.5, LINE),
    ("VALIGN", (0, 0), (-1, -1), "TOP"),
    ("LEFTPADDING", (0, 0), (-1, -1), 7),
    ("RIGHTPADDING", (0, 0), (-1, -1), 7),
    ("TOPPADDING", (0, 0), (-1, -1), 7),
    ("BOTTOMPADDING", (0, 0), (-1, -1), 7),
]))
story += [summary, PageBreak()]

# Overview and permissions
story += [
    p("1. Gambaran fitur", "H1LMG"),
    p("Live Monitoring membantu pihak yang berwenang melihat aktivitas kerja yang sengaja dibagikan oleh pengguna. Setiap sesi dimulai dari browser pengguna target dan memerlukan persetujuan untuk memilih window yang akan dibagikan."),
    p("Yang dikirim", "H2LMG"),
    bullet("Satu window kerja yang dipilih pengguna melalui dialog browser."),
    bullet("Kamera fisik, jika perangkat tersedia dan pengguna memberikan izin."),
    bullet("Tanpa audio; aplikasi tidak meminta mikrofon."),
    p("Yang tidak dilakukan", "H2LMG"),
    bullet("Tidak merekam atau menyimpan video layar dan kamera."),
    bullet("Tidak mengendalikan mouse, keyboard, atau aplikasi pada perangkat target."),
    bullet("Tidak melewati izin keamanan yang ditampilkan browser."),
    Spacer(1, 3 * mm),
    p("2. Hak akses", "H1LMG"),
]

role_data = [
    [p("Peran", "SmallLMG"), p("Kemampuan", "SmallLMG")],
    [p("Administrator", "BodyLMG"), p("Melihat direktori pengguna, mengatur relasi target–pemantau, dan memantau target yang aktif.", "BodyLMG")],
    [p("Pengguna target", "BodyLMG"), p("Memulai serta menghentikan siaran sendiri jika sudah memiliki minimal satu pemantau yang ditugaskan.", "BodyLMG")],
    [p("Pemantau", "BodyLMG"), p("Melihat siaran hanya dari pengguna target yang secara khusus ditugaskan kepadanya.", "BodyLMG")],
]
role_table = Table(role_data, colWidths=[38 * mm, doc.width - 38 * mm], repeatRows=1)
role_table.setStyle(TableStyle([
    ("BACKGROUND", (0, 0), (-1, 0), NAVY),
    ("TEXTCOLOR", (0, 0), (-1, 0), colors.white),
    ("GRID", (0, 0), (-1, -1), 0.5, LINE),
    ("VALIGN", (0, 0), (-1, -1), "TOP"),
    ("ROWBACKGROUNDS", (0, 1), (-1, -1), [colors.white, SOFT]),
    ("LEFTPADDING", (0, 0), (-1, -1), 7),
    ("RIGHTPADDING", (0, 0), (-1, -1), 7),
    ("TOPPADDING", (0, 0), (-1, -1), 7),
    ("BOTTOMPADDING", (0, 0), (-1, -1), 7),
]))
story += [role_table, Spacer(1, 3 * mm), callout("Catatan akses", "Target tidak dapat menjadi pemantau untuk dirinya sendiri. Satu target dapat memiliki lebih dari satu pemantau berwenang.", PALE_AMBER, AMBER), PageBreak()]

# Target workflow
story += [
    p("3. Memulai siaran", "H1LMG"),
    p("Langkah berikut dilakukan oleh pengguna yang window kerjanya akan dipantau."),
    step(1, "Buka Monitoring", "Dari direktori Monitoring, pilih <b>Bagikan layar saya</b>. Tombol tersedia setelah administrator mengatur akses pemantau."),
    step(2, "Pilih window kerja", "Tekan <b>1. Izinkan window kerja</b>, lalu pilih satu window pada dialog browser. Membagikan satu window lebih aman daripada seluruh layar."),
    step(3, "Ikuti pemeriksaan kamera", "Sistem memeriksa perangkat kamera fisik. Hasil pemeriksaan menentukan apakah izin kamera diperlukan."),
    step(4, "Mulai live", "Jika kamera fisik tersedia, tekan <b>2. Izinkan kamera</b>. Jika tidak ada kamera fisik, live otomatis berjalan hanya dengan window kerja."),
    step(5, "Pertahankan sesi", "Biarkan tab Monitoring terbuka. Gunakan tombol <b>Hentikan</b> saat selesai."),
    p("Keputusan kamera", "H2LMG"),
]

camera_data = [
    [p("Kondisi", "SmallLMG"), p("Perilaku sistem", "SmallLMG")],
    [p("Kamera fisik tersedia", "BodyLMG"), p("Izin kamera wajib diminta sebelum sesi aktif.", "BodyLMG")],
    [p("Tidak ada kamera fisik", "BodyLMG"), p("Sesi aktif menggunakan window kerja saja.", "BodyLMG")],
    [p("Hanya kamera virtual", "BodyLMG"), p("Kamera virtual diabaikan; sesi aktif dengan window kerja saja.", "BodyLMG")],
    [p("Izin kamera fisik ditolak", "BodyLMG"), p("Sesi tidak dilanjutkan diam-diam. Perbaiki izin lalu coba kembali.", "BodyLMG")],
]
camera_table = Table(camera_data, colWidths=[52 * mm, doc.width - 52 * mm], repeatRows=1)
camera_table.setStyle(TableStyle([
    ("BACKGROUND", (0, 0), (-1, 0), NAVY),
    ("TEXTCOLOR", (0, 0), (-1, 0), colors.white),
    ("GRID", (0, 0), (-1, -1), 0.5, LINE),
    ("VALIGN", (0, 0), (-1, -1), "TOP"),
    ("ROWBACKGROUNDS", (0, 1), (-1, -1), [colors.white, SOFT]),
    ("LEFTPADDING", (0, 0), (-1, -1), 7),
    ("RIGHTPADDING", (0, 0), (-1, -1), 7),
    ("TOPPADDING", (0, 0), (-1, -1), 7),
    ("BOTTOMPADDING", (0, 0), (-1, -1), 7),
]))
story += [camera_table, Spacer(1, 3 * mm), callout("Kamera virtual", "OBS Virtual Camera, ManyCam, Snap Camera, XSplit, NDI, DroidCam, Iriun, dan EpocCam tidak dihitung sebagai kamera fisik.", PALE_BLUE, BLUE), PageBreak()]

# Viewer/admin
story += [
    p("4. Melihat siaran", "H1LMG"),
    step(1, "Buka direktori", "Masuk ke menu <b>Monitoring</b>. Daftar hanya menampilkan target yang dapat diakses oleh akun pemantau."),
    step(2, "Pilih target", "Buka pengguna yang berstatus aktif. Jika belum ada sesi, tunggu target memulai live lalu muat ulang daftar."),
    step(3, "Pantau tayangan", "Pemantau menerima video window kerja dan, jika tersedia, video kamera. Pemantau tidak dapat mengendalikan perangkat target."),
    p("5. Mengatur akses", "H1LMG"),
    p("Pengaturan berikut hanya tersedia bagi administrator."),
    step(1, "Buka pengaturan akses", "Dari halaman Monitoring, masuk ke pengaturan akses monitoring."),
    step(2, "Tentukan target", "Pilih pengguna yang akan membagikan window kerja."),
    step(3, "Pilih pemantau", "Tambahkan satu atau beberapa akun yang diizinkan melihat target."),
    step(4, "Simpan", "Setelah disimpan, target dapat memulai sesi dan pemantau dapat melihatnya saat live."),
    callout("Keamanan", "Menghapus relasi akses mencegah pemantau tersebut mengakses sesi berikutnya. Semua endpoint sesi juga memeriksa otorisasi di server.", PALE_GREEN, GREEN),
    PageBreak(),
]

# Architecture and troubleshooting
story += [
    p("6. Arsitektur dan privasi", "H1LMG"),
    bullet("WebRTC mengirim media langsung antarbrowser."),
    bullet("Firestore menyimpan status ruang dan mendukung pertukaran sinyal real-time."),
    bullet("Endpoint ERP menyediakan jalur sinyal pendukung serta memeriksa peserta sesi."),
    bullet("Cloudflare TURN menyediakan kredensial sementara untuk jaringan yang tidak dapat tersambung langsung."),
    bullet("Server membuat sesi berdasarkan akun login; browser tidak dapat mengaku sebagai target lain."),
    bullet("Media tidak disimpan sebagai rekaman oleh fitur Live Monitoring."),
    p("Persyaratan lingkungan", "H2LMG"),
    bullet("Gunakan HTTPS pada produksi. `localhost` dapat digunakan untuk pengembangan."),
    bullet("Konfigurasi Firebase/Firestore dan Cloudflare TURN harus aktif."),
    bullet("Browser harus mendukung screen sharing (`getDisplayMedia`) dan WebRTC."),
    p("7. Troubleshooting", "H1LMG"),
]

troubles = [
    ("OBS tetap muncul pada menu browser", "OBS memasang driver kamera virtual di Windows. ERP mengabaikannya, tetapi browser tetap dapat menampilkannya. Nonaktifkan atau hapus driver virtual jika ingin menghilangkannya dari daftar browser."),
    ("Kamera dicabut, tombol kamera sempat muncul", "Browser mungkin belum memperbarui perangkat atau belum membuka label perangkat. Refresh halaman. Jika track yang diterima ternyata virtual, ERP menghentikannya dan melanjutkan tanpa kamera."),
    ("Izin kamera fisik ditolak", "Buka ikon izin pada bilah alamat, izinkan kamera, lalu mulai ulang sesi."),
    ("Window kerja tidak muncul", "Pastikan halaman memakai HTTPS, browser mendukung screen sharing, dan pengguna benar-benar memilih window pada dialog browser."),
    ("Pemantau tidak melihat target", "Pastikan administrator sudah menyimpan relasi target–pemantau dan akun target tidak terkunci."),
    ("Video tidak tersambung", "Periksa internet kedua perangkat, kredensial Cloudflare TURN, serta konfigurasi Firebase/Firestore."),
    ("Sesi langsung berhenti", "Jangan tutup atau refresh tab Monitoring dan jangan menghentikan share dari indikator browser."),
]
for title, body in troubles:
    story.append(KeepTogether([p(title, "H2LMG"), p(body)]))

story += [PageBreak(), p("8. Checklist verifikasi", "H1LMG")]
checks = [
    "Pengguna tanpa relasi akses tidak dapat memulai siaran atau melihat target lain.",
    "Administrator dapat menambah, mengubah, dan menghapus relasi target–pemantau.",
    "Window kerja berhasil dibagikan setelah pengguna menyetujui dialog browser.",
    "Kamera fisik yang tersedia tetap meminta izin kamera.",
    "Tanpa kamera fisik, sesi aktif menggunakan window kerja saja.",
    "OBS Virtual Camera dan kamera virtual umum lain tidak dianggap kamera fisik.",
    "Penolakan izin kamera fisik tidak otomatis dilewati.",
    "Pemantau berwenang menerima layar serta kamera opsional.",
    "Tombol Hentikan, penutupan tab, dan penghentian share browser mengakhiri sesi.",
    "Pemantau yang tidak ditugaskan mendapat penolakan akses.",
]
for item in checks:
    story.append(bullet(item))

story += [
    Spacer(1, 5 * mm),
    callout("Referensi implementasi", "Route: <b>routes/web.php</b><br/>Controller: <b>app/Http/Controllers/MonitoringController.php</b><br/>Antarmuka sesi: <b>resources/views/monitoring/index.blade.php</b>", SOFT, MUTED),
    Spacer(1, 5 * mm),
    p("Dokumentasi ini mengikuti perilaku aplikasi pada 8 Oktober 2026.", "SmallLMG"),
]

doc.build(story)
print(OUTPUT)
