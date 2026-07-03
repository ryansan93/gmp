SET NOCOUNT ON;
DECLARE @start date='2026-06-01', @end date='2026-06-14';
SELECT
    case when mi.no_invoice is not null and mi.no_invoice <> '' then mi.no_invoice else mi.no_mm end as nomor,
    isnull(nullif(konfir.supplier,''), m.no_supplier) as supplier,
    mi.nilai as kredit,
    case when mi.coa_tujuan='21180.200' then 'DOC' else mi.coa_tujuan end as jenis,
    m.unit,
    mi.no_mm, mi.coa_asal, mi.coa_tujuan, mi.tgl_mm
from mmitem mi
left join mm m on mi.no_mm = m.no_mm
left join (
    select nomor, supplier from konfirmasi_pembayaran_doc group by nomor, supplier
) konfir on mi.no_invoice = konfir.nomor
where mi.no_mm = 'MM2606190004'
  and mi.coa_tujuan in ('21180.300','21174.000','21180.200','21180.100')
  and cast(mi.tgl_mm as date) between @start and @end
  and not ( mi.coa_asal='71105.003' and exists (select 1 from cn_post_det cpd where cpd.nomor = mi.no_invoice) )
  and (mi.coa_asal is null or mi.coa_asal not in ('21180.300','21174.000','21180.200','21180.100','21173.000'));
