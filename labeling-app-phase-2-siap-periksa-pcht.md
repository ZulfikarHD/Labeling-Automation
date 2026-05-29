# Phase 2: Siap Periksa PCHT
## Labeling App - Peruri

**Timeline:** 1 Bulan (4 Sprints)
**Sprint Duration:** 1 Minggu
**Team Size Recommendation:** 1 Backend Dev, 1 Frontend Dev, 1 QA

---

## Product Backlog Overview

### Epic Summary

| Epic ID | Epic Name | Priority | Story Points | Sprints |
|---------|-----------|----------|--------------|---------|
| E1 | Halaman Siap Periksa PCHT | Critical | 5 | 1 |
| E2 | Filter Tim & Refresh Data | Critical | 13 | 1-2 |
| E3 | List List Card Daftar Produk Siap Periksa | Critical | 18 | 2-3 |
| E4 | Status Lifecycle Produk | Critical | 10 | 2 |
| E5 | Aksi & Navigasi ke Cetak Label | High | 8 | 3 |
| E6 | API Fetch Data per Tim | High | 13 | 3-4 |

**Total Estimated:** ~75 Story Points

---

## EPIC E1: Halaman Siap Periksa PCHT

### E1.1 - Akses Halaman Siap Periksa PCHT

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-1.1.1 | Sebagai **operator**, saya ingin **mengakses halaman Siap Periksa PCHT** agar **saya bisa melihat daftar produk PO yang siap diperiksa oleh tim saya** | 3 | Critical | 1 |
| S-1.1.2 | Sebagai **operator yang belum login**, saya ingin **diarahkan ke halaman login saat mencoba akses halaman Siap Periksa PCHT** agar **akses ke data produksi tetap terlindungi** | 2 | Critical | 1 |

**Acceptance Criteria - S-1.1.1:**
```gherkin
Feature: Akses Halaman Siap Periksa PCHT

Scenario: Operator membuka halaman Siap Periksa PCHT
  Given saya sudah login sebagai operator
  When saya mengakses halaman "/siap-periksa"
  Then saya melihat halaman dengan judul "Siap Periksa"
  And halaman menampilkan List List Card pemilihan tim
  And halaman menampilkan daftar produk siap periksa untuk tim saya

Scenario: Halaman menampilkan layout terautentikasi
  Given saya berada di halaman "/siap-periksa"
  Then halaman menampilkan navigasi utama
  And menu "Siap Periksa PCHT" dalam keadaan aktif/highlighted
```

**Acceptance Criteria - S-1.1.2:**
```gherkin
Feature: Proteksi Akses Halaman Siap Periksa PCHT

Scenario: Guest mencoba akses halaman Siap Periksa PCHT
  Given saya belum login
  When saya mengakses "/siap-periksa"
  Then saya diarahkan ke halaman login ("/login")

Scenario: Session expired saat mengakses halaman
  Given session login saya sudah expired
  When saya mencoba akses "/siap-periksa"
  Then saya diarahkan ke halaman login
  And pesan "Session berakhir, silakan login kembali" ditampilkan (opsional)
```

> **Catatan Teknis E1.1:**
> - **Business Logic:**
>   - Halaman Siap Periksa PCHT hanya dapat diakses oleh user yang sudah terautentikasi
>   - User yang belum login harus diarahkan ke halaman login
>   - Judul halaman (browser tab): "Siap Periksa Pcht"

---

## EPIC E2: Filter Tim & Refresh Data

### E2.1 - Pemilihan Tim (Workstation)

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-2.1.1 | Sebagai **operator**, saya ingin **dropdown pemilihan tim sudah terisi default tim saya** agar **saya langsung melihat antrian tim sendiri tanpa perlu setup** | 3 | Critical | 1 |
| S-2.1.2 | Sebagai **operator**, saya ingin **mengganti tim yang ditampilkan dari dropdown** agar **saya bisa melihat antrian produk tim lain saat dibutuhkan** | 5 | Critical | 1 |
| S-2.1.3 | Sebagai **operator**, saya ingin **melihat skeleton loading indicator saat data sedang di-refresh** agar **saya tahu sistem sedang memproses permintaan** | 2 | High | 2 |
| S-2.1.4 | Sebagai **operator**, saya ingin **melihat pesan "Belum Ada Produk" saat tidak ada produk siap periksa** agar **saya yakin bahwa tidak ada antrian, bukan karena bug** | 3 | High | 2 |

**Acceptance Criteria - S-2.1.1:**
```gherkin
Feature: Default Tim pada Dropdown

Scenario: Dropdown tim default sesuai workstation user
  Given saya login dengan workstation_id = 3
  When saya membuka halaman "/siap-periksa"
  Then dropdown "Pilih Tim" secara default terpilih pada workstation_id = 3
  And daftar produk yang tampil adalah produk dengan assigned_team = 3

Scenario: Dropdown menampilkan semua workstation tersedia
  Given saya berada di halaman "/siap-periksa"
  Then dropdown "Pilih Tim" menampilkan seluruh workstation yang ada di master data
  And opsi diurutkan berdasarkan nama workstation (ascending)
  And setiap opsi menampilkan nama workstation sebagai label
```

