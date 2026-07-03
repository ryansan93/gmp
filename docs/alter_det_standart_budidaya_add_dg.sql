/*
 * Tambah kolom `dg` (Daily Gain) pada det_standart_budidaya.
 * DG = selisih BB antar umur, dihitung otomatis dari input BB.
 * Jalankan sekali di database. Idempotent.
 */
IF NOT EXISTS (
    SELECT 1 FROM sys.columns
    WHERE object_id = OBJECT_ID('det_standart_budidaya') AND name = 'dg'
)
BEGIN
    ALTER TABLE det_standart_budidaya
        ADD dg decimal(18,2) NULL;
END
GO
