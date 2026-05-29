# Phase 5: Cetak Label MMEA
## Labeling App - Peruri

**Timeline:** ± 1 Bulan (5 Sprints)
**Sprint Duration:** 1 Minggu
**Team Size Recommendation:** 1 Backend Dev, 1 Frontend Dev, 1 QA

---

## Konteks Fitur

Fitur **Cetak Label MMEA** (URL: `/print-label-mmea`) digunakan operator untuk mencetak label produk **MMEA** (Minuman Mengandung Etil Alkohol) — beserta variannya **HPTL**. Operator memindai/mengetik nomor PO, sistem otomatis menarik spesifikasi order dari sumber data eksternal **Sirine (endpoint MMEA)**, menggabungkannya dengan data QC yang sudah ada (jika PO pernah diproses), lalu menampilkan daftar **rim** untuk diisi data pemeriksa dan jumlah lembar per rim. Operator dapat mencetak seluruh rim sekaligus (**batch**) atau satu rim saja (**satuan**).

**Alur singkat:**

1. Operator memindai/mengetik nomor PO → setelah jeda mengetik (debounce), sistem otomatis (a) menarik spesifikasi PO dari sumber data eksternal Sirine MMEA, (b) menarik data QC label yang sudah pernah tersimpan untuk PO tersebut, lalu (c) menghitung jumlah rim/label dan mengisi awal nilai tiap rim.
2. Untuk tiap rim, operator mengisi **NP Periksa 1** (wajib), **NP Periksa 2** (wajib), dan **Lbr Kirim** (jumlah lembar per rim, default 300). Operator dapat menambah/menghapus baris rim, serta memilih **Mode Cetak** (Periksa 1 & 2 / Periksa 1 saja / Periksa 2 saja).
3. Setelah konfirmasi, sistem menyimpan **spesifikasi produk** (upsert berdasarkan no_po) lalu menyimpan **data label per rim** (upsert berdasarkan kombinasi nomor PO + nomor rim), dengan logika penyetelan ulang timestamp pemeriksa hanya jika NP pemeriksa berubah.
4. Setelah penyimpanan sukses, sistem mencetak label (jumlah halaman = jumlah rim yang dicetak) tanpa menampilkan dialog cetak browser. Untuk cetak batch, form direset agar siap untuk PO berikutnya; untuk cetak satuan, form dibiarkan untuk melanjutkan rim lain.

**Perbedaan utama vs Cetak Label PCHT (Phase 3) & Inspeksi (Phase 4):**

| Aspek | PCHT / Inspeksi | MMEA (Phase 5) |
|---|---|---|
| Sumber spesifikasi | Sirine endpoint PCHT | Sirine endpoint **MMEA** (terpisah) |
| Konstanta lembar per rim | 500 (Order Besar/Personal) / 1000 (Inspeksi) | **300** |
| Penanda visual seri / warna | Ada (seri 1/2/3 dibedakan visual) | **Tidak ada seri** — satu warna aksen tunggal untuk OBC & NP |
| Algoritma label | Generate seluruh rim, sisi Kiri/Kanan, inschiet | **Per-rim sederhana** (tanpa potongan Kiri/Kanan, tanpa inschiet) |
| Field per rim | OBC, NP, dsb. | **NP Periksa 1, NP Periksa 2, Lbr Kirim** per nomor rim |
| Mode cetak selektif | Tidak ada | Ada (**both / p1_only / p2_only**) |
| Cetak satuan per item | Tidak | Ada (cetak per rim) |
| Tipe produk | PCHT | **MMEA** atau **HPTL** |
| Status PO setelah simpan | dinamis (0/1/2) | selalu **2 (Selesai)** |
| Pre-fill data QC tersimpan | Tidak relevan | **Ya** (PO yang pernah diproses dimuat kembali untuk diedit/lanjut cetak) |

---

## Product Backlog Overview

### Epic Summary

| Epic ID | Epic Name | Priority | Story Points | Sprints |
|---------|-----------|----------|--------------|---------|
| E1 | Halaman Cetak Label MMEA & Mode Cetak | Critical | 13 | 1 |
| E2 | Input PO & Auto-Fetch Spesifikasi MMEA | Critical | 16 | 1-2 |
| E3 | Inisialisasi Rim & Merge Data QC | Critical | 13 | 2 |
| E4 | Input Form Per-Rim & Manajemen Baris | Critical | 16 | 2-3 |
| E5 | Submit & Penyimpanan Data | Critical | 24 | 3-4 |
| E6 | Cetak Label MMEA (Batch & Satuan) | High | 19 | 4-5 |

**Total Estimated:** ~101 Story Points

---

## EPIC E1: Halaman Cetak Label MMEA & Mode Cetak

### E1.1 - Akses Halaman & Proteksi Auth

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-1.1.1 | Sebagai **operator MMEA**, saya ingin **mengakses halaman Cetak Label MMEA** agar **saya bisa mencetak label rim untuk PO MMEA/HPTL** | 3 | Critical | 1 |
| S-1.1.2 | Sebagai **operator yang belum login**, saya ingin **diarahkan ke halaman login saat mencoba akses halaman Cetak Label MMEA** agar **akses ke pencetakan label tetap terlindungi** | 2 | Critical | 1 |

**Acceptance Criteria - S-1.1.1:**
```gherkin
Feature: Akses Halaman Cetak Label MMEA

Scenario: Operator membuka halaman Cetak Label MMEA
  Given saya sudah login sebagai operator
  When saya mengakses halaman "/print-label-mmea"
  Then saya melihat halaman cetak label MMEA
  And halaman menampilkan bagian "Scan Nomor PO" untuk input nomor PO
  And halaman menampilkan pilihan "Mode Cetak"
  And halaman menampilkan area spesifikasi produk dan daftar rim untuk diisi

Scenario: Halaman dapat diakses dari menu navigasi utama
  Given saya berada di halaman manapun setelah login
  When saya membuka menu navigasi yang relevan
  Then opsi "Cetak Label MMEA" tersedia dan dapat diakses
  And saat opsi dipilih saya dibawa ke "/print-label-mmea"
```

**Acceptance Criteria - S-1.1.2:**
```gherkin
Feature: Proteksi Akses Halaman Cetak Label MMEA

Scenario: Pengunjung yang belum login mencoba akses
  Given saya belum login
  When saya mengakses "/print-label-mmea"
  Then saya diarahkan ke halaman login

Scenario: Session expired saat mengakses halaman
  Given session login saya sudah expired
  When saya mencoba akses "/print-label-mmea"
  Then saya diarahkan ke halaman login
```

> **Catatan Teknis E1.1:**
> - **Business Logic:**
>   - Halaman Cetak Label MMEA hanya dapat diakses oleh user yang sudah terautentikasi
>   - User yang belum terautentikasi harus diarahkan ke halaman login
>   - Halaman ini tidak memerlukan role khusus — semua operator yang terautentikasi dapat memakainya

---

### E1.2 - State Awal Form, Mode Scan-Ready & Pre-fill dari Parameter URL

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-1.2.1 | Sebagai **operator**, saya ingin **form ditampilkan kosong dengan fokus langsung di field nomor PO dan field rim terkunci sampai spesifikasi tersedia** agar **saya bisa langsung memindai/mengetik PO tanpa interaksi tambahan** | 3 | Critical | 1 |
| S-1.2.2 | Sebagai **operator**, saya ingin **halaman bisa dibuka dengan nomor PO yang sudah terisi dari parameter URL dan langsung memuat datanya** agar **saya bisa membuka PO tertentu langsung dari tautan/daftar lain** | 3 | High | 1 |

**Acceptance Criteria - S-1.2.1:**
```gherkin
Feature: State Awal Form Cetak Label MMEA

Scenario: Form ditampilkan dengan nilai default saat halaman dimuat tanpa parameter PO
  Given saya login sebagai operator
  When saya membuka halaman "/print-label-mmea" tanpa nomor PO
  Then form menampilkan field berikut dengan kondisi awal:
    | Field          | Default Value | Status saat awal |
    | no_po          | kosong        | aktif & menerima fokus otomatis |
    | Mode Cetak     | "Periksa 1 & Periksa 2" | dapat diubah |
    | Nomor Rim (rim 1) | 1          | read-only, tidak dapat diubah sebelum spesifikasi tersedia |
    | Lbr Kirim      | 300           | tidak dapat diubah sebelum spesifikasi tersedia |
    | Periksa 1      | kosong        | tidak dapat diubah sebelum spesifikasi tersedia |
    | Periksa 2      | kosong        | tidak dapat diubah sebelum spesifikasi tersedia |
  And user dapat langsung mulai mengetik/memindai nomor PO tanpa harus memilih field terlebih dahulu
  And aksi "Cetak Label" berada dalam kondisi tidak dapat dipicu

Scenario: Field rim terkunci sampai spesifikasi PO tersedia
  Given saya baru membuka halaman dan belum ada spesifikasi PO yang tertarik
  Then field Nomor Rim, Lbr Kirim, Periksa 1, dan Periksa 2 berada dalam kondisi tidak dapat diubah
  And aksi tambah/hapus baris rim tidak dapat dipicu
```

