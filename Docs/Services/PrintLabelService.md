# PrintLabelService Documentation

## Overview

Service class untuk menangani operasi pencetakan label dan manajemen data produksi dalam sistem manufacturing. Service ini bertanggung jawab untuk generate, manage, dan update label produksi dengan optimasi performa dan keamanan data.

## Purpose

Mengelola seluruh aspek pencetakan label produksi dengan fitur:
- Membuat dan mengelola label produksi (create & manage production labels)
- Menghitung dan memproses data inschiet (calculate & process inschiet data)
- Mengelola status pemeriksaan label (manage label inspection status)
- Mengoptimalkan performa database dengan batch processing
- Menjaga konsistensi data dengan database transactions

## Main Flow

1. **Menerima data PO dari controller**
   - Validasi dan format data input
   - Extract informasi penting (lembar, pemeriksa, team)

2. **Menghitung jumlah rim berdasarkan lembar**
   - Hitung rim berdasarkan SHEETS_PER_RIM (1000)
   - Tentukan apakah perlu generate label

3. **Generate label untuk setiap rim dan potongan**
   - Buat label untuk potongan Kiri dan Kanan
   - Batch processing untuk optimasi performa

4. **Menangani data inschiet jika ada sisa lembar**
   - Hitung sisa lembar (modulo operation)
   - Buat label khusus untuk inschiet (rim 999)

5. **Menyimpan semua data ke database dalam satu transaksi**
   - Gunakan database transaction untuk data integrity
   - Rollback jika terjadi error

## Security Features

- **Database Transactions**: Menjaga konsistensi data
- **Input Validation**: Validasi sebelum penyimpanan
- **Name Sanitization**: Format standar untuk nama pemeriksa (uppercase)
- **Error Handling**: Proper exception handling dengan rollback

## Performance Optimization

- **Batch Processing**: Insert/update data secara batch
- **Query Minimization**: Pengumpulan data sebelum database operation
- **Upsert Operations**: Efficient insert or update operations
- **Index Optimization**: Query pada kolom yang ter-index

## Constants

| Constant | Value | Description |
|----------|-------|-------------|
| `POTONGAN_TYPES` | `['Kiri', 'Kanan']` | Tipe potongan label yang tersedia |
| `INSCHIET_RIM_NUMBER` | `999` | Nomor rim khusus untuk inschiet |
| `SHEETS_PER_RIM` | `1000` | Jumlah lembar standar per rim |

## Public Methods

### `populateLabelForRegisteredPo(array $dataPo): void`

**Purpose**: Method utama untuk mengisi dan membuat label PO yang terdaftar

**Flow**:
1. Hitung jumlah rim dari total lembar
2. Generate label jika memenuhi syarat (> 1 rim)
3. Proses data inschiet jika ada sisa lembar

**Parameters**:
- `$dataPo`: Array berisi data PO dengan struktur:
  - `po`: (int) nomor PO
  - `jml_lembar`: (int) jumlah lembar total
  - `periksa1`: (string|null) NIP pemeriksa pertama
  - `periksa2`: (string|null) NIP pemeriksa kedua
  - `team`: (int) ID tim produksi
  - `start_rim`: (int|optional) rim mulai
  - `end_rim`: (int|optional) rim akhir

**Example**:
```php
$dataPo = [
    'po' => 12345,
    'jml_lembar' => 2500,
    'periksa1' => 'emp001',
    'periksa2' => 'emp002',
    'team' => 1
];
$service->populateLabelForRegisteredPo($dataPo);
```

### `createLabel(int $noPo, int $rimNumber, string $potongan, ?string $periksa1, ?string $periksa2, int $team): array`

**Purpose**: Membuat atau memperbarui label tunggal

**Flow**:
1. Handle khusus untuk rim inschiet (999)
2. Cari label berikutnya yang tersedia
3. Update/create label dengan data baru
4. Update assigned team di generated_products

**Parameters**:
- `$noPo`: Nomor PO
- `$rimNumber`: Nomor rim
- `$potongan`: Tipe potongan ('Kiri'/'Kanan')
- `$periksa1`: NIP pemeriksa 1 (nullable)
- `$periksa2`: NIP pemeriksa 2 (nullable)
- `$team`: ID tim

