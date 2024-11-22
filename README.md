
# Automated Label Generator

<div align="center">
    <img src="/public/img/logo.png" alt="Logo" width="200"/>
    <h3>Sistem Otomasi Pembuatan Label Produksi</h3>
</div>

## 📋 Deskripsi
Automated Label Generator adalah sistem yang dirancang untuk mengotomatisasi proses pembuatan dan pencetakan label produksi. Sistem ini dikembangkan khusus untuk Perum Percetakan Uang RI (PERURI) dengan fokus pada efisiensi dan akurasi dalam manajemen label produksi.

## 🚀 Fitur Utama

### 1. Pembuatan Label Otomatis
- Generasi label produksi secara otomatis
- Dukungan untuk Order Besar dan Order Kecil
- Format label yang dapat dikustomisasi
- Pencetakan batch label

### 2. Manajemen Order Produksi
- Registrasi Production Order (PO)
- Tracking Order Batch Control (OBC)
- Pengelolaan status produksi
- Verifikasi produk

### 3. Monitoring Produksi
- Pemantauan status produksi real-time
- Tracking kinerja tim
- Statistik produksi
- Status verifikasi

### 4. Manajemen Pengguna
- Sistem multi-user
- Kontrol akses berbasis peran
- Manajemen tim
- Keamanan sistem

## 🛠 Tech Stack

### Frontend
- Vue.js 3 (Composition API)
- TailwindCSS
- Inertia.js
- Lucide Icons

### Backend
- Laravel
- MySQL/PostgreSQL

### Desktop Integration
- Electron

## 📦 Instalasi

```bash
# Clone repository
git clone [repository-url]

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Start development server
npm run dev
php artisan serve
```

## 🔧 Konfigurasi

### Environment Variables
```env
APP_NAME="Automated Label Generator"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=label_generator
DB_USERNAME=root
DB_PASSWORD=
```

## 🖨 Penggunaan Printer

Sistem mendukung berbagai jenis printer dengan konfigurasi yang dapat disesuaikan:
- Format kertas A4/A5
- Pengaturan margin
- Kustomisasi ukuran label

## 🔒 Keamanan

- Autentikasi user
- CSRF protection
- Rate limiting
- Validasi input
- Sanitasi data

## 📱 Responsive Design

Sistem didesain untuk dapat diakses dari berbagai perangkat dengan tampilan yang responsif:
- Desktop (Electron app)
- Tablet
- Mobile devices

## 🤝 Kontribusi

Untuk berkontribusi pada project ini:
1. Fork repository
2. Buat branch baru (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## 📄 Lisensi

Hak Cipta © 2024 Perum Percetakan Uang RI (PERURI). Seluruh hak dilindungi.

## 📞 Kontak

Project Owner - [Nama PO]
Developer Team - [Kontak Tim Developer]

---

<div align="center">
    <p>Dibuat untuk SBU High Security Solution PERURI</p>
</div>
```