**Acceptance Criteria - S-2.1.2:**
```gherkin
Feature: Ganti Tim yang Ditampilkan

Scenario: User memilih tim lain dari dropdown
  Given saya berada di halaman "/siap-periksa"
  And dropdown saat ini terpilih tim "Tim A" (id=1)
  When saya memilih tim "Tim B" (id=2) dari dropdown
  Then sistem melakukan request fetch data untuk tim id=2
  And daftar produk di-update menampilkan produk dengan assigned_team = 2
  And produk dengan status "Selesai Diperiksa" tidak ditampilkan

Scenario: Data tetap konsisten setelah ganti tim
  Given saya berada di halaman "/siap-periksa"
  When saya ganti dropdown ke tim id=2
  Then jumlah produk yang ditampilkan sama dengan jumlah produk tim 2 dengan status < "Selesai Diperiksa"
  And setiap baris menampilkan data produk yang valid untuk tim tersebut

Scenario: Refresh data otomatis saat dropdown berubah
  Given saya berada di halaman "/siap-periksa" dengan dropdown tim id=1
  When saya mengubah dropdown ke tim id=2
  Then refresh data terjadi otomatis tanpa perlu klik tombol submit
```

**Acceptance Criteria - S-2.1.3:**
```gherkin
Feature: skeleton loading indicator saat Refresh

Scenario: Skeleton Loading Overlay muncul saat fetch data
  Given saya berada di halaman "/siap-periksa"
  When saya mengubah dropdown tim
  Then Skeleton Loading Overlay ditampilkan
  And user tidak dapat berinteraksi dengan halaman selama loading

Scenario: Skeleton Loading Overlay hilang setelah data ter-update
  Given Skeleton Loading Overlay sedang ditampilkan
  When response data dari server diterima (sukses atau gagal)
  Then Skeleton Loading Overlay disembunyikan
  And daftar produk ter-update sesuai response (jika sukses)

Scenario: Skeleton Loading Overlay tetap tampil minimal durasi tertentu
  Given response server sangat cepat (< 75ms)
  Then Skeleton Loading Overlay tetap ditampilkan minimal 75ms untuk menghindari flicker
```

**Acceptance Criteria - S-2.1.4:**
```gherkin
Feature: Empty State - Belum Ada Produk

Scenario: Tim tidak memiliki produk siap periksa
  Given saya memilih tim yang tidak memiliki produk dengan status "Siap Diperiksa" atau "Sedang Diperiksa"
  When data dari server diterima
  Then halaman menampilkan empty state:
    | Element       | Content                                                       |
    | Icon          | Icon clipboard/list (ikon visual)                             |
    | Judul         | "Belum Ada Produk"                                            |
    | Deskripsi     | "Belum ada produk yang siap diperiksa untuk tim ini"          |
  And List List Card produk tidak ditampilkan

Scenario: Empty state hilang saat data tersedia
  Given empty state sedang ditampilkan
  When saya ganti ke tim yang memiliki produk
  Then empty state disembunyikan
  And List List Card produk ditampilkan dengan daftar produk yang sesuai
```

> **Catatan Teknis E2.1:**
> - **Business Logic:**
>   - Daftar tim diambil dari master data workstation, diurutkan ascending berdasarkan nama
>   - Default tim yang terpilih = workstation yang ter-assign ke user yang sedang login
>   - Perubahan tim pada dropdown harus memicu refresh data secara otomatis (tidak perlu tombol submit)
>   - skeleton loading indicator wajib ditampilkan selama proses fetch data berlangsung
>   - Untuk menghindari flicker, skeleton loading indicator ditampilkan minimal 75ms walau response sangat cepat
>   - Empty state ditampilkan ketika daftar produk untuk tim terpilih kosong (status < "Selesai Diperiksa")

---

## EPIC E3: List List Card Daftar Produk Siap Periksa

### E3.1 - Kolom & Format Data List List Card

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-3.1.1 | Sebagai **operator**, saya ingin **melihat daftar produk dalam bentuk List List Card dengan kolom lengkap** agar **saya bisa identifikasi produk yang akan saya periksa** | 5 | Critical | 2 |
| S-3.1.2 | Sebagai **operator**, saya ingin **tanggal dibuat ditampilkan dalam format Indonesia (DD-Mon-YYYY)** agar **mudah dibaca sesuai konvensi lokal** | 2 | High | 2 |
| S-3.1.3 | Sebagai **operator**, saya ingin **nomor OBC ditampilkan dengan warna berbeda berdasarkan karakter ke-5** agar **saya bisa cepat membedakan tipe produk** | 3 | Medium | 2 |
| S-3.1.4 | Sebagai **operator**, saya ingin **header List List Card jelas dan deskriptif** agar **saya tahu informasi apa yang ditampilkan di setiap kolom** | 2 | High | 2 |

