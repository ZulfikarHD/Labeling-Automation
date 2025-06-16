# PrintLabelInspeksiController Documentation

## Overview

Controller untuk menangani proses inspeksi dan pencetakan label dalam sistem produksi. Controller ini mengelola workflow inspeksi label mulai dari registrasi PO baru, pemrosesan label secara batch, hingga update status progress dengan comprehensive logging dan error handling.

**Refactored Version**: Controller telah direfactor untuk meningkatkan readability, maintainability, dan mengurangi redundancy dengan memecah method kompleks menjadi method-method yang lebih kecil dan focused.

## Purpose

Mengelola proses inspeksi label produksi dengan fitur:
- Menampilkan interface inspeksi label dengan data workstation
- Mengambil spesifikasi PO untuk validasi dan referensi
- Menghitung sisa label yang belum diproses
- Memproses label secara batch dengan dual inspector system
- Auto-registrasi PO baru jika belum ada label
- Update status progress PO berdasarkan completion
- Comprehensive logging untuk audit trail dan debugging

## Architecture Improvements

### Constants
Controller menggunakan constants untuk status values yang meningkatkan readability:
```php
private const STATUS_IN_PROGRESS = 1;
private const STATUS_COMPLETED = 2;
```

### Method Organization
Controller diorganisir dengan clear separation of concerns:
- **Public Methods**: API endpoints yang dapat diakses
- **Private Methods**: Internal logic yang focused dan reusable
- **Response Methods**: Standardized response formatting
- **Validation Methods**: Centralized validation logic

## Main Flow

1. **User mengakses halaman inspeksi**
   - Load workstation list dan current user workstation
   - Render interface inspeksi dengan Inertia.js

2. **User input nomor PO**
   - Fetch spesifikasi PO untuk validasi
   - Cek ketersediaan label existing

3. **System memproses label**
   - Validate request data
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

### `getSpecification(int $no_po)`

**Purpose**: Mengambil data spesifikasi PO untuk validasi dan referensi

**Changes from Original**:
- Renamed from `fetchDataSpec()` untuk consistency
- Improved type hinting dengan `int` parameter
- Cleaner variable naming

**Parameters**:
- `$no_po`: Nomor PO yang dicari (int)

**Returns**: `JsonResponse`

**Response Success**:
```json
{
    "no_po": 12345,
    "no_obc": "OBC001",
    "nomor_plat": "B1234CD",
    "seri": 3,
    "other_fields": "..."
}
```

**Response Error (404)**:
```json
null
```

**Usage**:
```php
$specification = $controller->getSpecification(12345);
```

### `getRemainingLabelCount(int $no_po)`

**Purpose**: Menghitung jumlah label yang belum diproses untuk PO tertentu

**Changes from Original**:
- Renamed from `calcRemainingLabel()` untuk clarity
- Improved type hinting dengan `int` parameter
- Menggunakan `whereNull()` instead of `where('column', '=', null)`

**Parameters**:
- `$no_po`: Nomor PO (int)

**Returns**: `int` - Jumlah label yang belum diproses

**Logic**: Count labels dengan `np_users = null`

**Usage**:
```php
$remaining = $controller->getRemainingLabelCount(12345);
echo "Sisa label: " . $remaining;
```

### `store(Request $request)`

**Purpose**: Memproses label secara batch dengan dual inspector system

**Major Improvements**:
- **Extracted validation** ke method terpisah untuk reusability
- **Simplified main flow** dengan method extraction
- **Improved error handling** dengan dedicated response methods
- **Better transaction management** dengan cleaner structure

**Flow**:
1. Validate request data menggunakan `validateRequest()`
2. Start database transaction
3. Ensure PO exists menggunakan `ensureProductionOrderExists()`
4. Finish previous user session
5. Process labels menggunakan `processLabels()`
6. Update progress status menggunakan `updateProductionOrderStatus()`
7. Commit transaction dan return results menggunakan `successResponse()`

**Request Validation**:
```php
[
    'no_po' => 'required',
    'team' => 'required|exists:workstation,id',
    'jumlah_label' => 'required|integer|min:1',
    'np1' => 'required|string|max:4',  // Inspector 1 NIP
    'np2' => 'nullable|string|max:4',  // Inspector 2 NIP
]
```

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

### `validateRequest(Request $request): array`

**Purpose**: Centralized request validation

**Returns**: Array of validated data

**Benefits**:
- Reusable validation logic
- Consistent validation rules
- Type-safe return value

### `ensureProductionOrderExists(array $validatedData): void`

**Purpose**: Auto-register PO jika belum ada labels

**Logic**:
1. Check existing labels count
2. If zero, register new PO dan populate labels
3. Log creation process

**Benefits**:
- Extracted from main store method
- Clear single responsibility
- Comprehensive logging

### `processLabels(array $validatedData): array`

**Purpose**: Core label processing logic

**Returns**: Array dengan processing statistics
```php
[
    'processed_labels' => int,
    'failed_labels' => int,
    'remaining_labels' => int
]
```

