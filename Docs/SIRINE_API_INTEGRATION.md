# Integrasi Sirine API - Documentation

## 📋 Ringkasan Perubahan

Aplikasi labeling telah diintegrasikan dengan Sirine API untuk mengambil spesifikasi Production Order (PO) secara real-time. Perubahan ini memastikan data selalu sinkron dengan sistem pusat Sirine.

## 🎯 Tujuan

- **Sentralisasi Data**: Sirine API sebagai single source of truth untuk data PO
- **Real-time Updates**: Data PO selalu up-to-date dari sistem Sirine
- **Consistency**: Menghindari data duplikat dan tidak sinkron
- **Reliability**: Fallback ke database lokal jika Sirine tidak tersedia

## 📁 File yang Diubah/Dibuat

### Frontend (Vue.js)

1. **resources/js/Pages/OrderBesar/RegisterNomorPo.vue**
   - ✅ Fetch dari: `https://sirine.peruri.co.id/sirine/api/detail-order-pcht/{no_po}`
   - ❌ Tidak lagi: `/api/order-besar/register-no-po/{noPo}`

2. **resources/js/Pages/OrderKecil/CetakLabel.vue**
   - ✅ Fetch dari: `https://sirine.peruri.co.id/sirine/api/detail-order-pcht/{no_po}`
   - ❌ Tidak lagi: `/api/order-kecil/fetch-spec/{no_po}`

### Backend (Laravel)

#### **Baru Dibuat:**

1. **app/Services/SirineApiService.php** ⭐ NEW
   - Service utama untuk komunikasi dengan Sirine API
   - Fitur caching 30 menit untuk performa
   - Error handling dan logging yang comprehensive
   - Method validasi dan format data

#### **Diperbarui:**

2. **app/Services/SpecificationService.php**
   - Menggunakan `SirineApiService` sebagai sumber data utama
   - Auto-sync data Sirine ke database lokal
   - Fallback ke database lokal jika Sirine unavailable

3. **app/Http/Controllers/PrintLabel/PrintLabelInspeksiController.php**
   - Menggunakan `SpecificationService` untuk semua query
   - Tidak lagi query langsung ke model `Specification`

4. **app/Http/Controllers/ProductionOrderController.php**
   - Menggunakan `SpecificationService` di method `edit()`
   - Fallback error handling yang lebih baik

5. **app/Http/Controllers/OrderBesar/RegisterNomorPoController.php**
   - Method `show()` marked as `@deprecated`
   - Fixed import statements (DB & Log facades)

6. **app/Http/Controllers/OrderKecil/CetakLabelController.php**
   - Method `show()` marked as `@deprecated`

## 🔄 Arsitektur Baru

```
┌─────────────────┐
│   Frontend      │
│  (Vue.js)       │
└────────┬────────┘
         │
         ├──────────────────┐
         │                  │
         ▼                  ▼
┌─────────────────┐  ┌──────────────────┐
│  Sirine API     │  │ Laravel Backend  │
│  (External)     │  │                  │
└─────────────────┘  └────────┬─────────┘
         │                     │
         └──────────┬──────────┘
                    │
                    ▼
         ┌─────────────────────┐
         │ SirineApiService    │
         │  - Fetch dari API   │
         │  - Caching          │
         │  - Error handling   │
         └──────────┬──────────┘
                    │
                    ▼
         ┌─────────────────────────┐
         │ SpecificationService    │
         │  - Primary: Sirine API  │
         │  - Fallback: Local DB   │
         │  - Auto-sync            │
         └──────────┬──────────────┘
                    │
         ┌──────────┴─────────────┐
         │                        │
         ▼                        ▼
┌──────────────────┐    ┌──────────────────┐
│   Controllers    │    │   Local DB       │
│                  │    │ (specifications) │
└──────────────────┘    └──────────────────┘
```

## 📡 Sirine API Endpoint

### **GET** `/sirine/api/detail-order-pcht/{no_po}`

**Base URL:** `https://sirine.peruri.co.id`

