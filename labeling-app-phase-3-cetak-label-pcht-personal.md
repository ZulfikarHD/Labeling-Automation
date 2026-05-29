# Phase 3: Cetak Label Personal PCHT
## Labeling App - Peruri

**Timeline:** 1 Bulan (4 Sprints)
**Sprint Duration:** 1 Minggu
**Team Size Recommendation:** 1 Backend Dev, 1 Frontend Dev, 1 QA

---

## Konteks Fitur

Fitur **Cetak Label Personal PCHT** memungkinkan operator memproses sebuah PO (Production Order) PCHT berukuran kecil (Order Kecil) secara **one-shot** dalam satu sesi:

1. Operator memasukkan nomor PO → sistem mengambil spesifikasi otomatis dari sumber data eksternal Sirine.
2. Form auto-fill: OBC, jumlah lembar (rencet), perhitungan jumlah label.
3. Operator melengkapi `periksa1` (wajib) dan `periksa2` (opsional), lalu mengkonfirmasi.
4. Sistem mendaftarkan PO, men-generate label rim + inschiet, menutup sesi inspeksi sebelumnya milik operator, dan menandai status PO sebagai **Selesai Diperiksa** — semuanya dalam satu transaksi atomic.
5. Sistem mencetak label secara batch (satu copy per `jml_label`) tanpa menampilkan dialog cetak browser.

> Berbeda dari **Cetak Label (Order Besar)** yang bersifat iteratif (per rim, banyak sesi), Cetak Label Personal **menyelesaikan seluruh PO dalam satu kali submit**.

---

## Product Backlog Overview

### Epic Summary

| Epic ID | Epic Name | Priority | Story Points | Sprints |
|---------|-----------|----------|--------------|---------|
| E1 | Halaman Cetak Label Personal PCHT | Critical | 5 | 1 |
| E2 | Auto-Fetch Spesifikasi PO dari Sirine | Critical | 13 | 1-2 |
| E3 | Input Form, Format NP & Validasi | Critical | 21 | 2-3 |
| E4 | Submit & Pemrosesan Transactional | Critical | 26 | 2-3 |
| E5 | Cetak Label Otomatis (Batch) | High | 13 | 3 |
| E6 | Panel Verifikasi Pegawai Harian per Tim | High | 8 | 4 |

**Total Estimated:** ~86 Story Points

---

## EPIC E1: Halaman Cetak Label Personal PCHT

### E1.1 - Akses Halaman Cetak Label Personal PCHT

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-1.1.1 | Sebagai **operator**, saya ingin **mengakses halaman Cetak Label Personal PCHT** agar **saya bisa memproses satu PO kecil end-to-end dalam satu sesi** | 3 | Critical | 1 |
| S-1.1.2 | Sebagai **operator yang belum login**, saya ingin **diarahkan ke halaman login saat mencoba akses halaman Cetak Label Personal PCHT** agar **akses ke proses produksi tetap terlindungi** | 2 | Critical | 1 |

**Acceptance Criteria - S-1.1.1:**
```gherkin
Feature: Akses Halaman Cetak Label Personal PCHT

Scenario: Operator membuka halaman Cetak Label Personal PCHT
  Given saya sudah login sebagai operator
  When saya mengakses halaman "/print-label-pcht-personal"
  Then saya melihat halaman dengan judul "Cetak Label"
  And halaman menampilkan form cetak label dengan judul "Cetak Label Order Kecil"
  And halaman menampilkan panel daftar verifikasi pegawai untuk tim saya

Scenario: Halaman dapat diakses dari menu navigasi utama
  Given saya berada di halaman manapun setelah login
  When saya membuka menu navigasi "Cetak Label"
  Then opsi "Cetak Label Personal" tersedia dan dapat diakses
  And saat opsi dipilih saya dibawa ke "/print-label-pcht-personal"
```

**Acceptance Criteria - S-1.1.2:**
```gherkin
Feature: Proteksi Akses Halaman Cetak Label Personal PCHT

Scenario: Guest mencoba akses halaman
  Given saya belum login
  When saya mengakses "/print-label-pcht-personal"
  Then saya diarahkan ke halaman login

Scenario: Session expired saat mengakses halaman
  Given session login saya sudah expired
  When saya mencoba akses "/print-label-pcht-personal"
  Then saya diarahkan ke halaman login
```

> **Catatan Teknis E1.1:**
> - **Business Logic:**
>   - Halaman Cetak Label Personal PCHT hanya dapat diakses oleh user yang sudah terautentikasi
>   - User yang belum login harus diarahkan ke halaman login
>   - Judul halaman (browser tab): "Cetak Label"
>   - Judul utama halaman: "Cetak Label Order Kecil"

---

### E1.2 - State Awal Form

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-1.2.1 | Sebagai **operator**, saya ingin **form ditampilkan dengan tim default sesuai workstation saya** agar **saya tidak perlu memilih tim secara manual tiap kali** | 2 | Critical | 1 |

**Acceptance Criteria - S-1.2.1:**
```gherkin
Feature: State Awal Form Cetak Label Personal

Scenario: Form ditampilkan dengan nilai default
  Given saya login sebagai operator dengan workstation_id = 3
  When saya membuka halaman "/print-label-pcht-personal"
  Then form menampilkan field berikut dengan nilai default:
    | Field      | Default Value      | Status    |
    | team       | workstation user (3) | enabled |
    | po         | kosong             | enabled   |
    | obc        | kosong             | enabled   |
    | jml_rim    | kosong             | read-only |
    | jml_label  | kosong             | enabled   |
    | periksa1   | kosong             | enabled   |
    | periksa2   | kosong             | enabled   |
    | start_rim  | 1                  | internal  |
    | end_rim    | 1                  | internal  |
    | produk     | PCHT               | internal  |

Scenario: Daftar tim tersedia untuk dipilih
  Given saya berada di halaman cetak label personal
  Then user dapat memilih tim dari daftar seluruh workstation yang tersedia
  And daftar tim diurutkan berdasarkan nama workstation
```

> **Catatan Teknis E1.2:**
> - **Business Logic:**
>   - Tim default = workstation yang ter-assign ke user yang sedang login
>   - Daftar tim diambil dari master data workstation, diurutkan ascending berdasarkan nama
>   - Setiap entry workstation memiliki minimal: `id` dan `nama` (kolom `workstation`)
>   - Field `produk` selalu bernilai konstanta `"PCHT"` (tidak dapat diubah user)
>   - Field `start_rim` default = `1`
>   - Field `jml_rim` bersifat informatif (read-only), nilainya akan terisi setelah auto-fetch Sirine

---

## EPIC E2: Auto-Fetch Spesifikasi PO dari Sirine

### E2.1 - Input Nomor PO & Trigger Auto-Fetch

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-2.1.1 | Sebagai **operator**, saya ingin **mengetik nomor PO dan sistem otomatis mengambil spesifikasi dari Sirine** agar **saya tidak perlu menginput OBC dan jumlah lembar secara manual** | 5 | Critical | 1 |
| S-2.1.2 | Sebagai **operator**, saya ingin **fetch spesifikasi dilakukan setelah saya berhenti mengetik (debounce)** agar **tidak ada request berlebihan ke Sirine saat saya sedang mengetik** | 3 | High | 1 |
| S-2.1.3 | Sebagai **operator**, saya ingin **melihat indikator proses saat data Sirine sedang diambil** agar **saya tahu sistem sedang bekerja** | 2 | High | 1 |

