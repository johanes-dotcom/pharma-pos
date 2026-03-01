# Spesifikasi Sistem Penjualan Apotek

## 1. Pendahuluan
- **Nama Sistem**: Pharmacy Sales System (PharmaPOS)
- **Tipe**: Web Application - Point of Sale untuk Apotek
- **Target**: Apotek skala menengah dengan potensi multi-cabang
- **Teknologi**: Laravel 10+, MySQL, Bootstrap 5

## 2. Tujuan Utama
1. Mengurangi kesalahan pencatatan stok
2. Mempercepat transaksi kasir
3. Menyediakan laporan real-time
4. Mengontrol obat kadaluarsa
5. Dashboard eksekutif

## 3. Struktur Database

### Tabel Master Data
- `roles` - Peran pengguna (Admin, Kasir, Manager)
- `users` - Data pengguna dengan relasi ke roles
- `kategori` - Kategori obat
- `supplier` - Data supplier
- `pelanggan` - Data pelanggan
- `obat` - Data obat dengan detail (barcode, harga, stok, kadaluarsa)

### Tabel Transaksi
- `pembelian` - Header transaksi pembelian
- `detail_pembelian` - Detail item pembelian
- `penjualan` - Header transaksi penjualan (POS)
- `detail_penjualan` - Detail item penjualan

### Tabel Sistem
- `stok_log` - Riwayat perubahan stok
- `audit_log` - Log perubahan data sensitif

## 4. Modul yang Akan Dibangun

### Phase 1 - Core System (Minggu 1-4)
1. **Authentication & Role**
   - Login/Logout
   - Middleware role-based
   - Password hashing (bcrypt)

2. **Master Data**
   - CRUD Obat (nama, barcode, kategori, harga beli, harga jual, stok, kadaluarsa)
   - CRUD Supplier
   - CRUD Pelanggan
   - CRUD Kategori

3. **Pembelian**
   - Input pembelian dari supplier
   - Update stok otomatis saat barang masuk
   - Catat ke stok_log

4. **Penjualan (POS)**
   - Scan/input barcode
   - Hitung subtotal & total otomatis
   - Kurangi stok otomatis (database transaction)
   - Cetak struk (print-friendly)

### Phase 2 - Laporan & Optimasi (Minggu 5-6)
1. Laporan penjualan harian & bulanan
2. Produk terlaris
3. Laba rugi (harga beli vs harga jual)
4. Export PDF/Excel

### Phase 3 - Enterprise (Minggu 7-8)
1. Siapkan struktur multi-cabang
2. Audit trail lengkap

## 5. Standar Keamanan
- Validasi input (Laravel Validation)
- Prepared statements (Eloquent ORM)
- CSRF Protection (Laravel built-in)
- Audit log untuk setiap perubahan data

## 6. Standar Kualitas
- Service Layer pattern
- Repository pattern (optional)
- Clean Code principles
- Git-ready structure

## 7. User Roles
- **Admin**: Akses penuh semua fitur
- **Manager**: Master data, laporan, tidak bisa hapus user
- **Kasir**: Transaksi POS, lihat laporan sederhana