**Acceptance Criteria - S-1.2.2:**
```gherkin
Feature: Pre-fill Nomor PO dari Parameter URL

Scenario: Halaman dibuka dengan nomor PO pada URL
  Given saya membuka "/print-label-mmea/5000000001"
  When halaman selesai dimuat
  Then field nomor PO terisi otomatis dengan "5000000001"
  And sistem otomatis menjalankan pengambilan spesifikasi & data QC untuk PO tersebut tanpa saya perlu mengetik ulang

Scenario: Nilai PO pada URL berubah saat navigasi
  Given saya sedang berada di halaman dengan PO "5000000001"
  When saya bernavigasi ke halaman yang sama dengan PO berbeda "5000000002"
  Then field nomor PO diperbarui menjadi "5000000002"
  And sistem menarik ulang spesifikasi & data QC untuk PO baru tersebut
```

> **Catatan Teknis E1.2:**
> - **Business Logic:**
>   - Halaman wajib memberikan fokus otomatis ke field `no_po` saat dimuat (mode "scan-first")
>   - Seluruh field rim (Nomor Rim, Lbr Kirim, Periksa 1, Periksa 2) dan aksi cetak/tambah/hapus baris hanya aktif setelah spesifikasi PO berhasil tersedia (`no_obc` terisi)
>   - Nilai Lbr Kirim default tiap rim = **300**
>   - Nomor PO dapat diterima sebagai **parameter opsional** dari URL; jika ada, halaman langsung menjalankan pengambilan spesifikasi & data QC secara otomatis
>   - Perubahan nilai nomor PO yang berasal dari navigasi juga memicu pengambilan ulang data

---

### E1.3 - Pemilihan Mode Cetak

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-1.3.1 | Sebagai **operator**, saya ingin **memilih mode cetak (Periksa 1 & 2, hanya Periksa 1, atau hanya Periksa 2)** agar **saya bisa mencetak ulang hanya untuk pemeriksa tertentu sesuai kebutuhan** | 2 | High | 1 |

**Acceptance Criteria - S-1.3.1:**
```gherkin
Feature: Pemilihan Mode Cetak

Scenario: Daftar mode cetak tersedia
  Given saya berada di halaman Cetak Label MMEA
  Then user dapat memilih mode cetak dari pilihan yang tersedia:
    | Mode      | Arti                                            |
    | both      | Cetak NP Periksa 1 dan NP Periksa 2 pada label  |
    | p1_only   | Cetak hanya NP Periksa 1 (Periksa 2 dikosongkan)|
    | p2_only   | Cetak hanya NP Periksa 2 (Periksa 1 dikosongkan)|
  And mode default adalah "Periksa 1 & Periksa 2" (both)

Scenario: Mode cetak mempengaruhi NP yang tampil di label tercetak
  Given saya memilih mode cetak "p1_only"
  When saya mencetak label
  Then setiap label tercetak menampilkan NP Periksa 1
  And NP Periksa 2 dikosongkan pada label tercetak
```

> **Catatan Teknis E1.3:**
> - **Business Logic:**
>   - Mode cetak memiliki 3 nilai: `both` (default), `p1_only`, `p2_only`
>   - `p2_only` → NP Periksa 1 dikosongkan pada label; `p1_only` → NP Periksa 2 dikosongkan pada label; `both` → keduanya tercetak
>   - Mode cetak hanya mempengaruhi **konten label tercetak**, tidak mempengaruhi data pemeriksa yang disimpan

---

## EPIC E2: Input PO & Auto-Fetch Spesifikasi MMEA

### E2.1 - Input Nomor PO (Scan-Friendly) & Debounced Auto-Fetch ke Sirine MMEA

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-2.1.1 | Sebagai **operator**, saya ingin **mengetik atau memindai nomor PO dan sistem otomatis mengambil spesifikasi MMEA dari Sirine** agar **saya tidak perlu menginput data spesifikasi PO secara manual** | 5 | Critical | 1 |
| S-2.1.2 | Sebagai **operator**, saya ingin **fetch spesifikasi dilakukan setelah saya berhenti mengetik (debounce)** agar **tidak terjadi request berlebihan ke Sirine saat saya sedang mengetik** | 3 | High | 1 |
| S-2.1.3 | Sebagai **operator**, saya ingin **melihat indikator proses saat data sedang diambil** agar **saya tahu sistem sedang bekerja** | 2 | High | 1 |

**Acceptance Criteria - S-2.1.1:**
```gherkin
Feature: Auto-Fetch Spesifikasi MMEA dari Sirine

Scenario: PO valid memicu auto-fill spesifikasi
  Given saya berada di halaman Cetak Label MMEA
  When saya memasukkan nomor PO "5000000001"
  And sumber data MMEA mengembalikan data dengan jenis="MMEA", no_obc="TST010110", rencet=1500, jml_order=1500
  Then sistem menampilkan informasi spesifikasi produk yang terdiri dari:
    | Informasi      | Nilai      |
    | Produk         | MMEA       |
    | No OBC         | TST010110  |
    | Nomor Plat     | (tidak tersedia dari order — kosong) |
    | Lembar Cetak   | 1500       |
  And field rim (Nomor Rim, Lbr Kirim, Periksa 1, Periksa 2) menjadi aktif
  And daftar rim terisi sesuai hasil perhitungan (lihat E3)

Scenario: Mengganti PO mereset state spesifikasi sebelumnya
  Given saya sudah berhasil menarik spesifikasi PO sebelumnya
  When saya mengganti nilai nomor PO
  Then pesan error PO sebelumnya (jika ada) dibersihkan
  And sistem akan menjalankan pengambilan ulang (mengikuti aturan debounce)
```

**Acceptance Criteria - S-2.1.2:**
```gherkin
Feature: Debounce Auto-Fetch ke Sirine MMEA

Scenario: Auto-fetch hanya dijalankan setelah user berhenti mengetik
  Given saya sedang mengetik nomor PO di field
  When saya mengetik karakter dengan jeda < 500 ms antar karakter
  Then sistem TIDAK menjalankan pengambilan data selama saya masih mengetik
  When saya berhenti mengetik selama 500 ms
  Then sistem baru menjalankan pengambilan spesifikasi & data QC

Scenario: Mengetik ulang membatalkan pengambilan sebelumnya
  Given saya baru saja berhenti mengetik dan pengambilan data sedang dijadwalkan
  When saya mengetik karakter baru sebelum debounce selesai
  Then pengambilan berikutnya dijadwalkan ulang dari awal (500 ms setelah ketikan terakhir)
```

**Acceptance Criteria - S-2.1.3:**
```gherkin
Feature: Indikator Loading Saat Pengambilan Data

Scenario: Indikator proses ditampilkan selama pengambilan berlangsung
  Given saya memasukkan nomor PO dan menunggu data
  When pengambilan data sedang berlangsung
  Then indikator proses sedang berlangsung ditampilkan
  When pengambilan data selesai (baik sukses maupun gagal)
  Then indikator proses hilang
```

> **Catatan Teknis E2.1:**
> - **Business Logic:**
>   - Auto-fetch dipicu oleh perubahan nilai field `no_po`, dengan delay debounce **500 ms** setelah ketikan terakhir
>   - Sumber data spesifikasi PO MMEA adalah **API eksternal Sirine (endpoint khusus MMEA)** — terpisah dari endpoint PCHT
>   - **Mapping data order → tampilan / state form:**
>     - `produk = response.jenis` (diharapkan bernilai `MMEA` atau `HPTL`)
>     - `no_obc = response.no_obc` (penentu apakah spesifikasi dianggap tersedia)
>     - `jml_lbr (Lembar Cetak) = response.rencet`
>     - `no_plat = "-"` (saat ini tidak tersedia dari sumber order; disediakan untuk kebutuhan mendatang)
>     - `jml_order = response.jml_order` (jumlah order, dipakai untuk perhitungan lembar per rim — lihat E3)
>   - Spesifikasi dianggap "tersedia" jika `no_obc` terisi; selama belum tersedia, field rim & aksi cetak terkunci
>   - Setiap perubahan nilai `no_po` membersihkan pesan error PO sebelumnya
> - **Pesan Error Standar:**
>   - "Nomor Po Tidak Ditemukan Harap Hubungi admin untuk memperbaharui data order"

**API Contract - S-2.1.1 (External — Sirine MMEA):**
```
GET https://sirine.peruri.co.id/sirine/api/detail-order-mmea/{no_po}

Response 200 (success):
{
  "no_po": 5000000001,
  "no_obc": "TST010110",
  "jenis": "MMEA",        // atau "HPTL"
  "rencet": 1500,          // jumlah lembar cetak
  "jml_order": 1500        // jumlah order (untuk perhitungan lembar rim terakhir)
}

Response (error):
HTTP non-200, error jaringan, atau payload kosong/tanpa "no_obc"
→ diperlakukan sebagai "PO tidak ditemukan"
```

---

### E2.2 - Tampilan Spesifikasi Produk

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-2.2.1 | Sebagai **operator**, saya ingin **melihat spesifikasi produk (Produk, No OBC, Nomor Plat, Lembar Cetak) setelah PO tertarik** agar **saya bisa memverifikasi PO yang saya proses sudah benar sebelum mencetak** | 3 | High | 2 |

