# PrintLabelPersonal (CetakLabelController - OrderKecil) Documentation

## Overview

Controller untuk menangani pencetakan label order kecil/personal dalam sistem produksi. Controller ini mengelola workflow pencetakan label individual dengan fokus pada order kecil yang memerlukan proses yang lebih sederhana dan cepat dibandingkan order besar.

## Purpose

Mengelola proses pencetakan label untuk order kecil dengan fitur:
- Menampilkan interface pencetakan label personal
- Mengambil spesifikasi produk berdasarkan nomor PO
- Registrasi PO baru untuk order kecil
- Generate label secara otomatis untuk seluruh PO
- Update status progress PO menjadi completed
- Session management untuk user sebelumnya

## Main Flow

1. **User mengakses halaman cetak label**
   - Load workstation list dan current user workstation
   - Render interface pencetakan dengan Inertia.js

2. **User input nomor PO dan data label**
   - Get spesifikasi produk untuk validasi
   - Input data petugas dan workstation

3. **System generate label untuk setiap rim**
   - Auto-register PO baru jika belum ada
   - Generate semua label untuk PO secara batch
   - Finish previous user session

4. **Update status progress PO**
   - Set status PO menjadi completed (status 2)
   - Return success response

## Dependencies

- **PrintLabelService**: Service untuk generate dan manage label
- **ProductionOrderService**: Service untuk manage PO dan status
- **SpecificationService**: Service untuk get spec produk
- **UpdateStatusProgress**: Trait untuk update status PO
- **StoreGeneratedProductsRequest**: Form request untuk validasi
- **Inertia.js**: Untuk rendering SPA interface

## Public Methods

### `index(Workstations $workstations)`

**Purpose**: Menampilkan halaman utama cetak label personal

**Flow**:
1. Get list workstation yang tersedia
2. Get current user workstation ID
3. Render view dengan data yang diperlukan

**Parameters**:
- `$workstations`: Model workstation untuk dependency injection

**Returns**: `\Inertia\Response`

**Data yang dikirim ke view**:
- `listTeam`: Collection daftar workstation
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

### `show(int $no_po, SpecificationService $specificationService)`

**Purpose**: Get detail spesifikasi produk berdasarkan nomor PO

**Flow**:
1. Get spec dari service berdasar nomor PO
2. Return data spec ke frontend

**Parameters**:
- `$no_po`: Nomor PO yang dicari
- `$specificationService`: Service untuk get spec (dependency injection)

**Returns**: `mixed` - Data spesifikasi produk

**Usage**:
```php
// GET /api/order-kecil/spec/{no_po}
$spec = $controller->show(12345, $specificationService);
```

**Response Example**:
```json
{
    "no_po": 12345,
    "product_name": "Product A",
    "specifications": {
        "size": "A4",
        "color": "Blue",
        "quantity": 1000
    }
}
```

### `cetakLabel(StoreGeneratedProductsRequest $request)`

**Purpose**: Handle request cetak label baru untuk order kecil

**Flow**:
1. Validate request menggunakan form request
2. Start database transaction
3. Register PO baru melalui ProductionOrderService
4. Generate label untuk seluruh PO melalui PrintLabelService
5. Update status user sebelumnya (finish previous session)
6. Update progress PO menjadi completed (status 2)
7. Commit atau rollback transaction

**Parameters**:
- `$request`: StoreGeneratedProductsRequest dengan validasi otomatis

**Request Validation** (dari StoreGeneratedProductsRequest):
```php
[
    'po' => 'required|integer',
    'obc' => 'required|string',
    'jml_lembar' => 'required|integer|min:1',
    'start_rim' => 'required|integer|min:1',
    'end_rim' => 'required|integer|min:1',
    'periksa1' => 'required|string|max:4',
    'periksa2' => 'nullable|string|max:4',
    'team' => 'required|exists:workstations,id'
]
```

**Returns**: `\Illuminate\Http\JsonResponse`

**Response Success (200)**:
```json
{
    "message": "Label berhasil dibuat"
}
```

**Response Error (422)**:
```json
{
    "error": "Terjadi kesalahan saat memproses permintaan. Silakan coba lagi."
}
```

**Transaction Logic**:
1. **Register PO**: Create new PO record
2. **Populate Labels**: Generate all labels for the PO
3. **Finish Session**: Close previous user session
4. **Update Progress**: Set PO status to completed

**Error Handling**:
- Comprehensive logging untuk debugging
- Database transaction rollback pada error
- User-friendly error messages

## Private Methods

### `countNullNp(string $noPo): int`

**Purpose**: Helper untuk hitung label yang belum diproses

**Logic**: Menghitung jumlah label yang:
- Belum memiliki NP user (`np_users = null`)
- Untuk PO tertentu

**Parameters**:
- `$noPo`: Nomor PO yang dihitung

**Returns**: `int` - Jumlah label yang belum diproses

**Usage**:
```php
$remainingLabels = $this->countNullNp('12345');
echo "Sisa label: " . $remainingLabels;
```

**Note**: Method ini tidak digunakan dalam flow utama tetapi tersedia untuk monitoring

## Database Tables Interaction

### workstations
**Purpose**: Manajemen workstation dan team
**Fields**:
- `id`: ID workstation
- `workstation`: Nama workstation

**Operations**:
- `listWorkstation()`: Get available workstations

### specifications
**Purpose**: Spesifikasi produk
**Fields**:
- `no_po`: Nomor PO (Primary Key)
- Product specification fields

**Operations**:
- Service call untuk get specification data

### generated_products
**Purpose**: PO management
**Fields**:
- `no_po`: Nomor PO
- `no_obc`: Nomor OBC
- `type`: Tipe produk
- `status`: Status PO
- `sum_rim`: Total rim
- `start_rim`: Rim awal
- `end_rim`: Rim akhir
- `assigned_team`: Tim yang ditugaskan