**Logic**:
1. Initialize counters
2. Loop through requested label count
3. Get next available label menggunakan `getNextAvailableLabel()`
4. Update label menggunakan `updateLabel()`
5. Track success/failure counts
6. Return statistics

**Benefits**:
- Separated from main store method
- Clear processing logic
- Comprehensive error handling per label

### `getNextAvailableLabel(int $no_po)`

**Purpose**: Get next available label untuk processing

**Returns**: `GeneratedLabels` model atau `null`

**Logic**: Query labels dengan `np_users = null` ordered by `no_rim` ASC

**Benefits**:
- Reusable query logic
- Consistent label selection
- Clear method naming

### `updateLabel($label, array $validatedData): bool`

**Purpose**: Update individual label dengan inspector data

**Parameters**:
- `$label`: GeneratedLabels model instance
- `$validatedData`: Validated request data

**Returns**: `bool` - Success/failure status

**Logic**:
1. Update label dengan inspector data
2. Set timestamps
3. Log success/failure
4. Return boolean result

**Benefits**:
- Individual error handling
- Detailed logging per label
- Boolean return untuk easy checking

### `updateProductionOrderStatus(int $no_po, int $remainingLabels): void`

**Purpose**: Update status progress PO

**Parameters**:
- `$no_po`: Nomor PO
- `$remainingLabels`: Jumlah label yang tersisa

**Logic**: 
- Use constants untuk status values
- Set status berdasarkan remaining labels
- Update `generated_products` table

**Benefits**:
- Uses meaningful constants
- Clear status logic
- Separated concern

### Response Methods

#### `successResponse(array $result): JsonResponse`

**Purpose**: Standardized success response formatting

**Benefits**:
- Consistent response structure
- Reusable success formatting
- Clear data organization

#### `validationErrorResponse(ValidationException $e, Request $request): JsonResponse`

**Purpose**: Standardized validation error response

**Benefits**:
- Consistent error formatting
- Comprehensive logging
- User-friendly error messages

#### `systemErrorResponse(Exception $e, Request $request): JsonResponse`

**Purpose**: Standardized system error response

**Benefits**:
- Comprehensive error logging dengan trace
- User-friendly error messages
- Consistent error structure

## Database Tables Interaction

### specifications
**Purpose**: Validasi dan referensi PO
**Fields**:
- `no_po`: Nomor PO (Primary Key)
- `no_obc`: Nomor OBC
- `nomor_plat`: Nomor plat kendaraan
- `seri`: Seri produk
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
- `np_user_p2`: NIP inspector 2
- `workstation`: ID workstation
- `start`: Timestamp mulai
- `finish`: Timestamp selesai

**Operations**:
- `where()->count()`: Count existing/remaining labels
- `whereNull()->orderBy()->first()`: Get next available label
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
2. **Status 1**: In Progress (ada label yang sudah/sedang diproses) - `STATUS_IN_PROGRESS`
3. **Status 2**: Completed (semua label sudah diproses) - `STATUS_COMPLETED`

### Error Handling Rules
1. **Individual Label Errors**: Tidak menghentikan batch processing
2. **Transaction Safety**: Rollback jika terjadi critical error
3. **Comprehensive Logging**: Semua aktivitas dan error di-log
4. **Graceful Degradation**: Partial success handling

## Logging Strategy

### Info Level Logging
- PO creation dan label population
- Label processing start/completion
- Processing statistics dan results

### Debug Level Logging
- Individual label processing success
- Detailed processing information per label

### Warning Level Logging
- Validation failures
- No available labels found
- Business logic warnings

### Error Level Logging
- Individual label processing failures
- Critical system errors
- Exception details dengan trace

**Log Structure Examples**:
```php
// Processing start
Log::info('Starting label processing', [
    'no_po' => 12345,
    'requested_labels' => 5,
    'inspector_1' => 'EMP1',
    'inspector_2' => 'EMP2'
]);

// Individual label success
Log::debug('Label processed successfully', [
    'label_id' => 123,
    'no_rim' => 'RIM001',
    'potongan' => 'A'
]);

// Processing completion
Log::info('Label processing completed successfully', [
    'no_po' => 12345,
    'processed_labels' => 5,
    'failed_labels' => 0,
    'remaining_labels' => 10
]);
```

## Error Handling

### Exception Types
1. **ValidationException**: Invalid request data
2. **ModelNotFoundException**: PO/Specification not found
3. **DatabaseException**: Database operation failures
4. **SystemException**: General system errors

### Error Recovery Strategies
- **Individual Label Failures**: Continue processing remaining labels
- **Critical Failures**: Rollback transaction dan return error
- **Partial Success**: Return statistics dengan failed/success counts
- **Graceful Degradation**: Handle partial failures appropriately

### Transaction Management
```php
DB::beginTransaction();
try {
    $this->ensureProductionOrderExists($validatedData);
    $this->printLabelService->finishPreviousUserSession($validatedData['np1']);
    
    $result = $this->processLabels($validatedData);
    $this->updateProductionOrderStatus($validatedData['no_po'], $result['remaining_labels']);
    
    DB::commit();
    return $this->successResponse($result);
} catch (\Exception $e) {
    DB::rollback();
    return $this->systemErrorResponse($e, $request);
}
```

