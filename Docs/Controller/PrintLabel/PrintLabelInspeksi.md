# PrintLabelInspeksiController Documentation

## Overview

Controller untuk menangani proses inspeksi dan pencetakan label dalam sistem produksi. Controller ini mengelola workflow inspeksi label mulai dari registrasi PO baru, pemrosesan label secara batch, hingga update status progress dengan comprehensive logging dan error handling.

## Purpose

Mengelola proses inspeksi label produksi dengan fitur:
- Menampilkan interface inspeksi label dengan data workstation
- Mengambil spesifikasi PO untuk validasi dan referensi
- Menghitung sisa label yang belum diproses
- Memproses label secara batch dengan dual inspector system
- Auto-registrasi PO baru jika belum ada label
- Update status progress PO berdasarkan completion
- Comprehensive logging untuk audit trail dan debugging

## Main Flow

1. **User mengakses halaman inspeksi**
   - Load workstation list dan current user workstation
   - Render interface inspeksi dengan Inertia.js

2. **User input nomor PO**
   - Fetch spesifikasi PO untuk validasi
   - Cek ketersediaan label existing

3. **System memproses label**
   - Auto-register PO jika belum ada label
   - Finish previous user session
   - Process labels secara batch dengan dual inspector
   - Update progress status berdasarkan remaining labels

4. **System memberikan feedback**
   - Return processing results dengan statistics
   - Log semua aktivitas untuk audit trail

## Dependencies

- **ProductionOrderService**: Untuk registrasi PO baru
- **PrintLabelService**: Untuk populate dan manage label
- **Inertia.js**: Untuk rendering SPA interface
- **Laravel Logging**: Untuk comprehensive audit trail

## Public Methods

### `index()`

**Purpose**: Menampilkan halaman inspeksi label

**Flow**:
1. Ambil daftar workstation yang tersedia
2. Get current user workstation ID
3. Render view dengan data yang diperlukan

**Returns**: `\Inertia\Response`

**Data yang dikirim ke view**:
- `listTeam`: Array daftar workstation
- `currentTeam`: ID workstation user saat ini

**Example Response Data**:
```php
[
    'listTeam' => [
        ['id' => 1, 'workstation' => 'WS001'],
        ['id' => 2, 'workstation' => 'WS002']
    ],
    'currentTeam' => 1
]
```

### `fetchDataSpec(Int $no_po)`

**Purpose**: Mengambil data spesifikasi PO untuk validasi dan referensi

**Parameters**:
- `$no_po`: Nomor PO yang dicari

**Returns**: `JsonResponse`

**Response Success**:
```json
{
    "data": {
        "no_po": 12345,
        "specification_details": "...",
        "other_fields": "..."
    }
}
```

**Response Error (404)**:
```json
{
    "success": false,
    "message": "Spesifikasi PO tidak ditemukan"
}
```

**Response Error (500)**:
```json
{
    "success": false,
    "message": "Terjadi kesalahan saat mengambil data spesifikasi"
}
```

**Error Handling**:
- Comprehensive logging dengan error details dan trace
- Proper HTTP status codes
- User-friendly error messages

### `calcRemainingLabel(Int $no_po)`

**Purpose**: Menghitung jumlah label yang belum diproses untuk PO tertentu

**Parameters**:
- `$no_po`: Nomor PO

**Returns**: `int` - Jumlah label yang belum diproses

**Logic**: Count labels dengan `np_users = null`

**Usage**:
```php
$remaining = $controller->calcRemainingLabel(12345);
echo "Sisa label: " . $remaining;
```

### `store(Request $request)`

**Purpose**: Memproses label secara batch dengan dual inspector system

**Flow**:
1. Start database transaction
2. Validate request data
3. Check existing labels, auto-register PO if needed
4. Finish previous user session
5. Process labels in batch with error handling
6. Update progress status
7. Commit transaction dan return results

**Request Validation**:
```php
[
    'no_po' => 'required|exists:specifications,no_po',
    'team' => 'required|exists:workstations,id',
    'jumlah_label' => 'required|integer|min:1',
    'np1' => 'required|string|max:4',  // Inspector 1 NIP
    'np2' => 'required|string|max:4',  // Inspector 2 NIP
]
```

**Processing Logic**:
1. **Auto PO Registration**: Jika tidak ada label existing, register PO baru dan populate labels
2. **Session Management**: Finish previous session untuk inspector 1
3. **Batch Processing**: Process labels satu per satu dengan error handling per label
4. **Progress Update**: Update status berdasarkan remaining labels (1=In Progress, 2=Completed)

**Response Success**:
```json
{
    "success": true,
    "message": "Label berhasil diproses",
    "data": {
        "processed_labels": 5,
        "failed_labels": 0,
        "remaining_labels": 10,
        "status": "in_progress"
    }
}
```

**Response Validation Error (422)**:
```json
{
    "success": false,
    "message": "Data yang dikirim tidak valid",
    "errors": {
        "no_po": ["The no po field is required."]
    }
}
```

**Response System Error (500)**:
```json
{
    "success": false,
    "message": "Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.",
    "error_code": "SYSTEM_ERROR"
}
```

## Private Methods

### `updateProgress(Int $no_po, Int $status)`

**Purpose**: Update status progress PO

**Parameters**:
- `$no_po`: Nomor PO
- `$status`: Status baru (1=In Progress, 2=Completed)

**Logic**: Update status di tabel `generated_products`

## Database Tables Interaction

### specifications
**Purpose**: Validasi dan referensi PO
**Fields**:
- `no_po`: Nomor PO (Primary Key)
- Other specification fields

**Operations**:
- `where()->first()`: Get specification data