**Acceptance Criteria - S-2.1.1:**
```gherkin
Feature: Auto-Fetch Spesifikasi PO dari Sirine

Scenario: PO valid memicu auto-fill field
  Given saya berada di halaman cetak label personal
  And tidak ada error PO yang ditampilkan
  When saya memasukkan nomor PO "4000000001"
  And Sirine mengembalikan data dengan no_obc="ABC012345" dan rencet=20000
  Then field "obc" otomatis terisi "ABC012345"
  And field "jml_lembar" otomatis terisi 20000
  And field "jml_label" otomatis terisi 40
  And field "end_rim" otomatis terisi 20
  And field "jml_rim" otomatis menampilkan "20000 / 40 Rim"
  And field "obc" menjadi read-only

Scenario: PO tidak ditemukan di Sirine
  Given saya berada di halaman cetak label personal
  When saya memasukkan nomor PO "9999999999"
  And Sirine merespon error/tidak menemukan PO
  Then pesan error "Nomor PO Tidak Ditemukan di Sirine" ditampilkan di bawah field PO
  And field "obc" tetap dalam kondisi editable
  And tidak ada field lain yang ter-auto-fill
```

**Acceptance Criteria - S-2.1.2:**
```gherkin
Feature: Debounce Auto-Fetch

Scenario: Auto-fetch hanya dijalankan setelah user berhenti mengetik
  Given saya sedang mengetik nomor PO di field
  When saya mengetik karakter dengan jeda < 500 ms antar karakter
  Then sistem TIDAK mengirim request ke Sirine selama saya masih mengetik
  When saya berhenti mengetik selama 500 ms
  Then sistem baru mengirim request fetch spesifikasi ke Sirine

Scenario: Mengetik ulang membatalkan fetch sebelumnya
  Given saya baru saja berhenti mengetik dan fetch sedang berlangsung
  When saya mengetik karakter baru sebelum response Sirine kembali
  Then fetch berikutnya dijadwalkan ulang dari awal (500 ms setelah ketikan terakhir)
```

**Acceptance Criteria - S-2.1.3:**
```gherkin
Feature: Indikator Loading Saat Fetch Sirine

Scenario: Loading ditampilkan selama fetch
  Given saya memasukkan nomor PO dan menunggu fetch
  When request ke Sirine sedang berlangsung
  Then indikator proses sedang berlangsung ditampilkan
  When response Sirine kembali (baik sukses maupun gagal)
  Then indikator proses hilang
```

> **Catatan Teknis E2.1:**
> - **Business Logic:**
>   - Auto-fetch dipicu oleh perubahan nilai field PO, dengan delay debounce **500 ms** setelah ketikan terakhir
>   - Sumber data spesifikasi PO: **API eksternal Sirine** (`GET /detail-order-pcht/{no_po}`)
>   - Auto-fetch dilakukan dari sisi client langsung ke Sirine (tidak melalui backend internal)
>   - Setelah fetch sukses, field `obc` menjadi read-only (mencegah operator mengubah OBC yang sudah diverifikasi Sirine)
>   - Jika user mengubah nomor PO setelah sukses fetch, field `obc` dikembalikan ke kondisi editable saat fetch ulang gagal
>   - **Mapping data Sirine → form:**
>     - `obc = response.no_obc`
>     - `jml_lembar = response.rencet`
>     - `jml_label = ceil(rencet / 500)`
>     - `end_rim = max(1, floor(rencet / 500 / 2))`
>     - `jml_rim` (display) = `"{rencet} / {jml_label} Rim"`
> - **Pesan Error Standar:**
>   - "Nomor PO Tidak Ditemukan di Sirine"

**API Contract - S-2.1.1 (External — Sirine):**
```
GET https://sirine.peruri.co.id/sirine/api/detail-order-pcht/{no_po}

Response 200 (success):
{
  "no_po": 4000000001,
  "no_obc": "ABC012345",
  "rencet": 20000,
  "jenis": "P",
  "mesin": "1",
  "desain": "2024"
}

Response (error):
HTTP non-200 atau payload tanpa "no_obc"/"no_po" → diperlakukan sebagai "PO tidak ditemukan"
```

---

### E2.2 - Penanda Visual Berdasarkan Seri OBC

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-2.2.1 | Sebagai **operator**, saya ingin **OBC ditampilkan dengan penanda visual berbeda sesuai seri** agar **saya bisa membedakan jenis PO secara cepat saat verifikasi label** | 3 | High | 2 |

**Acceptance Criteria - S-2.2.1:**
```gherkin
Feature: Penanda Visual OBC Berdasarkan Seri

Scenario: Seri ditentukan dari karakter ke-5 OBC
  Given Sirine mengembalikan no_obc dengan karakter ke-5 = "3"
  Then field "seri" diset = "3"
  And OBC pada label memiliki penanda visual khusus seri 3

Scenario: Seri default untuk OBC dengan karakter ke-5 di luar 1-3
  Given no_obc memiliki karakter ke-5 di luar rentang "1"-"3"
  Then field "seri" diset = "1" (default)
  And OBC ditampilkan dengan penanda visual default

Scenario Outline: Mapping seri terhadap penanda OBC
  Given no_obc dengan karakter ke-5 = <char5>
  Then seri = <seri>
  And OBC ditampilkan dengan penanda visual yang dapat dibedakan antar seri

  Examples:
    | char5 | seri |
    | "1"   | "1"  |
    | "2"   | "2"  |
    | "3"   | "3"  |
    | "9"   | "1"  |
    | "A"   | "1"  |
```

> **Catatan Teknis E2.2:**
> - **Business Logic:**
>   - `seri = no_obc[4]` jika `no_obc[4]` ada dan `<= "3"`, selain itu `seri = "1"`
>   - OBC pada label tercetak harus dapat dibedakan secara visual antar seri (khususnya seri 3 vs lainnya) — implementasi visual bebas
>   - Field `seri` tidak ditampilkan di form (hanya digunakan untuk styling label cetak)

---

## EPIC E3: Input Form, Format NP & Validasi

### E3.1 - Field Input & Aturan Format

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-3.1.1 | Sebagai **operator**, saya ingin **mengisi field periksa1 (wajib) dan periksa2 (opsional)** agar **identitas inspektur tercatat di label** | 3 | Critical | 2 |
| S-3.1.2 | Sebagai **sistem**, saya ingin **memformat NP pegawai panjang menjadi 5 karakter** agar **NP yang dicetak konsisten dengan format internal** | 2 | High | 2 |
| S-3.1.3 | Sebagai **operator**, saya ingin **mereset form ke kondisi awal** agar **saya bisa segera memproses PO berikutnya tanpa refresh halaman** | 2 | High | 2 |

**Acceptance Criteria - S-3.1.1:**
```gherkin
Feature: Input Inspektur (periksa1 & periksa2)

Scenario: Operator mengisi periksa1 dan periksa2
  Given form sudah ter-auto-fill dengan data Sirine
  When saya mengisi:
    | Field    | Value  |
    | periksa1 | I444   |
    | periksa2 | I555   |
  Then kedua field tersimpan apa adanya di state form (akan diuppercase saat submit)

Scenario: periksa1 wajib, periksa2 opsional
  Given form sudah ter-auto-fill
  When saya menjalankan aksi "Buat Label" tanpa mengisi periksa1
  Then submit tidak dilanjutkan ke backend
  And field periksa1 menampilkan indikator wajib diisi

Scenario: periksa2 boleh dikosongkan
  Given saya mengisi periksa1 = "I444" dan periksa2 dikosongkan
  When saya submit
  Then proses berjalan normal tanpa error validasi periksa2
```

