/* ============================================================================
   BERSIHKAN SISA CLONE GAGAL: peternak 26M0900 (MUHAMAD ROFIK, id sumber 2040) di GML_ERP_LIVE
   Database: GML_ERP_LIVE  (jalankan dgn database aktif GML_ERP_LIVE, atau biarkan nama 3-bagian di bawah)
   ============================================================================
   Kejadian (2026-10-05 08:15): clone mitra ke GML gagal di tabel lampiran (PK bentrok id 15883) setelah
   baris-baris lain sudah terlanjur masuk. Tabel mitra/mitra_mapping/kandang/telepon_mitra/bangunan_kandang
   di GML tidak punya PK, jadi baris dgn id yg SUDAH dipakai peternak lain di GML ikut masuk -> ada id ganda:

     mitra          id 2040  : 26M0829 SUGIANTO (milik GML, status delete)  +  26M0900 MUHAMAD ROFIK (sisa clone)
     mitra_mapping  id 2040  : 26M0829 (milik GML)                          +  26M0900 (sisa clone)
     telepon_mitra  id 2070  : 082234964059 (milik GML, mitra 2040)         +  085258852523 (sisa clone)
     kandang        id 2749  : sisa clone (mapping 2040; kandang GML 2746 TIDAK disentuh)
     bangunan_kandang id 3007: sisa clone (kandang 2749; bangunan GML 3005 TIDAK disentuh)

   Skrip ini menghapus HANYA 5 baris sisa clone di atas (dicocokkan lewat id + isi kolom, bukan id saja).
   Tidak ada baris lampiran/log_tables/mitra_posisi utk peternak ini di GML (sudah dicek). Baris lokasi/
   perusahaan yg ikut ter-clone tidak dihapus (tidak berbahaya).

   CARA PAKAI: jalankan BAGIAN A (preview), lalu BAGIAN B sebagai SATU batch (DEFAULT ROLLBACK = simulasi).
   Kalau angkanya benar, ganti "ROLLBACK TRAN" jadi "COMMIT TRAN" lalu jalankan ulang. Terakhir BAGIAN C.
   Setelah ini clone peternak 26M0900 bisa diulang dari menu Master Peternak (kode clone terbaru memakai
   id baru di GML, jadi tidak bentrok lagi).
   ============================================================================ */


/* ============================== BAGIAN A - PREVIEW ============================== */
select 'mitra' as tabel, id, nomor, nama, mstatus, status from GML_ERP_LIVE.dbo.mitra where id = 2040;
select 'mitra_mapping' as tabel, id, mitra, nomor, nim from GML_ERP_LIVE.dbo.mitra_mapping where id = 2040;
select 'telepon_mitra' as tabel, id, mitra, nomor from GML_ERP_LIVE.dbo.telepon_mitra where id = 2070;
select 'kandang' as tabel, id, mitra_mapping, kandang, unit, tipe, ekor_kapasitas from GML_ERP_LIVE.dbo.kandang where mitra_mapping = 2040;
select 'bangunan_kandang' as tabel, id, kandang, bangunan, meter_panjang, meter_lebar from GML_ERP_LIVE.dbo.bangunan_kandang where kandang in (2746, 2749);
-- Harapan: sisa clone = baris mitra 26M0900, mapping 26M0900, telepon 085258852523, kandang 2749, bangunan_kandang 3007.


/* ============================== BAGIAN B - KOREKSI ============================== */
SET XACT_ABORT ON;
BEGIN TRAN;

-- Pengaman: tiap baris sisa clone harus ada TEPAT 1 (dicocokkan lewat id + isi kolom)
IF (SELECT COUNT(*) FROM GML_ERP_LIVE.dbo.mitra WHERE id = 2040 AND nomor = '26M0900' AND nama = 'MUHAMAD ROFIK') <> 1
    THROW 50001, 'Baris mitra 2040/26M0900 tidak tepat 1. Dibatalkan.', 1;
IF (SELECT COUNT(*) FROM GML_ERP_LIVE.dbo.mitra_mapping WHERE id = 2040 AND nomor = '26M0900') <> 1
    THROW 50002, 'Baris mitra_mapping 2040/26M0900 tidak tepat 1. Dibatalkan.', 1;
IF (SELECT COUNT(*) FROM GML_ERP_LIVE.dbo.telepon_mitra WHERE id = 2070 AND mitra = 2040 AND nomor = '085258852523') <> 1
    THROW 50003, 'Baris telepon_mitra 2070/085258852523 tidak tepat 1. Dibatalkan.', 1;
IF (SELECT COUNT(*) FROM GML_ERP_LIVE.dbo.kandang WHERE id = 2749 AND mitra_mapping = 2040 AND unit = 35 AND ekor_kapasitas = 4500) <> 1
    THROW 50004, 'Baris kandang 2749 tidak tepat 1. Dibatalkan.', 1;
IF (SELECT COUNT(*) FROM GML_ERP_LIVE.dbo.bangunan_kandang WHERE id = 3007 AND kandang = 2749 AND meter_panjang = 24 AND meter_lebar = 9) <> 1
    THROW 50005, 'Baris bangunan_kandang 3007 tidak tepat 1. Dibatalkan.', 1;

