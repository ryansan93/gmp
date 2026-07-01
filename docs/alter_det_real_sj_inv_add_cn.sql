/*
 * Tambah kolom `cn` pada det_real_sj_inv.
 * Dipakai CreditNotePosting (jenis BKL) untuk menyimpan total pemakaian Credit Note per invoice.
 * Setelah kolom terisi, pph & total(netto) invoice dihitung ulang:
 *   pph   = round((bruto - cn) * tarif_pph%, 0)
 *   total = (bruto - cn) - pph
 *
 * Jalankan sekali di database (gmp_erp_live). Idempotent.
 */
IF NOT EXISTS (
    SELECT 1 FROM sys.columns
    WHERE object_id = OBJECT_ID('det_real_sj_inv') AND name = 'cn'
)
BEGIN
    ALTER TABLE det_real_sj_inv
        ADD cn decimal(18,2) NOT NULL CONSTRAINT DF_det_real_sj_inv_cn DEFAULT (0);
END
GO

/*
 * Snapshot tampilan modal saat posting CN, disimpan di cn_post_det agar view/edit
 * menampilkan No. SJ, Nominal (tagihan) & Sisa Tagihan untuk SEMUA jenis (termasuk BKL),
 * tanpa bergantung recompute live (yang bisa berubah setelah CN diterapkan).
 * Jalankan sekali. Idempotent.
 */
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id = OBJECT_ID('cn_post_det') AND name = 'no_sj')
    ALTER TABLE cn_post_det ADD no_sj varchar(150) NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id = OBJECT_ID('cn_post_det') AND name = 'tagihan')
    ALTER TABLE cn_post_det ADD tagihan decimal(18,2) NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id = OBJECT_ID('cn_post_det') AND name = 'sisa')
    ALTER TABLE cn_post_det ADD sisa decimal(18,2) NULL;
