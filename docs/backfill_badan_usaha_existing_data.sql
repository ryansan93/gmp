/* ============================================================================
   BACKFILL: isi kolom badan_usaha untuk data lama (yang masih NULL)
   ============================================================================
   Konteks: kolom badan_usaha di tabel mitra/pelanggan/ekspedisi baru
   ditambahkan (lihat alter_mitra_badan_usaha.sql, alter_pelanggan_badan_usaha.sql,
   alter_ekspedisi_badan_usaha.sql), jadi semua data lama nilainya NULL.

   Script ini menebak Badan Usaha dari AWALAN nama yang sudah tersimpan
   (misal "PT ...", "CV ...", "PT ... TBK") -- HANYA mengisi baris yang
   awalannya jelas, baris tanpa awalan (kebanyakan perorangan) dibiarkan NULL,
   tidak ditebak paksa jadi "Perorangan" (BU08) karena berisiko salah.

   PENTING:
   - Jalankan blok PREVIEW dulu (SELECT saja, aman) untuk lihat berapa baris
     yang bakal kena & contoh datanya, sebelum menjalankan blok UPDATE.
   - Idempotent: hanya menyentuh baris yang badan_usaha-nya masih NULL, aman
     dijalankan berkali-kali.
   - Backup / pastikan sudah ada snapshot data sebelum menjalankan UPDATE di
     database live.
   ============================================================================ */


/* ============================================================================
   STEP 1 -- PREVIEW (SELECT saja, tidak mengubah data)
   ============================================================================ */

-- Supplier (tabel pelanggan, tipe='supplier') -- kandidat terbanyak
SELECT
    CASE
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'PT %' AND UPPER(nama) LIKE '%TBK%' THEN 'BU09 - PT Tbk'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'PT %' OR UPPER(LTRIM(RTRIM(nama))) LIKE 'PT.%' THEN 'BU01 - PT'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'CV %' OR UPPER(LTRIM(RTRIM(nama))) LIKE 'CV.%' THEN 'BU02 - CV'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'KOPERASI %' OR UPPER(LTRIM(RTRIM(nama))) LIKE 'KOP %' THEN 'BU03 - Koperasi'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'UD %' OR UPPER(LTRIM(RTRIM(nama))) LIKE 'UD.%' THEN 'BU06 - UD'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'FIRMA %' THEN 'BU05 - Firma'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'YAYASAN %' THEN 'BU07 - Yayasan'
        ELSE 'TIDAK DIKENALI (dibiarkan NULL)'
    END AS tebakan_badan_usaha,
    COUNT(*) AS jml_nomor
FROM (
    SELECT p1.nomor, p1.nama FROM pelanggan p1
    RIGHT JOIN (SELECT MAX(id) id, nomor FROM pelanggan WHERE tipe = 'supplier' GROUP BY nomor) p2
        ON p1.id = p2.id
    WHERE p1.badan_usaha IS NULL
) x
GROUP BY
    CASE
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'PT %' AND UPPER(nama) LIKE '%TBK%' THEN 'BU09 - PT Tbk'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'PT %' OR UPPER(LTRIM(RTRIM(nama))) LIKE 'PT.%' THEN 'BU01 - PT'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'CV %' OR UPPER(LTRIM(RTRIM(nama))) LIKE 'CV.%' THEN 'BU02 - CV'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'KOPERASI %' OR UPPER(LTRIM(RTRIM(nama))) LIKE 'KOP %' THEN 'BU03 - Koperasi'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'UD %' OR UPPER(LTRIM(RTRIM(nama))) LIKE 'UD.%' THEN 'BU06 - UD'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'FIRMA %' THEN 'BU05 - Firma'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'YAYASAN %' THEN 'BU07 - Yayasan'
        ELSE 'TIDAK DIKENALI (dibiarkan NULL)'
    END
ORDER BY jml_nomor DESC;

-- Ganti 'supplier' -> 'pelanggan' di WHERE clause di atas utk preview tabel Pelanggan.
-- Untuk Peternak (mitra) dan Ekspedisi, jalankan query sejenis (lihat contoh di STEP 2).


/* ============================================================================
   STEP 2 -- UPDATE (jalankan satu per satu setelah preview di atas dicek)
   ============================================================================ */

-- 2a. Supplier (pelanggan, tipe='supplier')
UPDATE pelanggan
SET badan_usaha = CASE
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'PT %' AND UPPER(nama) LIKE '%TBK%' THEN 'BU09'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'PT %' OR UPPER(LTRIM(RTRIM(nama))) LIKE 'PT.%' THEN 'BU01'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'CV %' OR UPPER(LTRIM(RTRIM(nama))) LIKE 'CV.%' THEN 'BU02'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'KOPERASI %' OR UPPER(LTRIM(RTRIM(nama))) LIKE 'KOP %' THEN 'BU03'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'UD %' OR UPPER(LTRIM(RTRIM(nama))) LIKE 'UD.%' THEN 'BU06'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'FIRMA %' THEN 'BU05'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'YAYASAN %' THEN 'BU07'
        ELSE badan_usaha
    END
