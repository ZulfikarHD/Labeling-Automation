# ProduksiPegawaiController Documentation

## Overview

**Controller untuk monitoring aktivitas produksi pegawai dalam sistem labeling**

Controller untuk menangani monitoring dan tracking aktivitas produksi pegawai dalam sistem. Controller ini mengelola workflow monitoring dengan fokus pada visualisasi aktivitas tim produksi dan status workstation.

## Purpose

Mengelola proses monitoring produksi pegawai dengan fitur:
- Monitoring aktivitas tim produksi real-time
- Tracking status workstation
- Visualisasi data produksi per tim
- Interface monitoring untuk supervisor
- Integrasi dengan sistem verifikasi tim

### Method Organization
Controller diorganisir dengan clear separation of concerns:
- **Public Methods**: Endpoint untuk rendering halaman monitoring
- **Model Injection**: Menggunakan Workstations model untuk data dasar
- **Response Methods**: Inertia response untuk SSR Vue.js

## Main Flow

1. **Initial Page Load**
   - User mengakses halaman monitoring produksi
   - Controller mengambil data workstation melalui `Workstations::listWorkstation()`
   - Controller render halaman Vue dengan data workstation dasar

2. **Frontend Dynamic Loading**
   - Vue component melakukan fetch ke `/api/active-teams` dengan parameter date
   - API endpoint `PendapatanHarianController::getActiveTeams()` dipanggil
   - TeamActivityService mengquery `generated_labels` table untuk tim aktif

3. **Data Filtering**
   - Frontend memfilter workstation berdasarkan tim yang aktif pada tanggal
   - Hanya tim yang memiliki aktivitas produksi yang ditampilkan
   - Component `TableVerifikasiPegawai` di-render untuk setiap tim aktif

4. **Real-time Updates**
   - User dapat mengubah tanggal filter
   - System otomatis reload data tim aktif berdasarkan tanggal baru
   - Data verifikasi pegawai di-fetch dari `/api/pendapatan-harian`

## Dependencies

- **Workstations Model**: Model untuk akses data workstation dan tim
- **Inertia**: Framework untuk Server-Side Rendering dengan Vue.js
- **Laravel Controller**: Base controller dengan middleware support

**Note**: TeamActivityService digunakan oleh `PendapatanHarianController` untuk API endpoints, bukan oleh controller ini.

## Public Methods

### `index(Workstations $workstations)`

**Purpose**: Menampilkan halaman monitoring produksi pegawai dengan data workstation dasar

**Flow**:
1. Menerima dependency injection Workstations model
2. Mengambil data workstation melalui listWorkstation() (id dan nama workstation)
3. Render halaman Vue dengan data workstation
4. Frontend kemudian melakukan API calls untuk data dinamis (tim aktif, data produksi)

**Parameters**:
- `$workstations`: Instance Workstations model untuk akses data workstation

**Returns**: `Response` - Inertia response dengan halaman Vue dan data tim

**Response Data Structure**:
```php
[
    'teams' => [
        // Data dari $workstations->listWorkstation()
        [
            'id' => 1,
            'workstation' => 'Team A' // Nama workstation/tim
        ],
        [
            'id' => 2,
            'workstation' => 'Team B'
        ]
        // ... more teams
    ]
]
```

**Usage**:
```php
// Route akan memanggil method ini
Route::get('/monitoring/produksi-pegawai', [ProduksiPegawaiController::class, 'index']);
```

**Frontend Integration**:
- Renders: `MonitoringProduksi/ProduksiPegawai.vue`
- Props: `teams` array dengan data workstation

## Database Tables Interaction

### workstation (table name)
**Purpose**: Menyimpan data workstation dan tim produksi
**Fields**:
- `id`: Primary key workstation
- `workstation`: Nama workstation/tim
- `created_at`: Timestamp pembuatan
- `updated_at`: Timestamp update terakhir

**Operations**:
- `listWorkstation()`: Mengambil id dan workstation, diurutkan berdasarkan nama workstation

### generated_labels (digunakan oleh TeamActivityService)
**Purpose**: Menyimpan data label yang telah digenerate untuk tracking aktivitas tim
**Fields** (yang digunakan):
- `workstation`: ID workstation yang mengerjakan
- `start`: Tanggal mulai produksi
- `np_users`: Nama pegawai (untuk filter non-mesin)

**Operations**:
- `getActiveTeams()`: Query tim yang aktif pada tanggal tertentu
- `hasActivity()`: Cek apakah tim memiliki aktivitas pada tanggal tertentu

## Business Rules

### Data Access Rules
1. **Workstation Visibility**: Semua workstation dapat dilihat dalam monitoring
2. **Real-time Data**: Data workstation harus up-to-date untuk monitoring akurat
3. **Team Association**: Setiap workstation terkait dengan tim produksi

