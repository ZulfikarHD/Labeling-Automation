# Migration Guide - Sirine API Integration

## 📋 Overview

Panduan ini menjelaskan langkah-langkah untuk melakukan migration dari sistem lama (database lokal) ke sistem baru (Sirine API).

## ⚡ Quick Start

### 1. Pastikan Dependencies Terupdate

```bash
cd /home/sirinedev/WebApp/Developement/labeling

# Pastikan semua dependencies terinstall
composer install
yarn install
```

### 2. Test Koneksi ke Sirine API

```bash
# Test manual dengan curl
curl https://sirine.peruri.co.id/sirine/api/detail-order-pcht/3000278276
```

Expected response: JSON dengan data PO

### 3. Clear Cache (Penting!)

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 4. Build Frontend

```bash
yarn run build
# atau untuk development
yarn run dev
```

### 5. Test Aplikasi

1. **Test Register Nomor PO:**
   - Navigate ke `/order-besar/register-no-po`
   - Input nomor PO yang valid
   - Verifikasi data muncul dengan benar

2. **Test Cetak Label Order Kecil:**
   - Navigate ke `/order-kecil/cetak-label`
   - Input nomor PO yang valid
   - Verifikasi data muncul dengan benar

3. **Test Print Label MMEA:**
   - Navigate ke halaman print label MMEA
   - Input nomor PO
   - Verifikasi data muncul dengan benar

## 🔄 Perubahan Perilaku Sistem

### Sebelum Migration:

```
User Input PO → Backend Query Local DB → Return Data
```

### Setelah Migration:

```
User Input PO → Frontend Fetch Sirine API → Return Data
                         ↓ (parallel)
                Backend SpecificationService → Sirine API
                         ↓ (sukses)
                    Auto-sync ke Local DB
                         ↓ (gagal)
                    Fallback ke Local DB
```

## ⚠️ Breaking Changes

### 1. Frontend API Endpoints

#### RegisterNomorPo.vue
**Before:**
```javascript
axios.get(`/api/order-besar/register-no-po/${form.po}`)
```

**After:**
```javascript
axios.get(`https://sirine.peruri.co.id/sirine/api/detail-order-pcht/${form.po}`)
```

#### CetakLabel.vue
**Before:**
```javascript
axios.get(`/api/order-kecil/fetch-spec/${form.po}`)
```

**After:**
```javascript
axios.get(`https://sirine.peruri.co.id/sirine/api/detail-order-pcht/${form.po}`)
```

### 2. Error Messages

Error messages telah diperbarui untuk lebih deskriptif:

**Before:**
```
"Nomor PO Tidak Ditemukan"
```

**After:**
```
"Nomor PO Tidak Ditemukan di Sirine"
```

### 3. Backend Endpoints (Deprecated)

Endpoint berikut masih berfungsi tetapi tidak digunakan lagi:

- `GET /api/order-besar/register-no-po/{noPo}`
- `GET /api/order-kecil/fetch-spec/{no_po}`

**Note:** Endpoint ini dipertahankan untuk backward compatibility dan akan dihapus di versi mendatang.

## 📊 Data Migration

### Database Lokal (specifications table)

**Status:** Tetap digunakan sebagai fallback & cache

**Tidak perlu migrasi data** karena:
1. Data akan di-sync otomatis saat fetch dari Sirine
2. Data lama tetap berguna sebagai fallback
3. Tidak ada perubahan struktur table

### Jika Ingin Sync Semua Data (Optional)

Buat command Laravel untuk sync batch:

```php
// app/Console/Commands/SyncAllSpecifications.php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GeneratedProducts;
use App\Services\SpecificationService;

class SyncAllSpecifications extends Command
{
    protected $signature = 'sirine:sync-all';
    protected $description = 'Sync all PO specifications from Sirine API';

    public function handle(SpecificationService $specService)
    {
        $this->info('Starting sync from Sirine API...');
        
        $pos = GeneratedProducts::select('no_po')
            ->distinct()
            ->orderBy('no_po', 'desc')
            ->limit(100) // Limit untuk safety
            ->get();

        $bar = $this->output->createProgressBar(count($pos));

        foreach ($pos as $po) {
            try {
                $specService->getSpecByNomorPo($po->no_po);
                $this->info(" ✓ PO {$po->no_po} synced");
            } catch (\Exception $e) {
                $this->error(" ✗ PO {$po->no_po} failed: {$e->getMessage()}");
            }
            $bar->advance();
            
            // Rate limiting
            usleep(500000); // 0.5 detik delay
        }

        $bar->finish();
        $this->info("\nSync completed!");
    }
}
```

Run sync:
```bash
php artisan sirine:sync-all
```

## 🧪 Testing Checklist

### Pre-Migration Tests:

- [ ] Backup database
- [ ] Export data specifications (jika perlu)
- [ ] Document current API endpoints
- [ ] Test koneksi ke Sirine API

### Post-Migration Tests:

#### Functional Tests:

- [ ] ✅ Register Nomor PO - Input valid PO
- [ ] ✅ Register Nomor PO - Input invalid PO
- [ ] ✅ Cetak Label Order Kecil - Input valid PO
- [ ] ✅ Cetak Label Order Kecil - Input invalid PO
- [ ] ✅ Print Label MMEA - Input valid PO
- [ ] ✅ Edit Production Order - Load spec dari Sirine
- [ ] ✅ Print Label Inspeksi - Fetch spec dari Sirine

#### Integration Tests:

- [ ] ✅ Sirine API available - Data fetch sukses
- [ ] ✅ Sirine API unavailable - Fallback ke local DB
- [ ] ✅ Data sync ke local DB setelah fetch Sirine
- [ ] ✅ Cache working (30 menit)
- [ ] ✅ Error handling appropriate

#### Performance Tests:

- [ ] ✅ First fetch ≤ 2 detik
- [ ] ✅ Cached fetch ≤ 100ms
- [ ] ✅ Multiple concurrent requests handled
- [ ] ✅ No memory leaks

#### Error Handling Tests:

- [ ] ✅ Network timeout handled gracefully
- [ ] ✅ Invalid PO shows proper error message
- [ ] ✅ Incomplete data from Sirine handled
- [ ] ✅ Log files contain proper information

## 🔍 Monitoring

### Metrics to Monitor:

1. **API Response Time:**
   ```bash
   # Check Laravel log
   grep "Sirine API" storage/logs/laravel.log | tail -50
   ```

2. **Error Rate:**
   ```bash
   # Count errors
   grep "ERROR.*Sirine" storage/logs/laravel.log | wc -l
   ```

3. **Cache Hit Rate:**
   ```bash
   # Monitor cache usage
   php artisan cache:stats
   ```

4. **Fallback Usage:**
   ```bash
   # Check fallback to local DB
   grep "fallback.*local DB" storage/logs/laravel.log | tail -20
   ```

## 🚨 Rollback Plan

Jika terjadi masalah kritis:

### Quick Rollback (Frontend Only):

1. Revert frontend changes:
   ```bash
   git revert <commit-hash>
   yarn run build
   ```

2. Frontend akan kembali menggunakan endpoint lokal

3. Backend tetap support endpoint lama (tidak dihapus)

### Full Rollback (Frontend + Backend):

```bash
# 1. Checkout ke commit sebelum migration
git checkout <previous-commit-hash>