WHERE tipe = 'supplier'
  AND badan_usaha IS NULL;

-- 2b. Pelanggan (pelanggan, tipe='pelanggan')
UPDATE pelanggan
SET badan_usaha = CASE
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'PT %' AND UPPER(nama) LIKE '%TBK%' THEN 'BU09'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'PT %' OR UPPER(LTRIM(RTRIM(nama))) LIKE 'PT.%' THEN 'BU01'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'CV %' OR UPPER(LTRIM(RTRIM(nama))) LIKE 'CV.%' THEN 'BU02'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'KOPERASI %' OR UPPER(LTRIM(RTRIM(nama))) LIKE 'KOP %' THEN 'BU03'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'UD %' OR UPPER(LTRIM(RTRIM(nama))) LIKE 'UD.%' THEN 'BU06'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'FIRMA %' THEN 'BU05'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'YAYASAN %' THEN 'BU07'
        ELSE badan_usaha
    END
WHERE tipe = 'pelanggan'
  AND badan_usaha IS NULL;

-- 2c. Ekspedisi
UPDATE ekspedisi
SET badan_usaha = CASE
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'PT %' AND UPPER(nama) LIKE '%TBK%' THEN 'BU09'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'PT %' OR UPPER(LTRIM(RTRIM(nama))) LIKE 'PT.%' THEN 'BU01'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'CV %' OR UPPER(LTRIM(RTRIM(nama))) LIKE 'CV.%' THEN 'BU02'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'KOPERASI %' OR UPPER(LTRIM(RTRIM(nama))) LIKE 'KOP %' THEN 'BU03'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'UD %' OR UPPER(LTRIM(RTRIM(nama))) LIKE 'UD.%' THEN 'BU06'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'FIRMA %' THEN 'BU05'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'YAYASAN %' THEN 'BU07'
        ELSE badan_usaha
    END
WHERE badan_usaha IS NULL;

-- 2d. Peternak (mitra) -- hampir semua perorangan, biasanya tidak ada yang kena,
--     tetap dijalankan utk jaga-jaga ada plasma yang terdaftar atas nama PT/CV.
UPDATE mitra
SET badan_usaha = CASE
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'PT %' AND UPPER(nama) LIKE '%TBK%' THEN 'BU09'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'PT %' OR UPPER(LTRIM(RTRIM(nama))) LIKE 'PT.%' THEN 'BU01'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'CV %' OR UPPER(LTRIM(RTRIM(nama))) LIKE 'CV.%' THEN 'BU02'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'KOPERASI %' OR UPPER(LTRIM(RTRIM(nama))) LIKE 'KOP %' THEN 'BU03'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'UD %' OR UPPER(LTRIM(RTRIM(nama))) LIKE 'UD.%' THEN 'BU06'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'FIRMA %' THEN 'BU05'
        WHEN UPPER(LTRIM(RTRIM(nama))) LIKE 'YAYASAN %' THEN 'BU07'
        ELSE badan_usaha
    END
WHERE badan_usaha IS NULL;


/* ============================================================================
   STEP 3 -- VERIFIKASI setelah UPDATE
   ============================================================================ */

SELECT 'supplier' AS grup, bu.nama_badan_usaha, COUNT(*) AS jml
FROM pelanggan p
LEFT JOIN master_badan_usaha bu ON bu.id_badan_usaha = p.badan_usaha
WHERE p.tipe = 'supplier'
GROUP BY bu.nama_badan_usaha

UNION ALL

SELECT 'pelanggan', bu.nama_badan_usaha, COUNT(*)
FROM pelanggan p
LEFT JOIN master_badan_usaha bu ON bu.id_badan_usaha = p.badan_usaha
WHERE p.tipe = 'pelanggan'
GROUP BY bu.nama_badan_usaha

UNION ALL

SELECT 'ekspedisi', bu.nama_badan_usaha, COUNT(*)
FROM ekspedisi e
LEFT JOIN master_badan_usaha bu ON bu.id_badan_usaha = e.badan_usaha
GROUP BY bu.nama_badan_usaha

UNION ALL

SELECT 'peternak', bu.nama_badan_usaha, COUNT(*)
FROM mitra m
LEFT JOIN master_badan_usaha bu ON bu.id_badan_usaha = m.badan_usaha
GROUP BY bu.nama_badan_usaha

ORDER BY grup, jml DESC;

-- Baris dengan nama_badan_usaha NULL di hasil di atas = masih belum ketebak
-- (nama tanpa awalan PT/CV/dst), silakan isi manual lewat form Edit kalau perlu.
