# Phase 4: Cetak Label Inspeksi
## Labeling App - Peruri

**Timeline:** 1 Bulan (4 Sprints)
**Sprint Duration:** 1 Minggu
**Team Size Recommendation:** 1 Backend Dev, 1 Frontend Dev, 1 QA

---

## Konteks Fitur

Fitur **Cetak Label Inspeksi** memungkinkan operator inspektur mengisi label inspeksi untuk PO PCHT secara **iteratif** — berbeda dengan Cetak Label Personal PCHT (Phase 3) yang bersifat one-shot.

**Alur singkat:**

1. Operator memindai/mengetik nomor PO → sistem secara otomatis (a) mengambil spesifikasi PO dari sumber data eksternal Sirine, (b) menghitung jumlah **sisa label** yang masih kosong di sistem internal untuk PO tersebut.
2. Operator menentukan **jumlah label yang akan diisi pada sesi ini** (default = seluruh sisa label, dapat dikurangi), lalu mengisi NP pegawai pemeriksa (`np1` wajib, `np2` opsional).
3. Setelah konfirmasi, sistem dalam satu transaksi atomic:
   - (Lazy) Mendaftarkan PO + men-generate seluruh label rim jika PO belum pernah terdaftar.
   - Menutup seluruh sesi inspeksi sebelumnya milik `np1` yang masih terbuka.
   - Mengisi sejumlah `jumlah_label` label kosong pada PO ini — diambil dari **nomor rim tertinggi turun ke bawah**, sisi **Kanan dulu lalu Kiri**, dan **mengecualikan label inschiet** (rim spesial 999).
   - Memperbarui status PO menjadi **Sedang Diperiksa (1)** jika masih ada label tersisa, atau **Selesai Diperiksa (2)** jika seluruh label terisi.
4. Setelah backend sukses, sistem mencetak label batch otomatis (jumlah salinan = `jumlah_label`) tanpa menampilkan dialog cetak browser, lalu mereset form agar siap untuk PO/sesi berikutnya.

**Perbedaan utama vs Phase 3 (Cetak Label Personal PCHT — Order Kecil):**

| Aspek | Phase 3 (Order Kecil) | Phase 4 (Inspeksi) |
|---|---|---|
| Sifat sesi | One-shot — 1 PO selesai dalam 1 submit | Iteratif — boleh beberapa kali submit per PO hingga sisa = 0 |
| Pendaftaran PO | Selalu dibuat baru di sini | Lazy — hanya dibuat jika belum pernah terdaftar |
| Field input | `obc`, `start_rim`, `end_rim`, `jml_lembar`, `jml_rim`, `periksa1`, `periksa2` | Hanya `team`, `jumlah_label`, `np1`, `np2` (sisanya auto / tarik dari Sirine) |
| Status akhir PO | Selalu `2` (Selesai Diperiksa) | Dinamis: `1` jika masih ada sisa, `2` jika habis |
| Algoritma label | Generate seluruh rim sekaligus | Update sejumlah `N` label dari rim tertinggi turun, Kanan-first, exclude inschiet |
| Panel verifikasi pegawai harian | Ada | **Tidak ada** |
| Sisa label real-time | Tidak relevan (selalu seluruh PO) | Wajib ditampilkan dan menjadi batas atas `jumlah_label` |

---

## Product Backlog Overview

### Epic Summary

| Epic ID | Epic Name | Priority | Story Points | Sprints |
|---------|-----------|----------|--------------|---------|
| E1 | Halaman Cetak Label Inspeksi | Critical | 5 | 1 |
| E2 | Input PO & Auto-Fetch Spesifikasi | Critical | 18 | 1-2 |
| E3 | Input Form Inspeksi & Validasi | Critical | 21 | 2-3 |
| E4 | Submit Inspeksi & Pemrosesan Atomic | Critical | 29 | 3 |
| E5 | Cetak Label Otomatis & Penanganan Hasil | High | 16 | 4 |

**Total Estimated:** ~89 Story Points

---

## EPIC E1: Halaman Cetak Label Inspeksi

### E1.1 - Akses Halaman & Proteksi Auth

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-1.1.1 | Sebagai **operator inspektur**, saya ingin **mengakses halaman Cetak Label Inspeksi** agar **saya bisa melanjutkan/menyelesaikan inspeksi label sebuah PO secara bertahap** | 3 | Critical | 1 |
| S-1.1.2 | Sebagai **operator yang belum login**, saya ingin **diarahkan ke halaman login saat mencoba akses halaman Cetak Label Inspeksi** agar **akses ke pencatatan inspeksi tetap terlindungi** | 2 | Critical | 1 |

**Acceptance Criteria - S-1.1.1:**
```gherkin
Feature: Akses Halaman Cetak Label Inspeksi

Scenario: Operator membuka halaman Cetak Label Inspeksi
  Given saya sudah login sebagai operator
  When saya mengakses halaman "/print-label-inspeksi"
  Then saya melihat halaman dengan judul "Print Label Inspeksi"
  And halaman menampilkan bagian "Scan Nomor PO" untuk input nomor PO
  And halaman menampilkan area form input data inspeksi (team, jumlah label, NP pemeriksa)
  And halaman dapat digunakan untuk memproses PO yang sudah pernah terdaftar maupun yang belum

Scenario: Halaman dapat diakses dari menu navigasi utama
  Given saya berada di halaman manapun setelah login
  When saya membuka menu navigasi yang relevan
  Then opsi "Cetak Label Inspeksi" tersedia dan dapat diakses
  And saat opsi dipilih saya dibawa ke "/print-label-inspeksi"
```

**Acceptance Criteria - S-1.1.2:**
```gherkin
Feature: Proteksi Akses Halaman Cetak Label Inspeksi

Scenario: Pengunjung yang belum login mencoba akses
  Given saya belum login
  When saya mengakses "/print-label-inspeksi"
  Then saya diarahkan ke halaman login

Scenario: Session expired saat mengakses halaman
  Given session login saya sudah expired
  When saya mencoba akses "/print-label-inspeksi"
  Then saya diarahkan ke halaman login
```

> **Catatan Teknis E1.1:**
> - **Business Logic:**
>   - Halaman Cetak Label Inspeksi hanya dapat diakses oleh user yang sudah terautentikasi
>   - User yang belum terautentikasi harus diarahkan ke halaman login
>   - Judul halaman (browser tab): "Print Label Inspeksi"
>   - Halaman ini tidak memerlukan role khusus — semua operator yang terautentikasi dapat memakainya

---

### E1.2 - State Awal Form & Mode Scan-Ready

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-1.2.1 | Sebagai **operator**, saya ingin **form ditampilkan dengan team default sesuai workstation saya dan fokus input langsung di field nomor PO** agar **saya bisa langsung memindai/mengetik PO tanpa interaksi tambahan** | 2 | Critical | 1 |

**Acceptance Criteria - S-1.2.1:**
```gherkin
Feature: State Awal Form Cetak Label Inspeksi

Scenario: Form ditampilkan dengan nilai default saat halaman dimuat
  Given saya login sebagai operator dengan workstation_id = 3
  When saya membuka halaman "/print-label-inspeksi"
  Then form menampilkan field berikut dengan nilai default:
    | Field         | Default Value         | Status saat awal |
    | no_po         | kosong                | aktif & menerima fokus otomatis |
    | team          | workstation user (3)  | tidak dapat diubah sebelum spesifikasi ter-fetch |
    | jumlah_label  | 0                     | tidak dapat diubah sebelum spesifikasi ter-fetch |
    | np1           | kosong                | tidak dapat diubah sebelum spesifikasi ter-fetch |
    | np2           | kosong                | tidak dapat diubah sebelum spesifikasi ter-fetch |
  And user dapat langsung mulai mengetik/memindai nomor PO tanpa harus memilih field terlebih dahulu

Scenario: Daftar team tersedia untuk dipilih
  Given saya berada di halaman Cetak Label Inspeksi
  Then user dapat memilih team dari daftar seluruh workstation yang tersedia
  And daftar team diurutkan secara alfabet berdasarkan nama workstation

Scenario: Field input lain dikunci sampai spesifikasi PO berhasil ditarik
  Given saya baru membuka halaman dan belum mengetik nomor PO
  Then field team, jumlah_label, np1, dan np2 berada dalam kondisi tidak dapat diubah
  And aksi "Cetak Label" berada dalam kondisi tidak dapat dipicu
```

> **Catatan Teknis E1.2:**
> - **Business Logic:**
>   - Team default = workstation yang ter-assign ke user yang sedang login (`workstation_id` user)
>   - Daftar team diambil dari master data workstation, diurutkan ascending berdasarkan nama workstation
>   - Setiap entry workstation memiliki minimal: `id` (integer) dan `workstation` (string nama, maksimal 25 karakter)
>   - Halaman wajib memberikan fokus otomatis ke field `no_po` saat dimuat (mode "scan-first")
>   - Field input selain `no_po` (team, jumlah_label, np1, np2) hanya menjadi aktif setelah spesifikasi PO berhasil ditarik dari Sirine (lihat E2.1)

---

## EPIC E2: Input PO & Auto-Fetch Spesifikasi

### E2.1 - Input Nomor PO (Scan-Friendly) & Debounced Auto-Fetch ke Sirine

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-2.1.1 | Sebagai **operator**, saya ingin **mengetik atau memindai nomor PO dan sistem otomatis mengambil spesifikasi dari Sirine** agar **saya tidak perlu menginput data spesifikasi PO secara manual** | 5 | Critical | 1 |
| S-2.1.2 | Sebagai **operator**, saya ingin **fetch spesifikasi dilakukan setelah saya berhenti mengetik (debounce)** agar **tidak terjadi request berlebihan ke Sirine saat saya sedang mengetik** | 3 | High | 1 |
| S-2.1.3 | Sebagai **operator**, saya ingin **melihat indikator proses saat data Sirine sedang diambil** agar **saya tahu sistem sedang bekerja** | 2 | High | 1 |

