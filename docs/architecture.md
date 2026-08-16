# Arsitektur Aplikasi

Backend mengikuti dependency rule berlapis:

```text
HTTP Adapter
  Controllers, Form Requests, API Resources
        |
        v
Application
  Use Cases
        |
        v
Domain Ports
  Repository Contracts
        ^
        |
Infrastructure
  Eloquent Repository Implementations
```

## Tanggung Jawab

- `app/Http`: menerima HTTP request, memanggil use case, dan membentuk JSON response.
- `app/Application/UseCases`: mengorkestrasi aturan aplikasi dan transaksi bisnis.
- `app/Domain/Contracts`: mendefinisikan port penyimpanan yang dibutuhkan aplikasi.
- `app/Infrastructure/Persistence`: mengimplementasikan port menggunakan Eloquent.
- `app/Policies`: mengatur otorisasi resource berdasarkan role.
- `app/Models`: merepresentasikan entity persistence dan relasi database.

Controller tidak boleh berisi query Eloquent atau aturan bisnis. Implementasi repository didaftarkan melalui `AppServiceProvider`, sehingga use case bergantung pada contract, bukan implementasi database konkret.

## Aturan Akses

- Super Admin: kelola user, supplier, obat, transaksi, laporan, dan SAW.
- Admin: kelola obat, membuat dan menghapus faktur pembelian, membuat dan menghapus transaksi; melihat laporan dan SAW.
- Admin tidak dapat mengelola user dan supplier.
- Registrasi publik dinonaktifkan. User hanya dibuat oleh Super Admin.