**Acceptance Criteria - S-2.2.1:**
```gherkin
Feature: Tampilan Spesifikasi Produk MMEA

Scenario: Spesifikasi ditampilkan setelah PO valid tertarik
  Given spesifikasi PO "5000000001" berhasil tertarik
  Then halaman menampilkan informasi: Produk, No OBC, Nomor Plat, Lembar Cetak
  And kondisi "spesifikasi tersedia" dapat dibedakan secara visual dari kondisi "belum tersedia"

Scenario: Lembar Cetak nol ditandai
  Given spesifikasi tertarik namun nilai Lembar Cetak = 0
  Then nilai Lembar Cetak ditampilkan dengan penanda visual yang dapat dibedakan (menandakan data perlu diperiksa)

Scenario: Spesifikasi kosong sebelum PO tertarik
  Given belum ada PO yang berhasil tertarik
  Then seluruh nilai spesifikasi ditampilkan sebagai placeholder kosong ("---")
```

> **Catatan Teknis E2.2:**
> - **Business Logic:**
>   - Informasi yang ditampilkan: `produk`, `no_obc`, `no_plat`, `jml_lbr` (Lembar Cetak)
>   - Selama spesifikasi belum tersedia, seluruh nilai ditampilkan kosong (placeholder)
>   - Nilai Lembar Cetak = 0 perlu dapat dibedakan secara visual sebagai kondisi yang perlu diperhatikan

---

### E2.3 - Penanganan PO Tidak Ditemukan

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-2.3.1 | Sebagai **operator**, saya ingin **menerima pesan jelas saat nomor PO tidak ditemukan** agar **saya tahu PO tersebut salah ketik atau datanya belum tersedia** | 3 | Critical | 1 |

**Acceptance Criteria - S-2.3.1:**
```gherkin
Feature: Penanganan PO Tidak Ditemukan

Scenario: PO tidak ditemukan di sumber data
  Given saya berada di halaman Cetak Label MMEA
  When saya memasukkan nomor PO "9999999999"
  And sumber data MMEA merespon error atau payload tidak valid
  Then saya menerima pesan "Nomor Po Tidak Ditemukan Harap Hubungi admin untuk memperbaharui data order"
  And informasi spesifikasi produk direset (Produk, No OBC, Nomor Plat, Lembar Cetak kosong)
  And daftar rim direset ke kondisi awal
  And field rim tetap dalam kondisi tidak dapat diubah
  And aksi "Cetak Label" tidak dapat dipicu

Scenario: Pesan error dibersihkan saat user mengubah PO
  Given pesan PO tidak ditemukan sedang ditampilkan
  When saya mengubah nilai nomor PO
  Then pesan error dibersihkan
  And debounce pengambilan data kembali berjalan normal
```

> **Catatan Teknis E2.3:**
> - **Business Logic:**
>   - Response sumber data yang non-200, error jaringan, atau payload kosong/tanpa `no_obc` → diperlakukan sebagai "PO tidak ditemukan"
>   - Saat status "tidak ditemukan", state spesifikasi (`produk`, `no_obc`, `no_plat`, `jml_lbr`) direset ke kosong dan daftar rim dikembalikan ke kondisi awal
>   - Setiap perubahan nilai `no_po` membersihkan pesan error PO sebelumnya
> - **Pesan Error Standar:**
>   - "Nomor Po Tidak Ditemukan Harap Hubungi admin untuk memperbaharui data order"

---

## EPIC E3: Inisialisasi Rim & Merge Data QC

### E3.1 - Perhitungan Jumlah Rim/Label (Konstanta 300 Lembar per Rim)

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-3.1.1 | Sebagai **sistem**, saya ingin **menghitung jumlah rim (label) dari jumlah lembar cetak dengan basis 300 lembar per rim, atau mengikuti jumlah label yang sudah tersimpan** agar **daftar rim yang ditampilkan sesuai kebutuhan PO** | 5 | Critical | 2 |
| S-3.1.2 | Sebagai **sistem**, saya ingin **menentukan jumlah lembar (Lbr Kirim) untuk rim terakhir berdasarkan sisa pembagian order** agar **total lembar pada label sesuai jumlah order sebenarnya** | 3 | High | 2 |

**Acceptance Criteria - S-3.1.1:**
```gherkin
Feature: Perhitungan Jumlah Rim MMEA

Scenario: PO belum punya data QC — jumlah rim dihitung dari lembar cetak
  Given spesifikasi PO tertarik dengan rencet = 1500 dan belum ada data QC tersimpan
  When sistem menghitung jumlah rim
  Then jumlah rim/label = ceil(1500 / 300) = 5
  And daftar rim terisi 5 baris dengan Nomor Rim 1..5

Scenario Outline: Jumlah rim = pembulatan ke atas dari rencet / 300
  Given rencet = <rencet> dan belum ada data QC
  Then jumlah rim = <jumlah_rim>

  Examples:
    | rencet | jumlah_rim |
    | 300    | 1          |
    | 301    | 2          |
    | 600    | 2          |
    | 900    | 3          |
    | 1500   | 5          |
    | 2000   | 7          |

Scenario: PO sudah punya data QC — jumlah rim mengikuti jumlah label tersimpan
  Given PO sudah memiliki 3 label QC tersimpan
  When sistem menghitung jumlah rim
  Then jumlah rim/label = 3 (mengikuti jumlah data tersimpan, bukan dihitung ulang dari rencet)
```

**Acceptance Criteria - S-3.1.2:**
```gherkin
Feature: Perhitungan Lbr Kirim Rim Terakhir

Scenario: Order satu rim — seluruh lembar dimasukkan ke rim tunggal
  Given perhitungan menghasilkan hanya 1 rim
  And jml_order = 250
  Then Lbr Kirim rim tunggal tersebut diisi = 250 (seluruh order)

Scenario: Order banyak rim — rim awal 300, rim terakhir mengikuti sisa
  Given perhitungan menghasilkan 3 rim
  And jml_order = 850
  Then rim 1 dan rim 2 diisi Lbr Kirim = 300 (default)
  And rim terakhir (rim 3) diisi Lbr Kirim = 850 mod 300 = 250

Scenario: Sisa pembagian habis — rim terakhir penuh 300
  Given jml_order = 900 menghasilkan 3 rim
  Then rim terakhir diisi Lbr Kirim = 300 (karena 900 mod 300 = 0)

Scenario: Kasus khusus rencet = 1500 — rim terakhir tetap default 300
  Given rencet = 1500 (menghasilkan 5 rim)
  Then rim terakhir diisi Lbr Kirim = 300 (tidak memakai sisa pembagian jml_order)
```

> **Catatan Teknis E3.1:**
> - **Business Logic — Konstanta:**
>   - **1 rim label MMEA = 300 lembar** (berbeda dari PCHT/Inspeksi)
> - **Business Logic — Jumlah Rim/Label:**
>   - Jika PO **belum** memiliki data label tersimpan → `jumlah_rim = ceil(rencet / 300)`
>   - Jika PO **sudah** memiliki data label tersimpan → `jumlah_rim = jumlah label tersimpan`
> - **Business Logic — Lbr Kirim per Rim (saat belum ada data QC):**
>   - Default tiap rim = **300**
>   - Jika hanya **1 rim** → rim tunggal diisi = `jml_order` (seluruh jumlah order)
>   - Jika **lebih dari 1 rim** → rim awal = 300; **rim terakhir** = `jml_order mod 300` (atau `300` bila `jml_order mod 300 == 0`)
>   - **Kasus khusus:** jika `rencet == 1500`, rim terakhir tetap memakai default `300` (tidak menerapkan rumus sisa pembagian)
>   - Operator dapat mengubah nilai Lbr Kirim tiap rim secara manual setelahnya
> - **Catatan:** `rencet` (lembar cetak) dan `jml_order` (jumlah order) adalah dua field berbeda dari sumber order; jumlah rim memakai `rencet`, sedangkan Lbr Kirim rim terakhir memakai `jml_order`

---

### E3.2 - Pre-fill dari Data QC yang Sudah Tersimpan

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-3.2.1 | Sebagai **operator**, saya ingin **data rim yang sudah pernah disimpan untuk PO ini dimuat kembali (NP Periksa 1/2 & Lbr Kirim per rim)** agar **saya bisa mencetak ulang atau melanjutkan pengisian tanpa mengetik ulang dari awal** | 5 | Critical | 2 |

**Acceptance Criteria - S-3.2.1:**
```gherkin
Feature: Pre-fill Data QC MMEA

Scenario: PO sudah pernah diproses — rim dimuat dengan data tersimpan
  Given PO "5000000001" sudah memiliki label QC tersimpan terurut berdasarkan nomor rim:
    | nomor_rim | periksa1 | periksa2 | lbr_kemas |
    | 1         | I444     | I666     | 300       |
    | 2         | I555     | I777     | 250       |
  When spesifikasi PO tertarik
  Then daftar rim ditampilkan dengan 2 baris
  And rim 1 ter-prefill: Periksa 1 = "I444", Periksa 2 = "I666", Lbr Kirim = 300
  And rim 2 ter-prefill: Periksa 1 = "I555", Periksa 2 = "I777", Lbr Kirim = 250

Scenario: PO belum pernah diproses — rim kosong dengan default
  Given PO belum memiliki label QC tersimpan
  When spesifikasi PO tertarik
  Then daftar rim ditampilkan kosong (Periksa 1 & 2 kosong)
  And Lbr Kirim mengikuti aturan perhitungan default (lihat E3.1)

Scenario: Data QC diurutkan berdasarkan nomor rim
  Given data QC tersimpan tidak berurutan
  When data QC dimuat ke daftar rim
  Then rim ditampilkan terurut menaik berdasarkan nomor rim
```