**Acceptance Criteria - S-2.1.1:**
```gherkin
Feature: Auto-Fetch Spesifikasi PO dari Sirine

Scenario: PO valid memicu auto-fill spesifikasi
  Given saya berada di halaman Cetak Label Inspeksi
  And tidak ada error PO yang ditampilkan
  When saya memasukkan nomor PO "4000000001"
  And Sirine mengembalikan data dengan no_obc="ABC312345", mesin="M01", rencet=20000
  Then sistem menampilkan informasi spesifikasi PO yang terdiri dari:
    | Informasi       | Nilai      |
    | No OBC          | ABC312345  |
    | Nomor Mesin     | M01        |
    | Seri            | 3          |
    | Sisa Label      | (dihitung dari sistem internal — lihat E2.2) |
  And field team, jumlah_label, np1, dan np2 menjadi aktif dan dapat diisi
  And nilai `no_obc` dan `rencet` tersimpan secara internal untuk dipakai saat submit

Scenario: Input PO terlalu pendek tidak memicu fetch
  Given saya berada di halaman Cetak Label Inspeksi
  When saya mengetik nomor PO dengan panjang kurang dari 3 karakter
  Then sistem TIDAK mengirim request ke Sirine
  And informasi spesifikasi PO tetap kosong
  And field input lain tetap tidak dapat diubah

Scenario: Mengganti PO mereset state spesifikasi
  Given saya sudah berhasil fetch spesifikasi PO sebelumnya
  When saya mengganti nilai nomor PO
  Then pesan error PO sebelumnya (jika ada) dibersihkan
  And sistem akan menjalankan fetch ulang (mengikuti aturan debounce)
```

**Acceptance Criteria - S-2.1.2:**
```gherkin
Feature: Debounce Auto-Fetch ke Sirine

Scenario: Auto-fetch hanya dijalankan setelah user berhenti mengetik
  Given saya sedang mengetik nomor PO di field
  When saya mengetik karakter dengan jeda < 500 ms antar karakter
  Then sistem TIDAK mengirim request ke Sirine selama saya masih mengetik
  When saya berhenti mengetik selama 500 ms
  Then sistem baru mengirim request fetch spesifikasi ke Sirine

Scenario: Mengetik ulang membatalkan fetch sebelumnya
  Given saya baru saja berhenti mengetik dan fetch sedang dijadwalkan
  When saya mengetik karakter baru sebelum debounce selesai
  Then fetch berikutnya dijadwalkan ulang dari awal (500 ms setelah ketikan terakhir)
```

**Acceptance Criteria - S-2.1.3:**
```gherkin
Feature: Indikator Loading Saat Fetch Sirine

Scenario: Indikator proses ditampilkan selama fetch berlangsung
  Given saya memasukkan nomor PO dan menunggu fetch
  When request ke Sirine sedang berlangsung
  Then indikator proses sedang berlangsung ditampilkan
  When response Sirine kembali (baik sukses maupun gagal)
  Then indikator proses hilang
```

> **Catatan Teknis E2.1:**
> - **Business Logic:**
>   - Auto-fetch dipicu oleh perubahan nilai field `no_po`, dengan delay debounce **500 ms** setelah ketikan terakhir
>   - Auto-fetch hanya dijalankan jika panjang `no_po` ≥ 3 karakter — selain itu state spesifikasi direset
>   - Sumber data spesifikasi PO: **API eksternal Sirine** (`GET /detail-order-pcht/{no_po}`), dipanggil dari sisi client
>   - **Mapping data Sirine → tampilan / state form:**
>     - `no_obc = response.no_obc`
>     - `nomor_mesin = response.mesin ?? "---"`
>     - `seri = "1"` jika `no_obc[4] > "3"`, selain itu `seri = no_obc[4]` (lihat E2.3)
>     - `rencet` (jumlah lembar) tersimpan untuk dipakai saat submit
>   - Field `team`, `jumlah_label`, `np1`, `np2` hanya menjadi aktif setelah spesifikasi PO berhasil ter-fetch
> - **Pesan Error Standar:**
>   - "Nomor PO Tidak Ditemukan di Sirine"

**API Contract - S-2.1.1 (External — Sirine):**
```
GET https://sirine.peruri.co.id/sirine/api/detail-order-pcht/{no_po}

Response 200 (success):
{
  "no_po": 4000000001,
  "no_obc": "ABC312345",
  "rencet": 20000,
  "jenis": "P",
  "mesin": "M01",
  "desain": "2024"
}

Response (error):
HTTP non-200 atau payload tanpa "no_obc" → diperlakukan sebagai "PO tidak ditemukan"
```

---

### E2.2 - Live Count Sisa Label dari Sistem Internal

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-2.2.1 | Sebagai **operator**, saya ingin **melihat jumlah sisa label yang masih kosong untuk PO ini** agar **saya tahu berapa label yang bisa saya isi pada sesi ini** | 5 | Critical | 1 |
| S-2.2.2 | Sebagai **operator**, saya ingin **estimasi sisa label tetap muncul meskipun PO belum pernah terdaftar di sistem** agar **saya tetap bisa melanjutkan flow inspeksi untuk PO baru** | 3 | High | 2 |

**Acceptance Criteria - S-2.2.1:**
```gherkin
Feature: Live Count Sisa Label dari Sistem

Scenario: PO sudah pernah terdaftar — sisa label diambil dari sistem
  Given fetch spesifikasi PO "4000000001" berhasil
  And PO tersebut sudah pernah terdaftar dan sebagian labelnya sudah terisi
  And di sistem masih ada 16 label rim (non-inschiet) dengan np_users kosong untuk PO ini
  When sistem menanyakan sisa label untuk PO "4000000001"
  Then sistem mengembalikan jumlah sisa label = 16
  And informasi "Sisa Label" pada halaman menampilkan 16
  And field `jumlah_label` pada form diinisialisasi = 16

Scenario: Seluruh label sudah terisi
  Given fetch spesifikasi PO "4000000001" berhasil
  And tidak ada lagi label rim dengan np_users kosong (di luar inschiet) untuk PO tersebut
  When sistem menanyakan sisa label
  Then sisa label = 0
  And informasi "Sisa Label" ditampilkan dengan penanda visual yang dapat dibedakan dari kondisi "masih ada sisa"
  And aksi "Cetak Label" berada dalam kondisi tidak dapat dipicu

Scenario: Label inschiet tidak dihitung sebagai sisa
  Given PO memiliki 2 label dengan no_rim = 999 (inschiet) yang np_users kosong
  And 0 label rim regular yang np_users kosong
  Then sisa label yang dilaporkan = 0
  And aksi "Cetak Label" tidak dapat dipicu
```

**Acceptance Criteria - S-2.2.2:**
```gherkin
Feature: Estimasi Sisa Label untuk PO yang Belum Terdaftar

Scenario: PO belum pernah terdaftar — sisa label diestimasi dari rencet
  Given fetch spesifikasi PO "4000000999" berhasil dengan rencet = 20000
  And PO tersebut belum pernah terdaftar di sistem (tidak ada label apapun)
  When sistem menanyakan sisa label
  Then sistem mengembalikan flag "belum terdaftar" dan jumlah sisa = 0
  And halaman menampilkan estimasi sisa label = max(1, floor(20000 / 1000)) × 2 = 40
  And field `jumlah_label` pada form diinisialisasi = 40

Scenario: Sumber data sisa label internal tidak tersedia
  Given fetch spesifikasi Sirine berhasil
  When request ke sumber data sisa label internal gagal
  Then sistem tetap melanjutkan dengan menggunakan estimasi sisa = max(1, floor(rencet / 1000)) × 2
  And user tetap bisa melanjutkan flow (tidak diblokir oleh kegagalan endpoint sisa label)
```

> **Catatan Teknis E2.2:**
> - **Business Logic — Perhitungan Sisa Label Aktual (untuk PO yang sudah terdaftar):**
>   - Sisa label = jumlah label dengan `no_po_generated_products = {no_po}` AND `no_rim != 999` AND `np_users IS NULL`
>   - Label inschiet (`no_rim = 999`) **tidak termasuk** dalam perhitungan sisa label
> - **Business Logic — Estimasi Sisa Label (untuk PO yang belum terdaftar):**
>   - Estimasi sisa label = `max(1, floor(rencet / 1000)) × 2`
>   - Rumus ini berasal dari: 1 rim label = 1000 lembar, dan setiap rim menghasilkan 2 label (potongan "Kiri" + "Kanan")
> - **Business Logic — Flow Pengambilan:**
>   - Setelah fetch spesifikasi Sirine sukses, sistem juga mengambil informasi sisa label dari sumber data internal
>   - Response sumber data internal: `{ registered: boolean, count: integer }`
>     - `registered = true, count = N` → pakai `N` sebagai sisa label
>     - `registered = false` → pakai estimasi dari `rencet`
>     - Jika pemanggilan internal gagal → fallback ke estimasi dari `rencet`
> - **Konstanta:** 1 rim label = **1000** lembar (berbeda dengan 1 rim produk Order Besar/Personal yang = 500 lembar)