**Acceptance Criteria - S-3.1.1:**
```gherkin
Feature: Kolom List List Card Daftar Produk

Scenario: List List Card menampilkan 7 kolom standar
  Given saya berada di halaman "/siap-periksa" dengan data produk
  Then List List Card menampilkan kolom berikut:
    | Kolom        | Sumber Data         | Format                             |
    | No. PO       | no_po               | text                               |
    | OBC          | no_obc              | text (dengan pewarnaan)            |
    | Jenis        | type                | text (contoh: PCHT)                |
    | No. Rim      | start_rim - end_rim | format: "{start_rim} - {end_rim}"  |
    | Status       | status              | badge dengan label & warna         |
    | Waktu Dibuat | created_at          | format: DD-Mon-YYYY                |
    | Aksi         | -                   | tombol "Pilih"                     |

Scenario: Setiap baris produk menampilkan data lengkap
  Given ada produk dengan data berikut:
    | Field      | Value          |
    | no_po      | 1234567890     |
    | no_obc     | ABC123         |
    | type       | PCHT           |
    | start_rim  | 1              |
    | end_rim    | 40             |
    | status     | 0              |
    | created_at | 2026-05-29     |
  When produk ditampilkan dalam List List Card
  Then baris menampilkan:
    | Kolom        | Display                |
    | No. PO       | 1234567890             |
    | OBC          | ABC123                 |
    | Jenis        | PCHT                   |
    | No. Rim      | 1 - 40                 |
    | Status       | badge "Siap Diperiksa" |
    | Waktu Dibuat | 29-Mei-2026            |
    | Aksi         | tombol "Pilih"         |
```

**Acceptance Criteria - S-3.1.2:**
```gherkin
Feature: Format Tanggal Indonesia

Scenario: Tanggal ditampilkan dalam format DD-Mon-YYYY Bahasa Indonesia
  Given produk memiliki created_at = "2026-05-29T08:21:00"
  When tanggal ditampilkan di kolom "Waktu Dibuat"
  Then tanggal ditampilkan sebagai "29-Mei-2026"

Scenario: List List Card mapping bulan ke nama Indonesia
  Given saya melihat berbagai tanggal di List List Card
  Then bulan diformat sebagai:
    | Bulan Angka | Nama Singkat |
    | 1           | Jan          |
    | 2           | Feb          |
    | 3           | Mar          |
    | 4           | Apr          |
    | 5           | Mei          |
    | 6           | Jun          |
    | 7           | Jul          |
    | 8           | Agu          |
    | 9           | Sep          |
    | 10          | Okt          |
    | 11          | Nov          |
    | 12          | Des          |

Scenario: Hari satu digit di-padding dengan nol
  Given produk memiliki created_at = "2026-05-05"
  Then tanggal ditampilkan sebagai "05-Mei-2026"
```

**Acceptance Criteria - S-3.1.3:**
```gherkin
Feature: Pewarnaan OBC berdasarkan Karakter ke-5

Scenario: OBC dengan karakter ke-5 = "3" ditampilkan warna merah
  Given produk memiliki no_obc = "ABC312"
  When OBC ditampilkan di List Card
  Then teks "ABC312" ditampilkan dengan warna merah

Scenario: OBC dengan karakter ke-5 selain "3" ditampilkan warna biru
  Given produk memiliki no_obc = "ABC212"
  When OBC ditampilkan di List Card
  Then teks "ABC212" ditampilkan dengan warna biru

Scenario: OBC dengan panjang kurang dari 5 karakter
  Given produk memiliki no_obc = "ABC1"
  When OBC ditampilkan di List Card
  Then teks ditampilkan dengan warna default (biru)
  And tidak terjadi error
```

**Acceptance Criteria - S-3.1.4:**
```gherkin
Feature: Header List Card Produk

Scenario: List Card memiliki judul dan deskripsi
  Given List Card produk ditampilkan (ada data)
  Then judul section: "Daftar Produk Siap Periksa"
  And subtitle: "Daftar produk yang siap untuk diperiksa oleh tim"

Scenario: Header kolom selalu visible saat scroll
  Given List Card memiliki banyak baris
  When user scroll ke bawah
  Then header kolom tetap visible (sticky header) — opsional untuk UX
```