**Response Structure:**
```json
{
  "no_po": 3000278276,
  "no_obc": "PST410070",
  "jenis": "P",
  "tgl_obc": "2025-12-02",
  "tgl_jt": "2025-12-22",
  "tgl_bb": "2025-12-11",
  "tgl_cetak": "2025-12-12",
  "tgl_verif": "2025-12-13",
  "tgl_kemas": "2025-12-13",
  "jml_order": 9500,
  "rencet": 9976,
  "jml_bb": 9976,
  "jml_cd": 0,
  "total_cd": 9976,
  "jml_cetak": 9976,
  "hcs_verif": 9800,
  "hcts_verif": 176,
  "hcs_sisa": 300,
  "total_hcts": 476,
  "kemas": 9500,
  "kirim": 0,
  "status": "-",
  "mesin": "TGN-1011",
  "desain": 2026,
  "created_at": "2025-12-04T08:53:33.000000Z",
  "updated_at": "2025-12-13T03:43:12.000000Z",
  "gilir_cetak": null
}
```

## 🛠 Cara Penggunaan

### Di Controller

```php
use App\Services\SirineApiService;

class YourController extends Controller
{
    protected $sirineApiService;

    public function __construct(SirineApiService $sirineApiService)
    {
        $this->sirineApiService = $sirineApiService;
    }

    public function getOrderData($no_po)
    {
        try {
            // Ambil data lengkap dari Sirine
            $orderData = $this->sirineApiService->getOrderSpecification($no_po);
            
            // Atau ambil dalam format Specification model
            $spec = $this->sirineApiService->getSpecificationFormatted($no_po);
            
            return response()->json($spec);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Data tidak ditemukan',
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
```

### Menggunakan SpecificationService (Recommended)

```php
use App\Services\SpecificationService;

class YourController extends Controller
{
    protected $specificationService;

    public function __construct(SpecificationService $specificationService)
    {
        $this->specificationService = $specificationService;
    }

    public function getSpec($no_po)
    {
        try {
            // Otomatis ambil dari Sirine, fallback ke local DB
            $spec = $this->specificationService->getSpecByNomorPo($no_po);
            
            return response()->json($spec);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Spesifikasi tidak ditemukan'
            ], 404);
        }
    }
}
```

## 🔧 Konfigurasi

### Caching

Cache duration dapat diubah di `SirineApiService.php`:

```php
private const CACHE_DURATION = 30; // dalam menit
```

### Timeout

Request timeout ke Sirine API:

```php
Http::timeout(10) // 10 detik
```

## ⚠️ Error Handling

### Flow Error Handling:

1. **Frontend mencoba fetch dari Sirine API**
   - ✅ Sukses: Tampilkan data
   - ❌ Gagal: Tampilkan error "Nomor PO Tidak Ditemukan di Sirine"

2. **Backend (SpecificationService)**
   - ✅ Sirine API berhasil: Gunakan data dari Sirine, sync ke local DB
   - ❌ Sirine API gagal: 
     - Coba ambil dari local DB
     - Jika local DB kosong: Throw exception
     - Jika ada di local DB: Gunakan data local

### Error Messages:

| Kondisi | Message |
|---------|---------|
| PO tidak ditemukan di Sirine | "Nomor PO Tidak Ditemukan di Sirine" |
| Data tidak lengkap | "Data tidak lengkap dari Sirine untuk PO: {no_po}" |
| Gagal koneksi ke Sirine | "Gagal mengambil data dari Sirine untuk PO: {no_po}" |
| Tidak ada di Sirine & Local DB | "Spesifikasi untuk PO {no_po} tidak ditemukan di Sirine maupun database lokal" |

## 📊 Database Sync

### Auto-Sync Process:

Setiap kali data berhasil diambil dari Sirine API, sistem otomatis melakukan sync ke database lokal:

```php
Specification::updateOrCreate(
    ['no_po' => $spec['no_po']],
    [
        'no_obc' => $spec['no_obc'],
        'seri' => $spec['seri'],
        'type' => $spec['type'],
        'rencet' => $spec['rencet'],
        'mesin' => $spec['mesin'] ?? '-',
    ]
);
```

### Tujuan Sync:
- **Backup data**: Jika Sirine down, masih bisa menggunakan data cached
- **Reporting**: Query lokal lebih cepat untuk laporan
- **Audit trail**: Menyimpan history data

## 🔍 Monitoring & Debugging

### Log Files:

Semua operasi Sirine API dicatat di Laravel log:

```bash
tail -f storage/logs/laravel.log
```

### Log Events:

1. **Sukses fetch dari Sirine**: Info level
2. **Gagal fetch dari Sirine**: Warning level
3. **Fallback ke local DB**: Warning level
4. **Error system**: Error level dengan stack trace