### UI/UX Rules
1. **Responsive Design**: Interface harus responsive untuk berbagai device
2. **Real-time Updates**: Data monitoring harus dapat di-refresh secara real-time
3. **Status Visualization**: Status tim harus tervisualisasi dengan jelas

## Error Handling

### Exception Types
1. **Model Exception**: Jika terjadi error saat mengakses data workstation
2. **Service Exception**: Jika TeamActivityService mengalami error
3. **Inertia Exception**: Jika terjadi error saat rendering halaman

### Error Recovery
- **Graceful Degradation**: Jika data tidak tersedia, tampilkan pesan informatif
- **Fallback Data**: Gunakan data cached jika database tidak accessible

## Usage Examples

### Basic Usage
```javascript
// Frontend akan menerima data dari controller
export default {
    props: {
        teams: Array
    },
    mounted() {
        console.log('Teams data:', this.teams);
        // Process teams data untuk monitoring interface
    }
}
```

### Route Definition
```php
// Dalam routes/web.php
Route::middleware(['auth', 'role1access'])->group(function () {
    Route::get('/monitoring-produksi/produksi-pegawai', [ProduksiPegawaiController::class, 'index'])
        ->name('monitoringProduksi.produksiPegawai');
});
```

### API Integration
```javascript
// Frontend melakukan API calls untuk data dinamis
const fetchActiveTeams = async () => {
    const response = await axios.get(`/api/active-teams?date=${form.date}`)
    activeTeams.value = response.data
}

// TableVerifikasiPegawai component juga fetch data
const fetchData = async () => {
    const [teamResponse, produksiResponse] = await Promise.all([
        axios.get(`/api/team-name/${props.team}`),
        axios.get(`/api/pendapatan-harian?date=${dateFilter.value}&team=${props.team}`)
    ]);
}
```

## Performance Considerations

### Database Optimization
- Index pada kolom yang sering diquery:
  - `workstation.workstation` (untuk sorting)
  - `generated_labels.workstation` (untuk filtering tim aktif)
  - `generated_labels.start` (untuk filtering tanggal)
  - `generated_labels.np_users` (untuk filtering non-mesin)

### Memory Management
- Efficient data loading dengan selective fields
- Pagination jika data workstation sangat besar

### Query Optimization
- Eager loading relationships jika diperlukan
- Caching data workstation yang jarang berubah

## Security Considerations

### Access Control
- Middleware `auth` untuk memastikan user terautentikasi
- Middleware `role1access` untuk kontrol akses berdasarkan role
- CSRF protection untuk semua form submissions

### Data Integrity
- Validasi data workstation sebelum ditampilkan
- Sanitization output data untuk mencegah XSS

## Integration Points

### With Services
- **TeamActivityService**: Digunakan oleh `PendapatanHarianController` untuk API `/api/active-teams`, bukan langsung oleh controller ini

### With Frontend
- **Vue.js 3**: Component-based UI dengan Composition API
- **Inertia.js**: SSR framework untuk seamless SPA experience

### With Database
- **Eloquent ORM**: Melalui Workstations model untuk data access

## API Endpoints

### Web Routes
```php
// Main controller route
Route::get('/monitoring-produksi/produksi-pegawai', [ProduksiPegawaiController::class, 'index'])
    ->name('monitoringProduksi.produksiPegawai');
```

### API Routes (digunakan oleh frontend)
```php
// API routes yang dipanggil oleh Vue component
Route::get('/api/active-teams', [PendapatanHarianController::class, 'getActiveTeams']);
Route::get('/api/team-name/{id}', [Workstations::class, 'getTeamName']);
Route::get('/api/pendapatan-harian', [PendapatanHarianController::class, 'gradeHarian']);
```

## Architecture Improvements

### Service Layer Pattern
- Menggunakan TeamActivityService untuk separation of concerns
- Business logic terpisah dari controller logic
- Memudahkan unit testing dan maintenance

### Dependency Injection
- Constructor injection untuk TeamActivityService
- Method injection untuk Workstations model
- Type hinting untuk better IDE support dan type safety

## Notes

- Controller ini menggunakan pattern yang bersih dengan minimal business logic
- Controller hanya bertugas untuk initial page load, data dinamis di-handle oleh API endpoints lain
- Frontend melakukan multiple API calls untuk data real-time (active teams, team names, production data)
- Menggunakan Inertia.js untuk seamless integration antara Laravel dan Vue.js
- Frontend component terletak di `resources/js/Pages/MonitoringProduksi/ProduksiPegawai.vue`
- Component `TableVerifikasiPegawai` di-render dinamis berdasarkan tim yang aktif
- Sistem menggunakan date filtering untuk menampilkan data produksi per tanggal
- TeamActivityService digunakan oleh `PendapatanHarianController` untuk menentukan tim aktif
- Data aktivitas tim diambil dari `generated_labels` table berdasarkan tanggal dan workstation
- Controller ini adalah bagian dari modul monitoring produksi yang lebih besar