**Returns**: Array dengan struktur:
```php
// Success
[
    'status' => 'success',
    'message' => 'Label berhasil dibuat',
    'data' => [
        'no_rim' => 1,
        'potongan' => 'Kiri'
    ]
]

// Error
[
    'status' => 'error',
    'message' => 'Order sudah selesai'
]
```

### `insertInschiet(int $noPo, int $lembar, ?string $periksa1 = null, ?string $periksa2 = null, ?int $workstation = null): void`

**Purpose**: Handle pembuatan dan update data inschiet

**Flow**:
1. Hitung sisa lembar untuk inschiet (lembar % SHEETS_PER_RIM)
2. Jika ada sisa, buat record dalam transaksi
3. Update data inschiet dan label terkait

**Parameters**:
- `$noPo`: Nomor PO
- `$lembar`: Total lembar
- `$periksa1`: NIP pemeriksa 1 (optional)
- `$periksa2`: NIP pemeriksa 2 (optional)
- `$workstation`: ID workstation (optional)

**Logic**:
- Hanya proses jika `$calcInschiet > 0`
- Menggunakan database transaction
- Throw exception jika terjadi error

### `finishPreviousUserSession(string $npPegawai): void`

**Purpose**: Selesaikan sesi user sebelumnya

**Logic**:
- Update timestamp `finish` untuk semua label yang belum selesai (`finish = null`)
- Filter berdasarkan `np_users` = `$npPegawai`
- Set `finish` = `now()`

**Use Case**: Dipanggil sebelum user memulai sesi baru untuk memastikan sesi sebelumnya ter-close dengan benar.

## Private Methods

### `shouldGenerateLabels(int $totalSheets): bool`

**Purpose**: Cek apakah perlu generate label berdasarkan total lembar

**Rules**:
- Generate jika hasil bagi lembar dengan SHEETS_PER_RIM > 1
- Handle pembagian dengan 0 (safety check)

**Logic**:
```php
return self::SHEETS_PER_RIM > 0 ? ($totalSheets / self::SHEETS_PER_RIM) > 1 : false;
```

### `generateLabels(array $dataPo, int $sumRim): void`

**Purpose**: Generate label untuk PO dengan batch processing

**Flow**:
1. Buka transaksi database
2. Format nama pemeriksa (uppercase)
3. Kumpulkan semua data label dalam array
4. Insert/update secara batch menggunakan upsert
5. Commit atau rollback transaksi

**Batch Processing Logic**:
- Loop dari `start_rim` sampai `end_rim`
- Untuk setiap rim, buat 2 label (Kiri & Kanan)
- Kumpulkan dalam array `$labels`
- Single upsert operation untuk semua data

### `findNextAvailableLabel(int $noPo, int $rimNumber, string $potongan): ?array`

**Purpose**: Mencari label berikutnya yang tersedia untuk diproses

**Search Algorithm**:
1. Cek potongan saat ini
2. Jika potongan Kiri terisi, cek Kanan
3. Jika keduanya terisi, pindah ke rim berikutnya
4. Reset potongan ke 'Kiri' untuk rim baru
5. Ulangi sampai menemukan slot kosong atau tidak ada rim

**Return Values**:
- `['rim' => int, 'potongan' => string]`: Jika ditemukan slot kosong
- `null`: Jika semua label sudah terisi

### `updateInschietData(int $noPo, ?int $calcInschiet, ?string $periksa1, ?string $potongan): void`

**Purpose**: Update data inschiet untuk PO tertentu

**Logic Branching**:
- Jika `$potongan === "Kiri"`: Update `np_kiri`
- Jika `$potongan === "Kanan"`: Update `np_kanan`
- Jika `$potongan === null`: UpdateOrCreate dengan data lengkap

**Database Operation**:
- Menggunakan `updateOrCreate` untuk upsert operation
- Format nama pemeriksa sebelum save

### `createInschietLabels(int $noPo, ?string $periksa1, ?string $periksa2, ?int $workstation): void`

**Purpose**: Buat label inschiet untuk kedua potongan

