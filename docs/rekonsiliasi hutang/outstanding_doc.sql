select
--    data.supplier,
--    supl.nama as nama_supplier,
    data.jenis,
    data.unit,
    cast(isnull(sum(data.saldo_awal), 0) as decimal(15, 2)) as saldo_awal,
    cast(isnull(sum(data.debet), 0) as decimal(15, 2)) as debet,
    cast(isnull(sum(data.kredit), 0) as decimal(15, 2)) as kredit,
    cast(isnull(sum(data.saldo_akhir), 0) as decimal(15, 2)) as saldo_akhir
from
(
    select
        _data.supplier,
        _data.jenis,
        _data.unit,
        isnull(sum(_data.saldo_awal), 0) as saldo_awal,
        isnull(sum(_data.debet), 0) as debet,
        isnull(sum(_data.kredit), 0) as kredit,
        (isnull(sum(_data.saldo_awal), 0)+isnull(sum(_data.debet), 0))-isnull(sum(_data.kredit), 0) as saldo_akhir
    from
    (
        /* SALDO AWAL */
        select
            sa.supplier,
            (isnull(sum(sa.debet), 0) - isnull(sum(sa.kredit), 0)) as saldo_awal,
            0 as debet,
            0 as kredit,
            sa.jenis,
            sa.unit
        from
        (
            /* TRANSAKSI BULAN BERJALAN */
            select
                trans.supplier,
                0 as saldo_awal,
                isnull(sum(trans.debet), 0) as debet,
                isnull(sum(trans.kredit), 0) as kredit,
                trans.jenis,
                trans.unit
            from
            (
                /* DEBET */
                select
                    kpd.nomor,
                    kpd.supplier,
                    kpdd.total as debet,
                    0 as kredit,
                    'DOC' as jenis,
                    kpdd.kode_unit as unit
                from konfirmasi_pembayaran_doc_det kpdd
                left join
                    konfirmasi_pembayaran_doc kpd
                    on
                        kpdd.id_header = kpd.id
                where
                    kpd.tgl_bayar < '2026-06-01'

                union all

                select 
                    kpp.nomor, 	
                    kpp.supplier, 
                    kppd.total as debet,
                    0 as kredit,
                    'PAKAN' as jenis,
                    kppd.kode_unit as unit
                from konfirmasi_pembayaran_pakan_det kppd
                left join
                    konfirmasi_pembayaran_pakan kpp
                    on
                        kppd.id_header = kpp.id
                where
                    kpp.tgl_bayar < '2026-06-01'

                union all

                /* OA PAKAN - DEBET dari freight (terima_pakan) + pindah/retur (oa_pindah_pakan).
                   unit = segmen ke-2 antar-slash dari no_sj/no_order. */
                select
                    oa.nomor,
                    oa.supplier,
                    sum(oa.total) as debet,
                    0 as kredit,
                    'OA PAKAN' as jenis,
                    oa.unit
                from (
                    select tp.no_bbm as nomor, kp.ekspedisi_id as supplier, substring(kp.no_sj, charindex('/',kp.no_sj+'//')+1, charindex('/',kp.no_sj+'//',charindex('/',kp.no_sj+'//')+1)-charindex('/',kp.no_sj+'//')-1) as unit, sum(dtp.jumlah)*kp.ongkos_angkut as total from det_terima_pakan dtp
                    left join terima_pakan tp on dtp.id_header = tp.id
                    left join kirim_pakan kp on tp.id_kirim_pakan = kp.id
                    where tp.tgl_terima < '2026-06-01' and kp.jenis_kirim = 'opkg'
                    group by tp.no_bbm, kp.ekspedisi_id, kp.ongkos_angkut, kp.no_sj
                    union all
                    select opp.no_sj as nomor, coalesce(krm.ekspedisi_id, (select min(e.nomor) from ekspedisi e where e.nama = opp.ekspedisi)) as supplier, coalesce(nullif(substring(opp.no_sj, charindex('/',opp.no_sj+'//')+1, charindex('/',opp.no_sj+'//',charindex('/',opp.no_sj+'//')+1)-charindex('/',opp.no_sj+'//')-1),''), substring(krm.no_order, charindex('/',krm.no_order+'//')+1, charindex('/',krm.no_order+'//',charindex('/',krm.no_order+'//')+1)-charindex('/',krm.no_order+'//')-1)) as unit, opp.ongkos_angkut as total from oa_pindah_pakan opp
                    left join ( select kp.no_sj, tp.no_bbm as kode_trans, kp.ekspedisi_id, tp.tgl_terima as tanggal, kp.no_order from kirim_pakan kp left join terima_pakan tp on kp.id = tp.id_kirim_pakan group by kp.no_sj, tp.no_bbm, kp.ekspedisi_id, tp.tgl_terima, kp.no_order union all select no_retur as no_sj, no_retur as kode_trans, ekspedisi_id, tgl_retur as tanggal, no_order from retur_pakan rp ) krm on opp.no_sj = krm.no_sj
                    where coalesce(krm.tanggal, opp.tgl_terima) < '2026-06-01'
                ) oa
                group by oa.nomor, oa.supplier, oa.unit
                /* END - OA PAKAN */

                /* ===================================================================
                   DEBET OA versi KONFIRMASI - DINONAKTIFKAN (revert ke freight 2026-06-23,
                   karena saldo awal tak cocok GL: konfir < freight = freight belum dikonfirmasi).
                -----------------------------------------------------------------------
                select kpop.nomor, kpop.ekspedisi_id as supplier, (kpop.total + kpop.potongan_pph_23) as debet, 0 as kredit, 'OA PAKAN' as jenis, oa_unit.unit
                from konfirmasi_pembayaran_oa_pakan kpop
                left join ( select kpopd.id_header, min(substring(kp.no_order, charindex('/',kp.no_order+'//')+1, charindex('/',kp.no_order+'//',charindex('/',kp.no_order+'//')+1)-charindex('/',kp.no_order+'//')-1)) as unit from konfirmasi_pembayaran_oa_pakan_det kpopd left join kirim_pakan kp on kp.no_sj = kpopd.no_sj group by kpopd.id_header ) oa_unit on oa_unit.id_header = kpop.id
                where kpop.tgl_bayar < '2026-06-01'
                =================================================================== */

                union all

                select
                    kpv.nomor, 
                    kpv.supplier, 
                    kpv.total as debet,
                    0 as kredit,
                    pc.kode as jenis,
                    kpvd.kode_unit as unit
                from konfirmasi_pembayaran_voadip_det kpvd
                left join
                    konfirmasi_pembayaran_voadip kpv
                    on
                        kpvd.id_header = kpv.id
                left join
                    (select * from pelanggan_coa where kode like '%OVK%') pc
                    on
                        pc.no_pelanggan = kpv.supplier
                where
                    kpv.tgl_bayar < '2026-06-01'

                union all

                select
                    kpp.nomor,
                    kpp.mitra as supplier,
                    kpp.total as debet,
                    0 as kredit,
                    'RHPP' as jenis,
                    SUBSTRING(REPLACE(REPLACE(kpp.invoice, 'INV/RHPP/G/', ''), 'INV/RHPP/', ''), 1, 3) as unit
                from konfirmasi_pembayaran_peternak kpp
                where
                    kpp.tgl_bayar < '2026-06-01'

                union all

                select
                    op.no_order as nomor,
                    op.supplier,
                    op.total as debet,
                    0 as kredit,
                    'PERALATAN' as jenis,
                    op.unit
                from order_peralatan op
                where
                    op.tgl_order < '2026-06-01'

                union all

                select
                    dpd.nomor,
                    konfir.supplier,
                    dpd.pakai as debet,
                    0 as kredit,
                    case
                        when dp.jenis_dn = 'PKN' then
                            'PAKAN'
                        else
                            dp.jenis_dn 
                    end as jenis,
                    konfir.kode_unit as unit
                from dn_post_det dpd 
                left join
                    dn_post dp 
                    on
                        dpd.id_header = dp.id
                left join
                    (
                        /* DOC */
                        select
                            kpd.nomor,
                            kpd.supplier,
                            kpdd.kode_unit
                        from konfirmasi_pembayaran_doc kpd 
                        left join
                            (select id_header, kode_unit from konfirmasi_pembayaran_doc_det group by id_header, kode_unit) kpdd
                            on
                                kpd.id = kpdd.id_header
                            
                        union all
                        
                        /* PAKAN */
                        select
                            kpp.nomor,
                            kpp.supplier,
                            kppd.kode_unit
                        from konfirmasi_pembayaran_pakan kpp 
                        left join
                            (select id_header, kode_unit from konfirmasi_pembayaran_pakan_det group by id_header, kode_unit) kppd
                            on
                                kpp.id = kppd.id_header
                            
                        union all
                        
                        /* OVK */
                        select
                            kpv.nomor,
                            kpv.supplier,
                            kpvd.kode_unit
                        from konfirmasi_pembayaran_voadip kpv 
                        left join
                            (select id_header, kode_unit from konfirmasi_pembayaran_voadip_det group by id_header, kode_unit) kpvd
                            on
                                kpv.id = kpvd.id_header
                                
                        union all
                        
                        /* RHPP */
                        select
                            kpp.nomor,
                            kpp.mitra as supplier,
                            SUBSTRING(REPLACE(REPLACE(kpp.invoice, 'INV/RHPP/G/', ''), 'INV/RHPP/', ''), 1, 3) as kode_unit
                        from konfirmasi_pembayaran_peternak kpp
                        
                        union all
                        
                        /* OA PAKAN */
                        select
                            kpop.nomor,
                            kpop.ekspedisi_id as supplier,
                            kpopd.kode_unit
                        from konfirmasi_pembayaran_oa_pakan kpop 
                        left join
                            (
                                select 
                                    kpopd.id_header, 
                                    SUBSTRING( REPLACE(REPLACE(kp.no_order, 'OPK/', ''), 'OP/', ''), 1, 3 ) as kode_unit
                                from konfirmasi_pembayaran_oa_pakan_det kpopd 
                                left join
                                    (
                                        select no_sj, no_order from kirim_pakan kp 
                                        
                                        union all
                                        
                                        select no_retur as no_sj, no_order from retur_pakan rp 
                                    ) kp 
                                    on
                                        kpopd.no_sj = kp.no_sj
                                group by 
                                    kpopd.id_header, 
                                    SUBSTRING( REPLACE(REPLACE(kp.no_order, 'OPK/', ''), 'OP/', ''), 1, 3 )
                            ) kpopd
                            on
                                kpop.id = kpopd.id_header
                    ) konfir
                    on
                        konfir.nomor = dpd.nomor
                where
                    dp.tanggal < '2026-06-01'

                union all

                /* INVOICE LEWAT MEMO */
                select * from (
                    select
                        case
                            when mi.no_invoice is not null and mi.no_invoice <> '' then
                                mi.no_invoice 
                            else
                                mi.no_mm
                        end as nomor,
                        m.no_supplier as supplier,
                        mi.nilai as debet,
                        0 as kredit,
                        case
                            when mi.coa_asal = '21180.300' then
                                'OVK'
                            when mi.coa_asal = '21174.000' then
                                'OVK EXTERN'
                            when mi.coa_asal = '21180.200' then
                                'DOC'
                            when mi.coa_asal = '21180.100' then
                                'PAKAN'
                        end as jenis,
                        -- pc.kode as jenis,
                        isnull(nullif(mi.unit, ''), m.unit) as unit
                    from mmitem mi
                    left join
                        mm m
                        on
                            mi.no_mm = m.no_mm
                    left join
                        pelanggan_coa pc
                        on
                            pc.no_pelanggan = m.no_supplier and
                            pc.no_coa = mi.coa_asal
                    where
                        mi.coa_asal in ('21180.300', '21174.000', '21180.200', '21180.100') and
                        not exists (
                            select 1 from (
                                select nomor from konfirmasi_pembayaran_doc
                                union all select nomor from konfirmasi_pembayaran_pakan
                                union all select nomor from konfirmasi_pembayaran_voadip
                                union all select nomor from konfirmasi_pembayaran_oa_pakan
                                union all select isnull(nullif(invoice,''), nomor) from konfirmasi_pembayaran_peternak
                            ) kk where kk.nomor = mi.no_invoice
                        ) and
                        /* kontrol: lewati memo reklasifikasi antar-akun hutang (mis. koreksi CN DOC<->PAKAN); tagihan asli lewat memo coa_tujuan-nya akun stok/biaya, bukan hutang */
                        (mi.coa_tujuan is null or mi.coa_tujuan not in ('21180.300', '21174.000', '21180.200', '21180.100', '21173.000')) and
                        cast(mi.tgl_mm as date) < '2026-06-01'
                ) inv_mm
                /* END - INVOICE LEWAT MEMO */
                /* END - DEBET */

                union all

                /* KREDIT */
                select
                    cpd.nomor,
                    konfir.supplier,
                    0 as debet,
                    cpd.pakai as kredit,
                    case
                        when cp.jenis_cn = 'PKN' then
                            'PAKAN'
                        else
                            cp.jenis_cn 
                    end as jenis,
                    konfir.kode_unit as unit
                from cn_post_det cpd 
                left join
                    cn_post cp
                    on
                        cpd.id_header = cp.id
                left join
                    (
                        /* DOC */
                        select
                            kpd.nomor,
                            kpd.supplier,
                            kpdd.kode_unit
                        from konfirmasi_pembayaran_doc kpd 
                        left join
                            (select id_header, kode_unit from konfirmasi_pembayaran_doc_det group by id_header, kode_unit) kpdd
                            on
                                kpd.id = kpdd.id_header
                            
                        union all
                        
                        /* PAKAN */
                        select
                            kpp.nomor,
                            kpp.supplier,
                            kppd.kode_unit
                        from konfirmasi_pembayaran_pakan kpp 
                        left join
                            (select id_header, kode_unit from konfirmasi_pembayaran_pakan_det group by id_header, kode_unit) kppd
                            on
                                kpp.id = kppd.id_header
                            
                        union all
                        
                        /* OVK */
                        select
                            kpv.nomor,
                            kpv.supplier,
                            kpvd.kode_unit
                        from konfirmasi_pembayaran_voadip kpv 
                        left join
                            (select id_header, kode_unit from konfirmasi_pembayaran_voadip_det group by id_header, kode_unit) kpvd
                            on
                                kpv.id = kpvd.id_header
                                
                        union all
                        
                        /* RHPP */
                        select
                            kpp.nomor,
                            kpp.mitra as supplier,
                            SUBSTRING(REPLACE(REPLACE(kpp.invoice, 'INV/RHPP/G/', ''), 'INV/RHPP/', ''), 1, 3) as kode_unit
                        from konfirmasi_pembayaran_peternak kpp
                        
                        union all
                        
                        /* OA PAKAN */
                        select
                            kpop.nomor,
                            kpop.ekspedisi_id as supplier,
                            kpopd.kode_unit
                        from konfirmasi_pembayaran_oa_pakan kpop 
                        left join
                            (
                                select 
                                    kpopd.id_header, 
                                    SUBSTRING( REPLACE(REPLACE(kp.no_order, 'OPK/', ''), 'OP/', ''), 1, 3 ) as kode_unit
                                from konfirmasi_pembayaran_oa_pakan_det kpopd 
                                left join
                                    (
                                        select no_sj, no_order from kirim_pakan kp 
                                        
                                        union all
                                        
                                        select no_retur as no_sj, no_order from retur_pakan rp 
                                    ) kp 
                                    on
                                        kpopd.no_sj = kp.no_sj
                                group by 
                                    kpopd.id_header, 
                                    SUBSTRING( REPLACE(REPLACE(kp.no_order, 'OPK/', ''), 'OP/', ''), 1, 3 )
                            ) kpopd
                            on
                                kpop.id = kpopd.id_header
                    ) konfir
                    on
                        konfir.nomor = cpd.nomor
                where
                    cp.tanggal < '2026-06-01'

                union all
        
                select
                    bp.no_faktur,
                    op.supplier,
                    0 as debet,
                    bp.jml_bayar as kredit,
                    'PERALATAN' as jenis,
                    op.unit 
                from bayar_peralatan bp 
                left join
                    order_peralatan op 
                    on
                        op.no_order = bp.no_order 
                where
                    bp.tgl_realisasi is not null and bp.tgl_realisasi < '2026-06-01'

                union all

                select 
                    rp.nomor,
                    rp.supplier,
                    0 as debet,
                    case
                        when rpd.transaksi = 'DOC' then
                            case
                                when konfir.tanggal <= '2025-09-20' then
                                    rpd.transfer
                                else
                                    rpd.transfer+konfir.pph
                            end
                        else
                            rpd.transfer
                    end as kredit,
                    pc.kode as jenis,
                    konfir.kode_unit as unit
                from realisasi_pembayaran_det rpd
                left join
                    realisasi_pembayaran rp
                    on
                        rpd.id_header = rp.id
                left join
                    (
                        select kpdd.kode_unit, kpd.nomor, kpd.tgl_bayar as tanggal, ((kpdd.total - case when kpd.tgl_bayar >= '2026-01-01' then isnull((select sum(cpd.pakai) from cn_post_det cpd where cpd.nomor = kpd.nomor), 0) else 0 end - isnull((select sum(mi.nilai) from mmitem mi where mi.coa_asal = '12040.000' and mi.coa_tujuan = '21180.200' and mi.no_invoice = kpd.nomor), 0)) * (0.25/100)) as pph from konfirmasi_pembayaran_doc_det kpdd 
                        left join
                            konfirmasi_pembayaran_doc kpd 
                            on
                                kpdd.id_header = kpd.id
                        left join
                            (
                                select td1.* from terima_doc td1
                                right join
                                    (select max(id) as id, no_order from terima_doc group by no_order) td2
                                    on
                                        td1.id = td2.id
                            ) td
                            on
                                td.no_order = kpdd.no_order
                        group by
                            kpdd.kode_unit, kpd.nomor, kpd.tgl_bayar, kpdd.total
                            
                        union all
                        
                        select kppd.kode_unit, kpp.nomor, null as tanggal, 0 as pph from konfirmasi_pembayaran_pakan_det kppd 
                        left join
                            konfirmasi_pembayaran_pakan kpp 
                            on
                                kppd.id_header = kpp.id
                        group by
                            kppd.kode_unit, kpp.nomor
                            
                        union all
                        
                        select kpvd.kode_unit, kpv.nomor, null as tanggal, 0 as pph from konfirmasi_pembayaran_voadip_det kpvd 
                        left join
                            konfirmasi_pembayaran_voadip kpv 
                            on
                                kpvd.id_header = kpv.id
                        group by
                            kpvd.kode_unit, kpv.nomor

                        union all

                        /* pembayaran lewat memo: hitung PPh 0.25% atas nilai DOC memo (coa_asal 21180.200) agar cocok dgn potongan PPh di GL */
                        select m.unit as kode_unit, m.no_mm as nomor, null as tanggal,
                            isnull((
                                select sum(mi.nilai) * 0.0025
                                from mmitem mi
                                where mi.no_mm = m.no_mm and mi.coa_asal = '21180.200'
                            ), 0) as pph
                        from mm m
                    ) konfir
                    on
                        rpd.no_bayar = konfir.nomor
                left join
                    pelanggan_coa pc
                    on
                        pc.no_pelanggan = rp.supplier and
                        pc.kode like '%'+REPLACE(rpd.transaksi, 'VOADIP', 'OVK')+'%'
                where
                    (rp.tgl_realisasi is not null and rp.tgl_realisasi < '2026-06-01') and
                    rpd.transaksi not in ('PLASMA', 'OA PAKAN')

                union all

                /* KREDIT PLASMA - pakai rp.peternak sebagai supplier */
                select
                    rpd.no_bayar as nomor,
                    rp.peternak as supplier,
                    0 as debet,
                    rpd.transfer as kredit,
                    'RHPP' as jenis,
                    SUBSTRING(REPLACE(REPLACE(rpd.no_bayar, 'INV/RHPP/G/', ''), 'INV/RHPP/', ''), 1, 3) as unit
                from realisasi_pembayaran_det rpd
                left join
                    realisasi_pembayaran rp
                    on
                        rpd.id_header = rp.id
                where
                    rpd.transaksi = 'PLASMA' and
                    (rp.tgl_realisasi is not null and rp.tgl_realisasi < '2026-06-01')

                union all

                /* KREDIT OA PAKAN - pakai rp.ekspedisi sebagai supplier.
                   kredit = transfer + PPh23 konfir (= bruto yg dilunasi) agar cocok GL turun 21212 (11130.002 + 24623). */
                select
                    rpd.no_bayar as nomor,
                    rp.ekspedisi as supplier,
                    0 as debet,
                    (rpd.transfer + isnull(kpop.potongan_pph_23, 0)) as kredit,
                    'OA PAKAN' as jenis,
                    oa_unit.unit
                from realisasi_pembayaran_det rpd
                left join
                    realisasi_pembayaran rp
                    on
                        rpd.id_header = rp.id
                left join
                    konfirmasi_pembayaran_oa_pakan kpop
                    on
                        kpop.nomor = rpd.no_bayar
                left join
                    /* unit per BYO (=1 unit): konfir det no_sj -> kirim_pakan.no_order, segmen ke-2 antar-slash */
                    (
                        select kpopd.id_header,
                            min(substring(kp.no_order, charindex('/',kp.no_order+'//')+1, charindex('/',kp.no_order+'//',charindex('/',kp.no_order+'//')+1)-charindex('/',kp.no_order+'//')-1)) as unit
                        from konfirmasi_pembayaran_oa_pakan_det kpopd
                        left join kirim_pakan kp on kp.no_sj = kpopd.no_sj
                        group by kpopd.id_header
                    ) oa_unit
                    on
                        oa_unit.id_header = kpop.id
                where
                    rpd.transaksi = 'OA PAKAN' and
                    (rp.tgl_realisasi is not null and rp.tgl_realisasi < '2026-06-01')

                union all

                /* BAYAR LEWAT MEMO */
                select * from (
                    select
                        case
                            when mi.no_invoice is not null and mi.no_invoice <> '' then
                                mi.no_invoice 
                            else
                                mi.no_mm
                        end as nomor,
                        isnull(nullif(konfir.supplier,''), m.no_supplier) as supplier,
                        0 as debet,
                        mi.nilai as kredit,
                        case
                            when mi.coa_tujuan = '21180.300' then
                                'OVK'
                            when mi.coa_tujuan = '21174.000' then
                                'OVK EXTERN'
                            when mi.coa_tujuan = '21180.200' then
                                'DOC'
                            when mi.coa_tujuan = '21180.100' then
                                'PAKAN'
                        end as jenis,
                        -- pc.kode as jenis,
                        isnull(nullif(mi.unit, ''), m.unit) as unit
                    from mmitem mi
                    left join
                        mm m
                        on
                            mi.no_mm = m.no_mm
                    left join
                        (
                            select nomor, supplier from konfirmasi_pembayaran_voadip group by nomor, supplier

                            union all

                            select nomor, supplier from konfirmasi_pembayaran_pakan group by nomor, supplier

                            union all

                            select nomor, supplier from konfirmasi_pembayaran_doc group by nomor, supplier

                            union all

                            select nomor, ekspedisi_id as supplier from konfirmasi_pembayaran_oa_pakan group by nomor, ekspedisi_id

                            union all

                            select nomor, mitra as supplier from konfirmasi_pembayaran_peternak group by nomor, mitra
                        ) konfir
                        on
                            mi.no_invoice = konfir.nomor
                    left join
                        pelanggan_coa pc
                        on
                            pc.no_pelanggan = isnull(nullif(konfir.supplier,''), m.no_supplier) and
                            pc.no_coa = mi.coa_tujuan
                    where
                        mi.coa_tujuan in ('21180.300', '21174.000', '21180.200', '21180.100') and
                        cast(mi.tgl_mm as date) < '2026-06-01' and
                        /* kontrol CN: jika memo koreksi CN (coa_asal 71105.003) invoice-nya sudah dicatat di cn_post, jangan dibaca (hindari double count dgn cn_post_det) */
                        not (
                            mi.coa_asal = '71105.003' and
                            exists (select 1 from cn_post_det cpd where cpd.nomor = mi.no_invoice)
                        ) and
                        /* kontrol reklasifikasi: lewati memo yg coa_asal-nya akun hutang lain (mis. koreksi CN salah akun DOC<->PAKAN); pembayaran asli coa_asal-nya bank/kas/CN, bukan hutang */
                        (mi.coa_asal is null or mi.coa_asal not in ('21180.300', '21174.000', '21180.200', '21180.100', '21173.000'))
                ) byr_mm
                /* END - BAYAR LEWAT MEMO */
                /* END - KREDIT */
            ) trans
            group by
                trans.supplier,
                trans.jenis,
                trans.unit
        ) sa
        group by
            sa.supplier,
            sa.jenis,
            sa.unit
        /* END - SALDO AWAL */

        union all

        /* TRANSAKSI BULAN BERJALAN */
        select
            trans.supplier,
            0 as saldo_awal,
            isnull(sum(trans.debet), 0) as debet,
            isnull(sum(trans.kredit), 0) as kredit,
            trans.jenis,
            trans.unit
        from
        (
            /* DEBET */
            select 
                kpd.nomor,
                kpd.supplier,
                kpdd.total as debet,
                0 as kredit,
                'DOC' as jenis,
                kpdd.kode_unit as unit
            from konfirmasi_pembayaran_doc_det kpdd
            left join
                konfirmasi_pembayaran_doc kpd
                on
                    kpdd.id_header = kpd.id
            left join
                (
                    select td1.* from terima_doc td1
                    right join
                        (select max(id) as id, no_order from terima_doc group by no_order) td2
                        on
                            td1.id = td2.id
                ) td
                on
                    td.no_order = kpdd.no_order
            where
                cast(td.datang as date) between '2026-06-01' and '2026-06-14'

            union all

            select 
                kpp.nomor, 	
                kpp.supplier, 
                kppd.total as debet,
                0 as kredit,
                'PAKAN' as jenis,
                kppd.kode_unit as unit
            from konfirmasi_pembayaran_pakan_det kppd
            left join
                konfirmasi_pembayaran_pakan kpp
                on
                    kppd.id_header = kpp.id
            where
                kpp.tgl_bayar between '2026-06-01' and '2026-06-14'

            union all

            /* OA PAKAN - DEBET dari freight (terima_pakan) + pindah/retur (oa_pindah_pakan).
               unit = segmen ke-2 antar-slash dari no_sj/no_order. */
            select
                oa.nomor,
                oa.supplier,
                sum(oa.total) as debet,
                0 as kredit,
                'OA PAKAN' as jenis,
                oa.unit
            from (
                select tp.no_bbm as nomor, kp.ekspedisi_id as supplier, substring(kp.no_sj, charindex('/',kp.no_sj+'//')+1, charindex('/',kp.no_sj+'//',charindex('/',kp.no_sj+'//')+1)-charindex('/',kp.no_sj+'//')-1) as unit, sum(dtp.jumlah)*kp.ongkos_angkut as total from det_terima_pakan dtp
                left join terima_pakan tp on dtp.id_header = tp.id
                left join kirim_pakan kp on tp.id_kirim_pakan = kp.id
                where tp.tgl_terima between '2026-06-01' and '2026-06-14' and kp.jenis_kirim = 'opkg'
                group by tp.no_bbm, kp.ekspedisi_id, kp.ongkos_angkut, kp.no_sj
                union all
                select opp.no_sj as nomor, coalesce(krm.ekspedisi_id, (select min(e.nomor) from ekspedisi e where e.nama = opp.ekspedisi)) as supplier, coalesce(nullif(substring(opp.no_sj, charindex('/',opp.no_sj+'//')+1, charindex('/',opp.no_sj+'//',charindex('/',opp.no_sj+'//')+1)-charindex('/',opp.no_sj+'//')-1),''), substring(krm.no_order, charindex('/',krm.no_order+'//')+1, charindex('/',krm.no_order+'//',charindex('/',krm.no_order+'//')+1)-charindex('/',krm.no_order+'//')-1)) as unit, opp.ongkos_angkut as total from oa_pindah_pakan opp
                left join ( select kp.no_sj, tp.no_bbm as kode_trans, kp.ekspedisi_id, tp.tgl_terima as tanggal, kp.no_order from kirim_pakan kp left join terima_pakan tp on kp.id = tp.id_kirim_pakan group by kp.no_sj, tp.no_bbm, kp.ekspedisi_id, tp.tgl_terima, kp.no_order union all select no_retur as no_sj, no_retur as kode_trans, ekspedisi_id, tgl_retur as tanggal, no_order from retur_pakan rp ) krm on opp.no_sj = krm.no_sj
                where coalesce(krm.tanggal, opp.tgl_terima) between '2026-06-01' and '2026-06-14'
            ) oa
            group by oa.nomor, oa.supplier, oa.unit
            /* END - OA PAKAN */

            /* ===================================================================
               DEBET OA versi KONFIRMASI - DINONAKTIFKAN (revert ke freight 2026-06-23,
               karena saldo awal tak cocok GL: konfir < freight = freight belum dikonfirmasi).
            -----------------------------------------------------------------------
            select kpop.nomor, kpop.ekspedisi_id as supplier, (kpop.total + kpop.potongan_pph_23) as debet, 0 as kredit, 'OA PAKAN' as jenis, oa_unit.unit
            from konfirmasi_pembayaran_oa_pakan kpop
            left join ( select kpopd.id_header, min(substring(kp.no_order, charindex('/',kp.no_order+'//')+1, charindex('/',kp.no_order+'//',charindex('/',kp.no_order+'//')+1)-charindex('/',kp.no_order+'//')-1)) as unit from konfirmasi_pembayaran_oa_pakan_det kpopd left join kirim_pakan kp on kp.no_sj = kpopd.no_sj group by kpopd.id_header ) oa_unit on oa_unit.id_header = kpop.id
            where kpop.tgl_bayar between '2026-06-01' and '2026-06-14'
            =================================================================== */

            union all

            select
                kpv.nomor, 
                kpv.supplier, 
                kpv.total as debet,
                0 as kredit,
                pc.kode as jenis,
                kpvd.kode_unit as unit
            from konfirmasi_pembayaran_voadip_det kpvd
            left join
                konfirmasi_pembayaran_voadip kpv
                on
                    kpvd.id_header = kpv.id
            left join
                (select * from pelanggan_coa where kode like '%OVK%') pc
                on
                    pc.no_pelanggan = kpv.supplier
            where
                kpv.tgl_bayar between '2026-06-01' and '2026-06-14'

            union all

            select
                kpp.nomor,
                kpp.mitra as supplier,
                kpp.total as debet,
                0 as kredit,
                'RHPP' as jenis,
                SUBSTRING(REPLACE(REPLACE(kpp.invoice, 'INV/RHPP/G/', ''), 'INV/RHPP/', ''), 1, 3) as unit
            from konfirmasi_pembayaran_peternak kpp
            where
                kpp.tgl_bayar between '2026-06-01' and '2026-06-14'

            union all

            select
                op.no_order as nomor,
                op.supplier,
                op.total as debet,
                0 as kredit,
                'PERALATAN' as jenis,
                op.unit
            from order_peralatan op
            where
                op.tgl_order between '2026-06-01' and '2026-06-14'

            union all

            select
                dpd.nomor,
                konfir.supplier,
                dpd.pakai as debet,
                0 as kredit,
                case
                    when dp.jenis_dn = 'PKN' then
                        'PAKAN'
                    else
                        dp.jenis_dn 
                end as jenis,
                konfir.kode_unit as unit
            from dn_post_det dpd 
            left join
                dn_post dp 
                on
                    dpd.id_header = dp.id
            left join
                (
                    /* DOC */
                    select
                        kpd.nomor,
                        kpd.supplier,
                        kpdd.kode_unit
                    from konfirmasi_pembayaran_doc kpd 
                    left join
                        (select id_header, kode_unit from konfirmasi_pembayaran_doc_det group by id_header, kode_unit) kpdd
                        on
                            kpd.id = kpdd.id_header
                        
                    union all
                    
                    /* PAKAN */
                    select
                        kpp.nomor,
                        kpp.supplier,
                        kppd.kode_unit
                    from konfirmasi_pembayaran_pakan kpp 
                    left join
                        (select id_header, kode_unit from konfirmasi_pembayaran_pakan_det group by id_header, kode_unit) kppd
                        on
                            kpp.id = kppd.id_header
                        
                    union all
                    
                    /* OVK */
                    select
                        kpv.nomor,
                        kpv.supplier,
                        kpvd.kode_unit
                    from konfirmasi_pembayaran_voadip kpv 
                    left join
                        (select id_header, kode_unit from konfirmasi_pembayaran_voadip_det group by id_header, kode_unit) kpvd
                        on
                            kpv.id = kpvd.id_header
                            
                    union all
                    
                    /* RHPP */
                    select
                        kpp.nomor,
                        kpp.mitra as supplier,
                        SUBSTRING(REPLACE(REPLACE(kpp.invoice, 'INV/RHPP/G/', ''), 'INV/RHPP/', ''), 1, 3) as kode_unit
                    from konfirmasi_pembayaran_peternak kpp
                    
                    union all
                    
                    /* OA PAKAN */
                    select
                        kpop.nomor,
                        kpop.ekspedisi_id as supplier,
                        kpopd.kode_unit
                    from konfirmasi_pembayaran_oa_pakan kpop 
                    left join
                        (
                            select 
                                kpopd.id_header, 
                                SUBSTRING( REPLACE(REPLACE(kp.no_order, 'OPK/', ''), 'OP/', ''), 1, 3 ) as kode_unit
                            from konfirmasi_pembayaran_oa_pakan_det kpopd 
                            left join
                                (
                                    select no_sj, no_order from kirim_pakan kp 
                                    
                                    union all
                                    
                                    select no_retur as no_sj, no_order from retur_pakan rp 
                                ) kp 
                                on
                                    kpopd.no_sj = kp.no_sj
                            group by 
                                kpopd.id_header, 
                                SUBSTRING( REPLACE(REPLACE(kp.no_order, 'OPK/', ''), 'OP/', ''), 1, 3 )
                        ) kpopd
                        on
                            kpop.id = kpopd.id_header
                ) konfir
                on
                    konfir.nomor = dpd.nomor
            where
                dp.tanggal between '2026-06-01' and '2026-06-14'

            union all

            /* INVOICE LEWAT MEMO */
            select * from (
                select
                    case
                        when mi.no_invoice is not null and mi.no_invoice <> '' then
                            mi.no_invoice 
                        else
                            mi.no_mm
                    end as nomor,
                    m.no_supplier as supplier,
                    mi.nilai as debet,
                    0 as kredit,
                    case
                        when mi.coa_asal = '21180.300' then
                            'OVK'
                        when mi.coa_asal = '21174.000' then
                            'OVK EXTERN'
                        when mi.coa_asal = '21180.200' then
                            'DOC'
                        when mi.coa_asal = '21180.100' then
                            'PAKAN'
                    end as jenis,
                    -- pc.kode as jenis,
                    isnull(nullif(mi.unit, ''), m.unit) as unit
                from mmitem mi
                left join
                    mm m
                    on
                        mi.no_mm = m.no_mm
                left join
                    pelanggan_coa pc
                    on
                        pc.no_pelanggan = m.no_supplier and
                        pc.no_coa = mi.coa_asal
                where
                    mi.coa_asal in ('21180.300', '21174.000', '21180.200', '21180.100') and
                    not exists (
                        select 1 from (
                            select nomor from konfirmasi_pembayaran_doc
                            union all select nomor from konfirmasi_pembayaran_pakan
                            union all select nomor from konfirmasi_pembayaran_voadip
                            union all select nomor from konfirmasi_pembayaran_oa_pakan
                            union all select isnull(nullif(invoice,''), nomor) from konfirmasi_pembayaran_peternak
                        ) kk where kk.nomor = mi.no_invoice
                    ) and
                    /* kontrol: lewati memo reklasifikasi antar-akun hutang (mis. koreksi CN DOC<->PAKAN); tagihan asli lewat memo coa_tujuan-nya akun stok/biaya, bukan hutang */
                    (mi.coa_tujuan is null or mi.coa_tujuan not in ('21180.300', '21174.000', '21180.200', '21180.100', '21173.000')) and
                    cast(mi.tgl_mm as date) between '2026-06-01' and '2026-06-14'
            ) inv_mm
            /* END - INVOICE LEWAT MEMO */
            /* END - DEBET */

            union all

            /* KREDIT */
            select
                cpd.nomor,
                konfir.supplier,
                0 as debet,
                cpd.pakai as kredit,
                case
                    when cp.jenis_cn = 'PKN' then
                        'PAKAN'
                    else
                        cp.jenis_cn 
                end as jenis,
                konfir.kode_unit as unit
            from cn_post_det cpd 
            left join
                cn_post cp
                on
                    cpd.id_header = cp.id
            left join
                (
                    /* DOC */
                    select
                        kpd.nomor,
                        kpd.supplier,
                        kpdd.kode_unit
                    from konfirmasi_pembayaran_doc kpd 
                    left join
                        (select id_header, kode_unit from konfirmasi_pembayaran_doc_det group by id_header, kode_unit) kpdd
                        on
                            kpd.id = kpdd.id_header
                        
                    union all
                    
                    /* PAKAN */
                    select
                        kpp.nomor,
                        kpp.supplier,
                        kppd.kode_unit
                    from konfirmasi_pembayaran_pakan kpp 
                    left join
                        (select id_header, kode_unit from konfirmasi_pembayaran_pakan_det group by id_header, kode_unit) kppd
                        on
                            kpp.id = kppd.id_header
                        
                    union all
                    
                    /* OVK */
                    select
                        kpv.nomor,
                        kpv.supplier,
                        kpvd.kode_unit
                    from konfirmasi_pembayaran_voadip kpv 
                    left join
                        (select id_header, kode_unit from konfirmasi_pembayaran_voadip_det group by id_header, kode_unit) kpvd
                        on
                            kpv.id = kpvd.id_header
                            
                    union all
                    
                    /* RHPP */
                    select
                        kpp.nomor,
                        kpp.mitra as supplier,
                        SUBSTRING(REPLACE(REPLACE(kpp.invoice, 'INV/RHPP/G/', ''), 'INV/RHPP/', ''), 1, 3) as kode_unit
                    from konfirmasi_pembayaran_peternak kpp
                    
                    union all
                    
                    /* OA PAKAN */
                    select
                        kpop.nomor,
                        kpop.ekspedisi_id as supplier,
                        kpopd.kode_unit
                    from konfirmasi_pembayaran_oa_pakan kpop 
                    left join
                        (
                            select 
                                kpopd.id_header, 
                                SUBSTRING( REPLACE(REPLACE(kp.no_order, 'OPK/', ''), 'OP/', ''), 1, 3 ) as kode_unit
                            from konfirmasi_pembayaran_oa_pakan_det kpopd 
                            left join
                                (
                                    select no_sj, no_order from kirim_pakan kp 
                                    
                                    union all
                                    
                                    select no_retur as no_sj, no_order from retur_pakan rp 
                                ) kp 
                                on
                                    kpopd.no_sj = kp.no_sj
                            group by 
                                kpopd.id_header, 
                                SUBSTRING( REPLACE(REPLACE(kp.no_order, 'OPK/', ''), 'OP/', ''), 1, 3 )
                        ) kpopd
                        on
                            kpop.id = kpopd.id_header
                ) konfir
                on
                    konfir.nomor = cpd.nomor
            where
                cp.tanggal between '2026-06-01' and '2026-06-14'

            union all
    
            select
                bp.no_faktur,
                op.supplier,
                0 as debet,
                bp.jml_bayar as kredit,
                'PERALATAN' as jenis,
                op.unit 
            from bayar_peralatan bp 
            left join
                order_peralatan op 
                on
                    op.no_order = bp.no_order 
            where
                bp.tgl_realisasi is not null and bp.tgl_realisasi between '2026-06-01' and '2026-06-14'

            union all

            select 
                rp.nomor,
                rp.supplier,
                0 as debet,
                case
                    when rpd.transaksi = 'DOC' then
                        case
                            when konfir.tanggal <= '2025-09-20' then
                                rpd.transfer
                            else
                                rpd.transfer+konfir.pph
                        end
                    else
                        rpd.transfer
                end as kredit,
                pc.kode as jenis,
                konfir.kode_unit as unit
            from realisasi_pembayaran_det rpd
            left join
                realisasi_pembayaran rp
                on
                    rpd.id_header = rp.id
            left join
                (
                    select kpdd.kode_unit, kpd.nomor, kpd.tgl_bayar as tanggal, ((kpdd.total - case when kpd.tgl_bayar >= '2026-01-01' then isnull((select sum(cpd.pakai) from cn_post_det cpd where cpd.nomor = kpd.nomor), 0) else 0 end - isnull((select sum(mi.nilai) from mmitem mi where mi.coa_asal = '12040.000' and mi.coa_tujuan = '21180.200' and mi.no_invoice = kpd.nomor), 0)) * (0.25/100)) as pph from konfirmasi_pembayaran_doc_det kpdd 
                    left join
                        konfirmasi_pembayaran_doc kpd 
                        on
                            kpdd.id_header = kpd.id
                    left join
                        (
                            select td1.* from terima_doc td1
                            right join
                                (select max(id) as id, no_order from terima_doc group by no_order) td2
                                on
                                    td1.id = td2.id
                        ) td
                        on
                            td.no_order = kpdd.no_order
                    group by
                        kpdd.kode_unit, kpd.nomor, kpd.tgl_bayar, kpdd.total
                        
                    union all
                    
                    select kppd.kode_unit, kpp.nomor, null as tanggal, 0 as pph from konfirmasi_pembayaran_pakan_det kppd 
                    left join
                        konfirmasi_pembayaran_pakan kpp 
                        on
                            kppd.id_header = kpp.id
                    group by
                        kppd.kode_unit, kpp.nomor
                        
                    union all
                    
                    select kpvd.kode_unit, kpv.nomor, null as tanggal, 0 as pph from konfirmasi_pembayaran_voadip_det kpvd 
                    left join
                        konfirmasi_pembayaran_voadip kpv 
                        on
                            kpvd.id_header = kpv.id
                    group by
                        kpvd.kode_unit, kpv.nomor

                    union all

                    /* pembayaran lewat memo: hitung PPh 0.25% atas nilai DOC memo (coa_asal 21180.200) agar cocok dgn potongan PPh di GL */
                    select m.unit as kode_unit, m.no_mm as nomor, null as tanggal,
                        isnull((
                            select sum(mi.nilai) * 0.0025
                            from mmitem mi
                            where mi.no_mm = m.no_mm and mi.coa_asal = '21180.200'
                        ), 0) as pph
                    from mm m
                ) konfir
                on
                    rpd.no_bayar = konfir.nomor
            left join
                pelanggan_coa pc
                on
                    pc.no_pelanggan = rp.supplier and
                    pc.kode like '%'+REPLACE(rpd.transaksi, 'VOADIP', 'OVK')+'%'
            where
                (rp.tgl_realisasi is not null and rp.tgl_realisasi between '2026-06-01' and '2026-06-14') and
                rpd.transaksi not in ('PLASMA', 'OA PAKAN')

            union all

            /* KREDIT PLASMA - pakai rp.peternak sebagai supplier */
            select
                rpd.no_bayar as nomor,
                rp.peternak as supplier,
                0 as debet,
                rpd.transfer as kredit,
                'RHPP' as jenis,
                SUBSTRING(REPLACE(REPLACE(rpd.no_bayar, 'INV/RHPP/G/', ''), 'INV/RHPP/', ''), 1, 3) as unit
            from realisasi_pembayaran_det rpd
            left join
                realisasi_pembayaran rp
                on
                    rpd.id_header = rp.id
            where
                rpd.transaksi = 'PLASMA' and
                (rp.tgl_realisasi is not null and rp.tgl_realisasi between '2026-06-01' and '2026-06-14')

            union all

            /* KREDIT OA PAKAN - pakai rp.ekspedisi sebagai supplier.
               kredit = transfer + PPh23 konfir (= bruto yg dilunasi) agar cocok GL turun 21212 (11130.002 + 24623). */
            select
                rpd.no_bayar as nomor,
                rp.ekspedisi as supplier,
                0 as debet,
                (rpd.transfer + isnull(kpop.potongan_pph_23, 0)) as kredit,
                'OA PAKAN' as jenis,
                oa_unit.unit
            from realisasi_pembayaran_det rpd
            left join
                realisasi_pembayaran rp
                on
                    rpd.id_header = rp.id
            left join
                konfirmasi_pembayaran_oa_pakan kpop
                on
                    kpop.nomor = rpd.no_bayar
            left join
                /* unit per BYO (=1 unit): konfir det no_sj -> kirim_pakan.no_order, segmen ke-2 antar-slash */
                (
                    select kpopd.id_header,
                        min(substring(kp.no_order, charindex('/',kp.no_order+'//')+1, charindex('/',kp.no_order+'//',charindex('/',kp.no_order+'//')+1)-charindex('/',kp.no_order+'//')-1)) as unit
                    from konfirmasi_pembayaran_oa_pakan_det kpopd
                    left join kirim_pakan kp on kp.no_sj = kpopd.no_sj
                    group by kpopd.id_header
                ) oa_unit
                on
                    oa_unit.id_header = kpop.id
            where
                rpd.transaksi = 'OA PAKAN' and
                (rp.tgl_realisasi is not null and rp.tgl_realisasi between '2026-06-01' and '2026-06-14')

            union all

            /* BAYAR LEWAT MEMO */
            select * from (
                select
                    case
                        when mi.no_invoice is not null and mi.no_invoice <> '' then
                            mi.no_invoice 
                        else
                            mi.no_mm
                    end as nomor,
                    isnull(nullif(konfir.supplier,''), m.no_supplier) as supplier,
                    0 as debet,
                    mi.nilai as kredit,
                    case
                        when mi.coa_tujuan = '21180.300' then
                            'OVK'
                        when mi.coa_tujuan = '21174.000' then
                            'OVK EXTERN'
                        when mi.coa_tujuan = '21180.200' then
                            'DOC'
                        when mi.coa_tujuan = '21180.100' then
                            'PAKAN'
                    end as jenis,
                    -- pc.kode as jenis,
                    isnull(nullif(mi.unit, ''), m.unit) as unit
                from mmitem mi
                left join
                    mm m
                    on
                        mi.no_mm = m.no_mm
                left join
                    (
                        select nomor, supplier from konfirmasi_pembayaran_voadip group by nomor, supplier

                        union all

                        select nomor, supplier from konfirmasi_pembayaran_pakan group by nomor, supplier

                        union all

                        select nomor, supplier from konfirmasi_pembayaran_doc group by nomor, supplier

                        union all

                        select nomor, ekspedisi_id as supplier from konfirmasi_pembayaran_oa_pakan group by nomor, ekspedisi_id

                        union all

                        select nomor, mitra as supplier from konfirmasi_pembayaran_peternak group by nomor, mitra
                    ) konfir
                    on
                        mi.no_invoice = konfir.nomor
                left join
                    pelanggan_coa pc
                    on
                        pc.no_pelanggan = isnull(nullif(konfir.supplier,''), m.no_supplier) and
                        pc.no_coa = mi.coa_tujuan
                where
                    mi.coa_tujuan in ('21180.300', '21174.000', '21180.200', '21180.100') and
                    cast(mi.tgl_mm as date) between '2026-06-01' and '2026-06-14' and
                    /* kontrol CN: jika memo koreksi CN (coa_asal 71105.003) invoice-nya sudah dicatat di cn_post, jangan dibaca (hindari double count dgn cn_post_det) */
                    not (
                        mi.coa_asal = '71105.003' and
                        exists (select 1 from cn_post_det cpd where cpd.nomor = mi.no_invoice)
                    ) and
                    /* kontrol reklasifikasi: lewati memo yg coa_asal-nya akun hutang lain (mis. koreksi CN salah akun DOC<->PAKAN); pembayaran asli coa_asal-nya bank/kas/CN, bukan hutang */
                    (mi.coa_asal is null or mi.coa_asal not in ('21180.300', '21174.000', '21180.200', '21180.100', '21173.000'))
            ) byr_mm
            /* END - BAYAR LEWAT MEMO */
            /* END - KREDIT */
        ) trans
        group by
            trans.supplier,
            trans.jenis,
            trans.unit
        /* END - TRANSAKSI BULAN BERJALAN */
    ) _data
    group by
        _data.supplier,
        _data.jenis,
        _data.unit
) data
left join
    (
        select p1.nomor, p1.nama, p1.tipe from pelanggan p1
        right join
            (select max(id) as id, nomor from pelanggan p where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) p2
            on
                p1.id = p2.id

        union all
                
        select e1.nomor, e1.nama, 'ekspedisi' as tipe from ekspedisi e1
        right join
            (select max(id) as id, nomor from ekspedisi e group by nomor) e2
            on
                e1.id = e2.id

        union all

        select m1.nomor, m1.nama, 'plasma' as tipe from mitra m1
        right join
            (select max(id) as id, nomor from mitra group by nomor) m2
            on
                m1.id = m2.id
    ) supl
    on
        supl.nomor = data.supplier
where data.jenis in ('RHPP')
group by
    data.jenis,
    data.unit
--    data.supplier,
--    supl.nama
order by
--    supl.nama asc,
    data.unit asc,
    data.jenis asc