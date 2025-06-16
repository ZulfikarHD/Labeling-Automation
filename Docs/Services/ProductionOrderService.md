# ProductionOrderService Documentation

## Overview

Service untuk mengelola Production Order (PO) dalam sistem produksi PCHT (Pita Cukai Hasil Tembakau). Service ini bertanggung jawab untuk registrasi, validasi, dan monitoring status Production Order dengan fokus pada manajemen rim dan label.

## Purpose

Mengelola seluruh aspek Production Order dengan fitur:
- Registrasi PO baru dengan validasi duplikasi
- Pengecekan status PO dan progress completion
- Manajemen label PCHT (Pita Cukai Hasil Tembakau)
- Perhitungan rim berdasarkan jumlah lembar
- Query builder untuk berbagai kebutuhan data PO

## Main Responsibilities

1. **PO Registration**: Mendaftarkan PO baru dengan validasi
2. **Status Checking**: Monitor status penyelesaian PO
3. **Label Management**: Mengelola label PCHT terkait PO
4. **Rim Calculation**: Hitung total rim berdasarkan jumlah lembar
5. **Data Querying**: Menyediakan query builder untuk akses data

## Constants

| Constant | Value | Description |
|----------|-------|-------------|
| `SHEETS_PER_RIM` | `500` | Jumlah lembar kertas per rim |
| `MIN_RIM` | `1` | Jumlah minimum rim yang diperbolehkan |
| `PRODUCT_TYPE` | `'PCHT'` | Tipe produk (Pita Cukai Hasil Tembakau) |
| `INITIAL_STATUS` | `0` | Status awal untuk PO baru |

## Public Methods

### `registerProductionOrder(array $productionOrder): void`

**Purpose**: Mendaftarkan Production Order baru ke dalam sistem

**Flow**:
1. Validasi duplikasi nomor PO
2. Perhitungan total rim berdasarkan jumlah lembar
3. Penyimpanan data PO dalam transaksi database

**Parameters**:
- `$productionOrder`: Array berisi data PO dengan struktur:
  - `po`: (int) Nomor PO
  - `obc`: (string) Nomor OBC
  - `jml_lembar`: (int) Total lembar
  - `start_rim`: (int) Nomor rim awal
  - `end_rim`: (int) Nomor rim akhir
  - `team`: (string) ID tim yang ditugaskan

**Throws**: `\Exception` ketika nomor PO sudah terdaftar dalam sistem

**Database Transaction**: Method ini menggunakan database transaction untuk menjamin atomicity

**Example**:
```php
$productionOrder = [
    'po' => 12345,
    'obc' => 'OBC001',
    'jml_lembar' => 2500,
    'start_rim' => 1,
    'end_rim' => 5,
    'team' => 'TEAM001'
];

try {
    $service->registerProductionOrder($productionOrder);
    echo "PO berhasil didaftarkan";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

### `registeredProductionOrder(int $no_po): Builder`

**Purpose**: Mengambil query builder untuk mencari PO berdasarkan nomor

**Parameters**:
- `$no_po`: Nomor PO yang dicari

**Returns**: `Builder` - Query builder untuk tabel `generated_products`

**Usage**:
```php
// Check if PO exists
$exists = $service->registeredProductionOrder(12345)->exists();

// Get PO data
$po = $service->registeredProductionOrder(12345)->first();

// Get specific fields
$poData = $service->registeredProductionOrder(12345)
    ->select(['no_po', 'status', 'sum_rim'])
    ->first();
```

### `cekLabelPchtTerdaftar(int $no_po): Builder`

**Purpose**: Mengambil query builder untuk label PCHT berdasarkan nomor PO

**Parameters**:
- `$no_po`: Nomor PO yang dicari

**Returns**: `Builder` - Query builder untuk tabel `generated_labels`

**Usage**:
```php
// Check if labels exist for PO
$hasLabels = $service->cekLabelPchtTerdaftar(12345)->exists();

// Count total labels
$totalLabels = $service->cekLabelPchtTerdaftar(12345)->count();