**Acceptance Criteria - S-3.1.2:**
```gherkin
Feature: Format NP Pegawai

Scenario Outline: NP > 5 karakter dipersingkat menjadi karakter pertama + 4 karakter terakhir
  Given NP input = <input>
  When saya submit form
  Then NP yang dikirim ke backend = <formatted>
  And NP yang tercetak di label = <formatted>

  Examples:
    | input        | formatted |
    | "I0123456"   | "I3456"   |
    | "X9876543"   | "X6543"   |
    | "I444"       | "I444"    |
    | ""           | ""        |

Scenario: NP diuppercase saat tersimpan
  Given periksa1 input = "i444"
  When backend menerima request
  Then nilai `np_users` yang tersimpan = "I444" (uppercase)
```

**Acceptance Criteria - S-3.1.3:**
```gherkin
Feature: Reset Form

Scenario: Operator menekan aksi "Clear"
  Given form sudah terisi (po, obc, periksa1, periksa2)
  When saya memicu aksi "Clear"
  Then seluruh field form dikembalikan ke nilai default awal
  And status fetch Sirine direset (obc kembali editable)
  And tidak ada request dikirim ke backend
```

> **Catatan Teknis E3.1:**
> - **Business Logic:**
>   - `periksa1` wajib diisi; `periksa2` opsional
>   - **Format NP:** jika panjang NP > 5 karakter, NP dipersingkat menjadi `karakter_pertama + 4_karakter_terakhir` (mis. `"I0123456"` → `"I3456"`)
>   - NP yang sudah diformat selalu di-uppercase sebelum disimpan ke storage (`np_users`, `np_user_p2`)
>   - Reset form mengembalikan seluruh field ke default state dan mengosongkan flag "data Sirine ter-fetch"

---

### E3.2 - Validasi Server-Side

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-3.2.1 | Sebagai **operator**, saya ingin **menerima pesan error validasi yang jelas dalam Bahasa Indonesia** agar **saya tahu field mana yang harus diperbaiki** | 5 | Critical | 3 |
| S-3.2.2 | Sebagai **sistem**, saya ingin **menolak PO yang sudah pernah terdaftar** agar **tidak terjadi duplikasi label dan kekacauan data produksi** | 5 | Critical | 3 |
| S-3.2.3 | Sebagai **sistem**, saya ingin **memvalidasi format OBC, range rim, dan field numerik** agar **data yang masuk ke tabel produksi konsisten dan benar** | 5 | Critical | 3 |

**Acceptance Criteria - S-3.2.1:**
```gherkin
Feature: Pesan Error Validasi Bahasa Indonesia

Scenario: Operator menerima daftar pesan error
  Given saya submit form dengan beberapa field tidak valid
  When backend menjawab dengan status 422
  Then saya menerima daftar pesan error per field dalam Bahasa Indonesia
  And pesan error mencakup minimal: nama field + alasan kegagalan
  And saya diberi kesempatan memperbaiki form tanpa kehilangan data input lainnya
```

**Acceptance Criteria - S-3.2.2:**
```gherkin
Feature: Validasi Unique Nomor PO

Scenario: Submit PO yang sudah terdaftar
  Given sudah ada PO "4000000001" terdaftar sebelumnya
  When saya submit form dengan po = "4000000001"
  Then sistem menolak submit dengan response error
  And saya menerima pesan "Nomor PO sudah terdaftar."
  And tidak ada record baru yang dibuat di storage
```

**Acceptance Criteria - S-3.2.3:**
```gherkin
Feature: Validasi Field Form

Scenario Outline: Validasi nomor PO
  Given saya submit form dengan po = <po>
  Then validasi mengembalikan pesan <pesan>

  Examples:
    | po           | pesan                                |
    | ""           | "Nomor PO harus diisi."              |
    | "12345"      | "Nomor PO minimal 10 karakter."      |
    | "123456789012345678901" | "Nomor PO maksimal 20 karakter." |

Scenario Outline: Validasi OBC
  Given saya submit form dengan obc = <obc>
  Then validasi mengembalikan pesan <pesan>

  Examples:
    | obc          | pesan                                              |
    | ""           | "Nomor OBC harus diisi."                           |
    | "AB1234"     | "Nomor OBC minimal 7 karakter."                    |
    | "ABCD0123456789" | "Nomor OBC maksimal 9 karakter."               |
    | "123456789"  | "Format OBC tidak valid. Harus diawali dengan 3 huruf." |

Scenario: Validasi range rim
  Given jml_lembar = 20000 (maks rim diizinkan = ceil(20000/500) = 40)
  And start_rim = 1
  When saya submit dengan end_rim = 50 (requested rims = 50)
  Then validasi gagal dengan pesan "Range rim (50) melebihi jumlah rim yang diizinkan (40)."

Scenario: end_rim harus >= start_rim
  Given start_rim = 5
  When saya submit dengan end_rim = 3
  Then validasi gagal dengan pesan "End rim harus lebih besar atau sama dengan start rim."

Scenario: Tim harus ada di master workstation
  Given saya submit dengan team = 999 (tidak ada di master workstation)
  Then validasi gagal karena tim tidak valid

Scenario: Field numerik wajib positif
  When saya submit dengan jml_lembar = 0
  Then validasi gagal dengan pesan "Jumlah lembar harus minimal 1."
  When saya submit dengan jml_rim = 0
  Then validasi gagal dengan pesan "Jumlah rim harus minimal 1."
```

> **Catatan Teknis E3.2:**
> - **Business Logic — Aturan Validasi Field:**
>   - `po`: wajib, string, panjang 10–20 karakter, **unik** (belum pernah terdaftar di registrasi PO)
>   - `obc`: wajib, string, panjang 7–9 karakter, **3 karakter pertama harus huruf alfabet (A–Z)**
>   - `team`: wajib, integer ≥ 1, harus ada di master workstation
>   - `produk`: wajib, string, maksimal 5 karakter (selalu `"PCHT"`)
>   - `jml_rim`: wajib, integer ≥ 1
>   - `jml_lembar`: wajib, integer ≥ 1
>   - `start_rim`: wajib, integer ≥ 1
>   - `end_rim`: wajib, integer, `>= start_rim`, dan `(end_rim - start_rim + 1) <= ceil(jml_lembar / 500)`
>   - `inschiet`: opsional, integer ≥ 0
>   - `periksa1`: opsional di backend (wajib di frontend), string
>   - `periksa2`: opsional, string
>   - Sebelum validasi, field numerik (`jml_rim`, `jml_lembar`, `start_rim`, `end_rim`, `inschiet`) dikonversi (cast) ke integer
> - **Response Format Error (422 — Validasi):**
>   - Body: `{ "message": "Validasi gagal", "errors": { "<field>": ["<pesan>", ...] } }`
> - **Response Format Error (422 — Application/Transaction):**
>   - Body: `{ "error": "Terjadi kesalahan saat memproses permintaan. Silakan coba lagi." }`
> - **Pesan Error Standar (Bahasa Indonesia):**
>   - PO: "Nomor PO harus diisi." / "Nomor PO minimal 10 karakter." / "Nomor PO maksimal 20 karakter." / "Nomor PO sudah terdaftar."
>   - OBC: "Nomor OBC harus diisi." / "Nomor OBC minimal 7 karakter." / "Nomor OBC maksimal 9 karakter." / "Format OBC tidak valid. Harus diawali dengan 3 huruf."
>   - Tim: "Tim harus diisi." / "Tim harus berupa angka." / "Tim harus minimal 1."
>   - Produk: "Produk harus diisi." / "Produk tidak boleh lebih dari 4 karakter."
>   - Rim: "Jumlah rim harus minimal 1." / "Start rim harus minimal 1." / "End rim harus lebih besar atau sama dengan start rim." / "Range rim ({requested}) melebihi jumlah rim yang diizinkan ({max})."
>   - Lembar: "Jumlah lembar harus minimal 1."
>   - Inschiet: "Inschiet tidak boleh kurang dari 0."

