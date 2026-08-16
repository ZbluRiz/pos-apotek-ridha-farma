# ERD POS Apotek

```mermaid
erDiagram
    USERS ||--o{ SALES : mencatat
    USERS ||--o{ PURCHASES : mencatat
    SUPPLIERS ||--o{ MEDICINES : memasok
    SUPPLIERS ||--o{ PURCHASES : menerbitkan
    MEDICINES ||--o{ SALE_DETAILS : dijual
    MEDICINES ||--o{ PURCHASE_DETAILS : dibeli
    SALES ||--o{ SALE_DETAILS : memiliki
    PURCHASES ||--o{ PURCHASE_DETAILS : memiliki

    USERS {
        bigint id PK
        string name
        string email UK
        string password
        enum role "super_admin, admin"
        timestamp email_verified_at
        timestamp created_at
        timestamp updated_at
    }

    SUPPLIERS {
        bigint id PK
        string nama_supplier
        text alamat
        string telepon
        timestamp created_at
        timestamp updated_at
    }

    MEDICINES {
        bigint id PK
        bigint supplier_id FK
        string kode_obat UK
        string nama_obat
        string kategori
        string satuan
        decimal harga_beli
        decimal harga_jual
        int stok
        int stok_minimum
        date tanggal_expired
        tinyint prioritas_owner
        timestamp created_at
        timestamp updated_at
    }

    SALES {
        bigint id PK
        bigint user_id FK
        string nomor_transaksi UK
        datetime tanggal
        decimal total_harga
        timestamp created_at
        timestamp updated_at
    }

    PURCHASES {
        bigint id PK
        bigint supplier_id FK
        bigint user_id FK
        string nomor_faktur UK
        date tanggal_faktur
        decimal total_harga
        string file_faktur
        text catatan
        timestamp created_at
        timestamp updated_at
    }

    SALE_DETAILS {
        bigint id PK
        bigint sale_id FK
        bigint medicine_id FK
        int qty
        decimal harga
        decimal subtotal
        timestamp created_at
        timestamp updated_at
    }

    PURCHASE_DETAILS {
        bigint id PK
        bigint purchase_id FK
        bigint medicine_id FK
        string nomor_batch
        int qty
        decimal harga_beli
        decimal subtotal
        date tanggal_expired
        int qty_retur
        string status_retur
        date tanggal_retur
        text catatan_retur
        timestamp created_at
        timestamp updated_at
    }

    PERSONAL_ACCESS_TOKENS {
        bigint id PK
        string tokenable_type
        bigint tokenable_id
        string name
        string token UK
        text abilities
        timestamp last_used_at
        timestamp expires_at
        timestamp created_at
        timestamp updated_at
    }
```

## Relasi

- `users` mencatat banyak `sales`.
- `users` mencatat banyak `purchases`.
- `suppliers` memasok banyak `medicines`.
- `suppliers` menerbitkan banyak `purchases`.
- `sales` memiliki banyak `sale_details`.
- `medicines` dapat muncul di banyak `sale_details`.
- `purchases` memiliki banyak `purchase_details`.
- `medicines` dapat muncul di banyak `purchase_details`.
- `purchase_details` menyimpan nomor batch, tanggal expired, dan status retur berdasarkan faktur pembelian.
- Token autentikasi disimpan oleh Laravel Sanctum pada `personal_access_tokens`.

## Kriteria SAW

| Kode | Kriteria | Tipe | Bobot |
|---|---|---:|---:|
| C1 | Jumlah Penjualan Obat | Benefit | 30% |
| C2 | Stok | Cost | 25% |
| C3 | Risiko Masa Kedaluwarsa | Cost | 20% |
| C4 | Tingkat Prioritas Owner (1-5) | Benefit | 15% |
| C5 | Harga Obat | Benefit | 10% |

C3 direpresentasikan sebagai risiko kedaluwarsa agar obat yang makin dekat masa kedaluwarsa mendapat penalti prioritas restock.