**API Contract - S-2.2.1:**
```
GET /api/print-label-inspeksi/count-remaining-label/{no_po}

Headers:
  Accept: application/json
  Authorization: Bearer {token}      # rekomendasi: endpoint WAJIB ter-auth di arsitektur baru

Response 200:
{
  "registered": true,
  "count": 16
}

Response 200 (PO belum terdaftar):
{
  "registered": false,
  "count": 0
}
```

---

### E2.3 - Penanda Visual OBC Berdasarkan Seri

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-2.3.1 | Sebagai **operator**, saya ingin **OBC ditampilkan dengan penanda visual berbeda sesuai seri** agar **saya bisa membedakan jenis PO secara cepat saat memverifikasi label fisik** | 2 | High | 2 |

**Acceptance Criteria - S-2.3.1:**
```gherkin
Feature: Penanda Visual OBC Berdasarkan Seri

Scenario: Seri ditentukan dari karakter ke-5 OBC
  Given Sirine mengembalikan no_obc dengan karakter ke-5 = "3"
  Then seri PO diset = "3"
  And tampilan informasi OBC dapat dibedakan secara visual dari seri lainnya

Scenario: Seri default untuk OBC dengan karakter ke-5 di luar 1-3
  Given no_obc memiliki karakter ke-5 di luar rentang "1"-"3" (mis. "9", "A")
  Then seri PO diset = "1" (default)
  And tampilan informasi OBC menggunakan penanda visual default

Scenario Outline: Mapping seri berdasarkan karakter ke-5 OBC
  Given no_obc dengan karakter ke-5 = <char5>
  Then seri = <seri>
  And tampilan OBC dapat dibedakan antar seri secara visual

  Examples:
    | char5 | seri |
    | "1"   | "1"  |
    | "2"   | "2"  |
    | "3"   | "3"  |
    | "4"   | "1"  |
    | "9"   | "1"  |
    | "A"   | "1"  |
```

> **Catatan Teknis E2.3:**
> - **Business Logic:**
>   - `seri = no_obc[4]` jika `no_obc[4]` ≤ "3", selain itu `seri = "1"`
>   - Tampilan OBC pada halaman dan pada label tercetak harus dapat dibedakan secara visual antar seri — terutama seri 3 vs seri lainnya. Implementasi visual bebas
>   - Nilai `seri` digunakan baik untuk tampilan informasi spesifikasi di halaman maupun untuk pewarnaan label cetak (lihat E5.2)

---

### E2.4 - Penanganan PO Tidak Ditemukan

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-2.4.1 | Sebagai **operator**, saya ingin **menerima pesan jelas saat nomor PO tidak ditemukan di Sirine** agar **saya tahu PO tersebut salah ketik atau memang belum ada** | 3 | Critical | 1 |

**Acceptance Criteria - S-2.4.1:**
```gherkin
Feature: Penanganan PO Tidak Ditemukan di Sirine

Scenario: PO tidak ditemukan
  Given saya berada di halaman Cetak Label Inspeksi
  When saya memasukkan nomor PO "9999999999"
  And Sirine merespon error atau payload tidak valid
  Then saya menerima pesan "Nomor PO Tidak Ditemukan di Sirine"
  And informasi spesifikasi PO direset (No OBC, Nomor Mesin, Seri, Sisa Label kosong)
  And field team, jumlah_label, np1, np2 tetap dalam kondisi tidak dapat diubah
  And aksi "Cetak Label" tidak dapat dipicu

Scenario: Pesan error dibersihkan saat user mengubah PO
  Given pesan "Nomor PO Tidak Ditemukan di Sirine" sedang ditampilkan
  When saya mengubah nilai nomor PO
  Then pesan error dibersihkan
  And debounce fetch kembali berjalan normal
```

> **Catatan Teknis E2.4:**
> - **Business Logic:**
>   - Response dari Sirine yang non-200, error jaringan, atau payload tanpa field wajib (`no_obc`) → diperlakukan sebagai "PO tidak ditemukan"
>   - Saat status "tidak ditemukan", state spesifikasi (`no_obc`, `nomor_mesin`, `seri`, `sisa_label`) direset ke kosong
>   - Setiap perubahan nilai `no_po` membersihkan pesan error PO sebelumnya
> - **Pesan Error Standar:**
>   - "Nomor PO Tidak Ditemukan di Sirine"

---

## EPIC E3: Input Form Inspeksi & Validasi

### E3.1 - Field Input & State Dependency

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-3.1.1 | Sebagai **operator**, saya ingin **memilih team dan mengisi jumlah label, np1 (wajib), serta np2 (opsional)** agar **identitas inspektur dan beban kerja sesi ini tercatat** | 3 | Critical | 2 |
| S-3.1.2 | Sebagai **operator**, saya ingin **default nilai jumlah_label sama dengan sisa label saat spesifikasi ter-fetch** agar **saya bisa langsung submit untuk menyelesaikan seluruh sisa PO tanpa mengubah angka** | 2 | High | 2 |
| S-3.1.3 | Sebagai **operator**, saya ingin **menekan Enter di field NP tidak ikut memicu submit form** agar **saya bisa berpindah field tanpa tidak sengaja men-submit** | 1 | Medium | 2 |

**Acceptance Criteria - S-3.1.1:**
```gherkin
Feature: Input Form Inspeksi

Scenario: Operator mengisi seluruh field inspeksi
  Given spesifikasi PO sudah berhasil ter-fetch
  When saya mengisi:
    | Field         | Value |
    | team          | 3     |
    | jumlah_label  | 10    |
    | np1           | I444  |
    | np2           | I555  |
  Then seluruh field tersimpan di state form (np1 & np2 akan diuppercase saat submit)
  And aksi "Cetak Label" dapat dipicu

Scenario: np1 wajib diisi sebelum submit
  Given spesifikasi PO sudah ter-fetch
  And np1 dikosongkan
  When saya mencoba memicu aksi "Cetak Label"
  Then submit tidak dilanjutkan ke backend
  And user diberi peringatan "Form Belum Lengkap" beserta keterangan field yang wajib diisi (team & NP 1)

Scenario: np2 boleh dikosongkan
  Given np1 = "I444" dan np2 dikosongkan
  When saya submit
  Then proses berjalan normal tanpa error validasi np2
```

**Acceptance Criteria - S-3.1.2:**
```gherkin
Feature: Default Jumlah Label = Sisa Label

Scenario: jumlah_label otomatis terisi sama dengan sisa label saat fetch sukses
  Given sisa label untuk PO "4000000001" = 16
  When fetch spesifikasi & sisa label selesai
  Then `jumlah_label` pada form diinisialisasi = 16

Scenario: Operator dapat mengurangi jumlah_label
  Given sisa label = 16 dan jumlah_label terisi 16
  When saya mengubah jumlah_label menjadi 5
  Then nilai jumlah_label = 5
  And tidak ada error selama 5 ≤ 16 dan > 0
```

**Acceptance Criteria - S-3.1.3:**
```gherkin
Feature: Enter di Field NP Tidak Memicu Submit

Scenario: User menekan Enter di field np1 atau np2
  Given saya sedang fokus di field np1 atau np2
  When saya menekan tombol Enter
  Then form TIDAK ter-submit secara tidak sengaja
  And fokus dapat berpindah ke field berikutnya secara normal
```

> **Catatan Teknis E3.1:**
> - **Business Logic:**
>   - Field input form: `team` (integer, wajib), `jumlah_label` (integer ≥ 1, wajib), `np1` (string max 5, wajib), `np2` (string max 5, opsional)
>   - Field `jumlah_label` diinisialisasi otomatis = nilai sisa label yang dilaporkan setelah fetch sukses
>   - Operator boleh menurunkan `jumlah_label` (≤ sisa label), tetapi tidak boleh menaikkannya melebihi sisa label (lihat E3.3)
>   - Field input lain (`no_obc`, `rencet`) di-passthrough dari spesifikasi Sirine — tidak diinput manual oleh user
>   - Tombol Enter pada field `np1` & `np2` harus dihalangi untuk men-submit form (untuk mencegah submit tidak sengaja saat user terbiasa menekan Enter antar field)

---

### E3.2 - Format NP Pegawai

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-3.2.1 | Sebagai **sistem**, saya ingin **memformat NP pegawai panjang menjadi maksimal 5 karakter** agar **NP yang disimpan dan tercetak konsisten dengan format internal** | 2 | High | 2 |

**Acceptance Criteria - S-3.2.1:**
```gherkin
Feature: Format NP Pegawai

Scenario Outline: NP > 4 karakter dipersingkat menjadi karakter pertama + 4 karakter terakhir
  Given NP input = <input>
  When saya submit form
  Then NP yang dikirim & yang tercetak di label = <formatted>

  Examples:
    | input         | formatted |
    | "I0123456"    | "I3456"   |
    | "X9876543"    | "X6543"   |
    | "I4444"       | "I4444"   |
    | "I44"         | "I44"     |
    | ""            | ""        |

Scenario: NP diuppercase saat dikirim ke backend dan saat disimpan
  Given np1 input = "i444"
  When backend menerima request
  Then nilai NP yang tersimpan = "I444" (uppercase)

Scenario: Format diterapkan pada np1 dan np2 secara independen
  Given np1 = "I0123456" dan np2 = "X1112222"
  When saya submit
  Then np1 yang dikirim = "I3456"
  And np2 yang dikirim = "X2222"
```