> **Catatan Teknis E3.1:**
> - **Business Logic:**
>   - List Card menampilkan 7 kolom: No. PO, OBC, Jenis, No. Rim, Status, Waktu Dibuat, Aksi
>   - Kolom "No. Rim" menggabungkan `start_rim` dan `end_rim` dengan format "{start_rim} - {end_rim}"
>   - Kolom "Waktu Dibuat" menggunakan format Indonesia: `DD-Mon-YYYY` (contoh: `29-Mei-2026`)
>   - Mapping nama bulan Indonesia (singkat): Jan, Feb, Mar, Apr, Mei, Jun, Jul, Agu, Sep, Okt, Nov, Des
>   - Hari ditampilkan dua digit (zero-padded), contoh: `05-Mei-2026`
>   - Pewarnaan OBC: jika karakter ke-5 (index 4) dari `no_obc` adalah `"3"` → warna merah; selain itu → warna biru
>   - Edge case OBC pendek (< 5 karakter): tidak boleh menyebabkan error, default ke warna biru

---

### E3.2 - Status Badge Visual

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-3.2.1 | Sebagai **operator**, saya ingin **status produk ditampilkan sebagai badge berwarna dengan label jelas** agar **saya bisa cepat mengenali status produk** | 3 | High | 3 |
| S-3.2.2 | Sebagai **operator**, saya ingin **status "Sedang Diperiksa" memiliki indikator animasi** agar **saya bisa membedakan produk yang sedang dikerjakan dari yang belum** | 3 | Medium | 3 |

**Acceptance Criteria - S-3.2.1:**
```gherkin
Feature: Badge Status Produk

Scenario: Status code dipetakan ke label dan warna
  Given produk memiliki kolom status berisi salah satu kode berikut
  Then badge ditampilkan dengan label & skema warna:
    | Status Code | Label             | Skema Warna  | Animasi |
    | 0           | Siap Diperiksa    | Slate (abu)  | tidak   |
    | 1           | Sedang Diperiksa  | Amber (kuning) | ya    |
    | 2           | Selesai Diperiksa | Emerald (hijau) | tidak |

Scenario: Badge dengan dot indikator
  Given badge status ditampilkan
  Then badge memiliki elemen visual:
    | Element       | Description                              |
    | Dot (titik)   | Indikator bulat kecil di kiri label      |
    | Label text    | Teks status (contoh: "Siap Diperiksa")   |
    | Background    | Warna sesuai skema status                |
```

**Acceptance Criteria - S-3.2.2:**
```gherkin
Feature: Animasi Status Sedang Diperiksa

Scenario: Status "Sedang Diperiksa" memiliki dot beranimasi
  Given produk memiliki status = 1 (Sedang Diperiksa)
  When badge ditampilkan
  Then dot indikator memiliki animasi "ping" (pulse)
  And animasi berulang terus menerus untuk menarik perhatian

Scenario: Status lain tidak memiliki animasi
  Given produk memiliki status = 0 atau status = 2
  When badge ditampilkan
  Then dot indikator statis (tidak beranimasi)
```

> **Catatan Teknis E3.2:**
> - **Business Logic:**
>   - Mapping status code → label & warna:
>     - `0` → "Siap Diperiksa" → warna slate/abu → tanpa animasi
>     - `1` → "Sedang Diperiksa" → warna amber/kuning → dengan animasi pulse pada dot
>     - `2` → "Selesai Diperiksa" → warna emerald/hijau → tanpa animasi
>   - Badge wajib memiliki dot indikator visual di sebelah kiri label
>   - Animasi hanya untuk status `1` (Sedang Diperiksa) untuk menarik perhatian operator
>   - Pada halaman Siap Periksa PCHT, hanya status `0` dan `1` yang akan tampil (status `2` di-filter out)

---

## EPIC E4: Status Lifecycle Produk

### E4.1 - Filter Produk Berdasarkan Status

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-4.1.1 | Sebagai **operator**, saya ingin **hanya melihat produk dengan status "Siap Diperiksa" dan "Sedang Diperiksa"** agar **saya fokus pada produk yang masih perlu dikerjakan** | 5 | Critical | 2 |
| S-4.1.2 | Sebagai **operator**, saya ingin **produk yang sudah "Selesai Diperiksa" otomatis hilang dari daftar** agar **antrian saya tetap bersih dari pekerjaan yang sudah selesai** | 3 | Critical | 2 |
| S-4.1.3 | Sebagai **operator**, saya ingin **hanya melihat produk yang ter-assign ke tim yang saya pilih** agar **antrian tim lain tidak mengganggu** | 2 | Critical | 2 |

**Acceptance Criteria - S-4.1.1:**
```gherkin
Feature: Filter Status Aktif Saja

Scenario: Hanya status 0 dan 1 yang tampil
  Given database memiliki produk dengan status:
    | no_po | status |
    | 100   | 0      |
    | 101   | 1      |
    | 102   | 2      |
  When saya membuka halaman "/siap-periksa" dengan tim yang sesuai
  Then List Card menampilkan produk no_po = 100 dan no_po = 101
  And produk no_po = 102 TIDAK ditampilkan

Scenario: Status "Siap Diperiksa" (0) sebagai antrian baru
  Given ada produk baru dengan status = 0
  When operator membuka halaman
  Then produk tampil dengan badge "Siap Diperiksa" (slate)

Scenario: Status "Sedang Diperiksa" (1) sebagai work-in-progress
  Given ada produk yang sebagian rim-nya sudah diperiksa
  When operator membuka halaman
  Then produk tampil dengan badge "Sedang Diperiksa" (amber, animated)
```