---

## EPIC E4: Submit & Pemrosesan Transactional

### E4.1 - Konfirmasi Sebelum Submit

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-4.1.1 | Sebagai **operator**, saya ingin **dimintai konfirmasi sebelum label dibuat** agar **saya dapat membatalkan jika data input salah sebelum dicatat permanen** | 3 | High | 2 |

**Acceptance Criteria - S-4.1.1:**
```gherkin
Feature: Konfirmasi Sebelum Submit

Scenario: Konfirmasi muncul saat submit
  Given form sudah terisi lengkap dan valid di sisi client
  When saya memicu aksi "Buat Label"
  Then sistem menampilkan permintaan konfirmasi sebelum mengirim ke backend
  And saya dapat membatalkan konfirmasi

Scenario: Operator membatalkan konfirmasi
  Given dialog konfirmasi sedang ditampilkan
  When saya memilih membatalkan
  Then tidak ada request dikirim ke backend
  And data form tetap dalam keadaan terisi

Scenario: Operator menyetujui konfirmasi
  Given dialog konfirmasi sedang ditampilkan
  When saya menyetujui konfirmasi
  Then submit ke backend dilanjutkan
  And indikator proses berlangsung ditampilkan
```

> **Catatan Teknis E4.1:**
> - **Business Logic:**
>   - Aksi "Buat Label" bersifat irreversible (mendaftarkan PO, membuat label, menutup sesi sebelumnya, mengubah status) — wajib didahului konfirmasi user
>   - Selama submit berjalan, aksi submit harus dinonaktifkan untuk mencegah double-submit

---

### E4.2 - Submit ke Backend & Pemrosesan Atomic

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-4.2.1 | Sebagai **sistem**, saya ingin **memproses pendaftaran PO + generate label + close session + update status dalam satu transaksi atomic** agar **tidak ada state setengah jadi jika salah satu langkah gagal** | 8 | Critical | 3 |
| S-4.2.2 | Sebagai **operator**, saya ingin **menerima konfirmasi keberhasilan setelah label berhasil dibuat** agar **saya tahu PO sudah diproses dengan sukses** | 2 | Critical | 3 |
| S-4.2.3 | Sebagai **operator**, saya ingin **menerima pesan error yang jelas jika proses gagal di tengah jalan** agar **saya bisa melaporkan masalah dan mencoba ulang** | 3 | High | 3 |

**Acceptance Criteria - S-4.2.1:**
```gherkin
Feature: Pemrosesan Atomic Submit Cetak Label Personal

Scenario: Submit sukses end-to-end
  Given form valid dengan payload berikut:
    | Field      | Value      |
    | team       | 1          |
    | po         | 4000000001 |
    | obc        | TST010110  |
    | start_rim  | 1          |
    | end_rim    | 5          |
    | produk     | PCHT       |
    | jml_lembar | 2500       |
    | jml_rim    | 5          |
    | periksa1   | I444       |
    | periksa2   | (kosong)   |
  When backend memproses request
  Then dalam satu transaksi atomic, sistem:
    | Langkah                                                    | Hasil                             |
    | Mendaftarkan PO di registrasi produk                       | record baru dengan status=0       |
    | Generate label rim (jika jml_lembar > 1000)                | label per rim, potongan Kiri+Kanan|
    | Insert data inschiet (jika jml_lembar mod 1000 > 0)        | data_inschiet + 2 label rim 999   |
    | Tutup sesi sebelumnya milik periksa1                       | finish = now() pada label terbuka |
    | Update status PO menjadi 2 (Selesai Diperiksa)             | status PO = 2                     |
  And respons HTTP 200 dengan pesan "Label berhasil dibuat"

Scenario: Salah satu langkah gagal → rollback penuh
  Given submit sedang berjalan
  When langkah generate label gagal (mis. constraint violation, koneksi DB drop)
  Then seluruh perubahan dalam transaksi di-rollback
  And tidak ada record baru di registrasi produk, label, maupun inschiet
  And status PO tidak berubah
  And backend mencatat error untuk keperluan debugging
  And user menerima pesan error "Terjadi kesalahan saat memproses permintaan. Silakan coba lagi."

Scenario: Submit untuk PO duplikat
  Given PO "4000000001" sudah terdaftar sebelumnya
  When saya submit form dengan po = "4000000001"
  Then backend menolak submit (validasi unique)
  And tidak ada operasi lanjutan yang dijalankan
  And user menerima pesan "Nomor PO sudah terdaftar."
```

**Acceptance Criteria - S-4.2.2:**
```gherkin
Feature: Konfirmasi Keberhasilan Submit

Scenario: Operator menerima konfirmasi sukses
  Given submit sukses (HTTP 200)
  When response diterima
  Then sistem menampilkan konfirmasi keberhasilan dengan judul "Berhasil" dan keterangan "Label Berhasil Dibuat"
  And form direset ke kondisi awal setelah konfirmasi
  And cetak label otomatis berjalan (lihat Epic E5)
```

**Acceptance Criteria - S-4.2.3:**
```gherkin
Feature: Penanganan Error Submit

Scenario: Error validasi (HTTP 422 dengan errors per field)
  Given backend menjawab 422 dengan body errors
  When response diterima
  Then user menerima daftar pesan error per field dalam Bahasa Indonesia
  And data form tetap terisi agar bisa diperbaiki

Scenario: Error aplikasi (HTTP 422 dengan key "error")
  Given backend menjawab 422 dengan body `{ "error": "Terjadi kesalahan..." }`
  When response diterima
  Then user menerima pesan error dari backend
  And data form tetap terisi

Scenario: Error server (HTTP 5xx atau jaringan)
  Given backend tidak merespon atau merespon 5xx
  When request gagal
  Then user menerima pesan default "Terjadi kesalahan pada server"
  And user diberi kesempatan mencoba ulang
```

> **Catatan Teknis E4.2:**
> - **Business Logic:**
>   - Pemrosesan submit Cetak Label Personal harus **atomic / transactional** — semua langkah berikut sukses bersama atau gagal bersama:
>     1. **Registrasi PO** (lihat Catatan Teknis E4.3 untuk aturan)
>     2. **Populate label rim** (lihat Catatan Teknis E4.3)
>     3. **Generate inschiet** (lihat Catatan Teknis E4.4)
>     4. **Tutup sesi inspeksi sebelumnya milik `periksa1`** (lihat Catatan Teknis E4.5)
>     5. **Update status PO = 2 (Selesai Diperiksa)** (lihat Catatan Teknis E4.6)
>   - Submit hanya dianggap sukses jika **kelima langkah** berhasil
>   - Jika ada langkah yang melempar exception, seluruh perubahan harus di-rollback dan response error dikembalikan
>   - Backend wajib mencatat error transaction ke sistem logging untuk keperluan debugging
> - **Response Format Sukses (HTTP 200):**
>   - Body: `{ "message": "Label berhasil dibuat" }`
> - **Response Format Error (HTTP 422):**
>   - Validasi: `{ "message": "Validasi gagal", "errors": { "<field>": ["<pesan>"] } }`
>   - Application/transaction: `{ "error": "Terjadi kesalahan saat memproses permintaan. Silakan coba lagi." }`