> **Catatan Teknis E3.2:**
> - **Business Logic:**
>   - Setelah spesifikasi tertarik, sistem juga mengambil data label QC tersimpan untuk `no_po` tersebut, **terurut menaik berdasarkan nomor rim**
>   - Tiap baris rim di-prefill dari data QC bila tersedia: `periksa1`, `periksa2`, `lbr_kemas` (Lbr Kirim)
>   - Bila baris rim tertentu belum ada datanya → Periksa 1 & 2 kosong, Lbr Kirim default 300 (kecuali rim terakhir mengikuti aturan E3.1)
>   - Data QC menentukan jumlah rim bila lebih dari 0 (lihat E3.1)
> - **Output bentuk data QC per item:** `{ nomor_po, nomor_rim, periksa1, periksa2, lbr_kemas, waktu_p1, waktu_p2 }`

**API Contract - S-3.2.1 (rekomendasi: WAJIB ter-auth di arsitektur baru):**
```
GET /api/print-label-mmea/qc-data/{no_po}

Headers:
  Accept: application/json
  Authorization: Bearer {token}

Response 200 (ada data, terurut nomor_rim ASC):
[
  { "nomor_po": 5000000001, "nomor_rim": 1, "periksa1": "I444", "periksa2": "I666", "lbr_kemas": 300, "waktu_p1": "...", "waktu_p2": "..." },
  { "nomor_po": 5000000001, "nomor_rim": 2, "periksa1": "I555", "periksa2": "I777", "lbr_kemas": 250, "waktu_p1": "...", "waktu_p2": "..." }
]

Response 200 (belum ada data):
[]
```

---

## EPIC E4: Input Form Per-Rim & Manajemen Baris

### E4.1 - Field Per-Rim & Pengaktifan Bertahap

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-4.1.1 | Sebagai **operator**, saya ingin **mengisi NP Periksa 1, NP Periksa 2, dan Lbr Kirim untuk tiap rim, dengan Nomor Rim otomatis (read-only)** agar **identitas pemeriksa dan jumlah lembar tiap rim tercatat** | 3 | Critical | 2 |
| S-4.1.2 | Sebagai **operator**, saya ingin **field pemeriksa baris berikutnya baru aktif setelah pemeriksa baris sebelumnya terisi** agar **pengisian dilakukan berurutan dan tidak ada rim yang terlewat kosong** | 3 | High | 2 |
| S-4.1.3 | Sebagai **operator**, saya ingin **menekan Enter di field pemeriksa tidak ikut memicu submit/cetak** agar **saya bisa berpindah field tanpa tidak sengaja mencetak** | 1 | Medium | 2 |

**Acceptance Criteria - S-4.1.1:**
```gherkin
Feature: Input Form Per-Rim MMEA

Scenario: Operator mengisi field tiap rim
  Given spesifikasi PO sudah tersedia dan daftar rim aktif
  When saya mengisi rim 1:
    | Field      | Value |
    | Nomor Rim  | 1 (otomatis, read-only) |
    | Lbr Kirim  | 300   |
    | Periksa 1  | I444  |
    | Periksa 2  | I666  |
  Then seluruh nilai tersimpan di state form (Periksa 1 & 2 akan diformat & diuppercase saat disimpan)

Scenario: Nomor Rim tidak dapat diubah manual
  Given daftar rim aktif
  Then field Nomor Rim setiap baris bersifat read-only / tidak dapat diubah
  And Nomor Rim diisi otomatis sesuai urutan baris
```

**Acceptance Criteria - S-4.1.2:**
```gherkin
Feature: Pengaktifan Field Pemeriksa Secara Bertahap

Scenario: Baris berikutnya aktif setelah baris sebelumnya terisi lengkap
  Given daftar rim memiliki 3 baris
  And rim 1 belum diisi Periksa 1 dan Periksa 2
  Then field pemeriksa rim 2 dan rim 3 berada dalam kondisi tidak dapat diubah
  When saya mengisi Periksa 1 dan Periksa 2 pada rim 1
  Then field pemeriksa rim 2 menjadi aktif

Scenario: Mengosongkan baris sebelumnya menonaktifkan baris setelahnya
  Given rim 1 dan rim 2 sudah terisi
  When saya mengosongkan Periksa 1 atau Periksa 2 pada rim 1
  Then field pemeriksa rim 2 kembali tidak dapat diubah
```

**Acceptance Criteria - S-4.1.3:**
```gherkin
Feature: Enter di Field Tidak Memicu Cetak

Scenario: User menekan Enter di field rim
  Given saya sedang fokus di field Nomor Rim / Lbr Kirim / Periksa 1 / Periksa 2
  When saya menekan tombol Enter
  Then form TIDAK ter-submit / tidak memicu cetak secara tidak sengaja
  And fokus dapat berpindah ke field berikutnya secara normal
```

> **Catatan Teknis E4.1:**
> - **Business Logic:**
>   - Field per rim: `nomor_rim` (otomatis, read-only), `jml_kemas`/Lbr Kirim (integer), `periksa1` (NP, wajib), `periksa2` (NP, wajib)
>   - Nomor Rim diisi otomatis berurutan sesuai posisi baris dan tidak dapat diubah manual
>   - **Pengaktifan bertahap:** field pemeriksa suatu baris hanya aktif jika pemeriksa (Periksa 1 **dan** Periksa 2) pada baris sebelumnya sudah terisi — mendorong pengisian berurutan dan mencegah rim terlewat
>   - Tombol Enter pada field rim tidak boleh memicu cetak (mencegah cetak tidak sengaja)

---

### E4.2 - Tambah & Hapus Baris Rim

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-4.2.1 | Sebagai **operator**, saya ingin **menambah atau menghapus baris rim (dengan batas maksimum & minimum)** agar **saya bisa menyesuaikan jumlah rim yang akan dicetak** | 5 | High | 3 |

**Acceptance Criteria - S-4.2.1:**
```gherkin
Feature: Manajemen Baris Rim

Scenario: Menambah baris rim
  Given daftar rim aktif dengan 2 baris
  When saya memicu aksi "Tambah Baris"
  Then daftar rim bertambah menjadi 3 baris
  And baris baru memiliki Nomor Rim berurutan, Periksa 1 & 2 kosong, Lbr Kirim default 300
  And jumlah rim yang akan dicetak ikut diperbarui

Scenario: Batas maksimum penambahan baris
  Given daftar rim sudah mencapai 5 baris
  Then aksi "Tambah Baris" tidak dapat dipicu

Scenario: Menghapus baris rim
  Given daftar rim memiliki 3 baris
  When saya memicu aksi "Hapus Baris"
  Then satu baris terakhir dihapus
  And Nomor Rim sisa baris diurutkan ulang secara berurutan tanpa lompatan
  And data Periksa 1/2 & Lbr Kirim baris yang tersisa tetap konsisten dengan posisinya

Scenario: Batas minimum baris
  Given daftar rim hanya memiliki 1 baris
  Then aksi "Hapus Baris" tidak dapat dipicu (minimal 1 baris)

Scenario: Manajemen baris hanya aktif setelah spesifikasi tersedia
  Given spesifikasi PO belum tersedia
  Then aksi tambah & hapus baris tidak dapat dipicu
```

> **Catatan Teknis E4.2:**
> - **Business Logic:**
>   - Penambahan baris dibatasi **maksimum 5 baris**; penghapusan dibatasi **minimum 1 baris**
>   - Baris baru: Nomor Rim berurutan, Periksa 1 & 2 kosong, Lbr Kirim default 300
>   - Saat menghapus baris, **penomoran rim baris yang tersisa harus diurutkan ulang** menjadi berurutan (1, 2, 3, …) tanpa lompatan
>   - Aksi tambah/hapus baris hanya aktif jika spesifikasi PO sudah tersedia dan tidak sedang dalam proses
>   - Jumlah rim yang akan dicetak (`jml_label`) selalu mengikuti jumlah baris aktual

---

### E4.3 - Reset Form

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-4.3.1 | Sebagai **operator**, saya ingin **mereset form ke kondisi awal** agar **saya bisa segera memproses PO berikutnya tanpa refresh halaman** | 2 | High | 3 |

**Acceptance Criteria - S-4.3.1:**
```gherkin
Feature: Reset Form MMEA

Scenario: Operator memicu aksi "Clear Form"
  Given form sudah terisi (no_po, daftar rim, pemeriksa, Lbr Kirim)
  When saya memicu aksi "Clear Form"
  Then field nomor PO dikosongkan
  And daftar rim dikembalikan ke kondisi awal (1 baris)
  And state spesifikasi direset (Produk, No OBC, Nomor Plat, Lembar Cetak kosong)
  And user menerima konfirmasi singkat bahwa form telah direset
  And tidak ada request dikirim ke penyimpanan

Scenario: Form direset otomatis setelah cetak batch sukses
  Given cetak batch berhasil dan proses cetak selesai ter-initiate
  Then form direset ke kondisi awal (seperti aksi "Clear Form")
  And halaman siap untuk PO berikutnya

Scenario: Form TIDAK direset setelah cetak satuan
  Given saya melakukan cetak satuan untuk satu rim dan berhasil
  Then form tetap dalam keadaan terisi
  And saya dapat melanjutkan mencetak rim lainnya
```

