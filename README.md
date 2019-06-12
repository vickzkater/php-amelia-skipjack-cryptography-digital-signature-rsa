### AMELIA - Aplikasi Manajemen Data Personalia
Aplikasi yang bertujuan untuk mengenkripsi file sehingga data tersimpan dengan aman di server karena file telah ditandatangani secara digital (digital signature), dikompres, lalu dienkripsi menjadi file berekstensi `.VIC` sehingga file hanya dapat dibuka kembali melalui proses dekripsi dari aplikasi ini.

Aplikasi ini dibuat dengan pendekatan OOP (Object-Oriented Programming) sehingga class-class yang dibutuhkan dibuat pada folder `framework`

### Algoritma yang Digunakan

- [x] Kriptografi Skipjack
- [x] Digital Signature RSA (SHA-1)
- [x] kompresi ZIP

### PHP Version Support

- [ ] PHP 7.3.x (not tested)
- [x] PHP 5.6.x

### Config
Untuk menggunakan aplikasi ini, jangan lupa lakukan konfigurasi database terlebih dulu di file `config.php`

### Support File Extensions

- [x] Image (PNG, BMP, JPG, GIF, JPEG)
- [x] Document (PDF, DOCS, XLS, TXT, CSV)