**Label Structure**:
- Nomor rim khusus (999)
- Potongan Kiri dan Kanan
- Data pemeriksa dan workstation
- Timestamp start jika ada periksa1

**Batch Creation**:
- Kumpulkan data untuk kedua potongan
- Single upsert operation

### `formatPeriksaName(?string $name): ?string`

**Purpose**: Format nama pemeriksa menjadi uppercase

**Logic**:
- Return `strtoupper($name)` jika name tidak null
- Return `null` jika name null
- Digunakan untuk standarisasi format nama di database

## Database Tables Interaction

### GeneratedLabels
**Fields**:
- `no_po_generated_products`: Nomor PO
- `no_rim`: Nomor rim
- `potongan`: Jenis potongan (Kiri/Kanan)
- `np_users`: NIP pemeriksa 1
- `np_user_p2`: NIP pemeriksa 2
- `start`: Timestamp mulai
- `finish`: Timestamp selesai
- `workstation`: ID workstation

**Operations**:
- `upsert()`: Batch insert/update
- `updateOrCreate()`: Single upsert
- `where()->update()`: Bulk update

### DataInschiet
**Fields**:
- `no_po`: Nomor PO
- `inschiet`: Jumlah lembar inschiet
- `np_kiri`: NIP pemeriksa kiri
- `np_kanan`: NIP pemeriksa kanan

**Operations**:
- `updateOrCreate()`: Upsert operation
- `where()->update()`: Conditional update

### generated_products
**Fields**:
- `no_po`: Nomor PO
- `assigned_team`: ID tim yang ditugaskan

**Operations**:
- `where()->update()`: Update assigned team

## Error Handling

### Transaction Pattern
```php
try {
    DB::transaction(function () use ($data) {
        // ... database operations
    });
} catch (\Exception $e) {
    DB::rollback();
    throw $e;
}
```

### Exception Types
- **Database Exceptions**: Connection, constraint violations
- **Logic Exceptions**: Invalid data, business rule violations
- **System Exceptions**: Memory, timeout issues

## Usage Examples

### Basic Label Population
```php
$printLabelService = new PrintLabelService();

$dataPo = [
    'po' => 12345,
    'jml_lembar' => 3500,
    'periksa1' => 'emp001',
    'periksa2' => null,
    'team' => 1
];

$printLabelService->populateLabelForRegisteredPo($dataPo);
```

### Single Label Creation
```php
$result = $printLabelService->createLabel(
    noPo: 12345,
    rimNumber: 1,
    potongan: 'Kiri',
    periksa1: 'emp001',
    periksa2: null,
    team: 1
);

if ($result['status'] === 'success') {
    echo "Label created: Rim {$result['data']['no_rim']}, {$result['data']['potongan']}";
}
```

### Inschiet Processing
```php
$printLabelService->insertInschiet(
    noPo: 12345,
    lembar: 2350, // Will create inschiet for 350 sheets
    periksa1: 'emp001',
    periksa2: 'emp002',
    workstation: 1
);
```

### Finish User Session
```php
$printLabelService->finishPreviousUserSession('emp001');
```

## Performance Considerations

### Batch Operations
- Use `upsert()` instead of multiple `insert()`/`update()`
- Collect data before database operations
- Minimize transaction scope

### Query Optimization
- Index on frequently queried columns:
  - `no_po_generated_products`
  - `no_rim`
  - `potongan`
  - `np_users`

### Memory Management
- Process large datasets in chunks
- Clear variables after batch operations
- Use generators for large loops

## Business Rules

### Rim Calculation
- Standard: 1000 lembar per rim
- Minimum: 1 rim (even if < 1000 lembar)
- Inschiet: Sisa lembar setelah pembagian rim

### Label Priority
1. Regular rims (1, 2, 3, ...)
2. Inschiet rim (999)

### Potongan Processing
1. Kiri first, then Kanan
2. Both potongan per rim before next rim
3. Reset to Kiri for new rim

## Notes

- Rim inschiet (999) memiliki handling khusus
- Semua nama pemeriksa otomatis dikonversi ke uppercase
- Database transaction digunakan untuk menjaga data integrity
- Batch processing digunakan untuk optimasi performa
- Service ini thread-safe dengan proper transaction handling