> **Catatan Teknis E4.3:**
> - **Business Logic:**
>   - Reset manual (aksi user) mengembalikan seluruh field ke kondisi awal: nomor PO kosong, daftar rim kembali 1 baris, spesifikasi kosong
>   - Reset otomatis hanya dilakukan setelah **cetak batch** sukses; setelah **cetak satuan** form sengaja dipertahankan agar operator dapat melanjutkan rim lain
>   - Konfirmasi reset hanya ditampilkan saat tidak ada proses yang sedang berjalan

---

### E4.4 - Format NP Pemeriksa

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-4.4.1 | Sebagai **sistem**, saya ingin **memformat NP pemeriksa yang panjang menjadi maksimal 5 karakter dan menyimpannya dalam huruf besar** agar **NP yang disimpan & tercetak konsisten dengan format internal** | 2 | High | 2 |

**Acceptance Criteria - S-4.4.1:**
```gherkin
Feature: Format NP Pemeriksa MMEA

Scenario Outline: NP > 4 karakter dipersingkat menjadi karakter pertama + 4 karakter terakhir
  Given NP input = <input>
  When data disimpan
  Then NP yang disimpan & tercetak = <formatted>

  Examples:
    | input     | formatted |
    | "I12345"  | "I2345"   |
    | "I67890"  | "I7890"   |
    | "I444"    | "I444"    |
    | "I4444"   | "I4444"   |
    | ""        | ""        |

Scenario: NP diuppercase saat disimpan
  Given Periksa 1 input = "i444"
  When data disimpan
  Then nilai NP yang tersimpan = "I444" (uppercase)

Scenario: Format diterapkan pada Periksa 1 dan Periksa 2 secara independen
  Given Periksa 1 = "I12345" dan Periksa 2 = "X11112"
  When data disimpan
  Then Periksa 1 tersimpan = "I2345"
  And Periksa 2 tersimpan = "X1112"
```

> **Catatan Teknis E4.4:**
> - **Business Logic:**
>   - **Format NP:** jika panjang NP > 4 karakter, NP dipersingkat menjadi `karakter_pertama + 4_karakter_terakhir` (hasil = 5 karakter)
>   - Jika panjang NP ≤ 4 karakter, NP dibiarkan apa adanya (tanpa padding)
>   - NP kosong dibiarkan kosong
>   - Format diterapkan ke `periksa1` dan `periksa2` secara independen
>   - NP yang sudah diformat selalu di-uppercase sebelum disimpan
>   - NP yang tercetak pada label = NP yang sudah diformat & di-uppercase
>   - Aturan format ini diterapkan secara **konsisten** di sisi input, sisi penyimpanan, dan sisi pembuatan label tercetak

---

## EPIC E5: Submit & Penyimpanan Data

### E5.1 - Konfirmasi Sebelum Cetak

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-5.1.1 | Sebagai **operator**, saya ingin **dimintai konfirmasi sebelum data disimpan & dicetak** agar **saya bisa membatalkan jika ada data yang salah** | 2 | High | 3 |

**Acceptance Criteria - S-5.1.1:**
```gherkin
Feature: Konfirmasi Sebelum Cetak MMEA

Scenario: Sistem meminta konfirmasi sebelum memproses
  Given form sudah terisi
  When saya memicu aksi cetak (batch maupun satuan)
  Then sistem menampilkan permintaan konfirmasi "Konfirmasi Cetak Label"
  And saya dapat memilih melanjutkan ("Ya, Cetak Label") atau membatalkan ("Batal")

Scenario: Operator membatalkan konfirmasi
  Given permintaan konfirmasi sedang ditampilkan
  When saya memilih membatalkan
  Then tidak ada data yang disimpan dan tidak ada cetak yang dijalankan
  And data form tetap dalam keadaan terisi

Scenario: Operator menyetujui konfirmasi
  Given permintaan konfirmasi sedang ditampilkan
  When saya menyetujui konfirmasi
  Then proses penyimpanan & cetak dilanjutkan
  And indikator proses berlangsung ditampilkan
  And aksi cetak dinonaktifkan selama proses berjalan (mencegah double-submit)
```

> **Catatan Teknis E5.1:**
> - **Business Logic:**
>   - Setiap aksi cetak (batch maupun satuan) wajib didahului permintaan konfirmasi user
>   - Selama proses penyimpanan & cetak berjalan, aksi cetak harus dinonaktifkan untuk mencegah double-submit

---

### E5.2 - Simpan Spesifikasi Produk

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-5.2.1 | Sebagai **sistem**, saya ingin **menyimpan/memperbarui spesifikasi produk PO MMEA saat cetak diproses** agar **PO MMEA tercatat sebagai produk dengan team & status yang benar** | 5 | Critical | 3 |

**Acceptance Criteria - S-5.2.1:**
```gherkin
Feature: Simpan Spesifikasi Produk MMEA

Scenario: Simpan produk baru saat cetak pertama
  Given PO "5000000001" belum tercatat sebagai produk
  And spesifikasi: no_obc="TST010110", produk="MMEA", jml_lbr=1500.7
  When proses penyimpanan produk dijalankan
  Then tercatat satu produk dengan:
    | Field         | Value      |
    | no_po         | 5000000001 |
    | no_obc        | TST010110  |
    | type          | MMEA       |
    | sum_rim       | 1501 (pembulatan ke atas dari 1500.7) |
    | start_rim     | 1          |
    | end_rim       | 1501       |
    | assigned_team | team MMEA  |
    | status        | 2 (Selesai)|

Scenario: Perbarui produk yang sudah ada
  Given PO "5000000001" sudah tercatat dengan no_obc lama dan status 1
  And dikirim spesifikasi baru: no_obc="NEW010110", jml_lbr=2000
  When proses penyimpanan produk dijalankan
  Then produk diperbarui: no_obc="NEW010110", sum_rim=2000, end_rim=2000, status=2
  And tidak terjadi duplikasi record untuk no_po yang sama

Scenario: Tipe produk HPTL juga didukung
  Given spesifikasi produk dengan produk="HPTL"
  When proses penyimpanan produk dijalankan
  Then type tercatat = "HPTL"
```

> **Catatan Teknis E5.2:**
> - **Business Logic:**
>   - Penyimpanan produk bersifat **upsert berdasarkan nomor PO** (nomor PO unik — tidak boleh ada duplikat produk untuk satu PO)
>   - Field yang disimpan/diperbarui:
>     - `no_obc` = dari spesifikasi
>     - `type` = `produk` (`MMEA` atau `HPTL`)
>     - `sum_rim` = **pembulatan ke atas** (half-up) dari `jml_lbr`
>     - `start_rim` = `1`
>     - `end_rim` = `sum_rim`
>     - `assigned_team` = **team MMEA** (konstanta team = 6)
>     - `status` = **2 (Selesai)** — flow MMEA selalu menandai produk sebagai selesai saat cetak
>   - Penyimpanan produk dijalankan **setiap kali** cetak diproses (batch maupun satuan), sebelum penyimpanan data label

**API Contract - S-5.2.1 (rekomendasi: WAJIB ter-auth di arsitektur baru):**
```
POST /api/print-label-mmea/store-product

Headers:
  Content-Type: application/json
  Accept: application/json
  Authorization: Bearer {token}

Request Body:
{
  "no_po":   5000000001,    // wajib (unik per produk)
  "no_obc":  "TST010110",   // wajib
  "produk":  "MMEA",        // "MMEA" | "HPTL"
  "jml_lbr": 1500.7         // jumlah lembar (akan dibulatkan ke atas → sum_rim)
}

Response 200:
{ "success": true }
```

---

### E5.3 - Simpan Data Label per Rim (Upsert) & Logika Waktu Pemeriksa

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-5.3.1 | Sebagai **sistem**, saya ingin **menyimpan/memperbarui data label tiap rim (NP Periksa 1/2 & Lbr Kirim) berdasarkan kombinasi nomor PO + nomor rim** agar **setiap rim tercatat tepat satu data dan dapat diperbarui tanpa duplikasi** | 8 | Critical | 3 |
| S-5.3.2 | Sebagai **sistem**, saya ingin **memperbarui timestamp pemeriksa (waktu_p1/waktu_p2) hanya jika NP pemeriksa pada rim tersebut berubah** agar **waktu pemeriksaan mencerminkan kapan pemeriksa sebenarnya berganti, bukan setiap kali cetak ulang** | 3 | High | 4 |