> **Catatan Teknis E3.2:**
> - **Business Logic:**
>   - **Format NP:** jika panjang NP > 4 karakter, NP dipersingkat menjadi `karakter_pertama + 4_karakter_terakhir` (hasil = 5 karakter)
>   - Jika panjang NP ≤ 4 karakter, NP dibiarkan apa adanya (tidak ada padding)
>   - NP kosong dibiarkan kosong
>   - Format diterapkan ke `np1` dan `np2` secara independen sebelum dikirim ke backend
>   - NP yang sudah diformat selalu di-uppercase sebelum disimpan ke storage
>   - NP yang tercetak pada label = NP yang sudah diformat & di-uppercase

---

### E3.3 - Validasi Frontend (Sisa Label & Field Wajib)

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-3.3.1 | Sebagai **operator**, saya ingin **menerima peringatan jika jumlah_label melebihi sisa label yang tersedia** agar **saya tidak men-submit data yang akan ditolak backend** | 3 | Critical | 2 |
| S-3.3.2 | Sebagai **operator**, saya ingin **menerima peringatan jelas saat field wajib belum lengkap** agar **saya tahu apa yang harus diperbaiki sebelum submit** | 2 | Critical | 2 |

**Acceptance Criteria - S-3.3.1:**
```gherkin
Feature: Validasi Jumlah Label vs Sisa Label

Scenario: jumlah_label = 0 atau negatif
  Given sisa label = 16
  When saya mengubah jumlah_label menjadi 0
  Then pesan validasi "Jumlah label harus lebih dari 0" ditampilkan
  And aksi "Cetak Label" tidak dapat dipicu

Scenario: jumlah_label melebihi sisa label
  Given sisa label = 16
  When saya mengubah jumlah_label menjadi 20
  Then pesan validasi "Jumlah label tidak boleh melebihi sisa label yang tersedia (16)" ditampilkan
  And aksi "Cetak Label" tidak dapat dipicu

Scenario: jumlah_label valid
  Given sisa label = 16
  When saya mengubah jumlah_label menjadi nilai antara 1 dan 16
  Then tidak ada pesan validasi yang ditampilkan
  And aksi "Cetak Label" dapat dipicu (jika field wajib lainnya juga sudah lengkap)
```

**Acceptance Criteria - S-3.3.2:**
```gherkin
Feature: Validasi Field Wajib Sebelum Submit

Scenario: Spesifikasi belum ter-fetch
  Given spesifikasi PO belum berhasil ter-fetch
  When saya mencoba memicu aksi "Cetak Label"
  Then submit tidak dijalankan
  And user diberi peringatan dengan judul "Data Belum Lengkap" dan keterangan "Silakan scan nomor PO terlebih dahulu"

Scenario: Team atau NP 1 kosong
  Given spesifikasi PO sudah ter-fetch
  And team kosong ATAU np1 kosong
  When saya mencoba memicu aksi "Cetak Label"
  Then submit tidak dijalankan
  And user diberi peringatan "Form Belum Lengkap" dengan keterangan "Silakan lengkapi Team dan NP 1 yang diperlukan"
```

> **Catatan Teknis E3.3:**
> - **Business Logic — Aturan Validasi Frontend:**
>   - `jumlah_label > 0` (tidak boleh nol atau negatif)
>   - `jumlah_label ≤ sisa_label` (tidak boleh melebihi sisa label yang dilaporkan sistem)
>   - `spesifikasi_terfetch == true` sebelum submit (`no_obc` harus terisi)
>   - `team` wajib terisi sebelum submit
>   - `np1` wajib terisi sebelum submit
> - **Pesan Error Standar (frontend):**
>   - "Jumlah label harus lebih dari 0"
>   - "Jumlah label tidak boleh melebihi sisa label yang tersedia ({sisa_label})"
>   - "Data Belum Lengkap — Silakan scan nomor PO terlebih dahulu"
>   - "Form Belum Lengkap — Silakan lengkapi Team dan NP 1 yang diperlukan"

---

### E3.4 - Validasi Server-Side

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-3.4.1 | Sebagai **operator**, saya ingin **menerima daftar pesan error per field dalam Bahasa Indonesia jika backend menolak data** agar **saya tahu field mana yang harus diperbaiki** | 5 | Critical | 3 |
| S-3.4.2 | Sebagai **sistem**, saya ingin **memvalidasi seluruh field payload sebelum memproses inspeksi** agar **data yang masuk ke storage konsisten dan benar** | 5 | Critical | 3 |

**Acceptance Criteria - S-3.4.1:**
```gherkin
Feature: Pesan Error Validasi Server-Side

Scenario: Backend mengembalikan daftar error per field
  Given saya submit form dengan beberapa field tidak valid
  When backend merespon dengan HTTP 422
  Then user menerima daftar pesan error per field dalam Bahasa Indonesia
  And pesan error mencakup minimal: nama field + alasan kegagalan
  And user diberi kesempatan memperbaiki form tanpa kehilangan input lainnya
```

**Acceptance Criteria - S-3.4.2:**
```gherkin
Feature: Validasi Server-Side Field Payload Inspeksi

Scenario Outline: Validasi field wajib
  Given saya submit form dengan field <field> kosong / tidak valid
  Then backend mengembalikan HTTP 422 dengan pesan error untuk <field>

  Examples:
    | field          |
    | no_po          |
    | team           |
    | jumlah_label   |
    | np1            |
    | no_obc         |
    | rencet         |

Scenario: team harus ada di master workstation
  Given saya submit dengan team = 999 (tidak terdaftar di master workstation)
  Then backend mengembalikan error karena team tidak valid

Scenario: jumlah_label harus integer >= 1
  Given saya submit dengan jumlah_label = 0 atau bukan angka
  Then backend mengembalikan error untuk jumlah_label

Scenario: np1 dan np2 maksimal 5 karakter
  Given saya submit dengan np1 = "I44446" (panjang 6)
  Then backend mengembalikan error untuk np1
  And error menyatakan panjang maksimal NP = 5 karakter

Scenario: rencet harus integer >= 1
  Given saya submit dengan rencet = 0
  Then backend mengembalikan error untuk rencet
```

> **Catatan Teknis E3.4:**
> - **Business Logic — Aturan Validasi Server-Side:**
>   - `no_po`: wajib
>   - `team`: wajib, harus ada di master workstation
>   - `jumlah_label`: wajib, integer ≥ 1
>   - `np1`: wajib, string, maksimal 5 karakter
>   - `np2`: opsional, string, maksimal 5 karakter
>   - `no_obc`: wajib
>   - `rencet`: wajib, integer ≥ 1
> - **Response Format Error (422 — Validasi):**
>   - HTTP status: `422`
>   - Body: `{ "success": false, "message": "Data yang dikirim tidak valid", "errors": { "<field>": ["<pesan>", ...] } }`

---

### E3.5 - Reset Form

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-3.5.1 | Sebagai **operator**, saya ingin **mereset form ke kondisi awal** agar **saya bisa segera memproses PO/sesi berikutnya tanpa refresh halaman** | 2 | High | 2 |

**Acceptance Criteria - S-3.5.1:**
```gherkin
Feature: Reset Form

Scenario: Operator memicu aksi "Clear Form"
  Given form sudah terisi (no_po, team, jumlah_label, np1, np2)
  When saya memicu aksi "Clear Form"
  Then seluruh field form dikembalikan ke nilai default awal (no_po kosong, team = workstation user, jumlah_label = 0, np1 & np2 kosong)
  And state spesifikasi PO direset (No OBC, Nomor Mesin, Seri, Sisa Label kosong)
  And pesan error PO & jumlah label dibersihkan
  And user menerima konfirmasi singkat bahwa form telah direset
  And tidak ada request dikirim ke backend

Scenario: Form direset otomatis setelah submit sukses
  Given submit inspeksi berhasil dan cetak label terkirim
  When proses cetak selesai ter-initiate
  Then form direset ke kondisi awal (seperti aksi "Clear Form")
  And halaman siap untuk PO/sesi berikutnya
```

> **Catatan Teknis E3.5:**
> - **Business Logic:**
>   - Reset manual (aksi user) mengembalikan seluruh field ke nilai default awal & mengosongkan flag "spesifikasi ter-fetch"
>   - Reset otomatis dilakukan setelah submit sukses dan cetak label selesai dikirim (lihat E5.4)

---

## EPIC E4: Submit Inspeksi & Pemrosesan Atomic

### E4.1 - Konfirmasi Sebelum Submit

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-4.1.1 | Sebagai **operator**, saya ingin **dimintai konfirmasi yang menampilkan rangkuman data sebelum label diproses** agar **saya bisa membatalkan jika ada data yang salah sebelum tercatat permanen** | 3 | High | 3 |

**Acceptance Criteria - S-4.1.1:**
```gherkin
Feature: Konfirmasi Sebelum Submit Inspeksi

Scenario: Permintaan konfirmasi menampilkan rangkuman data
  Given form sudah terisi lengkap dan lolos validasi frontend
  When saya memicu aksi "Cetak Label"
  Then sistem menampilkan permintaan konfirmasi yang berisi rangkuman:
    | Informasi      | Sumber                              |
    | No PO          | nilai field no_po                   |
    | Team           | nama team (bukan ID) dari master    |
    | Jumlah Label   | nilai field jumlah_label            |
    | NP1            | nilai field np1 (atau "-" jika kosong) |
    | NP2            | nilai field np2 (atau "-" jika kosong) |
  And saya dapat membatalkan konfirmasi

Scenario: Operator membatalkan konfirmasi
  Given permintaan konfirmasi sedang ditampilkan
  When saya memilih membatalkan
  Then tidak ada request dikirim ke backend
  And data form tetap dalam keadaan terisi

Scenario: Operator menyetujui konfirmasi
  Given permintaan konfirmasi sedang ditampilkan
  When saya menyetujui konfirmasi
  Then submit ke backend dilanjutkan
  And indikator proses berlangsung ditampilkan
  And aksi submit dinonaktifkan selama proses berjalan (mencegah double-submit)
```