# 2. Rebuild
composer install
yarn install
yarn run build

# 3. Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# 4. Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

## 📈 Performance Optimization

### Cache Strategy:

```php
// Increase cache duration jika diperlukan
// app/Services/SirineApiService.php
private const CACHE_DURATION = 60; // 60 menit
```

### Queue Jobs (Future Enhancement):

Untuk improve performance, pertimbangkan menggunakan queue:

```php
// app/Jobs/SyncSpecificationJob.php
class SyncSpecificationJob implements ShouldQueue
{
    public function handle(SirineApiService $service)
    {
        $service->getOrderSpecification($this->no_po);
    }
}

// Dispatch
SyncSpecificationJob::dispatch($no_po)->delay(now()->addSeconds(5));
```

## 🔐 Security Considerations

### 1. CORS Configuration

Pastikan CORS configured untuk allow requests ke Sirine:

```php
// config/cors.php
'allowed_origins' => [
    'https://sirine.peruri.co.id',
],
```

### 2. API Rate Limiting

Pertimbangkan implementasi rate limiting:

```php
// app/Http/Kernel.php
protected $middlewareGroups = [
    'api' => [
        'throttle:60,1', // 60 requests per menit
    ],
];
```

### 3. Error Information Disclosure

Pastikan error messages tidak expose sensitive info:

```php
// Jangan:
return response()->json(['error' => $exception->getMessage()], 500);

// Gunakan:
Log::error('Error detail', ['error' => $exception->getMessage()]);
return response()->json(['error' => 'Terjadi kesalahan sistem'], 500);
```

## 📝 Post-Migration Tasks

### Immediate (Day 1):

- [ ] Monitor error logs intensif
- [ ] Test semua fitur utama
- [ ] Verify data sync ke local DB
- [ ] Check performance metrics

### Short-term (Week 1):

- [ ] Analyze error patterns
- [ ] Optimize cache strategy if needed
- [ ] Update user documentation
- [ ] Train users on new error messages

### Medium-term (Month 1):

- [ ] Remove deprecated endpoints (optional)
- [ ] Implement queue for sync (optional)
- [ ] Setup automated monitoring
- [ ] Performance analysis & optimization

### Long-term:

- [ ] Consider API versioning
- [ ] Implement circuit breaker pattern
- [ ] Setup Sirine API health check
- [ ] Automated sync for all POs

## 💡 Tips & Best Practices

1. **Monitor logs regularly** dalam minggu pertama
2. **Keep local DB synced** untuk fallback reliability
3. **Document any issues** untuk future reference
4. **Communicate with users** tentang perubahan
5. **Have rollback plan ready** sebelum production deployment

## 🆘 Common Issues & Solutions

### Issue 1: "Call to undefined method"

**Cause:** Cache outdated

**Solution:**
```bash
php artisan optimize:clear
composer dump-autoload
```

### Issue 2: CORS Error di Browser

**Cause:** Frontend tidak bisa access Sirine API

**Solution:**
1. Check network tab di browser
2. Verify Sirine API endpoint accessible
3. Contact Sirine admin jika perlu

### Issue 3: Slow Performance

**Cause:** Sirine API response lambat

**Solution:**
1. Check network latency ke Sirine
2. Increase cache duration
3. Implement queue for non-critical operations

### Issue 4: Data Tidak Sync ke Local DB

**Cause:** Database connection issue atau permission

**Solution:**
```bash
# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check table permissions
>>> Specification::first();
```

## 📞 Support

**Technical Support:**
- Developer: Zulfikar Hidayatullah
- Phone: +62 857-1583-8733
- Location: Asia/Jakarta (WIB)

**Escalation:**
- Database issues → Database admin
- Sirine API issues → Sirine system admin
- Network issues → Infrastructure team

---

**Migration Date:** December 13, 2025  
**Version:** 1.0  
**Status:** ✅ Production Ready