**Acceptance Criteria - S-5.3.1:**
```gherkin
Feature: Simpan Data Label per Rim MMEA

Scenario: Simpan label baru untuk beberapa rim
  Given PO "5000000001" dengan input rim:
    | nomor_rim | periksa1 | periksa2 | lbr_kemas |
    | 1         | I444     | I666     | 100       |
    | 2         | I555     | I777     | 200       |
  When proses penyimpanan label dijalankan
  Then tersimpan 2 data label:
    | nomor_po   | nomor_rim | periksa1 | periksa2 | lbr_kemas |
    | 5000000001 | 1         | I444     | I666     | 100       |
    | 5000000001 | 2         | I555     | I777     | 200       |
  And NP disimpan dalam huruf besar dan sudah diformat (lihat E4.4)

Scenario: Memperbarui label rim yang sudah ada (tanpa duplikasi)
  Given sudah ada label rim 1 untuk PO "5000000001"
  When proses penyimpanan dijalankan ulang untuk rim 1 dengan Lbr Kirim = 150
  Then data rim 1 diperbarui (lbr_kemas = 150)
  And tidak terjadi duplikasi data untuk kombinasi nomor PO + nomor rim yang sama

Scenario: NP > 4 karakter diformat sebelum disimpan
  Given input rim 1 dengan periksa1="I12345", periksa2="I67890"
  When proses penyimpanan dijalankan
  Then periksa1 tersimpan = "I2345" dan periksa2 tersimpan = "I7890"

Scenario: Memproses banyak rim dalam satu permintaan
  Given input berisi 5 rim
  When proses penyimpanan dijalankan
  Then kelima rim tersimpan/diperbarui sesuai datanya
```

**Acceptance Criteria - S-5.3.2:**
```gherkin
Feature: Logika Timestamp Pemeriksa MMEA

Scenario: Rim baru — timestamp pemeriksa diisi waktu saat ini
  Given rim 1 belum pernah ada datanya
  When proses penyimpanan dijalankan dengan periksa1="I444", periksa2="I666"
  Then waktu_p1 dan waktu_p2 diisi dengan waktu saat ini

Scenario: NP Periksa berubah — timestamp pemeriksa terkait diperbarui
  Given rim 1 sudah ada dengan periksa1="I444" (waktu_p1 = kemarin), periksa2="I666" (waktu_p2 = kemarin)
  When proses penyimpanan dijalankan dengan periksa1="I444" (sama) dan periksa2="I888" (berbeda)
  Then waktu_p1 TIDAK berubah (NP Periksa 1 sama)
  And waktu_p2 diperbarui ke waktu saat ini (NP Periksa 2 berubah)

Scenario: NP Periksa tidak berubah — timestamp dipertahankan
  Given rim 1 sudah ada dengan periksa1 & periksa2 tertentu beserta timestamp lamanya
  When proses penyimpanan dijalankan dengan NP yang persis sama (setelah format & uppercase)
  Then waktu_p1 dan waktu_p2 tetap memakai nilai lama (tidak di-refresh)
```

> **Catatan Teknis E5.3:**
> - **Business Logic — Penyimpanan Label:**
>   - Penyimpanan label bersifat **upsert berdasarkan kombinasi (`nomor_po`, `nomor_rim`)** — satu rim tepat satu data
>   - Untuk setiap rim yang dikirim, sistem menyimpan/memperbarui: `periksa1`, `periksa2` (sudah diformat & uppercase — lihat E4.4), `lbr_kemas` (Lbr Kirim), `waktu_p1`, `waktu_p2`
>   - **Pemetaan input per rim:** untuk nomor rim `N`, NP diambil dari kunci pemeriksa `np_{N}`, dan Lbr Kirim dari kunci `no_{N}`; jika kunci spesifik tidak ditemukan, sistem **fallback ke data rim pertama** (`np_1` / `no_1`) — relevan untuk alur cetak satuan yang hanya mengirim satu rim
> - **Business Logic — Logika Timestamp Pemeriksa:**
>   - Jika rim **belum** ada datanya → `waktu_p1 = waktu_p2 = waktu saat ini`
>   - Jika rim **sudah** ada → untuk masing-masing pemeriksa: jika NP (setelah uppercase) **sama** dengan yang tersimpan, timestamp **dipertahankan**; jika **berbeda**, timestamp di-refresh ke waktu saat ini
>   - Logika ini berlaku independen untuk `waktu_p1` (berdasarkan `periksa1`) dan `waktu_p2` (berdasarkan `periksa2`)
> - **Catatan Konsistensi Data:** keunikan `(nomor_po, nomor_rim)` saat ini dijaga secara logis melalui mekanisme upsert; di arsitektur baru **direkomendasikan** menambahkan unique constraint pada `(nomor_po, nomor_rim)` di penyimpanan

**API Contract - S-5.3.1 (rekomendasi: WAJIB ter-auth di arsitektur baru):**
```
POST /api/print-label-mmea/store

Headers:
  Content-Type: application/json
  Accept: application/json
  Authorization: Bearer {token}

Request Body (batch):
{
  "no_po":     5000000001,
  "no_rim":    { "no_1": 1, "no_2": 2 },
  "periksa1":  { "np_1": "I444", "np_2": "I555" },
  "periksa2":  { "np_1": "I666", "np_2": "I777" },
  "jml_kemas": { "no_1": 100,    "no_2": 200 },
  "jml_label": 2
}

Request Body (satuan — hanya satu rim, memanfaatkan fallback np_1/no_1):
{
  "no_po":     5000000001,
  "no_rim":    { "no_1": 3 },
  "periksa1":  { "np_1": "I444" },
  "periksa2":  { "np_1": "I666" },
  "jml_kemas": { "no_1": 100 },
  "jml_label": 1
}

Response 200:
{ "success": true, "message": "Label berhasil diproses" }
```

---

### E5.4 - Penanganan Hasil Submit (Sukses & Error)

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-5.4.1 | Sebagai **operator**, saya ingin **menerima konfirmasi keberhasilan setelah cetak diproses** agar **saya tahu data telah tersimpan dan cetak telah dijalankan** | 3 | Critical | 4 |
| S-5.4.2 | Sebagai **operator**, saya ingin **menerima pesan error yang jelas jika proses gagal** agar **saya bisa memperbaiki input atau melaporkan masalah dan mencoba ulang** | 3 | High | 4 |

**Acceptance Criteria - S-5.4.1:**
```gherkin
Feature: Konfirmasi Keberhasilan MMEA

Scenario: Operator menerima konfirmasi sukses
  Given penyimpanan produk & label berhasil
  When proses cetak telah ter-initiate
  Then sistem menampilkan konfirmasi keberhasilan dengan judul "Berhasil" dan keterangan "Label Berhasil Dibuat"
  And untuk cetak batch, form direset setelahnya
  And untuk cetak satuan, form dipertahankan
```

**Acceptance Criteria - S-5.4.2:**
```gherkin
Feature: Penanganan Error Submit MMEA

Scenario: Error validasi (HTTP 422 dengan errors per field)
  Given proses gagal dengan response 422 berisi { errors: { field: [pesan, ...] } }
  When response diterima
  Then user menerima daftar pesan error per field dalam Bahasa Indonesia
  And seluruh pesan ditampilkan sebagai daftar yang mudah dibaca
  And data form tetap terisi agar bisa diperbaiki

Scenario: Error sistem / server (non-422)
  Given proses gagal dengan response berisi pesan error
  When response diterima
  Then user menerima pesan error dari server ("Terjadi kesalahan pada server" jika tidak ada detail)
  And data form tetap terisi
  And user diberi kesempatan mencoba ulang

Scenario: Cetak tidak dijalankan jika penyimpanan gagal
  Given penyimpanan produk atau label gagal
  When error diterima
  Then perintah cetak TIDAK dijalankan
  And indikator proses dikembalikan ke kondisi idle agar aksi cetak dapat dipicu ulang
```

> **Catatan Teknis E5.4:**
> - **Business Logic:**
>   - Urutan proses saat cetak: **konfirmasi → simpan produk → simpan label → cetak → konfirmasi sukses → (reset bila batch)**
>   - Cetak hanya dijalankan jika penyimpanan produk & label sukses
>   - **Parsing error:**
>     - Response 422 → ambil seluruh `errors[field]`, gabungkan, tampilkan sebagai daftar pesan
>     - Response lain dengan body → ambil `message` dari body
>     - Tidak ada detail → pakai default "Terjadi kesalahan pada server"
>   - Data form **tidak boleh** dibersihkan saat error — operator harus bisa memperbaiki tanpa mengetik ulang
>   - Indikator proses (`isLoading`) wajib dikembalikan ke idle setelah error agar aksi dapat dipicu ulang
> - **Catatan (kondisi saat ini):** endpoint penyimpanan saat ini **tidak memiliki validasi server-side** sehingga payload kosong/tidak lengkap dapat menyebabkan kegagalan server (HTTP 500). Di arsitektur baru **direkomendasikan** menambahkan validasi field wajib (lihat Risk Register).
> - **Pesan/Judul Standar:**
>   - Sukses: judul "Berhasil", teks "Label Berhasil Dibuat"
>   - Gagal: judul "Gagal", default "Terjadi kesalahan pada server"

---

## EPIC E6: Cetak Label MMEA (Batch & Satuan)

### E6.1 - Cetak Batch & Cetak Satuan

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-6.1.1 | Sebagai **operator**, saya ingin **mencetak label seluruh rim sekaligus (batch)** agar **saya bisa menyelesaikan pencetakan seluruh PO dalam satu aksi** | 5 | High | 4 |
| S-6.1.2 | Sebagai **operator**, saya ingin **mencetak label satu rim saja (satuan)** agar **saya bisa mencetak ulang rim tertentu tanpa mencetak ulang seluruhnya** | 3 | High | 5 |