> **Catatan Teknis E4.1:**
> - **Business Logic:**
>   - Aksi "Cetak Label" bersifat irreversible (mengisi label, menutup sesi sebelumnya, mengubah status PO) — wajib didahului konfirmasi user
>   - Rangkuman konfirmasi wajib menampilkan **nama team** (bukan ID-nya) untuk mencegah kekeliruan pemilihan
>   - Selama submit berjalan, aksi submit harus dinonaktifkan untuk mencegah double-submit

---

### E4.2 - Submit Atomic End-to-End

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-4.2.1 | Sebagai **sistem**, saya ingin **memproses lazy-register PO + tutup sesi sebelumnya + isi label + update status dalam satu transaksi atomic** agar **tidak ada state setengah jadi jika salah satu langkah gagal** | 8 | Critical | 3 |
| S-4.2.2 | Sebagai **operator**, saya ingin **menerima konfirmasi keberhasilan beserta informasi jumlah label yang diproses dan sisa label setelah submit** agar **saya tahu hasil sesi ini secara transparan** | 2 | Critical | 3 |

**Acceptance Criteria - S-4.2.1:**
```gherkin
Feature: Pemrosesan Atomic Submit Inspeksi

Scenario: Submit sukses untuk PO yang sudah terdaftar
  Given PO "4000000001" sudah terdaftar dengan 40 label rim (sisa 16 belum terisi)
  And form valid dengan payload:
    | Field        | Value      |
    | no_po        | 4000000001 |
    | team         | 3          |
    | jumlah_label | 10         |
    | np1          | I444       |
    | np2          | (kosong)   |
    | no_obc       | ABC312345  |
    | rencet       | 20000      |
  When backend memproses request
  Then dalam satu transaksi atomic, sistem:
    | Langkah                                                             | Hasil                                          |
    | Tidak meregister ulang PO (sudah ada labelnya)                      | tidak ada record duplikat                      |
    | Menutup seluruh sesi inspeksi sebelumnya milik np1="I444"           | finish = now() untuk seluruh label terbuka     |
    | Mengisi 10 label kosong dari rim tertinggi turun, Kanan-first, exclude inschiet | 10 label terupdate dengan np_users, np_user_p2, workstation, start, finish |
    | Update status PO sesuai sisa: sisa awal 16 - 10 = 6 → status = 1 (in_progress) | status PO = 1                         |
  And response HTTP 200 dikirim dengan ringkasan { processed, failed, remaining, status }

Scenario: Submit sukses untuk PO yang BELUM terdaftar (lazy create)
  Given PO "4000000999" belum pernah terdaftar di sistem
  And form valid dengan payload (rencet = 20000)
  When backend memproses request
  Then dalam satu transaksi atomic, sistem:
    | Langkah                                                                              | Hasil                                          |
    | Lazy-register PO baru                                                                | record baru di registrasi PO dengan no_obc=ABC312345, type=PCHT, status=0, start_rim=1, end_rim=floor(20000/1000)=20, assigned_team=3 |
    | Generate seluruh label rim (Kiri+Kanan) untuk rim 1..20 dengan np_users=NULL         | total 40 record label dengan np_users kosong   |
    | Generate inschiet jika ada sisa (20000 mod 1000 = 0 → tidak ada inschiet)            | tidak ada label inschiet                       |
    | Menutup sesi sebelumnya milik np1                                                    | finish = now() untuk label terbuka milik np1   |
    | Mengisi `jumlah_label` label kosong (algoritma E4.4)                                 | N label terupdate                              |
    | Update status PO sesuai sisa                                                         | status = 1 atau 2                              |
  And response HTTP 200 dengan ringkasan hasil

Scenario: Salah satu langkah gagal → rollback penuh
  Given submit sedang berjalan
  When langkah generate label gagal (mis. constraint violation, koneksi sumber data drop)
  Then seluruh perubahan dalam transaksi di-rollback
  And tidak ada record baru / perubahan apapun yang persist
  And status PO tidak berubah
  And backend mencatat error untuk keperluan debugging
  And user menerima pesan error "Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator."
```

**Acceptance Criteria - S-4.2.2:**
```gherkin
Feature: Konfirmasi Keberhasilan Submit Inspeksi

Scenario: Operator menerima konfirmasi sukses
  Given submit sukses (HTTP 200)
  When response diterima
  Then sistem menampilkan konfirmasi keberhasilan dengan judul "Berhasil" dan keterangan "Label Berhasil Dibuat"
  And response berisi ringkasan: processed_labels, failed_labels, remaining_labels, status ("in_progress"/"completed")
  And cetak label otomatis dijalankan (lihat E5.1)
  And form direset ke kondisi awal setelah konfirmasi & cetak terkirim
```

> **Catatan Teknis E4.2:**
> - **Business Logic:**
>   - Pemrosesan submit Cetak Label Inspeksi harus **atomic / transactional** — semua langkah berikut sukses bersama atau gagal bersama:
>     1. **Lazy-register PO** jika belum ada label apapun untuk PO ini (lihat Catatan Teknis E4.3)
>     2. **Tutup sesi inspeksi sebelumnya milik `np1`** (lihat Catatan Teknis E4.5)
>     3. **Seleksi & update `N` label kosong** (lihat Catatan Teknis E4.4)
>     4. **Update status PO** sesuai sisa label (lihat Catatan Teknis E4.6)
>   - Submit hanya dianggap sukses jika **seluruh langkah** berhasil
>   - Jika ada langkah yang melempar exception, seluruh perubahan harus di-rollback dan response error dikembalikan
>   - Backend wajib mencatat sukses dan error ke sistem logging dengan minimal: `no_po`, jumlah label diproses, sisa label, error message + trace
> - **Response Format Sukses (HTTP 200):**
>   - Body:
>     ```
>     {
>       "success": true,
>       "message": "Label berhasil diproses",
>       "data": {
>         "processed_labels": integer,
>         "failed_labels": integer,
>         "remaining_labels": integer,
>         "status": "in_progress" | "completed"
>       }
>     }
>     ```
> - **Response Format Error (HTTP 422 — Validasi):**
>   - Body: `{ "success": false, "message": "Data yang dikirim tidak valid", "errors": { "<field>": ["<pesan>"] } }`
> - **Response Format Error (HTTP 500 — System Error):**
>   - Body: `{ "success": false, "message": "Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.", "error_code": "SYSTEM_ERROR" }`

**API Contract - S-4.2.1:**
```
POST /api/print-label-inspeksi/store

Headers:
  Content-Type: application/json
  Accept: application/json
  Authorization: Bearer {token}      # rekomendasi: endpoint WAJIB ter-auth di arsitektur baru

Request Body:
{
  "no_po":         "4000000001",   // wajib
  "team":          3,              // int, harus exist di master workstation
  "jumlah_label":  10,             // int >= 1
  "np1":           "I444",         // string max 5, wajib
  "np2":           "I555",         // string max 5, opsional (boleh "" atau null)
  "no_obc":        "ABC312345",    // wajib
  "rencet":        20000           // int >= 1
}

Response 200:
{
  "success": true,
  "message": "Label berhasil diproses",
  "data": {
    "processed_labels": 10,
    "failed_labels": 0,
    "remaining_labels": 6,
    "status": "in_progress"
  }
}

Response 422 (validation):
{
  "success": false,
  "message": "Data yang dikirim tidak valid",
  "errors": {
    "np1": ["NP1 wajib diisi"],
    "team": ["Team tidak valid"]
  }
}

Response 500 (system error):
{
  "success": false,
  "message": "Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.",
  "error_code": "SYSTEM_ERROR"
}
```

---

### E4.3 - Lazy Creation PO (Jika Belum Terdaftar)

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-4.3.1 | Sebagai **sistem**, saya ingin **secara otomatis mendaftarkan PO + men-generate seluruh label rim jika PO belum pernah ada di sistem** agar **operator inspektur tidak perlu menunggu PO didaftarkan lewat flow lain terlebih dahulu** | 5 | Critical | 3 |

**Acceptance Criteria - S-4.3.1:**
```gherkin
Feature: Lazy Creation PO untuk Inspeksi

Scenario: PO sudah punya label di sistem → tidak buat ulang
  Given PO "4000000001" sudah memiliki minimal 1 label di sistem
  When backend memproses langkah lazy-create
  Then sistem TIDAK mendaftarkan ulang PO
  And TIDAK men-generate ulang label
  And langsung lanjut ke langkah berikutnya

Scenario: PO belum punya label → register + generate seluruh label
  Given PO "4000000999" belum memiliki label apapun di sistem
  And payload submit memiliki: rencet = 20000, no_obc = "ABC312345", team = 3
  When backend memproses langkah lazy-create
  Then sistem menambahkan record registrasi PO dengan:
    | Field         | Value                                            |
    | no_po         | 4000000999                                       |
    | no_obc        | ABC312345                                        |
    | type          | "PCHT" (konstan)                                 |
    | status        | 0 (akan diupdate di langkah E4.6)                |
    | sum_rim       | dihitung backend dari rencet                     |
    | start_rim     | 1                                                |
    | end_rim       | max(1, floor(rencet / 1000)) = 20                |
    | assigned_team | 3                                                |
  And sistem men-generate label rim (Kiri + Kanan) untuk rim 1..end_rim dengan np_users = NULL
  And sistem men-generate inschiet jika `rencet mod 1000 > 0`
  And total label rim regular ter-generate = (end_rim - start_rim + 1) × 2

Scenario: Lazy-create gagal untuk PO yang ternyata sudah terdaftar di registrasi PO (race condition)
  Given dua request masuk hampir bersamaan untuk PO "4000000999" yang belum terdaftar
  When backend salah satu request berhasil register, request kedua mencoba register
  Then request kedua menerima exception "Nomor PO sudah terdaftar dalam sistem"
  And transaksi request kedua di-rollback
```

