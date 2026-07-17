/*
 * Index noreg di tutup_siklus - dipakai oleh LEFT JOIN kolom "Tgl Tutup Siklus" & filter Tutup Siklus
 * (report/LembarKerjaHpp.php). Tabel tutup_siklus sebelumnya cuma punya index di id (PK), noreg full scan.
 *
 * Jalankan sekali di database (gmp_erp_live). Idempotent.
 */
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'IX_tutup_siklus_noreg' AND object_id = OBJECT_ID('tutup_siklus'))
BEGIN
    CREATE INDEX IX_tutup_siklus_noreg ON tutup_siklus (noreg);
END
GO
