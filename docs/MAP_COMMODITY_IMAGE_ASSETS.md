# Aset Gambar Popup Peta

Folder penyimpanan:

```text
public/images/commodities/
```

Ukuran ideal:

```text
800 x 600 px atau rasio 4:3, format webp/jpg/png.
```

Prioritas format:

```text
webp, jpg, jpeg, png, svg
```

Jika gambar belum ada, popup otomatis memakai:

```text
public/images/commodities/placeholder.svg
```

Gambar yang dibutuhkan dari data produk aktif saat ini:

| Komoditas | Nama file disarankan |
| --- | --- |
| Gabah / Padi | `gabah.webp` |
| Beras | `beras.webp` |
| Bawang Merah | `bawang-merah.webp` |
| Cabai Merah | `cabai-merah.webp` |
| Kangkung | `kangkung.webp` |

Nama opsional yang sudah disiapkan resolver-nya jika nanti ada data baru:

```text
tomat.webp
sawi.webp
bayam.webp
wortel.webp
kentang.webp
kubis.webp
bawang.webp
```

Catatan:

- File boleh memakai `.webp`, `.jpg`, `.jpeg`, `.png`, atau `.svg`.
- Jika produk punya `main_image`, gambar produk itu dipakai lebih dulu.
- Jika `main_image` kosong, sistem mencari gambar berdasarkan komoditas.
- Jika file komoditas belum ada, fallback ke placeholder supaya tidak ada broken image.