> **Catatan Teknis E4.3:**
> - **Business Logic — Lazy Creation:**
>   - Sebelum memproses inspeksi, backend mengecek: "Apakah sudah ada minimal 1 label untuk PO ini?"
>     - Jika ya → lewati langkah lazy-create
>     - Jika tidak → jalankan lazy-create
>   - Lazy-create terdiri dari 2 sub-langkah berurutan:
>     1. **Registrasi PO** ke registrasi produk
>     2. **Populate label** (generate label rim + inschiet) untuk PO baru tersebut
>   - **Parameter perhitungan rim saat lazy-create:**
>     - `end_rim = max(1, floor(rencet / 1000))`
>     - `start_rim = 1`
>     - `type = "PCHT"` (konstan)
>     - `assigned_team = team` yang dikirim user
>     - `status` awal = `0` (akan diupdate ke `1` atau `2` di langkah E4.6)
>   - **Generate label rim** dijalankan dengan aturan yang sama dengan Cetak Label Personal PCHT (Phase 3 E4.3) dengan catatan:
>     - `np_users` dan `np_user_p2` di-set NULL (label akan diisi oleh `processLabels` di langkah berikutnya)
>     - `start` dan `finish` di-set NULL
>   - **Inschiet** dibuat jika `rencet mod 1000 > 0`, dengan aturan yang sama dengan Phase 3 E4.4 (kecuali `np_kiri` dan `np_kanan` boleh NULL di lazy-create)
>   - **Konstanta:** 1 rim label = **1000** lembar (untuk perhitungan `end_rim` dan threshold inschiet pada flow inspeksi)
> - **Pesan Error Standar:**
>   - "Nomor PO sudah terdaftar dalam sistem" (race condition saat lazy-create)

---

### E4.4 - Seleksi & Update Label (Algoritma "Top-Down, Kanan-First, Exclude Inschiet")

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-4.4.1 | Sebagai **sistem**, saya ingin **mengisi sejumlah `jumlah_label` label kosong dengan urutan rim tertinggi turun, sisi Kanan dulu lalu Kiri, dan tidak menyentuh label inschiet** agar **operator yang mengambil rim dari atas tumpukan mendapat label yang sesuai dan inschiet tetap terpisah dari produksi rim normal** | 8 | Critical | 3 |

**Acceptance Criteria - S-4.4.1:**
```gherkin
Feature: Seleksi & Update Label Kosong

Scenario: Pengisian label dari rim tertinggi turun, Kanan dulu
  Given PO "4000000001" memiliki label berikut dengan np_users = NULL:
    | no_rim | potongan |
    | 1      | Kiri     |
    | 1      | Kanan    |
    | 2      | Kiri     |
    | 2      | Kanan    |
    | 3      | Kiri     |
    | 3      | Kanan    |
    | 999    | Kiri     |  # inschiet
    | 999    | Kanan    |  # inschiet
  And operator submit dengan jumlah_label = 3, np1 = "I444", team = 3
  When backend menjalankan seleksi label
  Then 3 label berikut yang diupdate (dalam urutan):
    | no_rim | potongan |
    | 3      | Kanan    |
    | 3      | Kiri     |
    | 2      | Kanan    |
  And label rim 999 (inschiet) TIDAK pernah diupdate oleh proses ini
  And setiap label yang diupdate memiliki:
    | Field        | Value                                   |
    | np_users     | "I444" (uppercase)                      |
    | np_user_p2   | uppercase np2 jika ada, NULL jika kosong |
    | workstation  | team (3)                                |
    | start        | timestamp saat ini                      |
    | finish       | timestamp saat ini                      |

Scenario: Jumlah label diminta lebih dari sisa yang ada (defensive)
  Given sisa label kosong (non-inschiet) untuk PO = 5
  And operator submit dengan jumlah_label = 10 (lolos validasi frontend karena sisa saat fetch berbeda dengan saat submit)
  When backend menjalankan seleksi label
  Then sistem mengisi seluruh sisa yang tersedia (5 label)
  And processed_labels = 5
  And remaining_labels = 0
  And tidak melempar exception untuk selisih ini

Scenario: Tidak ada label kosong tersisa
  Given seluruh label PO sudah terisi (sisa = 0)
  When backend mencoba seleksi label dengan jumlah_label > 0
  Then sistem mencatat warning "No available labels found" beserta no_po
  And processed_labels = 0
  And remaining_labels = 0
  And status PO tetap diupdate ke 2 (Completed) di langkah E4.6

Scenario: Update individual label gagal — dihitung sebagai failed
  Given dari N label yang diseleksi, satu di antaranya gagal terupdate karena exception
  Then sistem TIDAK menghentikan loop — label lainnya tetap dicoba
  And failed_labels bertambah untuk yang gagal
  And processed_labels mencatat yang sukses
  And error per-label dicatat di logging dengan label_id + no_po + pesan error
  And jika seluruh langkah lain sukses, transaksi tetap di-commit
```

> **Catatan Teknis E4.4:**
> - **Business Logic — Algoritma Seleksi Label:**
>   - Query label kandidat: `no_po_generated_products = {no_po}` AND `no_rim != 999` AND `np_users IS NULL`
>   - Urutan pengambilan (deterministik): `ORDER BY no_rim DESC, potongan ASC` — artinya rim tertinggi diisi pertama, dan untuk satu rim, potongan "Kanan" diisi sebelum "Kiri" (karena `"Kanan" < "Kiri"` secara alfabetis)
>   - Batasi `LIMIT = jumlah_label`
>   - **Label inschiet (`no_rim = 999`) wajib dikecualikan** dari seleksi pada flow inspeksi normal
> - **Business Logic — Update Per Label:**
>   - Setiap label terpilih diupdate dengan field:
>     - `np_users` = `np1` di-uppercase
>     - `np_user_p2` = `np2` di-uppercase jika ada, NULL jika kosong
>     - `workstation` = `team` yang dipilih operator
>     - `start` = timestamp saat ini
>     - `finish` = timestamp saat ini
>   - **Catatan:** `start` dan `finish` di-set bersamaan ke timestamp yang sama — sesi inspeksi pada flow ini bersifat instan (bukan range waktu)
> - **Business Logic — Error Handling Per Label:**
>   - Loop update label bersifat **tolerant** — kegagalan update satu label TIDAK menghentikan loop
>   - Hitung `processed_labels` (sukses) dan `failed_labels` (gagal) secara terpisah
>   - Logging wajib mencatat: untuk warning kasus "no available labels found", dan untuk error per-label (`label_id`, `no_po`, pesan error)
> - **Output Langkah Ini:**
>   - `processed_labels`, `failed_labels`, `remaining_labels` (= hasil re-count sisa label menggunakan query yang sama dengan E2.2)

---

### E4.5 - Tutup Sesi Inspeksi Sebelumnya milik Pegawai

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-4.5.1 | Sebagai **sistem**, saya ingin **menutup seluruh sesi inspeksi sebelumnya milik operator `np1`** agar **tidak ada label terbuka yang menggantung dari sesi-sesi sebelumnya saat operator mulai sesi baru** | 3 | High | 3 |

**Acceptance Criteria - S-4.5.1:**
```gherkin
Feature: Tutup Sesi Inspeksi Sebelumnya milik Pegawai

Scenario: Sesi sebelumnya milik np1 ditutup
  Given operator "I444" memiliki 3 label dari PO lain dengan finish = NULL:
    | no_po_generated_products | no_rim | potongan | np_users | finish |
    | 4000000099               | 5      | Kiri     | I444     | NULL   |
    | 4000000099               | 5      | Kanan    | I444     | NULL   |
    | 4000000088               | 1      | Kiri     | I444     | NULL   |
  When operator "I444" memproses submit inspeksi untuk PO lain
  Then semua 3 label di atas diupdate dengan finish = timestamp saat ini
  And tidak ada label terbuka tersisa milik "I444" di seluruh sistem

Scenario: Operator lain tidak terpengaruh
  Given operator "I555" memiliki 2 label dengan finish = NULL
  When operator "I444" memproses submit
  Then label milik "I555" TIDAK berubah (finish tetap NULL)

Scenario: Tidak ada sesi terbuka milik np1 — no-op tidak melempar error
  Given operator "I444" tidak memiliki label terbuka
  When operator "I444" memproses submit
  Then langkah ini tidak melempar exception
  And transaksi tetap lanjut ke langkah berikutnya
```

> **Catatan Teknis E4.5:**
> - **Business Logic:**
>   - Sebelum mengisi label baru, sistem mencari semua record label dengan `np_users = {np1}` AND `finish IS NULL` di seluruh PO (tidak dibatasi PO yang sedang diproses)
>   - Seluruh record yang cocok diupdate: `finish = timestamp saat ini`
>   - Tujuan: memastikan satu operator tidak memiliki "sesi terbuka" yang menggantung dari PO sebelumnya saat dia memulai/melanjutkan PO baru
>   - Operasi ini **idempotent** dan **safe untuk no-op** — tidak melempar exception meskipun tidak ada record yang ter-update

---

