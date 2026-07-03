SET NOCOUNT ON;
DECLARE @start date='2026-06-01';  -- saldo awal Juni = posisi s/d 31 Mei

/* === DEBET === */
DECLARE @d_konfir decimal(18,2);
SELECT @d_konfir = SUM(kpdd.total)
FROM konfirmasi_pembayaran_doc_det kpdd
JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header = kpd.id
WHERE kpd.tgl_bayar < @start;   -- FIX: tgl_bayar basis

DECLARE @d_invmm decimal(18,2);
SELECT @d_invmm = SUM(mi.nilai)
FROM mmitem mi
LEFT JOIN mm m ON mi.no_mm = m.no_mm
WHERE mi.coa_asal = '21180.200'
  AND NOT EXISTS (
      SELECT 1 FROM (
          select nomor from konfirmasi_pembayaran_doc
          union all select nomor from konfirmasi_pembayaran_pakan
          union all select nomor from konfirmasi_pembayaran_voadip
          union all select nomor from konfirmasi_pembayaran_oa_pakan
          union all select isnull(nullif(invoice,''), nomor) from konfirmasi_pembayaran_peternak
      ) kk where kk.nomor = mi.no_invoice
  )
  AND (mi.coa_tujuan is null or mi.coa_tujuan not in ('21180.300','21174.000','21180.200','21180.100','21173.000'))
  AND cast(mi.tgl_mm as date) < @start;

/* === KREDIT === */
-- transfer + pph (realisasi DOC)
DECLARE @k_transfer decimal(18,2), @k_pph decimal(18,2);
SELECT
  @k_transfer = SUM(rpd.transfer),
  @k_pph = SUM(CASE WHEN konfir.tanggal <= '2025-09-20' THEN 0 ELSE isnull(konfir.pph,0) END)
FROM realisasi_pembayaran_det rpd
JOIN realisasi_pembayaran rp ON rpd.id_header = rp.id
LEFT JOIN (
    select kpd.nomor, kpd.tgl_bayar as tanggal,
      ((kpdd.total - case when kpd.tgl_bayar >= '2026-01-01' then isnull((select sum(cpd.pakai) from cn_post_det cpd where cpd.nomor = kpd.nomor),0) else 0 end) * 0.0025) as pph
    from konfirmasi_pembayaran_doc_det kpdd
    join konfirmasi_pembayaran_doc kpd on kpdd.id_header = kpd.id
    group by kpd.nomor, kpd.tgl_bayar, kpdd.total
) konfir ON rpd.no_bayar = konfir.nomor
WHERE rp.tgl_realisasi is not null AND rp.tgl_realisasi < @start
  AND rpd.transaksi = 'DOC';

-- CN DOC
DECLARE @k_cn decimal(18,2);
SELECT @k_cn = SUM(cpd.pakai)
FROM cn_post_det cpd
JOIN cn_post cp ON cpd.id_header = cp.id
WHERE cp.tanggal < @start AND cp.jenis_cn = 'DOC';

-- BAYAR LEWAT MEMO DOC (coa_tujuan = 21180.200)
DECLARE @k_memo decimal(18,2);
SELECT @k_memo = SUM(mi.nilai)
FROM mmitem mi
WHERE mi.coa_tujuan = '21180.200'
  AND mi.coa_asal <> '71105.003'
  AND cast(mi.tgl_mm as date) < @start;

DECLARE @debet decimal(18,2) = isnull(@d_konfir,0) + isnull(@d_invmm,0);
DECLARE @kredit decimal(18,2) = isnull(@k_transfer,0) + isnull(@k_pph,0) + isnull(@k_cn,0) + isnull(@k_memo,0);

SELECT
  isnull(@d_konfir,0)   as debet_konfir,
  isnull(@d_invmm,0)    as debet_invmm,
  @debet                as DEBET_total,
  isnull(@k_transfer,0) as kredit_transfer,
  isnull(@k_pph,0)      as kredit_pph,
  isnull(@k_cn,0)       as kredit_cn,
  isnull(@k_memo,0)     as kredit_memo,
  @kredit               as KREDIT_total,
  (@debet - @kredit)    as SALDO_AWAL_DOC;