**Acceptance Criteria - S-6.1.1:**
```gherkin
Feature: Cetak Batch MMEA

Scenario: Cetak seluruh rim sekaligus
  Given daftar rim memiliki 3 baris terisi lengkap
  When saya memicu aksi "Cetak Label" (batch) dan menyetujui konfirmasi
  Then data produk & seluruh label rim disimpan
  And dihasilkan 3 halaman label (satu per rim)
  And setelah cetak ter-initiate, form direset untuk PO berikutnya

Scenario: Jumlah halaman cetak batch = jumlah rim
  Given daftar rim memiliki N baris
  When cetak batch dijalankan
  Then jumlah halaman label tercetak = N
  And setiap halaman berisi satu label dengan page-break antar halaman
```

**Acceptance Criteria - S-6.1.2:**
```gherkin
Feature: Cetak Satuan per Rim MMEA

Scenario: Cetak satu rim tertentu
  Given daftar rim memiliki beberapa baris
  And rim tertentu sudah terisi Periksa 1 dan Periksa 2
  When saya memicu aksi cetak satuan untuk rim tersebut dan menyetujui konfirmasi
  Then data produk & data rim tersebut disimpan
  And dihasilkan tepat 1 halaman label untuk rim tersebut
  And form TIDAK direset (dapat melanjutkan rim lain)

Scenario: Cetak satuan hanya tersedia untuk rim yang sudah lengkap
  Given rim tertentu belum terisi Periksa 1 atau Periksa 2
  Then aksi cetak satuan untuk rim tersebut tidak dapat dipicu
```

> **Catatan Teknis E6.1:**
> - **Business Logic:**
>   - **Cetak batch:** memproses & mencetak seluruh baris rim; jumlah halaman = jumlah rim; form direset setelah sukses
>   - **Cetak satuan:** memproses & mencetak hanya satu rim; menghasilkan tepat 1 halaman; form dipertahankan
>   - Cetak satuan hanya tersedia bila rim tersebut sudah memiliki Periksa 1 **dan** Periksa 2
>   - Pada cetak satuan, payload hanya berisi satu rim (memanfaatkan fallback pemetaan `np_1`/`no_1` — lihat E5.3)

---

### E6.2 - Mode Cetak Mempengaruhi Konten Label

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-6.2.1 | Sebagai **operator**, saya ingin **mode cetak menentukan NP pemeriksa mana yang tampil pada label** agar **saya bisa mencetak label hanya untuk pemeriksa tertentu** | 3 | Medium | 5 |

**Acceptance Criteria - S-6.2.1:**
```gherkin
Feature: Mode Cetak Mempengaruhi Konten Label MMEA

Scenario Outline: Mode cetak menentukan NP yang tampil
  Given mode cetak = <mode>
  And rim dengan Periksa 1 = "I444" dan Periksa 2 = "I666"
  When label dicetak
  Then NP Periksa 1 pada label = <np1_tercetak>
  And NP Periksa 2 pada label = <np2_tercetak>

  Examples:
    | mode    | np1_tercetak | np2_tercetak |
    | both    | I444         | I666         |
    | p1_only | I444         | (kosong)     |
    | p2_only | (kosong)     | I666         |

Scenario: Mode cetak tidak mengubah data yang disimpan
  Given mode cetak = "p1_only"
  When label dicetak dan data disimpan
  Then data Periksa 1 dan Periksa 2 tetap tersimpan lengkap (tidak terpengaruh mode cetak)
```

> **Catatan Teknis E6.2:**
> - **Business Logic:**
>   - Mode cetak hanya mempengaruhi **NP yang ditampilkan pada label tercetak**:
>     - `both` → tampilkan Periksa 1 & Periksa 2
>     - `p1_only` → tampilkan Periksa 1, kosongkan Periksa 2 di label
>     - `p2_only` → tampilkan Periksa 2, kosongkan Periksa 1 di label
>   - Mode cetak **tidak** mempengaruhi data yang disimpan ke storage

---

### E6.3 - Layout & Konten Label Tercetak

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-6.3.1 | Sebagai **operator**, saya ingin **setiap label tercetak berisi OBC, NP Periksa 1, NP Periksa 2, jumlah lembar (Lbr Kirim rim tersebut), tanggal, dan jam** agar **label dapat ditempel di rim fisik untuk traceability** | 5 | High | 4 |

**Acceptance Criteria - S-6.3.1:**
```gherkin
Feature: Konten & Layout Label Tercetak MMEA

Scenario: Field yang tercetak pada setiap label
  Given proses cetak berjalan untuk rim dengan:
    | Field     | Value     |
    | no_obc    | TST010110 |
    | periksa1  | I444      |
    | periksa2  | I666      |
    | lbr_kemas | 300       |
  Then label tercetak menampilkan field berikut:
    | Field          | Value                                                       |
    | NP Periksa 1   | I444 (uppercase, sesuai mode cetak)                         |
    | NP Periksa 2   | I666 (uppercase, sesuai mode cetak)                         |
    | OBC            | TST010110 (uppercase)                                       |
    | Jumlah Lembar  | "300 Lbr" (mengikuti Lbr Kirim rim tersebut)                |
    | Tanggal        | format "D-Bln-YYYY" Bahasa Indonesia (mis. "29-Mei-2026")   |
    | Jam            | format "H : M" (tanpa zero-padding, mis. "9 : 5")           |

Scenario: Ukuran label fisik
  Given label dicetak ke printer label
  Then setiap label berukuran 9 cm × 12 cm

Scenario: Tidak ada pembedaan warna berdasarkan seri
  Given label MMEA dicetak
  Then OBC dan NP menggunakan satu warna aksen yang konsisten
  And tidak ada variasi warna berdasarkan seri (berbeda dengan label PCHT)

Scenario: Format bulan dalam Bahasa Indonesia
  Given tanggal cetak = "2026-05-29"
  Then bulan ditampilkan sebagai "Mei"
  And contoh: "29-Mei-2026"
  And daftar nama bulan: Jan, Feb, Mar, Apr, Mei, Jun, Jul, Agu, Sep, Okt, Nov, Des

Scenario: Jumlah lembar per label mengikuti Lbr Kirim rim
  Given rim 1 memiliki Lbr Kirim = 300 dan rim 2 memiliki Lbr Kirim = 250
  When cetak batch dijalankan
  Then label rim 1 menampilkan "300 Lbr" dan label rim 2 menampilkan "250 Lbr"
```

> **Catatan Teknis E6.3:**
> - **Business Logic — Layout Label:**
>   - Ukuran label: **9 cm × 12 cm** per halaman
>   - Setiap label berisi field:
>     - NP Periksa 1 (sudah diformat & uppercase, dapat dikosongkan sesuai mode cetak)
>     - NP Periksa 2 (sudah diformat & uppercase, dapat dikosongkan sesuai mode cetak)
>     - OBC (huruf besar dari `no_obc`)
>     - Jumlah lembar = nilai `lbr_kemas` (Lbr Kirim) rim tersebut, ditampilkan sebagai `"{lbr} Lbr"`
>     - Tanggal cetak format `"D-Bln-YYYY"` (timezone Asia/Jakarta)
>     - Jam cetak format `"H : M"` (24-jam, tanpa zero-padding)
>   - Nama bulan (Bahasa Indonesia, 3 huruf): `["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"]`
>   - OBC & NP dicetak dengan **satu warna aksen yang konsisten**; **tidak ada** pembedaan warna berdasarkan seri (berbeda dari label PCHT/Inspeksi)
>   - Color fidelity saat cetak harus dipertahankan (warna aksen tercetak sesuai tampilan)
>   - Label MMEA **tidak** menggunakan barcode/QR (pure tekstual)

---

### E6.4 - Cetak Langsung Tanpa Dialog

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-6.4.1 | Sebagai **operator**, saya ingin **label tercetak langsung tanpa dialog cetak browser setelah proses sukses** agar **saya tidak perlu langkah manual tambahan untuk mencetak ke printer label** | 3 | High | 5 |

**Acceptance Criteria - S-6.4.1:**
```gherkin
Feature: Cetak Langsung Tanpa Dialog MMEA

Scenario: Cetak otomatis dipicu setelah penyimpanan sukses
  Given penyimpanan produk & label berhasil
  When proses cetak dimulai
  Then sistem menyiapkan konten label
  And perintah cetak dikirim ke printer default sistem operasi user
  And dialog cetak browser TIDAK ditampilkan (cetak langsung)

Scenario: Setiap label berada di halaman terpisah
  Given proses cetak berjalan dengan lebih dari satu rim
  Then setiap label diakhiri dengan page-break sebelum label berikutnya
  And tidak ada dua label dalam satu halaman fisik

Scenario: Cetak tidak dijalankan jika proses gagal
  Given penyimpanan gagal
  Then perintah cetak TIDAK dikirim
  And form tetap dalam keadaan terisi agar dapat dicoba ulang
```