// Get all labels for PO
$labels = $service->cekLabelPchtTerdaftar(12345)->get();
```

### `getListNomorRimPcht(int $no_po): Builder`

**Purpose**: Mengambil daftar nomor rim dan potongan untuk PCHT

**Parameters**:
- `$no_po`: Nomor PO yang dicari

**Returns**: `Builder` - Query builder dengan select `no_rim` dan `potongan`, diurutkan berdasarkan nomor rim secara ascending

**Usage**:
```php
// Get rim list
$rimList = $service->getListNomorRimPcht(12345)->get();

// Get specific rim range
$rimRange = $service->getListNomorRimPcht(12345)
    ->whereBetween('no_rim', [1, 5])
    ->get();

// Get distinct rim numbers
$rimNumbers = $service->getListNomorRimPcht(12345)
    ->distinct()
    ->pluck('no_rim');
```

### `isPoFinished(int $no_po): bool`

**Purpose**: Memeriksa status penyelesaian PO

**Logic**: PO dianggap selesai jika semua label telah diproses (ditandai dengan terisinya kolom `np_users`)

**Parameters**:
- `$no_po`: Nomor PO yang diperiksa

**Returns**: `bool` - `true` jika semua label telah diproses, `false` jika belum

**Algorithm**:
1. Query semua label untuk PO
2. Filter label yang `np_users` null atau kosong
3. Hitung jumlah label yang belum diproses
4. Return `true` jika count = 0 (semua selesai)

**Usage**:
```php
if ($service->isPoFinished(12345)) {
    echo "PO sudah selesai";
    // Update status PO atau trigger next process
} else {
    echo "PO masih dalam proses";
    // Show progress or remaining tasks
}
```

## Private Methods

### `calculateTotalRims(int $totalSheets): int`

**Purpose**: Menghitung total rim berdasarkan jumlah lembar kertas

**Formula**: `total_rim = max(floor(total_lembar / lembar_per_rim), minimum_rim)`

**Parameters**:
- `$totalSheets`: Total lembar kertas

**Returns**: `int` - Jumlah rim yang dihitung

**Logic**:
- Bagi total lembar dengan `SHEETS_PER_RIM` (500)
- Gunakan `floor()` untuk pembulatan ke bawah
- Gunakan `max()` dengan `MIN_RIM` (1) untuk memastikan minimal 1 rim

**Examples**:
```php
// 2500 lembar = floor(2500/500) = 5 rim
// 1200 lembar = floor(1200/500) = 2 rim  
// 300 lembar = max(floor(300/500), 1) = max(0, 1) = 1 rim
```

## Database Tables Interaction

### generated_products
**Fields**:
- `no_po`: Nomor PO (Primary Key)
- `no_obc`: Nomor OBC
- `type`: Tipe produk (PCHT)
- `status`: Status PO (0 = initial)
- `sum_rim`: Total rim yang dihitung
- `start_rim`: Nomor rim awal
- `end_rim`: Nomor rim akhir
- `assigned_team`: Tim yang ditugaskan

**Operations**:
- `create()`: Insert PO baru
- `where()->exists()`: Check keberadaan PO
- `where()->first()`: Get data PO

### generated_labels
**Fields**:
- `no_po_generated_products`: Nomor PO (Foreign Key)
- `no_rim`: Nomor rim
- `potongan`: Jenis potongan (Kiri/Kanan)
- `np_users`: NIP user yang memproses
- `start`: Timestamp mulai
- `finish`: Timestamp selesai

**Operations**:
- `where()->exists()`: Check keberadaan label
- `where()->count()`: Hitung jumlah label
- `where()->get()`: Ambil data label
- `select()->orderBy()`: Query dengan sorting

## Business Rules

### PO Registration Rules
1. **Unique PO Number**: Nomor PO harus unik dalam sistem
2. **Minimum Rim**: Setiap PO minimal memiliki 1 rim
3. **Rim Calculation**: Berdasarkan 500 lembar per rim
4. **Initial Status**: PO baru dimulai dengan status 0

### Completion Rules
1. **Label Processing**: PO selesai jika semua label terproses
2. **User Assignment**: Label dianggap terproses jika `np_users` terisi
3. **Progress Tracking**: Status completion berdasarkan label yang belum dikerjakan

### Data Integrity Rules
1. **Transaction Safety**: Registrasi PO menggunakan database transaction
2. **Validation**: Duplikasi PO dicegah dengan validation
3. **Consistency**: Rim calculation konsisten dengan business rule

## Error Handling

### Exception Types
- **Duplicate PO Exception**: Thrown ketika nomor PO sudah ada
- **Database Exception**: Connection atau constraint violations
- **Validation Exception**: Invalid input data

### Error Prevention
```php
// Check before registration
if (!$service->registeredProductionOrder($poNumber)->exists()) {
    $service->registerProductionOrder($data);
} else {
    // Handle duplicate PO
}
```

## Usage Examples

### Complete PO Registration Flow
```php
$productionOrderService = new ProductionOrderService();

