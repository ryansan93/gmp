-- Tambah kolom status (aktif/non-aktif) untuk item menu individual di tabel detail_fitur.
-- Sebelumnya hanya ms_fitur (kategori) yang punya status; detail_fitur (item menu di dalam
-- kategori) tidak bisa dinonaktifkan sendiri-sendiri.
-- Dijalankan di gmp_erp_live pada 2026-08-06, 230 baris di-backfill ke status=1 (aktif).

ALTER TABLE detail_fitur ADD status TINYINT NULL CONSTRAINT DF_detail_fitur_status DEFAULT 1;
GO
UPDATE detail_fitur SET status = 1 WHERE status IS NULL;
GO
