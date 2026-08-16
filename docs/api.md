# Dokumentasi API

Base URL:

```text
http://localhost:8000/api/v1
```

Mode autentikasi production memakai Laravel Sanctum SPA cookie mode:

1. Frontend memanggil `GET /sanctum/csrf-cookie` dengan `withCredentials: true`.
2. Frontend memanggil `POST /api/v1/auth/login`.
3. Request endpoint protected memakai cookie session `HttpOnly`, bukan Bearer token.

Header umum:

```http
Accept: application/json
Content-Type: application/json
X-XSRF-TOKEN: {token-dari-cookie-XSRF-TOKEN}
```

## Auth

### POST `/auth/login`

Ambil CSRF cookie terlebih dahulu:

```http
GET http://localhost:8000/sanctum/csrf-cookie
```

Request:

```json
{
  "email": "superadmin@apotek.test",
  "password": "password123"
}
```

Response:

```json
{
  "message": "Login berhasil.",
  "data": {
    "user": {
      "id": 1,
      "name": "Super Admin",
      "email": "superadmin@apotek.test",
      "role": "super_admin"
    }
  }
}
```

### GET `/auth/me`

Response:

```json
{
  "data": {
    "id": 1,
    "name": "Super Admin",
    "email": "superadmin@apotek.test",
    "role": "super_admin"
  }
}
```

### POST `/auth/logout`

Response:

```json
{
  "message": "Logout berhasil."
}
```

## Obat

### GET `/medicines`

Query opsional: `search`, `low_stock=1`, `near_expired=1`, `per_page`.

Response:

```json
{
  "data": [
    {
      "id": 1,
      "supplier_id": 1,
      "kode_obat": "OBT-1001",
      "nama_obat": "Paracetamol 500mg",
      "kategori": "Analgesik",
      "satuan": "tablet",
      "harga_beli": 5000,
      "harga_jual": 8000,
      "stok": 12,
      "stok_minimum": 10,
      "tanggal_expired": "2026-12-31",
      "prioritas_owner": 4,
      "is_low_stock": false,
      "is_near_expired": false
    }
  ]
}
```

### POST `/medicines`

Request:

```json
{
  "supplier_id": 1,
  "kode_obat": "OBT-1001",
  "nama_obat": "Paracetamol 500mg",
  "kategori": "Analgesik",
  "satuan": "tablet",
  "harga_beli": 5000,
  "harga_jual": 8000,
  "stok": 50,
  "stok_minimum": 10,
  "tanggal_expired": "2026-12-31",
  "prioritas_owner": 4
}
```

Endpoint lain:

- `GET /medicines/{id}`
- `PUT /medicines/{id}`
- `DELETE /medicines/{id}`

## Supplier

### GET `/suppliers`

### POST `/suppliers`

Request:

```json
{
  "nama_supplier": "PT Sehat Farma",
  "alamat": "Jl. Mawar No. 10",
  "telepon": "021123456"
}
```

Endpoint lain:

- `GET /suppliers/{id}`
- `PUT /suppliers/{id}`
- `DELETE /suppliers/{id}`

## Penjualan

### GET `/sales`

Query opsional: `search`, `start_date`, `end_date`, `per_page`.

Parameter `search` dapat digunakan untuk mencari nomor transaksi, nama obat, atau kode obat pada detail penjualan.

### POST `/sales`

Request:

```json
{
  "tanggal": "2026-06-05T10:00:00+07:00",
  "details": [
    {
      "medicine_id": 1,
      "qty": 2
    }
  ]
}
```

Response:

```json
{
  "message": "Transaksi berhasil dibuat.",
  "data": {
    "id": 1,
    "nomor_transaksi": "TRX-20260605-0001",
    "tanggal": "2026-06-05T03:00:00.000000Z",
    "total_harga": 16000,
    "details": [
      {
        "medicine_id": 1,
        "qty": 2,
        "harga": 8000,
        "subtotal": 16000
      }
    ]
  }
}
```

Endpoint lain:

- `GET /sales/{id}`
- `PUT /sales/{id}`
- `DELETE /sales/{id}`

## Faktur Pembelian

Faktur pembelian digunakan untuk menyimpan arsip asal pembelian obat, nomor batch, tanggal kedaluwarsa, dan status retur ke supplier.

### GET `/purchases`

Query opsional: `search`, `supplier_id`, `retur_status`, `per_page`.

Response:

```json
{
  "data": [
    {
      "id": 1,
      "supplier_id": 1,
      "supplier": {
        "id": 1,
        "nama_supplier": "PT Kimia Farma Trading",
        "alamat": "Jl. Budi Utomo No. 1",
        "telepon": "021-3456789"
      },
      "user_id": 1,
      "nomor_faktur": "FKT-KFT-20260601-001",
      "tanggal_faktur": "2026-06-01",
      "total_harga": 315000,
      "file_faktur": "arsip/faktur/FKT-KFT-20260601-001.pdf",
      "file_faktur_url": "/storage/arsip/faktur/FKT-KFT-20260601-001.pdf",
      "catatan": "Pembelian stok analgesik.",
      "details": [
        {
          "id": 1,
          "medicine_id": 1,
          "nomor_batch": "KFT-PCT-0626",
          "qty": 30,
          "harga_beli": 4500,
          "subtotal": 135000,
          "tanggal_expired": "2027-08-01",
          "is_expired": false,
          "days_to_expired": 392,
          "qty_retur": 0,
          "status_retur": "belum_retur",
          "tanggal_retur": null,
          "catatan_retur": null,
          "can_be_returned": false
        }
      ]
    }
  ]
}
```

### POST `/purchases`

Ketika faktur dibuat, stok obat bertambah sesuai `qty` pada detail.

Request memakai `multipart/form-data` karena `file_faktur` berupa upload foto/PDF.

```text
supplier_id=1
nomor_faktur=FKT-20260705-001
tanggal_faktur=2026-07-05
file_faktur=@faktur.jpg
catatan=Pembelian stok obat reguler.
details[0][medicine_id]=1
details[0][nomor_batch]=BATCH-001
details[0][qty]=10
details[0][harga_beli]=5000
details[0][tanggal_expired]=2027-07-05
```

Response:

```json
{
  "message": "Faktur pembelian berhasil dibuat.",
  "data": {
    "id": 1,
    "nomor_faktur": "FKT-20260705-001",
    "tanggal_faktur": "2026-07-05",
    "file_faktur": "faktur-pembelian/faktur.jpg",
    "file_faktur_url": "/storage/faktur-pembelian/faktur.jpg",
    "total_harga": 50000,
    "details": [
      {
        "medicine_id": 1,
        "qty": 10,
        "harga_beli": 5000,
        "subtotal": 50000,
        "tanggal_expired": "2027-07-05",
        "status_retur": "belum_retur"
      }
    ]
  }
}
```

Endpoint lain:

- `GET /purchases/{id}`
- `PUT /purchases/{id}`
- `DELETE /purchases/{id}`

### PATCH `/purchases/{purchase}/details/{purchaseDetail}/return`

Endpoint ini digunakan untuk menandai retur obat berdasarkan detail faktur. Jika `status_retur` menjadi `diretur`, stok obat dikurangi sesuai `qty_retur`.

Request:

```json
{
  "status_retur": "diretur",
  "qty_retur": 4,
  "tanggal_retur": "2026-07-05",
  "catatan_retur": "Retur ke supplier karena obat sudah kedaluwarsa."
}
```

Response:

```json
{
  "message": "Status retur faktur berhasil diperbarui.",
  "data": {
    "id": 1,
    "nomor_faktur": "FKT-20260705-001",
    "details": [
      {
        "id": 1,
        "qty": 10,
        "qty_retur": 4,
        "status_retur": "diretur",
        "tanggal_retur": "2026-07-05"
      }
    ]
  }
}
```

## Laporan

### GET `/reports/daily?date=2026-06-05`

### GET `/reports/monthly?month=6&year=2026`

### GET `/reports/yearly?year=2026`

Response:

```json
{
  "data": {
    "title": "Laporan harian 2026-06-05",
    "total_transaksi": 5,
    "total_penjualan": 250000,
    "total_item_terjual": 30,
    "top_medicines": [],
    "sales": []
  }
}
```

## SAW Restock Ranking

### GET `/saw/restock-ranking?start_date=2026-05-06&end_date=2026-06-05`

Response:

```json
{
  "meta": {
    "weights": {
      "penjualan": 0.3,
      "stok": 0.25,
      "expired": 0.2,
      "prioritas_owner": 0.15,
      "harga": 0.1
    },
    "start_date": "2026-05-06",
    "end_date": "2026-06-05"
  },
  "data": [
    {
      "ranking": 1,
      "medicine_id": 1,
      "kode_obat": "OBT-1001",
      "nama_obat": "Paracetamol 500mg",
      "stok": 3,
      "tanggal_expired": "2026-12-31",
      "prioritas_owner": 5,
      "nilai_preferensi": 0.88,
      "kriteria": {
        "penjualan": 40,
        "stok": 3,
        "harga": 8000,
        "expired": 80,
        "prioritas_owner": 5
      },
      "normalisasi": {
        "penjualan": 1,
        "stok": 1,
        "harga": 0.8,
        "expired": 0.5,
        "prioritas_owner": 1
      }
    }
  ]
}
```
