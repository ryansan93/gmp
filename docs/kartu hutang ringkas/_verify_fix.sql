SET NOCOUNT ON;
DECLARE @start date='2026-06-01';

-- Debet saldo awal DOC SETELAH FIX (basis tgl_bayar)
DECLARE @new_debet decimal(18,2);
SELECT @new_debet = SUM(kpdd.total)
FROM konfirmasi_pembayaran_doc_det kpdd
JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id
WHERE kpd.tgl_bayar < @start;
PRINT 'Debet saldo awal DOC (BARU tgl_bayar)   : '+CAST(@new_debet AS varchar);

-- Kredit saldo awal (transfer+pph+cn) -- sama seperti sebelumnya
DECLARE @rep_trf decimal(18,2), @rep_cn decimal(18,2);
SELECT @rep_trf = SUM(rpd.transfer) FROM realisasi_pembayaran_det rpd JOIN realisasi_pembayaran rp ON rpd.id_header=rp.id WHERE rpd.transaksi='DOC' AND rp.tgl_realisasi < @start;
SELECT @rep_cn = SUM(cpd.pakai) FROM cn_post_det cpd JOIN cn_post cp ON cpd.id_header=cp.id WHERE cp.tanggal < @start AND cpd.nomor LIKE 'BYD/%';
PRINT 'Kredit saldo awal DOC (trf+cn)          : '+CAST(ISNULL(@rep_trf,0)+ISNULL(@rep_cn,0) AS varchar);

-- Saldo awal DOC operasional BARU
PRINT 'Saldo awal DOC operasional (BARU)       : '+CAST(@new_debet - (ISNULL(@rep_trf,0)+ISNULL(@rep_cn,0)) AS varchar);

-- GL saldo awal DOC
DECLARE @gl_naik decimal(18,2), @gl_turun decimal(18,2);
SELECT @gl_naik = SUM(nominal) FROM det_jurnal WHERE coa_asal='21180.200' AND tanggal < @start;
SELECT @gl_turun = SUM(nominal) FROM det_jurnal WHERE coa_tujuan='21180.200' AND tanggal < @start;
PRINT 'Saldo awal DOC GL (naik-turun)          : '+CAST(@gl_naik-@gl_turun AS varchar);
PRINT '----';
PRINT 'Selisih saldo awal (op - GL)            : '+CAST((@new_debet-(ISNULL(@rep_trf,0)+ISNULL(@rep_cn,0)))-(@gl_naik-@gl_turun) AS varchar);