**API Contract - S-4.2.1** (request/response shape; URL bebas di arsitektur baru, contoh: `POST /api/print-label-pcht-personal`):
```
POST <endpoint cetak-label-personal>

Headers:
  Content-Type: application/json
  Accept: application/json
  Authorization: Bearer <token>          # (rekomendasi: endpoint WAJIB ter-auth di arsitektur baru)

Request Body:
{
  "team":       1,             // int, exists in master workstation
  "po":         "4000000001",  // string 10-20 char, unique pada registrasi PO
  "obc":        "ABC012345",   // string 7-9 char, 3 karakter awal huruf
  "start_rim":  1,             // int >= 1
  "end_rim":    20,            // int, >= start_rim, range <= ceil(jml_lembar/500)
  "produk":     "PCHT",        // string maksimal 5 karakter
  "jml_lembar": 20000,         // int >= 1
  "jml_rim":    40,            // int >= 1
  "jml_label":  40,            // (dikirim frontend, tidak divalidasi backend)
  "seri":       "1",           // (dikirim frontend, tidak divalidasi backend)
  "periksa1":   "I444",        // string (nullable), di-uppercase oleh backend
  "periksa2":   ""             // string (nullable)
}

Response 200:
{ "message": "Label berhasil dibuat" }

Response 422 (validation):
{
  "message": "Validasi gagal",
  "errors": {
    "po":  ["Nomor PO sudah terdaftar."],
    "obc": ["Format OBC tidak valid. Harus diawali dengan 3 huruf."]
  }
}

Response 422 (application error):
{ "error": "Terjadi kesalahan saat memproses permintaan. Silakan coba lagi." }
```

---

### E4.3 - Generate Label Records (Rim Kiri & Kanan)

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-4.3.1 | Sebagai **sistem**, saya ingin **mendaftarkan PO ke registrasi produk dengan kalkulasi jumlah rim otomatis** agar **PO siap diproses oleh modul lain (mis. Siap Periksa)** | 5 | Critical | 3 |
| S-4.3.2 | Sebagai **sistem**, saya ingin **men-generate label per rim untuk potongan Kiri & Kanan** agar **setiap rim memiliki label inspeksi yang dapat dilacak** | 5 | Critical | 3 |

**Acceptance Criteria - S-4.3.1:**
```gherkin
Feature: Registrasi PO ke Registrasi Produk

Scenario: PO baru didaftarkan dengan kalkulasi rim otomatis
  Given form valid dengan jml_lembar = 20000, start_rim = 1, end_rim = 40, team = 3
  When backend memproses registrasi PO
  Then record baru ditambahkan di registrasi produk dengan nilai:
    | Field         | Value                              |
    | no_po         | nilai dari form                    |
    | no_obc        | nilai dari form                    |
    | type          | "PCHT" (konstan)                   |
    | status        | 0 (Belum Diproses) — initial       |
    | sum_rim       | max(floor(20000/500), 1) = 40      |
    | start_rim     | 1                                  |
    | end_rim       | 40                                 |
    | assigned_team | 3                                  |
  And status PO akan diupdate ke 2 di langkah terakhir transaksi (lihat E4.6)

Scenario: PO sudah terdaftar → exception
  Given PO "4000000001" sudah terdaftar di registrasi produk
  When backend memproses registrasi PO dengan po = "4000000001"
  Then sistem melempar exception dengan pesan "Nomor PO sudah terdaftar dalam sistem"
  And transaksi seluruhnya di-rollback
```

**Acceptance Criteria - S-4.3.2:**
```gherkin
Feature: Generate Label Records per Rim

Scenario: Label dibuat untuk setiap rim dengan potongan Kiri dan Kanan
  Given PO didaftarkan dengan start_rim=1, end_rim=20, jml_lembar=20000, periksa1="I444"
  When backend menjalankan generate label
  Then tabel label terisi dengan records:
    | no_po_generated_products | no_rim | potongan | np_users | workstation |
    | {no_po}                  | 1      | Kiri     | I444     | {team}      |
    | {no_po}                  | 1      | Kanan    | I444     | {team}      |
    | {no_po}                  | 2      | Kiri     | I444     | {team}      |
    | {no_po}                  | 2      | Kanan    | I444     | {team}      |
    | ...                      | ...    | ...      | ...      | ...         |
    | {no_po}                  | 20     | Kiri     | I444     | {team}      |
    | {no_po}                  | 20     | Kanan    | I444     | {team}      |
  And total records rim = (end_rim - start_rim + 1) × 2 = 40

Scenario: periksa2 juga disertakan jika diisi
  Given periksa1 = "I444" dan periksa2 = "I555"
  When generate label berjalan
  Then setiap record label memiliki:
    | Field      | Value                              |
    | np_users   | "I444" (uppercase)                 |
    | np_user_p2 | "I555" (uppercase)                 |
    | start      | timestamp saat ini (karena periksa1 ada)  |
    | finish     | timestamp saat ini (karena periksa2 ada)  |

Scenario: Generate dilewati jika jml_lembar <= 1000
  Given jml_lembar = 800
  When backend memproses
  Then label rim TIDAK di-generate (tabel label tidak menerima rim 1..end_rim)
  And hanya proses inschiet yang berjalan (lihat E4.4)

Scenario: Upsert berdasarkan kombinasi unik
  Given label untuk (no_po=X, no_rim=5, potongan=Kiri) sudah ada
  When generate label berjalan ulang untuk (X, 5, Kiri)
  Then record yang sudah ada di-update (bukan duplikat)
```

> **Catatan Teknis E4.3:**
> - **Business Logic — Registrasi PO:**
>   - Sebelum insert, sistem mengecek apakah `no_po` sudah ada di registrasi produk; jika ya, throw exception "Nomor PO sudah terdaftar dalam sistem"
>   - `sum_rim = max(floor(jml_lembar / 500), 1)` — minimal selalu 1
>   - `type` = konstanta `"PCHT"`
>   - `status` awal = `0` (akan di-update menjadi `2` di langkah terakhir transaksi)
>   - `assigned_team` = `team` yang dipilih operator
>   - **Konstanta:** 1 rim produk = **500** lembar (untuk perhitungan `sum_rim`)
> - **Business Logic — Generate Label Rim:**
>   - Generate label hanya dijalankan jika `jml_lembar > 1000`
>   - Untuk setiap nomor rim `i` dari `start_rim` (fallback `1`) sampai `end_rim` (fallback `sum_rim`), buat 2 label: satu potongan `"Kiri"` dan satu potongan `"Kanan"`
>   - Total label rim = `(end_rim - start_rim + 1) × 2`
>   - Setiap label berisi:
>     - `no_po_generated_products` = nomor PO
>     - `no_rim` = nomor rim
>     - `potongan` ∈ `{"Kiri", "Kanan"}`
>     - `np_users` = `periksa1` di-uppercase (atau null jika kosong)
>     - `np_user_p2` = `periksa2` di-uppercase (atau null jika kosong)
>     - `start` = timestamp saat ini jika `periksa1` ada, selain itu null
>     - `finish` = timestamp saat ini jika `periksa2` ada, selain itu null
>     - `workstation` = `team`
>   - Upsert dilakukan berdasarkan kombinasi unik `(no_po_generated_products, no_rim, potongan)` — jika kombinasi sudah ada, record di-update
>   - **Konstanta:** 1 rim label = **1000** lembar (untuk threshold dan inschiet)