$poData = [
    'po' => 12345,
    'obc' => 'OBC001',
    'jml_lembar' => 3000,
    'start_rim' => 1,
    'end_rim' => 6,
    'team' => 'TEAM001'
];

try {
    // Register new PO
    $productionOrderService->registerProductionOrder($poData);
    
    // Verify registration
    $po = $productionOrderService->registeredProductionOrder(12345)->first();
    echo "PO {$po->no_po} registered with {$po->sum_rim} rims";
    
} catch (\Exception $e) {
    echo "Registration failed: " . $e->getMessage();
}
```

### Monitor PO Progress
```php
$poNumber = 12345;

// Check if PO exists
if ($productionOrderService->registeredProductionOrder($poNumber)->exists()) {
    
    // Get rim list
    $rims = $productionOrderService->getListNomorRimPcht($poNumber)->get();
    echo "Total rims: " . $rims->count();
    
    // Check completion status
    if ($productionOrderService->isPoFinished($poNumber)) {
        echo "PO is completed";
    } else {
        // Get pending labels
        $pendingLabels = $productionOrderService->cekLabelPchtTerdaftar($poNumber)
            ->where(function($query) {
                $query->whereNull('np_users')->orWhere('np_users', '');
            })
            ->count();
        echo "Pending labels: " . $pendingLabels;
    }
}
```

### Query Builder Usage
```php
// Complex queries using the builders
$service = new ProductionOrderService();

// Get PO with specific status
$activePOs = $service->registeredProductionOrder($poNumber)
    ->where('status', 1)
    ->get();

// Get labels with processing info
$processedLabels = $service->cekLabelPchtTerdaftar($poNumber)
    ->whereNotNull('np_users')
    ->with(['user', 'workstation'])
    ->get();

// Get rim statistics
$rimStats = $service->getListNomorRimPcht($poNumber)
    ->selectRaw('COUNT(*) as total_labels, no_rim')
    ->groupBy('no_rim')
    ->get();
```

## Performance Considerations

### Query Optimization
- Index pada kolom yang sering diquery:
  - `generated_products.no_po`
  - `generated_labels.no_po_generated_products`
  - `generated_labels.np_users`

### Memory Management
- Gunakan query builder untuk lazy loading
- Avoid loading semua data sekaligus untuk PO besar
- Use pagination untuk list data

### Database Transactions
- Minimal transaction scope untuk registrasi
- Avoid long-running transactions
- Proper rollback handling

## Integration Points

### With PrintLabelService
- PO registration triggers label generation
- Status checking depends on label processing
- Rim calculation affects label creation

### With Controllers
- Provides data for PO management interfaces
- Status checking for progress monitoring
- Query builders for flexible data access

### With Models
- Direct interaction dengan GeneratedProducts
- Query building untuk GeneratedLabels
- Relationship management

## Notes

- Service ini fokus pada PCHT (Pita Cukai Hasil Tembakau) products
- Rim calculation menggunakan 500 lembar per rim (berbeda dengan PrintLabelService yang 1000)
- PO completion status berdasarkan label processing, bukan manual status update
- Query builders memungkinkan flexible data access tanpa multiple methods
- Database transaction digunakan untuk data integrity pada registrasi PO