-- Pengaman: data milik GML yg id-nya sama HARUS tetap ada & tidak ikut terhapus
IF (SELECT COUNT(*) FROM GML_ERP_LIVE.dbo.mitra WHERE id = 2040 AND nomor = '26M0829') <> 1
   OR (SELECT COUNT(*) FROM GML_ERP_LIVE.dbo.mitra_mapping WHERE id = 2040 AND nomor = '26M0829') <> 1
   OR (SELECT COUNT(*) FROM GML_ERP_LIVE.dbo.telepon_mitra WHERE id = 2070 AND nomor = '082234964059') <> 1
    THROW 50006, 'Data milik GML (26M0829) tidak sesuai perkiraan. Dibatalkan.', 1;

-- Pengaman: tidak ada lampiran / log / posisi utk sisa clone (kalau ada, perlu ditangani manual)
IF EXISTS (SELECT 1 FROM GML_ERP_LIVE.dbo.mitra_posisi WHERE nomor = '26M0900')
    THROW 50007, 'Ada mitra_posisi utk 26M0900 di GML. Dibatalkan.', 1;
IF EXISTS (SELECT 1 FROM GML_ERP_LIVE.dbo.lampiran WHERE id IN (15883, 15884, 15885, 15886, 15887, 15888) AND filename LIKE '%Rofik%')
    THROW 50008, 'Ada lampiran Rofik di GML. Dibatalkan.', 1;

DECLARE @hapus TABLE (tabel varchar(40), jumlah int);

DELETE FROM GML_ERP_LIVE.dbo.bangunan_kandang WHERE id = 3007 AND kandang = 2749 AND meter_panjang = 24 AND meter_lebar = 9;
INSERT INTO @hapus VALUES ('bangunan_kandang', @@ROWCOUNT);

DELETE FROM GML_ERP_LIVE.dbo.kandang WHERE id = 2749 AND mitra_mapping = 2040 AND unit = 35 AND ekor_kapasitas = 4500;
INSERT INTO @hapus VALUES ('kandang', @@ROWCOUNT);

DELETE FROM GML_ERP_LIVE.dbo.telepon_mitra WHERE id = 2070 AND mitra = 2040 AND nomor = '085258852523';
INSERT INTO @hapus VALUES ('telepon_mitra', @@ROWCOUNT);

DELETE FROM GML_ERP_LIVE.dbo.mitra_mapping WHERE id = 2040 AND nomor = '26M0900';
INSERT INTO @hapus VALUES ('mitra_mapping', @@ROWCOUNT);

DELETE FROM GML_ERP_LIVE.dbo.mitra WHERE id = 2040 AND nomor = '26M0900' AND nama = 'MUHAMAD ROFIK';
INSERT INTO @hapus VALUES ('mitra', @@ROWCOUNT);

-- Harapan: masing-masing 1
SELECT tabel, jumlah AS dihapus FROM @hapus ORDER BY tabel;

-- Pengaman akhir: tidak ada id ganda tersisa & data GML 26M0829 utuh
IF EXISTS (SELECT id FROM GML_ERP_LIVE.dbo.mitra GROUP BY id HAVING COUNT(*) > 1)
   OR EXISTS (SELECT id FROM GML_ERP_LIVE.dbo.mitra_mapping GROUP BY id HAVING COUNT(*) > 1)
   OR EXISTS (SELECT id FROM GML_ERP_LIVE.dbo.telepon_mitra GROUP BY id HAVING COUNT(*) > 1)
   OR EXISTS (SELECT id FROM GML_ERP_LIVE.dbo.kandang GROUP BY id HAVING COUNT(*) > 1)
   OR EXISTS (SELECT id FROM GML_ERP_LIVE.dbo.bangunan_kandang GROUP BY id HAVING COUNT(*) > 1)
    THROW 50009, 'Masih ada id ganda setelah koreksi. Dibatalkan.', 1;

-- >>> DEFAULT = SIMULASI. Setelah angka di atas cocok, ganti ROLLBACK jadi COMMIT lalu jalankan ulang. <<<
ROLLBACK TRAN;
-- COMMIT TRAN;


/* ============================== BAGIAN C - VERIFIKASI ============================== */
-- Setelah COMMIT: tidak boleh ada nomor 26M0900 di GML, dan data 26M0829 tetap ada
select 'mitra 26M0900 (harus 0)' as cek, count(*) as n from GML_ERP_LIVE.dbo.mitra where nomor = '26M0900'
union all select 'mitra 26M0829 (harus 1)', count(*) from GML_ERP_LIVE.dbo.mitra where nomor = '26M0829'
union all select 'mitra_mapping id 2040 (harus 1)', count(*) from GML_ERP_LIVE.dbo.mitra_mapping where id = 2040
union all select 'telepon_mitra id 2070 (harus 1)', count(*) from GML_ERP_LIVE.dbo.telepon_mitra where id = 2070
union all select 'kandang 2749 (harus 0)', count(*) from GML_ERP_LIVE.dbo.kandang where id = 2749
union all select 'kandang 2746 (harus 1)', count(*) from GML_ERP_LIVE.dbo.kandang where id = 2746;
