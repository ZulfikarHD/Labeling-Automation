# CetakLabelController Documentation

## Overview

Controller untuk menangani pencetakan label order besar dalam sistem produksi. Controller ini mengelola seluruh proses dari pemilihan PO hingga pencetakan label untuk setiap rim dan potongan.

## Purpose

Mengelola proses pencetakan label untuk order besar dengan fitur:
- Generate label untuk setiap rim dan potongan
- Edit dan update label yang sudah ada
- Hapus label yang tidak diperlukan
- Update status progress PO secara otomatis
- Menangani rim inschiet khusus

## Main Flow

1. **User memilih PO dan workstation**
   - User mengakses halaman cetak label
   - System menampilkan data PO dan workstation yang tersedia

2. **System generate label untuk setiap rim dan potongan**
   - System mengecek rim yang tersedia
   - Generate label berdasarkan prioritas (inschiet → kiri → kanan)

3. **User dapat mencetak, mengedit dan menghapus label**
   - User dapat memproses label yang sudah di-generate
   - System menyimpan data petugas dan waktu mulai

4. **System update status progress PO**
   - Otomatis update status ketika semua label selesai
   - Tracking progress untuk monitoring

## Dependencies

- **PrintLabelService**: Service untuk generate dan manage label
- **ProductionOrderService**: Service untuk manage PO dan status
- **UpdateStatusProgress**: Trait untuk update status PO

## Constants

| Constant | Value | Description |
|----------|-------|-------------|
| `INSCHIET_RIM` | 999 | Rim khusus untuk inschiet |
| `POTONGAN_KIRI` | 'Kiri' | Identifier potongan kiri |
| `POTONGAN_KANAN` | 'Kanan' | Identifier potongan kanan |
| `ERROR_PROCESS` | 'Terjadi kesalahan saat memproses label' | Error message untuk proses |
| `ERROR_UPDATE` | 'Terjadi kesalahan saat memperbarui label' | Error message untuk update |
| `ERROR_DELETE` | 'Terjadi kesalahan saat menghapus label' | Error message untuk delete |
| `SUCCESS_DELETE` | 'Label berhasil dihapus' | Success message untuk delete |

## Public Methods

### `index(string $team, string $id)`

**Purpose**: Menampilkan halaman cetak label

**Flow**:
1. Fetch product berdasarkan ID
2. Fetch data rim dan potongan yang tersedia
3. Render view dengan data yang diperlukan

**Parameters**:
- `$team`: ID workstation
- `$id`: ID product

**Returns**: `\Inertia\Response`

**Data yang dikirim ke view**:
- `product`: Data product
- `listTeam`: Daftar workstation
- `crntTeam`: Workstation saat ini
- `noRim`: Nomor rim berikutnya
- `potongan`: Jenis potongan
- `date`: Tanggal saat ini

### `store(Request $request)`

**Purpose**: Menyimpan label baru

**Flow**:
1. Start database transaction
2. Selesaikan sesi user sebelumnya
3. Create label baru melalui PrintLabelService
4. Update status PO jika semua label selesai
5. Commit transaction

**Parameters**:
- `$request->po`: Nomor PO
- `$request->no_rim`: Nomor rim
- `$request->lbr_ptg`: Lebar potongan
- `$request->periksa1`: NIP petugas
- `$request->team`: ID workstation

**Returns**: `JsonResponse|RedirectResponse`

**Response Success**:
```json
{
    "status": "success",
    "poStatus": 1|2,
    "data": {...}
}
```

### `edit(Request $request)`

**Purpose**: Mengambil data label untuk diedit

**Parameters**:
- `$request->po`: Nomor PO
- `$request->dataRim`: Jenis potongan

**Returns**: `\Illuminate\Database\Eloquent\Collection`

**Fields yang dikembalikan**:
- `no_rim`: Nomor rim
- `np_users`: NIP user
- `potongan`: Jenis potongan
- `start`: Waktu mulai
- `finish`: Waktu selesai

### `update(Request $request)`

**Purpose**: Update label yang sudah ada

**Flow**:
1. Start database transaction
2. Update data label di GeneratedLabels
3. Update data inschiet jika rim adalah inschiet (999)
4. Commit transaction

**Parameters**:
- `$request->po`: Nomor PO
- `$request->noRim`: Nomor rim
- `$request->dataRim`: Jenis potongan
- `$request->npPetugas`: NIP petugas
- `$request->team`: ID workstation