---

### E4.4 - Generate Inschiet

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-4.4.1 | Sebagai **sistem**, saya ingin **mencatat data inschiet jika jumlah lembar tidak habis dibagi 1000** agar **sisa lembar tetap memiliki label dan record produksi** | 3 | Critical | 3 |

**Acceptance Criteria - S-4.4.1:**
```gherkin
Feature: Generate Inschiet

Scenario: Inschiet dibuat jika ada sisa lembar
  Given jml_lembar = 20500, periksa1 = "I444", team = 3
  When backend memproses generate inschiet
  Then record inschiet baru ditambahkan dengan nilai:
    | Field    | Value      |
    | no_po    | {no_po}    |
    | inschiet | 500 (= 20500 mod 1000) |
    | np_kiri  | I444       |
    | np_kanan | I444       |
  And 2 record label inschiet ditambahkan di tabel label:
    | no_po_generated_products | no_rim | potongan | np_users | workstation |
    | {no_po}                  | 999    | Kiri     | I444     | 3           |
    | {no_po}                  | 999    | Kanan    | I444     | 3           |

Scenario: Inschiet tidak dibuat jika lembar habis dibagi 1000
  Given jml_lembar = 20000 (20000 mod 1000 = 0)
  When backend memproses
  Then TIDAK ada record inschiet yang dibuat
  And TIDAK ada label rim 999

Scenario: Inschiet untuk PO sangat kecil (≤ 1000 lembar)
  Given jml_lembar = 500
  When backend memproses
  Then label rim biasa TIDAK dibuat (jml_lembar ≤ 1000)
  And record inschiet dibuat dengan inschiet = 500
  And label rim 999 dibuat (Kiri + Kanan)

Scenario: Upsert inschiet
  Given record inschiet untuk no_po = X sudah ada
  When generate inschiet berjalan ulang untuk no_po = X
  Then record inschiet di-update (bukan duplikat)
```

> **Catatan Teknis E4.4:**
> - **Business Logic:**
>   - Inschiet dihitung sebagai: `inschiet = jml_lembar mod 1000`
>   - Jika `inschiet <= 0` → proses inschiet TIDAK dijalankan
>   - Jika `inschiet > 0` → sistem membuat:
>     1. **Satu record data inschiet** dengan kolom: `no_po`, `inschiet`, `np_kiri = periksa1`, `np_kanan = periksa1` (note: `np_kanan` mengikuti periksa1, bukan periksa2)
>     2. **Dua record label inschiet** di tabel label dengan `no_rim = 999` (reserved untuk inschiet), satu untuk potongan `"Kiri"` dan satu untuk potongan `"Kanan"`
>   - Upsert data inschiet berdasarkan `no_po` (unique)
>   - Upsert label inschiet berdasarkan kombinasi `(no_po_generated_products, no_rim, potongan)`
>   - **Konstanta khusus:** `no_rim = 999` adalah nomor rim reserved untuk label inschiet (bukan nomor rim produksi nyata)

---

### E4.5 - Tutup Sesi Inspeksi Sebelumnya

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-4.5.1 | Sebagai **sistem**, saya ingin **menutup seluruh sesi inspeksi sebelumnya milik operator `periksa1`** agar **tidak ada label terbuka yang menggantung dari sesi-sesi sebelumnya** | 3 | High | 3 |

**Acceptance Criteria - S-4.5.1:**
```gherkin
Feature: Tutup Sesi Inspeksi Sebelumnya

Scenario: Sesi sebelumnya milik periksa1 ditutup
  Given operator "I444" memiliki 3 label dari PO lain dengan finish = null:
    | no_po_generated_products | no_rim | potongan | np_users | finish |
    | 4000000099               | 5      | Kiri     | I444     | null   |
    | 4000000099               | 5      | Kanan    | I444     | null   |
    | 4000000088               | 1      | Kiri     | I444     | null   |
  When operator "I444" memproses submit cetak label personal untuk PO baru
  Then semua 3 label di atas di-update: finish = timestamp saat ini
  And tidak ada label terbuka tersisa milik "I444"

Scenario: Operator lain tidak terpengaruh
  Given operator "I555" memiliki 2 label dengan finish = null
  When operator "I444" memproses submit
  Then label milik "I555" TIDAK berubah (finish tetap null)
```

> **Catatan Teknis E4.5:**
> - **Business Logic:**
>   - Setelah label baru ter-generate, sistem mencari semua record label dengan `np_users = periksa1` AND `finish IS NULL`
>   - Seluruh record yang cocok di-update: `finish = timestamp saat ini`
>   - Tujuan: memastikan satu operator tidak memiliki "sesi terbuka" yang menggantung dari PO sebelumnya saat dia menyelesaikan PO baru
>   - Operasi ini tidak melempar exception meskipun tidak ada record yang ter-update (no-op safe)

---

### E4.6 - Update Status PO ke Selesai

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-4.6.1 | Sebagai **sistem**, saya ingin **menandai PO sebagai "Selesai Diperiksa" (status = 2) setelah semua label dibuat** agar **PO tidak lagi muncul di antrian Siap Periksa** | 2 | Critical | 3 |

**Acceptance Criteria - S-4.6.1:**
```gherkin
Feature: Update Status PO

Scenario: Status PO diset ke Selesai Diperiksa
  Given PO baru saja didaftarkan dan label sudah ter-generate
  When langkah terakhir transaksi dijalankan
  Then field `status` pada registrasi produk untuk PO tersebut di-update menjadi `2`
  And PO ini tidak lagi tampil di daftar Siap Periksa (yang memfilter status < 2)

Scenario: Status tidak berubah jika transaksi gagal
  Given submit sedang dalam langkah update status
  When salah satu langkah sebelumnya melempar exception
  Then field status TIDAK ter-update (rollback transaksi)
```

> **Catatan Teknis E4.6:**
> - **Business Logic:**
>   - Setelah semua langkah sebelumnya sukses, sistem meng-update `status` pada registrasi produk untuk `no_po` tersebut = `2`
>   - **Mapping status:**
>     - `0` = Siap Diperiksa / Belum Diproses
>     - `1` = Sedang Diperiksa (digunakan oleh flow Order Besar, bukan di flow ini)
>     - `2` = Selesai Diperiksa
>   - Pada Cetak Label Personal PCHT, status SELALU berakhir di `2` (one-shot flow)
>   - Jika PO tidak ditemukan saat update status, operasi melempar exception dan transaksi di-rollback

---

## EPIC E5: Cetak Label Otomatis (Batch)

### E5.1 - Cetak Label Otomatis Tanpa Dialog

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-5.1.1 | Sebagai **operator**, saya ingin **label tercetak otomatis setelah submit berhasil** agar **saya tidak perlu langkah manual tambahan untuk mencetak ke printer label** | 5 | High | 3 |
| S-5.1.2 | Sebagai **operator**, saya ingin **label dicetak dalam jumlah salinan sesuai `jml_label`** agar **setiap rim mendapat label tercetak** | 3 | High | 3 |