> **Catatan Teknis E6.4:**
> - **Business Logic:**
>   - Cetak hanya dijalankan setelah penyimpanan produk & label sukses
>   - Cetak harus langsung (tanpa dialog cetak browser) untuk efisiensi operator — implementasi bebas selama tidak menampilkan dialog
>   - Margin halaman cetak: kiri & kanan ± 3 rem, top 0 (untuk kompatibilitas printer label/sticker)
>   - Setiap label dipisah dengan page-break; konten cetak berisi `jml_label` halaman (1 untuk cetak satuan)
>   - Setelah cetak ter-initiate, ada jeda singkat (± 1 detik) sebelum konfirmasi sukses & reset (untuk cetak batch) agar printer sempat memproses

---

## Sprint Roadmap

### Sprint 1: Halaman, Mode Cetak & Auto-Fetch (E1 + E2.1 + E2.3)
```
Sprint 1 (Week 1):
├── S-1.1.1: Akses halaman Cetak Label MMEA
├── S-1.1.2: Proteksi auth halaman
├── S-1.2.1: State awal form (mode scan-ready, field terkunci)
├── S-1.2.2: Pre-fill nomor PO dari parameter URL
├── S-1.3.1: Pemilihan mode cetak
├── S-2.1.1: Auto-fetch spesifikasi MMEA dari Sirine
├── S-2.1.2: Debounce 500 ms
├── S-2.1.3: Loading indicator saat fetch
├── S-2.3.1: Penanganan PO tidak ditemukan
└── Integration testing halaman & Sirine MMEA
```

### Sprint 2: Spesifikasi, Perhitungan Rim & Merge QC (E2.2 + E3 + E4.1 + E4.4)
```
Sprint 2 (Week 2):
├── S-2.2.1: Tampilan spesifikasi produk
├── S-3.1.1: Perhitungan jumlah rim (300 per rim / dari QC)
├── S-3.1.2: Perhitungan Lbr Kirim rim terakhir
├── S-3.2.1: Pre-fill data QC tersimpan
├── S-4.1.1: Field per-rim & nomor rim read-only
├── S-4.1.2: Pengaktifan field bertahap
├── S-4.1.3: Enter tidak memicu cetak
├── S-4.4.1: Format NP pemeriksa (truncate ke 5 char + uppercase)
└── Integration testing perhitungan rim & merge QC
```

### Sprint 3: Manajemen Baris, Reset, Konfirmasi & Penyimpanan (E4.2 + E4.3 + E5.1 + E5.2 + E5.3.1)
```
Sprint 3 (Week 3):
├── S-4.2.1: Tambah/hapus baris rim (max 5, min 1, reindex)
├── S-4.3.1: Reset form (manual & otomatis batch)
├── S-5.1.1: Konfirmasi sebelum cetak
├── S-5.2.1: Simpan spesifikasi produk (upsert, team & status)
├── S-5.3.1: Simpan data label per rim (upsert per nomor PO + rim)
└── Integration testing penyimpanan produk & label
```

### Sprint 4: Logika Waktu, Hasil Submit, Cetak Batch & Layout (E5.3.2 + E5.4 + E6.1.1 + E6.3)
```
Sprint 4 (Week 4):
├── S-5.3.2: Logika timestamp pemeriksa (refresh saat NP berubah)
├── S-5.4.1: Konfirmasi keberhasilan submit
├── S-5.4.2: Penanganan error submit (422 & server & jaringan)
├── S-6.1.1: Cetak batch seluruh rim
├── S-6.3.1: Layout & konten label tercetak (9×12 cm)
└── Integration testing flow cetak batch end-to-end
```

### Sprint 5: Cetak Satuan, Mode Cetak, Cetak Tanpa Dialog & Finalisasi (E6.1.2 + E6.2 + E6.4)
```
Sprint 5 (Week 5):
├── S-6.1.2: Cetak satuan per rim
├── S-6.2.1: Mode cetak mempengaruhi konten label
├── S-6.4.1: Cetak langsung tanpa dialog
├── End-to-end testing (PO baru, PO existing dengan QC, cetak batch & satuan)
├── Edge case testing (rencet=1500, 1 rim, max 5 rim, NP > 4 char, NP berubah/tetap)
└── Performance & regression testing
```

---

## Definition of Done (DoD)

Setiap user story dianggap **DONE** jika:

- [ ] Code sudah di-review oleh minimal 1 developer lain
- [ ] Unit tests written dan passing (coverage > 80%)
- [ ] Integration tests passing (termasuk minimal: simpan label baru, update label existing, konversi NP > 4 char, konversi NP uppercase, simpan produk dengan pembulatan sum_rim, update produk existing, qc-data terurut, qc-data PO kosong, multiple rim dalam satu request)
- [ ] No critical/high bugs dari QA
- [ ] UI responsive (mobile + desktop) — meskipun pemakaian utama desktop dengan scanner barcode
- [ ] Performance: page load < 2s, submit response < 5s
- [ ] Acceptance criteria terpenuhi semua
- [ ] Deployed ke staging
- [ ] Product Owner approved

---

## Success Metrics - Phase 5

| Metric | Target | Measurement |
|--------|--------|-------------|
| Cetak MMEA berhasil rate | > 97% | Total cetak sukses / total percobaan cetak |
| Waktu dari fetch PO hingga cetak terkirim | < 30 detik | Rata-rata waktu user-perceived |
| Sumber data MMEA (Sirine) response time | < 3 detik | Rata-rata response time fetch |
| Akurasi perhitungan jumlah rim | 100% | Jumlah rim = ceil(rencet/300) (atau jumlah QC tersimpan) |
| Akurasi Lbr Kirim rim terakhir | 100% | Sesuai aturan sisa pembagian / kasus khusus |
| Konsistensi NP tersimpan | 100% | NP tersimpan = hasil format (> 4 → first+last4) & uppercase |
| Keakuratan timestamp pemeriksa | 100% | waktu_p1/p2 hanya berubah saat NP terkait berubah |
| Tanpa duplikasi data rim | 0 duplikat | Tepat satu data per (nomor_po, nomor_rim) |
| Akurasi cetak | 100% | Jumlah halaman cetak = jumlah rim (batch) / 1 (satuan) |

---

## Risk Register

| Risk | Impact | Probability | Mitigation |
|------|--------|-------------|------------|
| Sumber data MMEA (Sirine) down/tidak responsif | Critical | Medium | Tampilkan pesan jelas, izinkan operator mencoba ulang; pertimbangkan fallback ke spesifikasi cache lokal jika tersedia |
| Pengambilan spesifikasi MMEA dilakukan langsung dari sisi klien (bergantung CORS & TLS klien) | High | Medium | Sediakan jalur pengambilan melalui backend dengan penanganan sertifikat & cache (seperti pada flow PCHT); kurangi ketergantungan pada konfigurasi klien |
| Endpoint penyimpanan & qc-data dapat dipanggil tanpa autentikasi | High | High | Wajib lindungi seluruh endpoint MMEA dengan autentikasi; tambahkan rate limit per user |
| Tidak ada validasi server-side pada penyimpanan (payload kosong/tidak lengkap → error server) | High | High | Tambahkan validasi field wajib (`no_po`, `no_rim`, `periksa1`, `periksa2`, `jml_kemas`) dengan response 422 + body `{ message, errors }` yang konsisten |
| Tidak ada unique constraint pada (nomor_po, nomor_rim) — risiko duplikasi saat race condition | Medium | Medium | Tambahkan unique constraint `(nomor_po, nomor_rim)` di penyimpanan label; pertimbangkan transaksi atomic untuk simpan produk + label |
| Penyimpanan produk & label tidak atomic (produk tersimpan tetapi label gagal) | Medium | Medium | Bungkus penyimpanan produk + label dalam satu transaksi atomic agar tidak ada state setengah jadi |
| Kasus khusus `rencet == 1500` (magic number) pada perhitungan Lbr Kirim rim terakhir | Medium | High | Konfirmasi business owner asal-usul aturan ini; ganti dengan aturan umum yang konsisten bila memungkinkan; tambahkan test eksplisit untuk rencet 1500 vs nilai lain |
| Ketidaksesuaian `jml_label` terhitung (`ceil(rencet/300)`) dengan batas tambah baris manual (maks 5) untuk order besar | Medium | Medium | Definisikan perilaku eksplisit untuk order dengan rim > 5; selaraskan batas baris dengan kebutuhan bisnis (apakah benar maksimal 5 rim?) |
| Cetak otomatis gagal karena printer default tidak terkonfigurasi di OS user | High | Medium | Sediakan pesan fallback dengan instruksi setup printer; sediakan opsi cetak manual sebagai cadangan; pertimbangkan integrasi printer langsung |
| Penyimpanan produk selalu menandai status = 2 (Selesai) meski rim belum lengkap terisi | Medium | Medium | Konfirmasi business owner: apakah MMEA memang one-shot selesai, atau perlu status dinamis seperti Inspeksi? Dokumentasikan keputusan |
| Format NP MMEA (threshold > 4) berbeda dengan flow lain | Medium | Medium | Sentralkan helper format NP; dokumentasikan & test threshold; selaraskan antar flow setelah kesepakatan business owner |
| Pembedaan `rencet` vs `jml_order` membingungkan saat perhitungan rim & Lbr Kirim | Medium | Medium | Dokumentasikan dengan jelas peran masing-masing field; tambahkan test untuk PO di mana `rencet != jml_order` |

---

*Document Version: 1.0*
*Author: Zulfikar Hidayatullah*
*Created: May 2026*