### E4.6 - Update Status PO Dinamis (1 atau 2)

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-4.6.1 | Sebagai **sistem**, saya ingin **menandai PO sebagai "Sedang Diperiksa" (1) atau "Selesai Diperiksa" (2) berdasarkan sisa label** agar **status PO selalu mencerminkan kondisi real seberapa banyak label yang sudah terisi** | 2 | Critical | 3 |

**Acceptance Criteria - S-4.6.1:**
```gherkin
Feature: Update Status PO Berdasarkan Sisa Label

Scenario: Sisa label > 0 → status = 1 (Sedang Diperiksa)
  Given setelah pengisian, sisa label = 6
  When backend menjalankan update status
  Then field `status` pada registrasi produk untuk PO tersebut diupdate menjadi 1

Scenario: Sisa label = 0 → status = 2 (Selesai Diperiksa)
  Given setelah pengisian, sisa label = 0
  When backend menjalankan update status
  Then field `status` pada registrasi produk untuk PO tersebut diupdate menjadi 2
  And PO ini tidak lagi tampil di daftar Siap Periksa (yang memfilter status < 2)

Scenario: Status tidak berubah jika transaksi gagal di langkah sebelumnya
  Given submit sedang dalam langkah sebelum update status
  When salah satu langkah sebelumnya melempar exception
  Then field status TIDAK ter-update (rollback transaksi penuh)
```

> **Catatan Teknis E4.6:**
> - **Business Logic:**
>   - Setelah seluruh langkah sebelumnya sukses, sistem meng-update `status` pada registrasi produk untuk `no_po` tersebut berdasarkan sisa label:
>     - `remaining_labels > 0` → `status = 1` (Sedang Diperiksa / In Progress)
>     - `remaining_labels == 0` → `status = 2` (Selesai Diperiksa / Completed)
>   - **Mapping status:**
>     - `0` = Belum Diproses (initial / saat lazy-create)
>     - `1` = Sedang Diperiksa / In Progress
>     - `2` = Selesai Diperiksa / Completed
>   - Perbedaan vs Cetak Label Personal PCHT (Phase 3 E4.6): pada flow Inspeksi status **boleh berakhir di `1`** karena sesi bersifat iteratif

---

## EPIC E5: Cetak Label Otomatis & Penanganan Hasil

### E5.1 - Cetak Otomatis Tanpa Dialog Setelah Submit Sukses

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-5.1.1 | Sebagai **operator**, saya ingin **label tercetak otomatis setelah submit berhasil** agar **saya tidak perlu langkah manual tambahan untuk mencetak ke printer label** | 5 | High | 4 |
| S-5.1.2 | Sebagai **operator**, saya ingin **label dicetak dalam jumlah salinan sesuai `jumlah_label`** agar **setiap rim yang baru saja saya isi mendapat label tercetak** | 3 | High | 4 |

**Acceptance Criteria - S-5.1.1:**
```gherkin
Feature: Cetak Label Otomatis

Scenario: Cetak otomatis dipicu setelah submit berhasil
  Given submit sukses (HTTP 200)
  When response sukses diterima
  Then sistem mempersiapkan konten label
  And perintah cetak dikirim ke printer default sistem operasi user
  And dialog cetak browser TIDAK ditampilkan (cetak langsung)

Scenario: Cetak tidak dijalankan jika submit gagal
  Given submit gagal (HTTP 422 atau 500)
  When error diterima
  Then perintah cetak TIDAK dikirim
  And form tetap dalam keadaan terisi agar dapat diperbaiki / dicoba ulang
```

**Acceptance Criteria - S-5.1.2:**
```gherkin
Feature: Jumlah Salinan Label Sesuai jumlah_label

Scenario: Jumlah salinan = jumlah_label
  Given jumlah_label = 10
  When proses cetak dimulai
  Then printer menerima 10 halaman label
  And setiap halaman berisi satu label

Scenario: Setiap label berada di halaman terpisah
  Given proses cetak berjalan dengan jumlah_label > 1
  Then setiap label diakhiri dengan page-break sebelum label berikutnya
  And tidak ada dua label dalam satu halaman fisik
```

> **Catatan Teknis E5.1:**
> - **Business Logic:**
>   - Cetak hanya dijalankan setelah backend mengembalikan HTTP 200
>   - Jumlah salinan label tercetak = nilai `jumlah_label` pada form (bukan total label PO; hanya yang baru diisi pada sesi ini)
>   - Cetak harus langsung (tanpa dialog cetak browser) untuk efisiensi operator — implementasi bebas selama tidak menampilkan dialog
>   - **Timeout sebelum success message & reset form:** ditentukan oleh jumlah label cetak:
>     - Jika `jumlah_label > 10` → timeout = `round(1000 × (jumlah_label / 5))` ms
>     - Selain itu → timeout = `1000` ms
>     - Tujuan: memberi waktu printer memproses batch besar sebelum form direset
>   - Margin halaman cetak: kiri & kanan ± 3 rem, top 0 (untuk kompatibilitas printer label/sticker)
>   - Penanda visual warna pada label tercetak harus persis sesuai tampilan layar (color fidelity penting karena seri dibedakan secara visual)

---

### E5.2 - Layout & Konten Label Tercetak

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-5.2.1 | Sebagai **operator**, saya ingin **setiap label tercetak berisi OBC, NP1, NP2, jumlah lembar (500), tanggal, dan jam** agar **label dapat ditempel di rim fisik untuk traceability inspeksi** | 5 | High | 4 |

**Acceptance Criteria - S-5.2.1:**
```gherkin
Feature: Konten & Layout Label Tercetak

Scenario: Field yang tercetak pada setiap label
  Given proses cetak berjalan untuk sesi dengan:
    | Field | Value      |
    | no_obc | ABC312345 |
    | np1    | I444      |
    | np2    | I555      |
    | seri   | 3         |
  Then setiap label tercetak menampilkan field berikut:
    | Field          | Value                                                       |
    | NP Periksa 1   | I444 (uppercase, dengan penanda visual sesuai seri)         |
    | NP Periksa 2   | I555 (uppercase, dengan penanda visual sesuai seri)         |
    | OBC            | ABC312345 (dengan penanda visual sesuai seri)               |
    | Jumlah Lembar  | "500 Lbr" (konstan per label)                               |
    | Tanggal        | format "D-Bln-YYYY" Bahasa Indonesia (mis. "29-Mei-2026")   |
    | Jam            | format "H : M" (tanpa zero-padding, mis. "9 : 5")           |

Scenario: Ukuran label fisik
  Given label dicetak ke printer label
  Then setiap label berukuran 9 cm × 12 cm

Scenario: Penanda visual OBC dan NP mengikuti seri
  Given seri = 3 (no_obc[4] = "3")
  Then OBC dan NP pada label menggunakan penanda visual khusus seri 3 (dapat dibedakan dari seri lainnya)
  Given seri = 1 atau 2 (atau default)
  Then OBC dan NP menggunakan penanda visual default

Scenario: Format bulan dalam Bahasa Indonesia
  Given tanggal cetak = "2026-05-29"
  Then bulan ditampilkan sebagai "Mei"
  And contoh: "29-Mei-2026"
  And daftar nama bulan: Jan, Feb, Mar, Apr, Mei, Jun, Jul, Agu, Sep, Okt, Nov, Des
```

> **Catatan Teknis E5.2:**
> - **Business Logic — Layout Label:**
>   - Ukuran label: **9 cm × 12 cm** per halaman
>   - Setiap label berisi field berikut:
>     - NP Periksa 1 (`np1` yang sudah diformat & uppercase)
>     - NP Periksa 2 (`np2` yang sudah diformat & uppercase, boleh kosong)
>     - OBC (huruf besar dari `no_obc`)
>     - Jumlah lembar = `500` (konstan per label — bukan total lembar PO)
>     - Tanggal cetak dalam format `"D-Bln-YYYY"` (timezone Asia/Jakarta)
>     - Jam cetak dalam format `"H : M"` (24-jam, tanpa zero-padding)
>   - Format nama bulan (Bahasa Indonesia, 3 huruf): `["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"]`
>   - OBC dan NP pada label harus dapat dibedakan secara visual antar seri (terutama seri 3 vs seri lainnya)
>   - Layout label Cetak Label Inspeksi **identik** dengan Cetak Label Personal PCHT (Phase 3 E5.2) — gunakan template yang sama
> - **Business Logic — Cetak Batch:**
>   - Konten cetak berisi `jumlah_label` halaman, masing-masing dengan satu label dan page-break antar halaman
>   - Label TIDAK menggunakan barcode/QR (pure tekstual)

---

### E5.3 - Penanganan Error Submit

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-5.3.1 | Sebagai **operator**, saya ingin **menerima pesan error yang jelas jika proses submit gagal** agar **saya bisa memperbaiki input atau melaporkan masalah dan mencoba ulang** | 3 | High | 4 |

**Acceptance Criteria - S-5.3.1:**
```gherkin
Feature: Penanganan Error Submit Inspeksi

Scenario: Error validasi (HTTP 422 dengan errors per field)
  Given backend menjawab 422 dengan body { success: false, message, errors: { field: [pesan, ...] } }
  When response diterima
  Then user menerima daftar pesan error per field dalam Bahasa Indonesia
  And seluruh pesan ditampilkan sebagai daftar yang mudah dibaca
  And data form tetap terisi agar bisa diperbaiki

Scenario: Error sistem (HTTP 500)
  Given backend menjawab 500 dengan body { success: false, message: "Terjadi kesalahan sistem...", error_code: "SYSTEM_ERROR" }
  When response diterima
  Then user menerima pesan error sistem dari backend
  And data form tetap terisi
  And user diberi kesempatan mencoba ulang

Scenario: Error jaringan / response tidak terdefinisi
  Given backend tidak merespon atau response tidak terbaca
  When request gagal
  Then user menerima pesan default "Terjadi kesalahan pada server"
  And user diberi kesempatan mencoba ulang
```