### Contoh Log:

```
[2025-12-13 10:30:45] local.WARNING: Gagal mengambil dari Sirine API, menggunakan database lokal 
{"no_po":3000278276,"error":"Connection timeout"}

[2025-12-13 10:31:12] local.ERROR: Error fetching from Sirine API 
{"no_po":3000278276,"error":"Data tidak lengkap dari Sirine untuk PO: 3000278276"}
```

## 🧪 Testing

### Manual Testing:

1. **Test fetch dari frontend:**
   ```
   1. Buka halaman Register Nomor PO
   2. Input nomor PO valid
   3. Verifikasi data muncul dari Sirine
   4. Check browser console untuk API call
   ```

2. **Test fallback mechanism:**
   ```
   1. Matikan koneksi internet (simulate Sirine down)
   2. Input nomor PO yang ada di local DB
   3. Verifikasi data tetap muncul dari local DB
   4. Check log untuk warning message
   ```

3. **Test cache:**
   ```
   1. Fetch PO pertama kali (check network tab: call ke Sirine)
   2. Fetch PO yang sama dalam 30 menit (should use cache)
   3. Clear cache: php artisan cache:clear
   4. Fetch lagi (should call Sirine again)
   ```

## 🚨 Troubleshooting

### Issue: "Nomor PO Tidak Ditemukan di Sirine"

**Possible Causes:**
1. PO belum ada di sistem Sirine
2. Koneksi ke Sirine gagal
3. Format nomor PO salah

**Solution:**
1. Verifikasi PO ada di Sirine
2. Check koneksi network ke sirine.peruri.co.id
3. Check Laravel log untuk detail error

### Issue: Data tidak update

**Possible Causes:**
1. Cache masih berlaku (30 menit)
2. Auto-sync gagal

**Solution:**
```bash
# Clear cache
php artisan cache:clear

# Clear specific PO cache (di controller)
$sirineApiService->clearCache($no_po);
```

### Issue: Performance lambat

**Possible Causes:**
1. Sirine API response lambat
2. Tidak menggunakan cache

**Solution:**
1. Increase timeout jika perlu
2. Pastikan caching aktif
3. Pertimbangkan menggunakan queue untuk sync

## 📈 Performance

### Metrics:

| Operation | Time (avg) | Cache Hit |
|-----------|------------|-----------|
| First fetch (Sirine API) | ~500-1000ms | No |
| Cached fetch | ~5-10ms | Yes |
| Fallback to local DB | ~20-50ms | No |

### Optimization Tips:

1. **Gunakan cache**: Cache berlaku 30 menit
2. **Batch operations**: Jika perlu fetch banyak PO
3. **Queue jobs**: Untuk operasi sync yang tidak urgent

## 🔐 Security

### Considerations:

1. **API Endpoint**: HTTPS connection ke Sirine
2. **Data validation**: Validasi struktur response dari Sirine
3. **Error disclosure**: Jangan expose detail error ke frontend
4. **Rate limiting**: Pertimbangkan rate limiting untuk API calls

## 📝 Maintenance

### Regular Tasks:

1. **Monitor log files**: Check error patterns
2. **Cache management**: Clear cache jika ada update besar
3. **Database cleanup**: Pertimbangkan cleanup data lama di Specification table
4. **API monitoring**: Monitor Sirine API uptime dan response time

### Backup Strategy:

1. Local DB tetap di-backup (data fallback)
2. Log files di-rotate dan di-archive
3. Cache di-clear saat deployment

## 🎓 Best Practices

1. **Selalu gunakan SpecificationService** untuk query spesifikasi
2. **Jangan query langsung** ke model Specification
3. **Handle error dengan graceful**: Berikan fallback yang jelas
4. **Log semua operasi**: Untuk debugging dan monitoring
5. **Test fallback mechanism**: Pastikan aplikasi tetap jalan saat Sirine down

## 📞 Support

Jika ada masalah dengan integrasi Sirine API:

1. Check Laravel log: `storage/logs/laravel.log`
2. Check browser console untuk frontend error
3. Verifikasi koneksi ke sirine.peruri.co.id
4. Contact: Zulfikar Hidayatullah (+62 857-1583-8733)

---

**Last Updated:** December 13, 2025  
**Version:** 1.0  
**Author:** Zulfikar Hidayatullah