## Code Quality Improvements

### Method Extraction Benefits
1. **Single Responsibility**: Each method has one clear purpose
2. **Testability**: Smaller methods are easier to unit test
3. **Reusability**: Extracted methods can be reused
4. **Readability**: Main flow is easier to understand

### Type Safety
- Consistent use of type hints (`int`, `array`, `bool`)
- Return type declarations untuk clarity
- Proper parameter typing

### Constants Usage
- Meaningful constant names instead of magic numbers
- Centralized status value definitions
- Improved code readability

### Error Handling Patterns
- Dedicated response methods untuk consistency
- Comprehensive logging dengan context
- User-friendly error messages
- Proper HTTP status codes

## Usage Examples

### Basic Label Processing
```javascript
// Frontend request
const response = await fetch('/api/print-label/inspeksi/store', {
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
if (result.success) {
    console.log('Processed:', result.data.processed_labels);
    console.log('Failed:', result.data.failed_labels);
    console.log('Remaining:', result.data.remaining_labels);
    console.log('Status:', result.data.status);
} else {
    console.error('Error:', result.message);
    if (result.errors) {
        console.error('Validation errors:', result.errors);
    }
}
```

### Fetch PO Specification
```javascript
// Get PO specification (updated endpoint)
const specResponse = await fetch(`/api/print-label/inspeksi/${poNumber}`);
const specData = await specResponse.json();

if (specData) {
    console.log('No OBC:', specData.no_obc);
    console.log('Nomor Plat:', specData.nomor_plat);
    console.log('Seri:', specData.seri);
} else {
    console.error('PO specification not found');
}
```

### Check Remaining Labels
```javascript
// Get remaining label count (updated endpoint)
const remainingResponse = await fetch(`/api/print-label/inspeksi/count-remaining-label/${poNumber}`);
const remainingCount = await remainingResponse.json();

console.log(`Remaining labels: ${remainingCount}`);
```

### Backend Usage
```php
// Using the refactored controller
$controller = new PrintLabelInspeksiController($poService, $labelService);

// Get specification
$spec = $controller->getSpecification(12345);

// Get remaining count
$remaining = $controller->getRemainingLabelCount(12345);

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
- Efficient query patterns dengan proper indexing

### Logging Optimization
- Use appropriate log levels untuk menghindari log spam
- Include context data untuk debugging
- Avoid logging sensitive information
- Structured logging untuk better analysis

## Security Considerations

### Input Validation
- Strict validation untuk semua input parameters
- Exists validation untuk foreign key references
- Length limits untuk NIP fields
- Type validation untuk numeric fields

### Access Control
- User workstation validation
- Inspector NIP format validation
- PO existence validation
- Proper authorization checks

### Data Integrity
- Database transactions untuk atomicity
- Proper error handling dan rollback
- Audit trail melalui comprehensive logging
- Consistent data state management

## Integration Points

### With Services
- **ProductionOrderService**: Auto PO registration
- **PrintLabelService**: Label population dan session management

### With Frontend
- **Inertia.js**: SPA interface rendering
- **AJAX API**: Real-time data fetching dan processing
- **Vue.js Components**: Reactive UI updates

### With Database
- **Multiple Tables**: Coordinated updates across tables
- **Transaction Management**: Ensure data consistency
- **Query Optimization**: Efficient data retrieval

## API Endpoints

### Updated Endpoints
```php
// Updated method names in routes
Route::get('/print-label/inspeksi/{no_po}', [PrintLabelInspeksiController::class, 'getSpecification']);
Route::get('/print-label/inspeksi/count-remaining-label/{no_po}', [PrintLabelInspeksiController::class, 'getRemainingLabelCount']);
Route::post('/print-label/inspeksi/store', [PrintLabelInspeksiController::class, 'store']);
```

## Testing Considerations

### Unit Testing
- Each extracted method dapat di-test secara individual
- Mock dependencies untuk isolated testing
- Test validation logic separately
- Test error handling scenarios

### Integration Testing
- Test complete workflow dari PO input hingga label processing
- Test transaction rollback scenarios
- Test partial failure handling
- Test API endpoint responses

## Migration Notes

### Breaking Changes
- Method names changed: `fetchDataSpec()` → `getSpecification()`
- Method names changed: `calcRemainingLabel()` → `getRemainingLabelCount()`
- Route endpoints updated accordingly

### Backward Compatibility
- Response formats remain the same
- Business logic unchanged
- Database schema unchanged

## Notes

- Controller telah direfactor untuk improved maintainability dan readability
- Method extraction memungkinkan better testing dan reusability
- Constants usage meningkatkan code clarity
- Improved error handling dengan dedicated response methods
- Transaction management yang lebih clean dan robust
- Comprehensive logging untuk better debugging dan audit trail
- Type safety improvements dengan proper type hints
- Performance optimization melalui efficient query patterns
- Security enhancements dengan proper validation dan error handling