> **Catatan Teknis E5.3:**
> - **Business Logic:**
>   - Frontend mem-parsing error berdasarkan tipe response:
>     - HTTP 422 → ambil seluruh `errors[field]`, flatten, tampilkan sebagai daftar pesan
>     - HTTP non-422 dengan body → ambil `message` dari body
>     - Tidak ada response → pakai default "Terjadi kesalahan pada server"
>   - Setiap error wajib ditampilkan dalam Bahasa Indonesia
>   - Data form **tidak boleh** dibersihkan saat error — operator harus bisa memperbaiki tanpa mengetik ulang seluruhnya
>   - State `isLoading` wajib dikembalikan ke `false` setelah error agar aksi submit dapat dipicu ulang
> - **Pesan Error Standar:**
>   - "Terjadi kesalahan pada server" (default jika tidak ada response detail)
>   - "Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator." (dari backend, untuk system error)

---

## Sprint Roadmap

### Sprint 1: Halaman, Input PO & Auto-Fetch (E1 + E2.1 + E2.4)
```
Sprint 1 (Week 1):
├── S-1.1.1: Akses halaman Cetak Label Inspeksi
├── S-1.1.2: Proteksi auth halaman
├── S-1.2.1: State awal form (default team, mode scan-ready)
├── S-2.1.1: Auto-fetch spesifikasi PO dari Sirine
├── S-2.1.2: Debounce 500 ms
├── S-2.1.3: Loading indicator saat fetch
├── S-2.4.1: Penanganan PO tidak ditemukan
└── Integration testing halaman & Sirine
```

### Sprint 2: Sisa Label, Spec UI & Form Input (E2.2 + E2.3 + E3.1-E3.3 + E3.5)
```
Sprint 2 (Week 2):
├── S-2.2.1: Live count sisa label (PO terdaftar)
├── S-2.2.2: Estimasi sisa label (PO belum terdaftar)
├── S-2.3.1: Penanda visual OBC berdasarkan seri
├── S-3.1.1: Field input form inspeksi
├── S-3.1.2: Default jumlah_label = sisa label
├── S-3.1.3: Enter di NP tidak memicu submit
├── S-3.2.1: Format NP pegawai (truncate ke 5 char)
├── S-3.3.1: Validasi frontend jumlah label vs sisa
├── S-3.3.2: Validasi frontend field wajib
├── S-3.5.1: Reset form
└── Integration testing form input & validasi frontend
```

### Sprint 3: Validasi Server & Submit Atomic (E3.4 + E4)
```
Sprint 3 (Week 3):
├── S-3.4.1: Pesan error validasi Bahasa Indonesia
├── S-3.4.2: Validasi server-side field payload
├── S-4.1.1: Konfirmasi sebelum submit dengan rangkuman
├── S-4.2.1: Submit atomic end-to-end
├── S-4.2.2: Konfirmasi keberhasilan submit
├── S-4.3.1: Lazy creation PO
├── S-4.4.1: Seleksi & update label (top-down, kanan-first, exclude inschiet)
├── S-4.5.1: Tutup sesi inspeksi sebelumnya milik pegawai
├── S-4.6.1: Update status PO dinamis (1 atau 2)
└── Integration testing flow submit & transactional
```

### Sprint 4: Cetak Label & Error Handling (E5 + Finalization)
```
Sprint 4 (Week 4):
├── S-5.1.1: Cetak otomatis tanpa dialog
├── S-5.1.2: Cetak multiple salinan sesuai jumlah_label
├── S-5.2.1: Layout & konten label tercetak (9×12 cm)
├── S-5.3.1: Penanganan error submit (422 & 500 & jaringan)
├── End-to-end testing (PO baru lazy-create + PO existing iteratif)
├── Edge case testing (sisa = 0, jumlah > sisa, np1 dengan sesi terbuka, dsb.)
└── Performance & regression testing
```

---

## Definition of Done (DoD)

Setiap user story dianggap **DONE** jika:

- [ ] Code sudah di-review oleh minimal 1 developer lain
- [ ] Unit tests written dan passing (coverage > 80%)
- [ ] Integration tests passing (termasuk minimal: happy path inspeksi PO existing, lazy-create PO baru, sisa label = 0, jumlah_label > sisa actual, dua operator submit PO yang sama)
- [ ] No critical/high bugs dari QA
- [ ] UI responsive (mobile + desktop) — meskipun pemakaian utama desktop dengan scanner barcode
- [ ] Performance: page load < 2s, submit response < 5s untuk `jumlah_label ≤ 50`
- [ ] Acceptance criteria terpenuhi semua
- [ ] Deployed ke staging
- [ ] Product Owner approved

---

## Success Metrics - Phase 4

| Metric | Target | Measurement |
|--------|--------|-------------|
| Submit inspeksi berhasil rate | > 97% | Total HTTP 200 / total submit |
| Waktu dari fetch PO hingga cetak terkirim | < 30 detik (untuk `jumlah_label ≤ 20`) | Rata-rata waktu user-perceived |
| Sirine API response time | < 3 detik | Rata-rata response time fetch Sirine |
| Live count sisa label akurat | 100% | Sisa yang dilaporkan = jumlah label real-time dengan `np_users IS NULL` (excl. inschiet) |
| Lazy-create PO sukses tanpa data setengah jadi | 100% | Jika lazy-create gagal di tengah, registrasi PO maupun label rim TIDAK persist |
| Konsistensi status PO | 100% | Status = 1 jika ada sisa, status = 2 jika sisa = 0 (atau status tetap saat rollback) |
| Akurasi cetak label | 100% | Jumlah halaman cetak = `jumlah_label` |
| Pengisian label oleh wrong session | 0 | Tidak ada label terisi di luar urutan algoritma (rim tertinggi turun, Kanan-first, exclude inschiet) |

---

## Risk Register

| Risk | Impact | Probability | Mitigation |
|------|--------|-------------|------------|
| Sirine API down/tidak responsif | Critical | Medium | Tampilkan pesan jelas, izinkan operator menunggu/mencoba ulang; pertimbangkan fallback ke spesifikasi cache lokal jika tersedia |
| SSL certificate Sirine expired | Critical | Low | Monitoring cert expiry + alert; jalur fetch alternatif dari backend dengan trust store terpisah |
| Endpoint submit & count-remaining-label dapat dipanggil tanpa autentikasi | High | High | Wajib lindungi seluruh endpoint inspeksi dengan autentikasi; tambahkan rate limit per user |
| Race condition: dua submit bersamaan untuk PO yang sama mengisi label yang sama | High | Medium | Lock label kandidat saat seleksi (SELECT ... FOR UPDATE atau equivalen); pastikan transaksi serializable; tambahkan unique constraint `(no_po, no_rim, potongan)` di storage label |
| Race condition: dua submit bersamaan untuk PO yang belum pernah ada → double lazy-create | High | Low | Unique constraint pada `no_po` di registrasi PO; tangani exception "Nomor PO sudah terdaftar" sebagai trigger retry tanpa lazy-create |
| Sisa label saat fetch berbeda dengan saat submit (stale data) | Medium | High | Validasi sisa di backend juga (bukan hanya frontend); jika `jumlah_label > sisa_aktual`, isi seluruh sisa & laporkan `processed_labels` apa adanya tanpa exception |
| Cetak otomatis gagal karena printer default tidak terkonfigurasi di OS user | High | Medium | Sediakan pesan fallback dengan instruksi setup printer; sediakan opsi "cetak manual" sebagai cadangan; pertimbangkan integrasi printer langsung (CUPS / native print agent) |
| Format NP terpotong tidak konsisten dengan flow lain | Medium | Medium | Sentralkan helper format NP; pastikan threshold (`> 4`) didokumentasikan & ditest; bila perlu, alignment dengan Phase 3 (`> 5`) dilakukan setelah kesepakatan business owner |
| Tutup sesi inspeksi sebelumnya menutup sesi yang masih aktif di flow Order Besar | Medium | Medium | Konfirmasi business owner: bila operator masuk ke flow inspeksi, seluruh sesi terbuka miliknya WAJIB ditutup (perilaku saat ini); dokumentasikan di SOP |
| Konstanta rim 1000 (Inspeksi) vs 500 (Order Besar/Personal) menyebabkan `sum_rim` & `end_rim` tidak sinkron saat lazy-create | High | High | Standarkan konstanta rim per produk & per konteks; saat lazy-create, gunakan satu konstanta tunggal yang konsisten antara `end_rim` dan `sum_rim`; tambahkan test untuk PO dengan `rencet` di batas seperti 500, 1000, 1500, 20000 |
| Label inschiet (rim 999) tidak sengaja terisi oleh proses inspeksi | High | Low | Filter `no_rim != 999` di query seleksi label & re-count sisa; tambahkan integration test eksplisit |
| Submit untuk PO yang `rencet ≤ 1000` di lazy-create tidak menghasilkan label rim | Medium | Medium | Definisikan perilaku eksplisit di business: apakah PO kecil seperti ini valid untuk inspeksi? Jika ya, sediakan minimal 1 rim label (sesuai `end_rim = max(1, floor(rencet/1000))`) |

---

*Document Version: 1.0*
*Author: Zulfikar Hidayatullah*
*Created: May 2026*