**Returns**: `JsonResponse|RedirectResponse`

### `delete(string $id)`

**Purpose**: Menghapus label

**Parameters**:
- `$id`: ID label yang akan dihapus

**Returns**: `RedirectResponse`

### `getData(string $team, string $id)`

**Purpose**: Mengambil data untuk API endpoint

**Parameters**:
- `$team`: ID workstation
- `$id`: ID product

**Returns**: `JsonResponse`

**Response**:
```json
{
    "product": {...},
    "noRim": 1,
    "potongan": "Kiri",
    "printData": [...]
}
```

## Private Methods

### `fetchNoRim(string $po): array`

**Purpose**: Mengambil rim berikutnya yang tersedia

**Flow**:
1. Cek rim inschiet terlebih dahulu
2. Jika tidak ada, cek rim reguler (kiri dan kanan)
3. Return rim yang tersedia berikutnya

**Logic Priority**:
1. Inschiet rim (999) - prioritas tertinggi
2. Rim dengan nomor terkecil antara kiri dan kanan
3. Jika semua selesai, return status 'Finished'

### `getBaseQuery(string $po)`

**Purpose**: Membuat base query untuk fetch rim

**Conditions**:
- PO sesuai parameter
- np_users null atau kosong (belum dikerjakan)
- Diurutkan berdasarkan no_rim

### `checkInschietRims($baseQuery): ?array`

**Purpose**: Cek ketersediaan rim inschiet

**Logic**:
- Cek rim 999 untuk potongan kiri dan kanan
- Return data jika ada yang belum dikerjakan (start = null)
- Return null jika tidak ada

### `getNextRim($baseQuery, string $potongan)`

**Purpose**: Mengambil rim berikutnya berdasarkan potongan

### `determineNextRim($nextKiri, $nextKanan): array`

**Purpose**: Menentukan rim berikutnya berdasarkan data kiri dan kanan

**Logic**:
- Jika keduanya kosong: return 'Finished'
- Jika salah satu kosong: return yang tersedia
- Jika keduanya ada: return yang nomor rimnya lebih kecil

### `updateGeneratedLabel(Request $request, string $npPetugas): void`

**Purpose**: Update data label yang di-generate

**Fields yang diupdate**:
- `np_users`: NIP petugas
- `workstation`: ID workstation
- `start`: Waktu mulai (now())

### `updateInschietData(Request $request, string $npPetugas): void`

**Purpose**: Update data inschiet

**Logic**:
- Jika potongan kiri: update field `np_kiri`
- Jika potongan kanan: update field `np_kanan`

## Database Tables

### GeneratedLabels
- `no_po_generated_products`: Nomor PO
- `no_rim`: Nomor rim
- `potongan`: Jenis potongan (Kiri/Kanan)
- `np_users`: NIP user
- `workstation`: ID workstation
- `start`: Waktu mulai
- `finish`: Waktu selesai

### DataInschiet
- `no_po`: Nomor PO
- `np_kiri`: NIP petugas kiri
- `np_kanan`: NIP petugas kanan

### GeneratedProducts
- `id`: ID product
- `no_po`: Nomor PO

### Workstations
- `id`: ID workstation
- `workstation`: Nama workstation

## Error Handling

Controller menggunakan database transaction untuk memastikan data consistency:

```php
try {
    DB::beginTransaction();
    // ... operations
    DB::commit();
} catch (\Exception $e) {
    DB::rollback();
    // ... error handling
}
```

## Usage Examples

### Mengakses halaman cetak label
```
GET /cetak-label/{team}/{id}
```

### Menyimpan label baru
```javascript
// POST request
{
    "po": "PO001",
    "no_rim": 1,
    "lbr_ptg": "A4",
    "periksa1": "EMP001",
    "team": "WS001"
}
```

### Update label
```javascript
// PUT request
{
    "po": "PO001",
    "noRim": 1,
    "dataRim": "Kiri",
    "npPetugas": "EMP001",
    "team": "WS001"
}
```

## Notes

- Rim inschiet (999) memiliki prioritas tertinggi dalam pemrosesan
- System otomatis update status PO ketika semua label selesai
- Semua operasi menggunakan database transaction untuk data integrity
- NIP petugas otomatis dikonversi ke uppercase