**Acceptance Criteria - S-5.1.1:**
```gherkin
Feature: Cetak Label Otomatis

Scenario: Cetak otomatis dipicu setelah submit berhasil
  Given submit sukses (HTTP 200 dengan pesan "Label berhasil dibuat")
  When response sukses diterima
  Then sistem mempersiapkan konten label
  And perintah cetak dikirim ke printer default sistem operasi user
  And dialog cetak browser TIDAK ditampilkan (cetak langsung)

Scenario: Konfirmasi keberhasilan ditampilkan setelah cetak terkirim
  Given perintah cetak sudah dikirim
  When proses cetak ter-initiate
  Then sistem menampilkan konfirmasi "Label Berhasil Dibuat"
  And form direset ke kondisi awal
```

**Acceptance Criteria - S-5.1.2:**
```gherkin
Feature: Cetak Multiple Salinan

Scenario: Jumlah salinan = jml_label
  Given jml_label = 40 (artinya rencet = 20000, ceil(20000/500) = 40 label)
  When proses cetak dimulai
  Then printer menerima 40 halaman label
  And setiap halaman berisi satu label

Scenario: Setiap label berada di halaman terpisah
  Given proses cetak berjalan dengan jml_label > 1
  Then setiap label diakhiri dengan page-break sebelum label berikutnya
  And tidak ada dua label dalam satu halaman fisik
```

> **Catatan Teknis E5.1:**
> - **Business Logic:**
>   - Cetak hanya dijalankan setelah backend mengembalikan HTTP 200
>   - Jumlah salinan label tercetak = nilai `jml_label` pada form (yang dihitung sebagai `ceil(rencet / 500)`)
>   - Cetak harus langsung (tanpa dialog cetak browser) untuk efisiensi operator — implementasi bebas selama tidak menampilkan dialog
>   - Setelah perintah cetak dikirim, form di-reset ke nilai default awal agar siap untuk PO berikutnya
>   - Margin halaman cetak: kiri & kanan ± 3 rem, top 0 (untuk kompatibilitas printer label/sticker)
>   - Penanda visual (warna) pada label tercetak harus persis sesuai tampilan layar (color fidelity penting karena seri dibedakan secara visual)

---

### E5.2 - Layout & Konten Label Tercetak

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-5.2.1 | Sebagai **operator**, saya ingin **setiap label tercetak berisi OBC, NP periksa1, NP periksa2, jumlah lembar (500), tanggal, dan jam** agar **label dapat ditempel di rim fisik untuk traceability inspeksi** | 5 | High | 3 |

**Acceptance Criteria - S-5.2.1:**
```gherkin
Feature: Konten & Layout Label Tercetak

Scenario: Field yang tercetak pada setiap label
  Given proses cetak berjalan untuk PO dengan:
    | Field    | Value      |
    | obc      | ABC312345  |
    | periksa1 | I444       |
    | periksa2 | I555       |
    | seri     | 3          |
  Then setiap label tercetak menampilkan field berikut:
    | Field      | Value                                |
    | NP Periksa 1 | I444                              |
    | NP Periksa 2 | I555                              |
    | OBC          | ABC312345 (dengan penanda visual seri 3) |
    | Jumlah Lembar| 500 Lbr                           |
    | Tanggal      | format "D-Bln-YYYY" (Bahasa Indonesia, mis. "29-Mei-2026") |
    | Jam          | format "H : M" (mis. "9 : 26")    |

Scenario: Ukuran label fisik
  Given label dicetak ke printer label
  Then setiap label berukuran 9 cm × 12 cm

Scenario: Penanda visual OBC mengikuti seri
  Given seri = 3 (no_obc[4] = "3")
  Then OBC dan NP pada label menggunakan penanda visual yang berbeda dari label seri lain
  Given seri = 1 atau 2 atau default
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
>     - NP Periksa 1 (`periksa1` yang sudah diformat & uppercase)
>     - NP Periksa 2 (`periksa2` yang sudah diformat & uppercase, boleh kosong)
>     - OBC (huruf besar dari `no_obc`)
>     - Jumlah lembar = `500` (konstan per label)
>     - Tanggal cetak dalam format `"D-Bln-YYYY"` (timezone Asia/Jakarta)
>     - Jam cetak dalam format `"H : M"` (24-jam)
>   - Format nama bulan (Bahasa Indonesia, 3 huruf): `["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"]`
>   - OBC pada label harus dapat dibedakan secara visual antar seri (terutama seri 3 vs lainnya)
> - **Business Logic — Cetak Batch:**
>   - Konten cetak berisi `jml_label` halaman, masing-masing dengan satu label dan page-break antar halaman
>   - Label TIDAK menggunakan barcode/QR (pure tekstual)

---

## EPIC E6: Panel Verifikasi Pegawai Harian per Tim

### E6.1 - Tampilkan Daftar Verifikasi Pegawai Hari Ini

| ID | User Story | Story Points | Priority | Sprint |
|----|------------|--------------|----------|--------|
| S-6.1.1 | Sebagai **operator**, saya ingin **melihat panel rangkuman verifikasi pegawai untuk tim saya hari ini** agar **saya bisa memantau target harian inspeksi tim selama bekerja** | 5 | High | 4 |
| S-6.1.2 | Sebagai **operator**, saya ingin **panel verifikasi terupdate saat saya mengganti tim** agar **saya bisa melihat statistik tim lain saat dibutuhkan** | 3 | Medium | 4 |

**Acceptance Criteria - S-6.1.1:**
```gherkin
Feature: Panel Verifikasi Pegawai Harian

Scenario: Panel menampilkan rangkuman per pegawai
  Given saya membuka halaman cetak label personal sebagai tim 3
  When panel verifikasi memuat data
  Then panel menampilkan judul "Data Verifikasi Tim {nama_tim}" dengan tanggal hari ini
  And tabel menampilkan kolom berikut untuk setiap pegawai aktif:
    | Kolom                            | Isi                              |
    | No                               | nomor urut                       |
    | NP                               | nomor pegawai                    |
    | Jumlah Verifikasi (Lembar)       | total lembar diperiksa hari ini  |
    | Jumlah Verifikasi (RIM)          | total rim diperiksa hari ini     |
    | Jumlah Verifikasi (PO)           | jumlah PO ditangani hari ini     |
    | Target Harian                    | "17.500 Lbr / 35 RIM" (konstan)  |
  And data dipaginasi dengan 10 baris per halaman

Scenario: Panel menampilkan indikator loading saat fetch
  Given panel sedang memuat data
  Then indikator proses berlangsung ditampilkan di area panel

Scenario: Panel kosong jika belum ada verifikasi
  Given belum ada verifikasi untuk tim & tanggal tertentu
  Then panel menampilkan keadaan kosong (tidak ada baris pegawai)
  And total verifikasi = 0
```

**Acceptance Criteria - S-6.1.2:**
```gherkin
Feature: Refresh Panel Saat Tim Berubah

Scenario: Mengganti tim memicu fetch ulang panel
  Given saya berada di halaman cetak label personal
  And panel sedang menampilkan data tim 3
  When saya mengubah pilihan tim menjadi tim 5
  Then panel memicu fetch ulang data untuk tim 5
  And judul panel berubah menjadi "Data Verifikasi Tim {nama_tim_5}"
  And tabel menampilkan data verifikasi untuk tim 5 hari ini