**Acceptance Criteria - S-4.1.2:**
```gherkin
Feature: Produk Selesai Hilang dari Antrian

Scenario: Produk yang baru saja selesai diperiksa hilang dari list
  Given saya melihat produk no_po = 100 dengan status "Sedang Diperiksa"
  When seluruh rim produk tersebut selesai diperiksa
  And status produk berubah menjadi "Selesai Diperiksa" (2)
  And saya refresh halaman atau ganti tim
  Then produk no_po = 100 tidak lagi muncul di daftar antrian

Scenario: Refresh data via dropdown me-refresh status terbaru
  Given produk no_po = 100 sudah selesai diperiksa di session lain
  When saya mengubah dropdown tim ke tim yang sama (re-trigger fetch)
  Then daftar produk di-update dan no_po = 100 tidak tampil
```

**Acceptance Criteria - S-4.1.3:**
```gherkin
Feature: Filter Berdasarkan Tim

Scenario: Hanya produk untuk tim terpilih yang tampil
  Given database memiliki produk:
    | no_po | assigned_team | status |
    | 200   | 1             | 0      |
    | 201   | 2             | 0      |
    | 202   | 1             | 1      |
  When saya pilih tim id = 1 dari dropdown
  Then List Card menampilkan produk no_po = 200 dan no_po = 202
  And produk no_po = 201 (tim 2) TIDAK ditampilkan

Scenario: Ganti tim me-refresh daftar
  Given saya melihat daftar produk untuk tim id = 1
  When saya ganti dropdown ke tim id = 2
  Then daftar produk di-update menampilkan hanya produk dengan assigned_team = 2
```

> **Catatan Teknis E4.1:**
> - **Business Logic (Filter Produk):**
>   - Halaman Siap Periksa PCHT hanya menampilkan produk dengan **status < "Selesai Diperiksa"** (yaitu kode `0` dan `1`)
>   - Produk dengan status `2` (Selesai Diperiksa) **wajib di-exclude** dari hasil query
>   - Filter tambahan: `assigned_team` harus sama dengan tim yang dipilih pada dropdown
>   - Saat halaman pertama dibuka, filter tim default = workstation yang ter-assign ke user login
> - **Status Code Reference:**
>   - `0` = "Siap Diperiksa" (PO baru terdaftar, belum ada rim yang diperiksa)
>   - `1` = "Sedang Diperiksa" (sebagian rim sudah diperiksa, masih ada yang belum)
>   - `2` = "Selesai Diperiksa" (semua rim sudah diperiksa — di-exclude dari halaman ini)
> - **Transisi Status (referensi):**
>   - `0 → 1`: ketika operator mulai memeriksa rim pertama dari sebuah PO
>   - `1 → 2`: ketika seluruh rim PO sudah diperiksa oleh operator
>   - `2 → 1`: ketika ada rim tambahan didaftarkan ke PO yang sudah selesai (re-open)

---

## EPIC E5: Aksi & Navigasi ke Cetak Label

### E5.1 - Tombol Pilih per Baris

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-5.1.1 | Sebagai **operator**, saya ingin **mengklik tombol "Pilih" pada baris produk** agar **saya bisa langsung lanjut ke halaman cetak label untuk produk tersebut** | 5 | Critical | 3 |
| S-5.1.2 | Sebagai **operator**, saya ingin **tombol "Pilih" jelas dan mudah diakses** agar **alur kerja saya cepat tanpa bingung mencari aksi** | 3 | High | 3 |

**Acceptance Criteria - S-5.1.1:**
```gherkin
Feature: Aksi Pilih Produk

Scenario: Klik tombol "Pilih" navigasi ke halaman cetak label
  Given saya berada di halaman "/siap-periksa"
  And ada produk dengan no_po = 1234567890 dan tim id = 3
  When saya klik tombol "Pilih" pada baris produk tersebut
  Then saya diarahkan ke halaman Cetak Label untuk produk tersebut
  And parameter yang dibawa adalah: tim = 3, id produk = ID database produk

Scenario: Tombol Pilih membawa konteks tim yang dipilih
  Given saya memilih tim id = 5 dari dropdown
  When saya klik "Pilih" pada salah satu produk
  Then navigasi membawa konteks tim id = 5 (bukan tim default user)

Scenario: Klik Pilih untuk produk dengan status Sedang Diperiksa
  Given produk no_po = 999 memiliki status "Sedang Diperiksa"
  When saya klik tombol "Pilih"
  Then saya diarahkan ke halaman Cetak Label untuk lanjut proses pemeriksaan
  And data label yang sudah ada (rim yang sudah diperiksa) tetap visible di halaman tujuan
```

