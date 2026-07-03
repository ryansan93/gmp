SET NOCOUNT ON;
DECLARE @start date='2026-06-01';

-- PPh yang masuk kredit saldo awal via realisasi_pembayaran (rpd.transfer+pph)
-- Report menambahkan pph ke transfer ketika tgl_bayar > 2025-09-20
WITH konfir AS (
    SELECT kpdd.kode_unit, kpd.nomor, kpd.tgl_bayar as tanggal,
           ((kpdd.total - CASE WHEN kpd.tgl_bayar >= '2026-01-01' THEN ISNULL((SELECT SUM(cpd.pakai) FROM cn_post_det cpd WHERE cpd.nomor = kpd.nomor),0) ELSE 0 END) * (0.25/100)) as pph
    FROM konfirmasi_pembayaran_doc_det kpdd
    JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id
    LEFT JOIN (SELECT td1.* FROM terima_doc td1 RIGHT JOIN (SELECT MAX(id) id, no_order FROM terima_doc GROUP BY no_order) td2 ON td1.id=td2.id) td ON td.no_order=kpdd.no_order
    GROUP BY kpdd.kode_unit, kpd.nomor, kpd.tgl_bayar, kpdd.total
)
SELECT 
    CAST(SUM(CASE WHEN rpd.transaksi='DOC' THEN
        CASE WHEN k.tanggal <= '2025-09-20' THEN 0 ELSE k.pph END
    ELSE 0 END) AS decimal(18,2)) pph_kredit_saldo_awal
FROM realisasi_pembayaran_det rpd
JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id
LEFT JOIN konfir k ON k.nomor=rpd.no_bayar
WHERE rpd.transaksi='DOC' AND rp.tgl_realisasi < @start;