Scenario: Tim = 0 (semua tim) tidak memicu fetch
  Given pilihan tim = 0 (artinya semua tim)
  When panel coba memuat data
  Then permintaan ke endpoint tidak dikirim
  And panel tidak menampilkan data
```

> **Catatan Teknis E6.1:**
> - **Business Logic:**
>   - Panel verifikasi mengambil dua data:
>     1. **Nama tim** berdasarkan `team_id` (untuk judul panel)
>     2. **Daftar pendapatan harian** untuk `team_id` + `tanggal` (default = hari ini)
>   - Tanggal default = hari ini (zona waktu Asia/Jakarta)
>   - Jika `team_id = 0` (placeholder "semua tim"), fetch TIDAK dijalankan
>   - Panel di-refresh ulang setiap kali nilai `team_id` berubah
>   - **Pagination:** 10 baris per halaman, navigasi prev/next standar
>   - **Target harian (konstanta bisnis):** `17.500 lembar / 35 rim` per pegawai per hari — ditampilkan di setiap baris sebagai pembanding pencapaian
>   - **Agregat ditampilkan di footer panel:**
>     - Total verifikasi lembar (jumlah seluruh pegawai)
>     - Total verifikasi rim
>     - Total PO yang ditangani

**API Contract - S-6.1.1:**
```
GET /api/team-name/{team_id}

Response 200:
{ "workstation": "Tim A" }

---

GET /api/pendapatan-harian?date={YYYY-MM-DD}&team={team_id}

Response 200:
{
  "data": [
    {
      "np": "I444",
      "jumlah_lembar": 12000,
      "jumlah_rim": 24,
      "jumlah_po": 3
    },
    ...
  ]
}
```

---

## Sprint Roadmap

### Sprint 1: Halaman & Auto-Fetch Sirine (E1 + E2.1)
```
Sprint 1 (Week 1):
├── S-1.1.1: Akses halaman Cetak Label Personal PCHT
├── S-1.1.2: Proteksi auth halaman
├── S-1.2.1: State awal form (default tim, default produk)
├── S-2.1.1: Auto-fetch spesifikasi PO dari Sirine
├── S-2.1.2: Debounce 500 ms
├── S-2.1.3: Loading indicator saat fetch
└── Integration testing halaman & Sirine
```

### Sprint 2: Input, Validasi, Konfirmasi (E2.2 + E3 + E4.1)
```
Sprint 2 (Week 2):
├── S-2.2.1: Penanda visual OBC berdasarkan seri
├── S-3.1.1: Input periksa1 & periksa2
├── S-3.1.2: Format NP (truncate 5 char + uppercase)
├── S-3.1.3: Reset form
├── S-3.2.1: Pesan error validasi Bahasa Indonesia
├── S-3.2.2: Validasi unique PO
├── S-3.2.3: Validasi field (OBC, rim, numerik)
├── S-4.1.1: Konfirmasi sebelum submit
└── Integration testing validasi & UX form
```

### Sprint 3: Submit Transactional & Cetak (E4.2-E4.6 + E5)
```
Sprint 3 (Week 3):
├── S-4.2.1: Submit atomic end-to-end
├── S-4.2.2: Konfirmasi keberhasilan
├── S-4.2.3: Error handling submit
├── S-4.3.1: Registrasi PO + kalkulasi sum_rim
├── S-4.3.2: Generate label rim (Kiri + Kanan)
├── S-4.4.1: Generate inschiet
├── S-4.5.1: Tutup sesi inspeksi sebelumnya
├── S-4.6.1: Update status PO = 2
├── S-5.1.1: Cetak otomatis tanpa dialog
├── S-5.1.2: Cetak multiple salinan
├── S-5.2.1: Layout & konten label tercetak (9×12 cm)
└── Integration testing flow submit + cetak
```

### Sprint 4: Panel Verifikasi & QA (E6 + Finalization)
```
Sprint 4 (Week 4):
├── S-6.1.1: Panel daftar verifikasi pegawai harian
├── S-6.1.2: Refresh panel saat tim berubah
├── End-to-end testing
├── Edge case testing (jml_lembar ≤ 1000, inschiet = 0, dsb.)
└── Performance & regression testing
```

---

## Definition of Done (DoD)

Setiap user story dianggap **DONE** jika:

- [ ] Code sudah di-review oleh minimal 1 developer lain
- [ ] Unit tests written dan passing (coverage > 80%)
- [ ] Integration tests passing (termasuk minimal: happy path submit, duplikat PO, jml_lembar ≤ 1000, inschiet = 0)
- [ ] No critical/high bugs dari QA
- [ ] UI responsive (mobile + desktop)
- [ ] Performance: page load < 2s, submit response < 5s
- [ ] Acceptance criteria terpenuhi semua
- [ ] Deployed ke staging
- [ ] Product Owner approved

---

## Success Metrics - Phase 3

| Metric | Target | Measurement |
|--------|--------|-------------|
| Cetak Label Personal berhasil rate | > 95% | Total HTTP 200 / total submit |
| Waktu dari input PO hingga cetak | < 60 detik | Rata-rata waktu user-perceived |
| Sirine API response time | < 3 detik | Rata-rata response time fetch Sirine |
| Error validasi rate | < 10% | Total error 422 / total submit |
| Akurasi cetak label | 100% | `jml_label` sesuai `ceil(rencet/500)` |
| Konsistensi status PO | 100% | Status = 2 setelah sukses, atau tetap (rollback) saat gagal |

---

## Risk Register

| Risk | Impact | Probability | Mitigation |
|------|--------|-------------|------------|
| Sirine API down/tidak responsif | Critical | Medium | Tampilkan pesan jelas, izinkan operator menunggu/mencoba ulang; pertimbangkan fallback ke spesifikasi cache lokal jika tersedia |
| SSL certificate Sirine expired | Critical | Low | Monitoring cert expiry + alert; jalur fetch alternatif dari backend dengan trust store terpisah |
| Endpoint submit dapat dipanggil tanpa autentikasi (public API) | High | High | Wajib lindungi endpoint submit dengan autentikasi; tambahkan rate limit per user |
| Duplikat label generation pada concurrent submit (race condition) | High | Low | Wajibkan transaksi atomic + unique constraint pada nomor PO di registrasi produk |
| Cetak otomatis gagal karena printer default tidak terkonfigurasi di OS user | High | Medium | Sediakan pesan fallback dengan instruksi setup printer; sediakan opsi "cetak manual" sebagai cadangan |
| Inschiet tidak konsisten dengan label saat sisa lembar = 0 | Medium | Low | Validasi di business layer + unit test untuk edge case `jml_lembar mod 1000 = 0` |
| Format NP terpotong salah jika operator memasukkan NP < 5 karakter | Medium | Medium | Format hanya berlaku jika panjang > 5; uji unit untuk semua panjang (0–10 karakter) |
| Tutup sesi inspeksi sebelumnya menutup sesi yang masih aktif di flow Order Besar | Medium | Medium | Konfirmasi business owner: bila operator memproses Order Kecil, semua sesi terbuka miliknya WAJIB ditutup (perilaku saat ini); dokumentasikan di SOP |
| Endpoint deprecated fetch spec backend masih dapat dipanggil pihak luar | Low | Low | Hapus endpoint deprecated saat migrasi (frontend baru cukup memanggil Sirine langsung) |

---

*Document Version: 1.0*
*Author: Zulfikar Hidayatullah*
*Created: May 2026*