**Acceptance Criteria - S-5.1.2:**
```gherkin
Feature: Visual & UX Tombol Pilih

Scenario: Tombol Pilih memiliki visual primary
  Given List Card menampilkan baris produk
  Then tombol "Pilih" memiliki:
    | Properti      | Value                       |
    | Label         | "Pilih"                     |
    | Variant       | primary                     |
    | Size          | small                       |
    | Icon          | arrow-right (panah kanan)   |
    | Icon position | right (di kanan label)      |

Scenario: Tombol Pilih ditampilkan di kolom Aksi
  Given List Card produk ditampilkan
  Then kolom paling kanan (kolom "Aksi") berisi tombol "Pilih"
  And tombol hanya muncul pada baris dengan produk valid
```

> **Catatan Teknis E5.1:**
> - **Business Logic:**
>   - Setiap baris produk memiliki tombol aksi "Pilih"
>   - Klik tombol "Pilih" memicu navigasi ke halaman Cetak Label PCHT
>   - Parameter yang dibawa ke halaman tujuan:
>     - `team` = ID tim yang sedang dipilih pada dropdown (bukan tim default user, karena user bisa berpindah-pindah konteks tim)
>     - `id` = ID database produk (primary key, bukan no_po)
>   - Navigasi harus menggunakan full page navigation (bukan modal) karena halaman Cetak Label adalah workflow terpisah dengan state-nya sendiri
> - **UX Standar:**
>   - Tombol "Pilih" wajib menggunakan variant `primary` dan ukuran `small`
>   - Icon panah kanan (arrow-right) di kanan label untuk menunjukkan navigasi maju

---

### E5.2 - Navigasi dari Menu Utama

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-5.2.1 | Sebagai **operator**, saya ingin **mengakses halaman Siap Periksa PCHT dari menu navigasi utama** agar **saya bisa berpindah dari halaman lain dengan cepat** | 3 | High | 3 |

**Acceptance Criteria - S-5.2.1:**
```gherkin
Feature: Akses dari Menu Navigasi Utama

Scenario: Menu "Siap Periksa PCHT" tersedia di navigasi utama
  Given saya berada di halaman lain (contoh: "/register-po-pcht")
  Then navigasi utama menampilkan menu "Siap Periksa PCHT" dengan icon dokumen
  When saya klik menu "Siap Periksa PCHT"
  Then saya diarahkan ke halaman "/siap-periksa"

Scenario: Menu aktif di-highlight saat halaman aktif
  Given saya berada di halaman "/siap-periksa"
  Then menu "Siap Periksa PCHT" pada navigasi memiliki visual aktif (highlight/bold)
  And menu lain tidak ter-highlight
```

> **Catatan Teknis E5.2:**
> - **Business Logic:**
>   - Menu "Siap Periksa PCHT" wajib ditampilkan di navigasi utama untuk semua user yang sudah login
>   - Label menu: "Siap Periksa PCHT" (atau setara, contoh: "Siap Periksa")
>   - Icon menu: representasi dokumen/check (visual)
>   - Menu di-highlight saat user berada di halaman terkait
>   - Posisi menu pada navigasi utama: di antara menu "Register Nomor PO" dan menu "Cetak Label" (urutan flow workflow)

---

## EPIC E6: API Fetch Data per Tim

### E6.1 - Endpoint Fetch Daftar Produk per Tim

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-6.1.1 | Sebagai **frontend**, saya ingin **endpoint API untuk fetch daftar produk siap periksa berdasarkan tim** agar **dropdown filter bisa refresh data tanpa reload halaman** | 5 | Critical | 3 |
| S-6.1.2 | Sebagai **frontend**, saya ingin **endpoint mengembalikan hanya produk dengan status < "Selesai Diperiksa"** agar **filter status konsisten antara initial load dan refresh** | 3 | Critical | 3 |
| S-6.1.3 | Sebagai **sistem**, saya ingin **endpoint API dilindungi autentikasi** agar **data produksi tidak bisa diakses oleh user yang belum login** | 5 | Critical | 4 |

**Acceptance Criteria - S-6.1.1:**
```gherkin
Feature: Endpoint Fetch Produk per Tim

Scenario: Frontend request data untuk tim spesifik
  Given user berada di halaman "/siap-periksa"
  When user mengubah dropdown tim ke tim id = 5
  Then frontend mengirim request GET ke endpoint fetch produk dengan parameter tim = 5

Scenario: Response berisi array produk
  Given request fetch ke endpoint dengan tim = 5
  When backend memproses request
  Then response berupa JSON array berisi produk dengan field minimal:
    | Field         | Type    | Keterangan                        |
    | id            | integer | ID database produk                |
    | no_po         | integer | Nomor PO                          |
    | no_obc        | string  | Nomor OBC                         |
    | type          | string  | Jenis produk (contoh: PCHT)       |
    | sum_rim       | integer | Total rim                         |
    | start_rim     | integer | Rim awal                          |
    | end_rim       | integer | Rim akhir                         |
    | assigned_team | integer | ID tim yang ter-assign            |
    | status        | integer | 0, 1, atau 2                      |
    | created_at    | datetime| Timestamp pembuatan               |

Scenario: Response kosong saat tim tidak punya produk
  Given tim id = 5 tidak memiliki produk dengan status < 2
  When request dikirim
  Then response berupa array kosong: `[]`
  And HTTP status: 200
```

