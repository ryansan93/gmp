/*
 * Tambah kolom tgl_tutup_siklus ke lembar_kerja_hpp_history - supaya snapshot histori (fitur "Proses HPP")
 * ikut simpan Tgl Tutup Siklus, sinkron dgn kolom yg sudah ditambahkan di report/LembarKerjaHpp.php
 * (LEFT JOIN tutup_siklus by noreg).
 *
 * Jalankan sekali di database (gmp_erp_live). Idempotent.
 */
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id = OBJECT_ID('lembar_kerja_hpp_history') AND name = 'tgl_tutup_siklus')
BEGIN
    ALTER TABLE lembar_kerja_hpp_history ADD tgl_tutup_siklus date NULL;
END
GO