### workstations
**Purpose**: Manajemen workstation dan team
**Fields**:
- `id`: ID workstation
- `workstation`: Nama workstation

**Operations**:
- `listWorkstation()`: Get available workstations
- Validation untuk team assignment

### generated_labels
**Purpose**: Core label processing
**Fields**:
- `no_po_generated_products`: Nomor PO
- `no_rim`: Nomor rim
- `potongan`: Jenis potongan
- `np_users`: NIP inspector 1
- `np_user_2`: NIP inspector 2
- `workstation`: ID workstation
- `start`: Timestamp mulai
- `finish`: Timestamp selesai

**Operations**:
- `where()->count()`: Count existing/remaining labels
- `where()->orderBy()->first()`: Get next available label
- `update()`: Process label dengan inspector data

### generated_products
**Purpose**: PO status management
**Fields**:
- `no_po`: Nomor PO
- `status`: Status PO (0=Initial, 1=In Progress, 2=Completed)

**Operations**:
- `where()->update()`: Update progress status

## Business Rules

### Label Processing Rules
1. **Dual Inspector System**: Setiap label memerlukan 2 inspector (np1 dan np2)
2. **Sequential Processing**: Labels diproses berdasarkan `no_rim` ascending
3. **Session Management**: Previous session harus di-finish sebelum memulai yang baru
4. **Auto Registration**: PO otomatis di-register jika belum ada labels

### Status Management Rules
1. **Status 0**: Initial (PO baru terdaftar)
2. **Status 1**: In Progress (ada label yang sudah/sedang diproses)
3. **Status 2**: Completed (semua label sudah diproses)

### Error Handling Rules
1. **Individual Label Errors**: Tidak menghentikan batch processing
2. **Transaction Safety**: Rollback jika terjadi critical error
3. **Comprehensive Logging**: Semua aktivitas dan error di-log

## Logging Strategy

### Info Level Logging
- PO creation dan label population
- Label processing start/completion
- Processing statistics

### Debug Level Logging
- Individual label processing success
- Detailed processing information

### Warning Level Logging
- Validation failures
- No available labels found
- Business logic warnings

### Error Level Logging
- Individual label processing failures
- Critical system errors
- Exception details dengan trace

**Log Structure Example**:
```php
Log::info('Starting label processing', [
    'no_po' => 12345,
    'requested_labels' => 5,
    'inspector_1' => 'EMP1',
    'inspector_2' => 'EMP2'
]);
```

## Error Handling

### Exception Types
1. **ValidationException**: Invalid request data
2. **ModelNotFoundException**: PO/Specification not found
3. **DatabaseException**: Database operation failures
4. **SystemException**: General system errors

### Error Recovery
- **Individual Label Failures**: Continue processing remaining labels
- **Critical Failures**: Rollback transaction dan return error
- **Partial Success**: Return statistics dengan failed/success counts

### Transaction Management
```php
DB::beginTransaction();
try {
    // ... processing logic
    DB::commit();
} catch (\Exception $e) {
    DB::rollback();
    // ... error handling
}
```

## Usage Examples

### Basic Label Processing
```javascript
// Frontend request
const response = await fetch('/api/print-label-inspeksi', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify({
        no_po: 12345,
        team: 1,
        jumlah_label: 5,
        np1: 'EMP1',
        np2: 'EMP2'
    })
});

const result = await response.json();
console.log('Processed:', result.data.processed_labels);
console.log('Remaining:', result.data.remaining_labels);
```

### Fetch PO Specification
```javascript
// Get PO specification
const specResponse = await fetch(`/api/fetch-data-spec/${poNumber}`);
const specData = await specResponse.json();

if (specData.success !== false) {
    console.log('PO Spec:', specData.data);
} else {
    console.error('Error:', specData.message);
}
```

### Check Remaining Labels
```php
// Backend usage
$controller = new PrintLabelInspeksiController($poService, $labelService);
$remaining = $controller->calcRemainingLabel(12345);

if ($remaining > 0) {
    echo "Masih ada {$remaining} label yang perlu diproses";
} else {
    echo "Semua label sudah selesai diproses";
}
```

## Performance Considerations

### Database Optimization
- Index pada kolom yang sering diquery:
  - `generated_labels.no_po_generated_products`
  - `generated_labels.np_users`
  - `generated_labels.no_rim`
  - `specifications.no_po`

### Memory Management
- Process labels satu per satu untuk menghindari memory issues
- Use database transactions dengan scope minimal
- Clear variables setelah batch processing

### Logging Optimization
- Use appropriate log levels untuk menghindari log spam
- Include context data untuk debugging
- Avoid logging sensitive information

## Security Considerations

### Input Validation
- Strict validation untuk semua input parameters
- Exists validation untuk foreign key references
- Length limits untuk NIP fields

### Access Control
- User workstation validation
- Inspector NIP format validation
- PO existence validation

### Data Integrity
- Database transactions untuk atomicity
- Proper error handling dan rollback
- Audit trail melalui comprehensive logging

## Integration Points

### With Services
- **ProductionOrderService**: Auto PO registration
- **PrintLabelService**: Label population dan session management

### With Frontend
- **Inertia.js**: SPA interface rendering
- **AJAX API**: Real-time data fetching dan processing

### With Database
- **Multiple Tables**: Coordinated updates across tables
- **Transaction Management**: Ensure data consistency

## Notes

- Controller menggunakan dual inspector system untuk quality assurance
- Auto-registration PO memudahkan workflow tanpa manual setup
- Comprehensive logging memungkinkan audit trail dan debugging yang efektif
- Error handling yang robust memastikan partial success dapat di-handle dengan baik
- Transaction management memastikan data consistency
- Performance optimization melalui sequential processing dan proper indexing