**Acceptance Criteria - S-6.1.2:**
```gherkin
Feature: Filter Status pada Endpoint

Scenario: Endpoint hanya return produk dengan status 0 atau 1
  Given tim id = 5 memiliki produk dengan status:
    | no_po | status |
    | 300   | 0      |
    | 301   | 1      |
    | 302   | 2      |
  When frontend request fetch untuk tim = 5
  Then response berisi produk no_po = 300 dan no_po = 301
  And produk no_po = 302 TIDAK ada di response

Scenario: Filter status konsisten antara initial load dan API refresh
  Given initial page load menampilkan 5 produk untuk tim default user
  When user ganti dropdown ke tim yang sama (re-trigger fetch via API)
  Then response API mengembalikan 5 produk yang sama (assuming no status changed)
  And filter logic identik (status < 2 + assigned_team match)
```

**Acceptance Criteria - S-6.1.3:**
```gherkin
Feature: Proteksi Endpoint dengan Autentikasi

Scenario: Request tanpa autentikasi ditolak
  Given saya tidak memiliki session login valid
  When saya kirim request GET ke endpoint fetch produk per tim
  Then response HTTP status: 401 Unauthorized
  And body response: `{ "message": "Unauthenticated." }`
  And tidak ada data produk yang ter-expose

Scenario: Request dengan autentikasi valid berhasil
  Given saya login sebagai user valid
  When saya kirim request GET ke endpoint fetch produk per tim
  Then response HTTP status: 200
  And body response berisi array produk sesuai filter

Scenario: User hanya bisa akses data untuk tim valid
  Given saya login sebagai operator
  When saya request data untuk tim id yang tidak ada di master workstation
  Then response berupa array kosong atau HTTP 404
  And tidak terjadi error 500
```

> **Catatan Teknis E6.1:**
> - **Business Logic:**
>   - Endpoint menerima parameter `team` (integer, ID workstation)
>   - Endpoint mengembalikan seluruh produk dengan kondisi:
>     - `assigned_team = {team_param}`
>     - `status < 2` (hanya status "Siap Diperiksa" dan "Sedang Diperiksa")
>   - Response berupa JSON array (kosong `[]` jika tidak ada data)
>   - Endpoint **wajib dilindungi autentikasi** — request tanpa session/token valid harus ditolak
>   - Parameter `team` harus divalidasi sebagai integer; nilai non-integer harus menghasilkan error 422
> - **Response Format Sukses:**
>   - HTTP status: 200
>   - Body: `[{ id, no_po, no_obc, type, sum_rim, start_rim, end_rim, assigned_team, status, created_at, updated_at }, ...]`
> - **Response Format Error:**
>   - Unauthorized: HTTP 401, body `{ "message": "Unauthenticated." }`
>   - Validation error: HTTP 422, body `{ "message": "Validasi gagal", "errors": { "team": ["..."] } }`
> - **Catatan Keamanan (Issue Lama):**
>   - Pada implementasi lama, endpoint ini **TIDAK** terlindungi autentikasi — issue ini wajib diperbaiki di rebuild

---

**API Contract - S-6.1.1:**
```
GET /api/siap-periksa/fetch/{team}

Headers:
  Authorization: Bearer {token}        (atau session cookie web)
  Accept: application/json

Path Parameters:
  team: integer (required, ID workstation)

Response 200:
[
  {
    "id": 1,
    "no_po": 1234567890,
    "no_obc": "ABC123",
    "type": "PCHT",
    "sum_rim": 40,
    "start_rim": 1,
    "end_rim": 40,
    "assigned_team": 3,
    "status": 0,
    "created_at": "2026-05-29T08:21:00.000000Z",
    "updated_at": "2026-05-29T08:21:00.000000Z"
  }
]

Response 401:
{
  "message": "Unauthenticated."
}

Response 422:
{
  "message": "Validasi gagal",
  "errors": {
    "team": ["Parameter tim harus berupa integer."]
  }
}
```

---

## Sprint Roadmap

### Sprint 1: Halaman & Pemilihan Tim (E1 + E2.1)
```
Sprint 1 (Week 1):
├── S-1.1.1: Akses halaman Siap Periksa PCHT
├── S-1.1.2: Halaman utama setelah login
├── S-1.1.3: Proteksi halaman (redirect login)
├── S-2.1.1: Default tim pada dropdown
├── S-2.1.2: Ganti tim me-refresh data
└── Integration testing akses halaman & filter tim
```

