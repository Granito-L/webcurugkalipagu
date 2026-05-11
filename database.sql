-- ============================================================
-- DATABASE: wisata_kalipagu
-- Wisata Curug Kalipagu Baturraden Banyumas
-- ============================================================

CREATE DATABASE IF NOT EXISTS wisata_kalipagu
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE wisata_kalipagu;

-- ============================================================
-- TABEL: users
-- Menyimpan data pengguna admin
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)  NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL COMMENT 'Hashed dengan password_hash()',
    full_name   VARCHAR(100) NOT NULL,
    role        ENUM('admin','editor') NOT NULL DEFAULT 'admin',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABEL: destinations
-- Menyimpan data 6 destinasi curug
-- ============================================================
CREATE TABLE IF NOT EXISTS destinations (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    slug        VARCHAR(100) NOT NULL UNIQUE COMMENT 'URL-friendly identifier',
    title       VARCHAR(150) NOT NULL,
    subtitle    VARCHAR(200),
    description TEXT        NOT NULL COMMENT 'Deskripsi singkat untuk card',
    history     LONGTEXT    NOT NULL COMMENT 'Sejarah dan filosofi lengkap',
    highlights  TEXT        COMMENT 'Fitur unggulan, dipisah dengan |',
    location    VARCHAR(200),
    altitude    VARCHAR(50)  COMMENT 'Ketinggian air terjun',
    best_time   VARCHAR(100) COMMENT 'Waktu terbaik berkunjung',
    image_path  VARCHAR(255) DEFAULT 'assets/images/placeholder.jpg',
    sort_order  TINYINT     DEFAULT 0,
    is_active   TINYINT(1)  DEFAULT 1,
    created_at  TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP   DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABEL: settings
-- Konfigurasi tampilan UI yang bisa diubah admin
-- ============================================================
CREATE TABLE IF NOT EXISTS settings (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_val TEXT,
    label       VARCHAR(150) COMMENT 'Label ramah untuk form admin',
    group_name  VARCHAR(50)  DEFAULT 'general' COMMENT 'Kelompok pengaturan',
    updated_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- DATA: users
-- Password default: Admin@12345 (ganti setelah deploy!)
-- ============================================================
INSERT INTO users (username, password, full_name, role) VALUES
('admin', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator Wisata', 'admin');
-- Catatan: hash di atas adalah 'password' — GANTI dengan hash yang aman!
-- Untuk generate hash baru: echo password_hash('Admin@12345', PASSWORD_DEFAULT);

-- ============================================================
-- DATA: destinations
-- ============================================================
INSERT INTO destinations 
  (slug, title, subtitle, description, history, highlights, location, altitude, best_time, image_path, sort_order)
VALUES

-- 1. Curug Bayan
('curug-bayan',
 'Curug Bayan',
 'Air Terjun Pertanda Kesuburan',
 'Curug Bayan merupakan air terjun ikonik di kawasan Kalipagu dengan aliran jernih yang menghujam bebatuan purba. Dikelilingi pepohonan hijau rapat, curug ini menawarkan panorama alam yang menyejukkan jiwa dan raga.',
 'Nama "Bayan" berasal dari bahasa Jawa kuno yang bermakna "pertanda" atau "pertanda baik". Menurut kepercayaan masyarakat Desa Ketenger yang turun-temurun, Curug Bayan dianggap sebagai penanda awal musim tanam yang menguntungkan. Para petani di lereng Gunung Slamet dahulu menjadikan debit air curug ini sebagai patokan: bila airnya deras, panen akan melimpah; bila surut, mereka bersiap menghadapi musim kering.\n\nLegenda lokal mengisahkan bahwa seorang tokoh sakti bernama Ki Bayan pernah bertapa di tepi curug ini selama empat puluh hari. Selama pertapaan tersebut, konon tak ada hewan buas yang berani mendekat, dan air curug memancarkan cahaya kebiruan di malam bulan purnama. Masyarakat percaya aura sakral Ki Bayan masih menghuni tempat ini, sehingga curug dijaga dengan penuh hormat hingga kini.\n\nSecara geologis, Curug Bayan terbentuk akibat patahan lava purba Gunung Slamet yang menciptakan tebing vertikal setinggi lebih dari dua puluh meter. Vegetasi di sekitarnya masih sangat alami, dengan berbagai spesies pakis raksasa, lumut hijau tebal, dan anggrek liar yang menempel di bebatuan berlumut.',
 'Kolam alami segar|Tebing berlumut purba|Jalur trekking 30 menit|Flora endemik lereng Slamet',
 'Desa Ketenger, Baturraden, Banyumas',
 '± 22 meter',
 'April – Oktober (musim kemarau)',
 'assets/images/curug-bayan.jpg',
 1),

-- 2. Curug Jenggala
('curug-jenggala',
 'Curug Jenggala',
 'Air Terjun di Rimba Tak Bertepi',
 'Tersembunyi di balik rimbunnya hutan tropis Kalipagu, Curug Jenggala menawarkan pengalaman petualangan autentik. Suara gemuruhnya sudah terdengar dari kejauhan, menjadi pemanggil jiwa-jiwa pencinta alam sejati.',
 'Kata "Jenggala" merujuk pada konsep Jawa kuno yang berarti "hutan lebat tak berpenghuni" atau "rimba belantara". Dalam naskah Babad Banyumas yang disalin ulang pada abad ke-19, disebutkan bahwa hutan di kawasan ini pernah menjadi tempat persembunyian para pejuang yang menolak tunduk pada kekuasaan penjajah.\n\nFilosofi Jenggala mengajarkan bahwa sejati kekuatan bukan pada keramaian, melainkan pada kesunyian yang produktif — seperti hutan yang diam namun menghidupi jutaan makhluk. Para leluhur mengajarkan kepada generasi muda bahwa memasuki rimba Jenggala berarti memasuki proses perenungan diri. Tidak mengherankan jika curug ini kerap dijadikan tujuan oleh mereka yang ingin "menemukan diri" di tengah alam.\n\nAliran Curug Jenggala berasal dari mata air tinggi di lereng barat Gunung Slamet, menembus celah-celah batuan andesit yang membentuk tebing bertingkat alami. Vegetasinya sangat rapat dengan kanopi pohon rasamala (Altingia excelsa) yang menutup hampir seluruh langit, menciptakan suasana mistis dan segar sepanjang hari.',
 'Suasana hutan tropis autentik|Tebing bertingkat alami|Spot foto dramatis|Trek menantang 45 menit',
 'Desa Ketenger, Baturraden, Banyumas',
 '± 18 meter',
 'Mei – September',
 'assets/images/curug-jenggala.jpg',
 2),

-- 3. Curug Penganten
('curug-penganten',
 'Curug Penganten',
 'Dua Aliran, Satu Jiwa',
 'Curug Penganten adalah fenomena alam yang memukau — dua aliran air yang terpisah di atas kemudian menyatu di kolam yang sama. Keindahan romantis ini menjadikannya destinasi favorit pasangan yang ingin merayakan cinta di tengah alam.',
 'Nama "Penganten" (pengantin dalam Bahasa Indonesia) diabadikan karena bentuk curug yang unik: dua pancuran air terpisah mengalir berdampingan dari celah tebing yang sama, lalu berpadu di kolam di bawahnya — persis seperti dua insan yang disatukan dalam ikatan suci pernikahan.\n\nMenurut cerita rakyat turun-temurun, pada jaman dahulu sepasang kekasih muda dari dua desa berseberangan yang saling bermusuhan melarikan diri ke hutan ini karena cinta mereka dilarang. Mereka berdua berdoa di tepi curug agar diberi kekuatan. Konon, pada malam itu air curug berubah menjadi dua aliran yang akhirnya menyatu, diyakini sebagai tanda restu alam atas cinta tulus mereka. Kisah ini kemudian menginspirasi masyarakat untuk menamai tempat ini Curug Penganten.\n\nSecara ilmiah, fenomena dua aliran ini terjadi akibat rekahan batuan vulkanik di puncak tebing yang membelah satu sumber air menjadi dua jalur berbeda sebelum keduanya bergabung kembali di bawah. Proses ini membentuk percikan air yang sangat indah, menciptakan pelangi mini saat cahaya matahari menembusnya di pagi hari.',
 'Fenomena dua aliran menyatu|Pelangi alami pagi hari|Kolam renang alami|Legenda romantis yang memukau',
 'Desa Ketenger, Baturraden, Banyumas',
 '± 15 meter (2 aliran)',
 'Sepanjang tahun (terbaik: pagi hari)',
 'assets/images/curug-penganten.jpg',
 3),

-- 4. Curug Mertelu
('curug-mertelu',
 'Curug Mertelu',
 'Tirta Suci Pengusir Malapetaka',
 'Curug Mertelu dikenal sebagai air terjun yang memiliki kualitas air paling jernih di kawasan Kalipagu. Airnya yang dingin menusuk tulang dan segar dipercaya memiliki khasiat penyembuhan, menjadikannya tempat ziarah alam tersendiri.',
 'Nama "Mertelu" berasal dari kata "mertu" dalam dialek Banyumas yang berarti "mertua" atau "leluhur yang dihormati", dan "lu" yang merupakan imbuhan penghormatan. Sehingga Curug Mertelu secara harfiah berarti "Air Terjun Para Leluhur yang Dimuliakan".\n\nDalam tradisi masyarakat Banyumasan, curug ini dianggap sebagai sumber air suci. Pada masa lalu, para dukun dan tetua adat mengambil air dari Curug Mertelu untuk keperluan upacara ruwatan — ritual pembersihan diri dari malapetaka. Air curug ini juga digunakan dalam prosesi pengobatan tradisional, dipercaya mampu menyembuhkan berbagai penyakit kulit dan memberikan ketenangan batin bagi mereka yang mandi di dalamnya.\n\nSetiap tahun pada bulan Suro (Muharram dalam kalender Islam), masyarakat setempat masih mengadakan ritual sederhana berupa doa bersama dan larung sesaji di tepi curug sebagai bentuk rasa syukur dan penghormatan kepada alam. Tradisi ini menjadi daya tarik budaya tersendiri bagi wisatawan yang ingin mengenal kearifan lokal Banyumas secara mendalam.',
 'Air paling jernih di Kalipagu|Tradisi ruwatan budaya|Khasiat air alami|Upacara adat Bulan Suro',
 'Desa Ketenger, Baturraden, Banyumas',
 '± 12 meter',
 'Juni – Agustus',
 'assets/images/curug-mertelu.jpg',
 4),

-- 5. Curug Rambat
('curug-rambat',
 'Curug Rambat',
 'Air yang Merayap Membelai Batu',
 'Berbeda dengan curug vertikal kebanyakan, Curug Rambat memiliki aliran air yang merambat dan meluncur di atas permukaan batu berlumut yang lebar dan landai. Tampilannya seperti tirai air alami yang memukau dan unik di antara curug-curug Kalipagu.',
 'Nama "Rambat" dengan tepat menggambarkan karakteristik fisik curug ini yang sangat istimewa. Dalam Bahasa Jawa, "rambat" berarti "merambat" atau "merayap perlahan". Airnya tidak jatuh bebas secara vertikal, melainkan mengalir dan meluncur perlahan di atas bidang batu andesit miring selebar hampir delapan meter — membentuk lembaran air tipis transparan yang menakjubkan.\n\nFilosofi Curug Rambat dalam kearifan lokal Banyumas sangat dalam: air yang merambat perlahan justru mampu mengikis batu yang keras dalam waktu panjang. Ini menjadi pengajaran tentang kesabaran, ketekunan, dan kekuatan yang tidak selalu tampil dengan kegarangan — terkadang kelembutan yang konsisten jauh lebih kuat daripada kekuatan yang keras.\n\nSudah menjadi kebiasaan para orang tua di desa setempat untuk membawa anak-anaknya ke Curug Rambat dan menceritakan filosofi ini sebagai bekal hidup. Tak jarang, batu-batu besar yang pernah menghalangi aliran justru telah terkikis habis oleh air yang merambat secara konsisten selama ratusan tahun — bukti nyata filosofi tersebut terukir di alam.',
 'Fenomena air merambat unik|Tirai air lebar 8 meter|Filosofi kesabaran kearifan lokal|Spot fotografer favorit',
 'Desa Ketenger, Baturraden, Banyumas',
 '± 8 meter (landai)',
 'Sepanjang tahun',
 'assets/images/curug-rambat.jpg',
 5),

-- 6. Pancuran Pitu
('pancuran-pitu',
 'Pancuran Pitu',
 'Tujuh Pancuran Sumber Kehidupan',
 'Pancuran Pitu adalah fenomena tujuh mata air panas alami yang menyembur dari celah batuan vulkanik. Diyakini sebagai titik energi sakral di kaki Gunung Slamet, tempat ini menjadi destinasi relaksasi spiritual yang tak tertandingi.',
 'Angka tujuh atau "pitu" dalam numerologi Jawa memiliki makna filosofis yang sangat kuat. Tujuh melambangkan kesempurnaan alam semesta, tujuh lapisan langit, tujuh warna pelangi, dan tujuh chakra dalam tubuh manusia. Pancuran Pitu karenanya dianggap sebagai tempat di mana energi alam tersalurkan secara sempurna melalui tujuh titik semburan air yang berbeda.\n\nDalam mitologi lokal, Pancuran Pitu dikisahkan terbentuk dari "tujuh tetesan keringat" Raden Kamandaka — tokoh legenda terkenal dari tanah Banyumas yang dikenal dalam kisah Lutung Kasarung. Selama pengembaraannya menelusuri lereng Gunung Slamet, Raden Kamandaka yang kelelahan berhenti di titik ini. Setiap tetesan keringatnya yang jatuh ke tanah konon memancar menjadi satu sumber air hangat. Legenda ini menjadikan Pancuran Pitu sebagai tempat yang sangat disakralkan.\n\nSecara ilmiah, Pancuran Pitu adalah manifestasi aktivitas vulkanik bawah tanah Gunung Slamet. Air tanah yang meresap dipanaskan oleh magma di kedalaman lalu naik kembali ke permukaan melalui celah-celah batuan, membawa mineral sulfur, belerang, dan unsur-unsur lain yang terbukti bermanfaat bagi kesehatan kulit. Suhu airnya berkisar antara 30–45°C, ideal untuk terapi rendam kaki dan relaksasi tubuh.',
 'Tujuh sumber air panas alami|Makna filosofi Jawa mendalam|Khasiat mineral vulkanik|Terapi relaksasi dan meditasi',
 'Kompleks Baturraden, Banyumas',
 'Sumber air panas (30-45°C)',
 'Sepanjang tahun (terbaik: pagi & sore)',
 'assets/images/pancuran-pitu.jpg',
 6);

-- ============================================================
-- DATA: settings (Konfigurasi UI)
-- ============================================================
INSERT INTO settings (setting_key, setting_val, label, group_name) VALUES
-- === Hero Section ===
('hero_headline',    'Jelajahi Keajaiban\nCurug Kalipagu',      'Teks Headline Hero',           'hero'),
('hero_subheadline', 'Enam permata tersembunyi di kaki Gunung Slamet, Baturraden, Banyumas — menanti untuk dijelajahi.',
                                                                 'Teks Sub-Headline Hero',        'hero'),
('hero_cta_text',    'Mulai Petualangan',                        'Teks Tombol CTA Hero',          'hero'),

-- === Branding & Colors ===
('color_primary',    '#2D6A4F',   'Warna Primer (hijau hutan)',    'colors'),
('color_secondary',  '#40916C',   'Warna Sekunder',                'colors'),
('color_accent',     '#B7E4C7',   'Warna Aksen',                   'colors'),
('color_dark',       '#1B4332',   'Warna Gelap',                   'colors'),
('color_text',       '#081C15',   'Warna Teks Utama',              'colors'),

-- === General Info ===
('site_title',       'Wisata Curug Kalipagu',                   'Nama Website',                  'general'),
('site_tagline',     'Baturraden · Banyumas · Jawa Tengah',     'Tagline Website',               'general'),
('contact_email',    'info@kalipagu.id',                        'Email Kontak',                  'general'),
('contact_phone',    '+62 812-3456-7890',                       'Telepon Kontak',                'general'),
('footer_text',      '© 2025 Wisata Curug Kalipagu. Seluruh hak cipta dilindungi.', 'Teks Footer', 'general'),

-- === About Section ===
('about_text',       'Kawasan Curug Kalipagu terletak di Desa Ketenger, Kecamatan Baturraden, Kabupaten Banyumas, Jawa Tengah. Berada di kaki Gunung Slamet — gunung berapi tertinggi di Jawa Tengah — kawasan ini menyimpan kekayaan alam dan budaya yang luar biasa. Enam air terjun utama dengan karakter masing-masing menjadikan Kalipagu destinasi wisata alam yang tak terlupakan.',
                                                                 'Teks Tentang Kami',             'about');