**Operations**:
- `create()`: Register new PO
- `update()`: Update progress status

### generated_labels
**Purpose**: Label management
**Fields**:
- `no_po_generated_products`: Nomor PO
- `no_rim`: Nomor rim
- `potongan`: Jenis potongan
- `np_users`: NIP user
- `start`: Timestamp mulai
- `finish`: Timestamp selesai
- `workstation`: ID workstation

**Operations**:
- Batch creation melalui PrintLabelService
- `whereNull('np_users')->count()`: Count unprocessed labels

## Business Rules

### Order Kecil Characteristics
1. **Complete Processing**: Semua label diproses sekaligus dalam satu transaksi
2. **Immediate Completion**: PO langsung set ke status completed (2)
3. **Single Session**: Tidak ada partial processing, semua label selesai dalam satu session
4. **Simplified Workflow**: Lebih sederhana dibanding order besar

### Processing Rules
1. **Auto Registration**: PO otomatis di-register jika belum ada
2. **Batch Generation**: Semua label di-generate sekaligus
3. **Session Management**: Previous session di-finish sebelum memulai
4. **Status Update**: PO langsung completed setelah processing

### Validation Rules
1. **Form Request Validation**: Menggunakan StoreGeneratedProductsRequest
2. **Workstation Validation**: Team harus exist di workstations table
3. **NIP Format**: Maksimal 4 karakter untuk petugas
4. **Rim Range**: start_rim dan end_rim harus valid

## Error Handling

### Exception Types
1. **ValidationException**: Invalid request data (handled by form request)
2. **DatabaseException**: Transaction failures
3. **ServiceException**: Service layer errors
4. **SystemException**: General system errors

### Error Recovery
- **Transaction Rollback**: Automatic rollback pada error
- **Comprehensive Logging**: Error details untuk debugging
- **User-Friendly Messages**: Clear error messages untuk user

### Transaction Management
```php
\DB::transaction(function() use ($validatedRequest) {
    // All operations in single transaction
    $this->productionOrderService->registerProductionOrder($validatedRequest);
    $this->printLabelService->populateLabelForRegisteredPo($validatedRequest);
    $this->printLabelService->finishPreviousUserSession($validatedRequest['periksa1']);
    $this->updateProgress($validatedRequest['po'], 2);
});
```

## Usage Examples

### Basic Label Creation
```javascript
// Frontend request
const response = await fetch('/api/order-kecil/cetak-label', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify({
        po: 12345,
        obc: 'OBC001',
        jml_lembar: 1000,
        start_rim: 1,
        end_rim: 2,
        periksa1: 'EMP1',
        periksa2: 'EMP2',
        team: 1
    })
});

const result = await response.json();
if (response.ok) {
    console.log('Success:', result.message);
} else {
    console.error('Error:', result.error);
}
```

### Get Product Specification
```javascript
// Get product specification
const specResponse = await fetch(`/api/order-kecil/spec/${poNumber}`);
const specData = await specResponse.json();
console.log('Product Spec:', specData);
```

### Backend Usage
```php
// Controller usage
$controller = new CetakLabelController($poService, $labelService);

// Get workstation list
$workstations = new Workstations();
$response = $controller->index($workstations);

// Get specification
$specService = new SpecificationService();
$spec = $controller->show(12345, $specService);

// Process label
$request = new StoreGeneratedProductsRequest($validatedData);
$result = $controller->cetakLabel($request);
```

## Performance Considerations

### Database Optimization
- Index pada kolom yang sering diquery:
  - `generated_labels.no_po_generated_products`
  - `generated_labels.np_users`
  - `workstations.id`
  - `specifications.no_po`

### Memory Management
- Single transaction untuk semua operations
- Batch processing melalui services
- Efficient query patterns

### Transaction Optimization
- Minimal transaction scope
- Fast commit/rollback
- Proper error handling

## Security Considerations

### Input Validation
- Form request validation untuk semua input
- Exists validation untuk foreign keys
- Length limits untuk NIP fields
- Integer validation untuk numeric fields

### Access Control
- User workstation validation
- Team assignment validation
- PO ownership validation

### Data Integrity
- Database transactions untuk atomicity
- Proper error handling dan rollback
- Audit trail melalui logging

## Integration Points

### With Services
- **ProductionOrderService**: PO registration dan management
- **PrintLabelService**: Label generation dan session management
- **SpecificationService**: Product specification retrieval

### With Frontend
- **Inertia.js**: SPA interface rendering
- **Form Requests**: Automatic validation
- **JSON API**: RESTful responses

### With Database
- **Multiple Tables**: Coordinated operations
- **Transaction Management**: Data consistency
- **Model Relationships**: Proper data linking

## Differences from Order Besar

### Processing Approach
- **Order Kecil**: Complete processing dalam satu transaksi
- **Order Besar**: Partial processing dengan manual control

### Status Management
- **Order Kecil**: Langsung completed setelah processing
- **Order Besar**: Progressive status updates

### User Interaction
- **Order Kecil**: Single action untuk complete PO
- **Order Besar**: Multiple interactions untuk individual labels

### Complexity
- **Order Kecil**: Simplified workflow
- **Order Besar**: Complex workflow dengan rim management

## Notes

- Controller ini didesain untuk order kecil yang memerlukan processing cepat dan sederhana
- Semua label di-generate dan diproses dalam satu transaksi untuk efficiency
- Status PO langsung set ke completed karena tidak ada partial processing
- Form request validation memastikan data integrity sebelum processing
- Transaction management memastikan atomicity untuk semua operations
- Integration dengan services memungkinkan code reuse dan separation of concerns