### Sprint 2: List Card & Status Filter (E2.1 + E3.1 + E4.1)
```
Sprint 2 (Week 2):
├── S-2.1.3: skeleton loading indicator
├── S-2.1.4: Empty state "Belum Ada Produk"
├── S-3.1.1: Kolom List Card daftar produk
├── S-3.1.2: Format tanggal Indonesia
├── S-3.1.3: Pewarnaan OBC
├── S-3.1.4: Header List Card
├── S-4.1.1: Filter status < "Selesai Diperiksa"
├── S-4.1.2: Produk selesai hilang dari daftar
├── S-4.1.3: Filter berdasarkan tim
└── Integration testing List Card & filter
```

### Sprint 3: Badge, Aksi & API (E3.2 + E5 + E6.1)
```
Sprint 3 (Week 3):
├── S-3.2.1: Status badge berwarna
├── S-3.2.2: Animasi status "Sedang Diperiksa"
├── S-5.1.1: Klik "Pilih" navigasi ke Cetak Label
├── S-5.1.2: UX tombol Pilih
├── S-5.2.1: Menu navigasi utama
├── S-6.1.1: Endpoint fetch produk per tim
├── S-6.1.2: Filter status pada endpoint
└── Integration testing aksi & API
```

### Sprint 4: Security & Finalisasi (E6.1 + QA)
```
Sprint 4 (Week 4):
├── S-6.1.3: Proteksi endpoint dengan autentikasi
├── End-to-end testing (login → filter tim → pilih produk → cetak label)
├── Edge case testing (empty state, tim tanpa produk, status transition)
├── Security testing (unauthorized access, parameter injection)
└── Performance & regression testing
```

---

## Definition of Done (DoD)

Setiap user story dianggap **DONE** jika:

- [ ] Code sudah di-review oleh minimal 1 developer lain
- [ ] Unit tests written dan passing (coverage > 80%)
- [ ] Integration tests passing
- [ ] No critical/high bugs dari QA
- [ ] UI responsive (mobile + desktop)
- [ ] Performance: page load < 2s, API refresh < 1s
- [ ] Acceptance criteria terpenuhi semua
- [ ] Deployed ke staging
- [ ] Product Owner approved

---

## Success Metrics - Phase 2

| Metric | Target | Measurement |
|--------|--------|-------------|
| Waktu load halaman Siap Periksa | < 2 detik | Rata-rata waktu render initial load |
| Waktu refresh data per tim | < 1 detik | Rata-rata waktu response API fetch produk |
| Akurasi filter status | 100% | Tidak ada produk status "Selesai Diperiksa" yang tampil di list |
| Akurasi filter tim | 100% | Hanya produk dengan assigned_team yang dipilih yang tampil |
| Endpoint API terlindungi auth | 100% | Request tanpa autentikasi ditolak (HTTP 401) |
| Empty state visibility rate | > 95% | User melaporkan pesan "Belum Ada Produk" jelas saat kosong |
| Navigasi ke Cetak Label sukses | > 99% | Tidak ada error 404/500 saat klik tombol "Pilih" |

---

## Risk Register

| Risk | Impact | Probability | Mitigation |
|------|--------|-------------|------------|
| Endpoint API fetch produk per tim dapat diakses tanpa autentikasi | Critical | High | Wajib lindungi endpoint dengan layer autentikasi (session/token); tambahkan integration test untuk skenario unauthenticated |
| Parameter `team` tidak divalidasi sebagai integer dapat memicu error 500 | High | Medium | Validasi tipe data parameter di backend; return 422 untuk input invalid |
| User dapat melihat antrian tim lain (cross-team visibility) | Medium | High | By design (operator boleh lihat tim lain); namun perlu audit log jika sensitif, atau batasi via role-based access |
| Produk dengan status "Selesai Diperiksa" muncul di list akibat bug filter | High | Low | Unit test untuk query filter; assertion explicit pada response API & initial load |
| Loading state stuck saat API timeout | Medium | Medium | Timeout handler di frontend (max 10 detik), tampilkan pesan error dan tombol retry |
| Format tanggal tidak sesuai locale Indonesia di environment server berbeda | Low | Medium | Hardcode mapping bulan Indonesia di frontend, jangan gantungkan pada server locale |
| OBC pewarnaan error untuk nilai dengan panjang < 5 karakter | Low | Low | Defensive coding (cek panjang sebelum akses index ke-5), unit test untuk edge case |
| Migrasi URL dari `/` ke `/siap-periksa` membingungkan user lama | Medium | Medium | Setup redirect `/` → `/siap-periksa`, sosialisasi perubahan URL ke user |

---

*Document Version: 1.0*
*Author: Zulfikar Hidayatullah*
*Created: May 2026*
