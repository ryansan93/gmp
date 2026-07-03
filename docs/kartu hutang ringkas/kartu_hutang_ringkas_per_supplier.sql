select 
--    data.*
--    data.supplier,
--    supl.nama as nama_supplier,
--    data.jenis,
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
            
--            sa.*
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
                cast(td.datang as date) < '2026-04-01'

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
                kpp.tgl_bayar < '2026-04-01'

            union all

            /* OA PAKAN */
            select
                oa.nomor,
                oa.supplier,
                sum(oa.total) as debet,
                0 as kredit,
                'OA PAKAN' as jenis,
                null as unit
            from (
                select kpop.nomor, kpop.ekspedisi_id as supplier, (kpop.total+kpop.potongan_pph_23) as total from konfirmasi_pembayaran_oa_pakan kpop
                where
                    kpop.tgl_bayar < '2026-04-01'

                union all

                select tp.no_bbm as nomor, kp.ekspedisi_id as supplier, sum(dtp.jumlah)*kp.ongkos_angkut as total from det_terima_pakan dtp
                left join
                    terima_pakan tp
                    on
                        dtp.id_header = tp.id
                left join
                    kirim_pakan kp
                    on
                        tp.id_kirim_pakan = kp.id
                where
                    tp.tgl_terima < '2026-04-01' and
                    kp.jenis_kirim = 'opkg' and
                    not exists (select * from konfirmasi_pembayaran_oa_pakan_det kpopd where no_sj = kp.no_sj)
                group by
                    tp.no_bbm, kp.ekspedisi_id, kp.ongkos_angkut

                union all

                select opp.no_sj as nomor, krm.ekspedisi_id as supplier, opp.ongkos_angkut as total from oa_pindah_pakan opp
                left join
                    (
                        select kp.no_sj, tp.no_bbm as kode_trans, kp.ekspedisi_id, tp.tgl_terima as tanggal from kirim_pakan kp
                        left join
                            terima_pakan tp 
                            on
                                kp.id = tp.id_kirim_pakan
                        group by
                            kp.no_sj, tp.no_bbm, kp.ekspedisi_id, tp.tgl_terima
                        
                        union all
                        
                        select no_retur as no_sj, no_retur as kode_trans, ekspedisi_id, tgl_retur as tanggal from retur_pakan rp 
                    ) krm
                    on
                        opp.no_sj = krm.no_sj
                where
                    krm.tanggal < '2026-04-01' and
                    not exists (select * from konfirmasi_pembayaran_oa_pakan_det kpopd where no_sj = opp.no_sj)
            ) oa
            group by
                oa.nomor,
                oa.supplier
            /* END - OA PAKAN */

            union all

            select
                kpv.nomor, 
                kpv.supplier, 
                kpv.total as debet,
                0 as kredit,
                case
                    when kpv.supplier = '19B004' then
                        'OVK'
                    else
                        'OVK EXTERN'
                end as jenis,
--                'OVK' as jenis,
                kpvd.kode_unit as unit
            from konfirmasi_pembayaran_voadip kpv
            left join
                konfirmasi_pembayaran_voadip_det kpvd 
                on
                    kpv.id = kpvd.id_header
            where
                kpv.tgl_bayar < '2026-04-01'

            union all

            select
                kpp.nomor,
                kpp.mitra as supplier,
                kpp.total as debet,
                0 as kredit,
                'RHPP' as jenis,
                null as unit
            from konfirmasi_pembayaran_peternak kpp
            where
                kpp.tgl_bayar < '2026-04-01'

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
                op.tgl_order < '2026-04-01'

            union all

            select
                d.nomor,
                d.supplier,
                d.tot_dn as debet,
                0 as kredit,
                d.jenis_dn as jenis,
                SUBSTRING(d.no_dok, 8, 3) as unit
            from dn d
            where
                d.tanggal < '2026-04-01'

            union all

            /* INVOICE LEWAT MEMO */
            select * from (
                select
                    mi.no_mm as nomor,
                    m.no_supplier as supplier,
                    mi.nilai as debet,
                    0 as kredit,
                    case
                        when mi.coa_asal = '21180.300' then
                            'OVK'
                        when mi.coa_asal = '21174.000' then
                            'OVK EXTERN'
                    end as jenis,
                    m.unit
                from mmitem mi
                left join
                    mm m
                    on
                        mi.no_mm = m.no_mm
                where 
                    mi.coa_asal in ('21180.300', '21174.000') and
                    cast(mi.tgl_mm as date) < '2026-04-01'
            ) inv_mm
            /* END - INVOICE LEWAT MEMO */
            /* END - DEBET */

            union all

            /* KREDIT */
            select
                c.nomor,
                c.supplier,
                0 as debet,
                c.tot_cn as kredit,
                case
                    when c.jenis_cn = 'PKN' then
                        'PAKAN'
                    else
                        c.jenis_cn 
                end as jenis,
                null as unit
            from cn c
            where
                c.tanggal < '2026-04-01'

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
                bp.tgl_realisasi < '2026-04-01'

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
                                -- rpd.transfer
                        end
                    else
                        rpd.transfer
                end as kredit,
                rpd.transaksi as jenis,
                konfir.kode_unit as unit
            from realisasi_pembayaran_det rpd
            left join
                realisasi_pembayaran rp
                on
                    rpd.id_header = rp.id
            left join
                (
                    select kpdd.kode_unit, kpd.nomor, kpd.tgl_bayar as tanggal, (kpdd.total * (0.25/100)) as pph from konfirmasi_pembayaran_doc_det kpdd 
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
                ) konfir
                on
                    rpd.no_bayar = konfir.nomor
            where
                rp.tgl_bayar < '2026-04-01'

            union all

            /* BAYAR LEWAT MEMO */
            select * from (
                select
                    mi.no_invoice as nomor,
                    konfir.supplier,
                    0 as debet,
                    mi.nilai as kredit,
                    case
                        when mi.coa_tujuan = '21180.300' then
                            'OVK'
                        when mi.coa_tujuan = '21174.000' then
                            'OVK EXTERN'
                    end as jenis,
                    m.unit
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
                where 
                    mi.coa_tujuan in ('21180.300', '21174.000') and
                    cast(mi.tgl_mm as date) < '2026-04-01'
            ) byr_mm
            /* END - BAYAR LEWAT MEMO */
            /* END - KREDIT */
        ) sa
--        where
--        	sa.supplier = '19B004' and
--        	sa.nomor = 'BYV/03/26/00073'
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
--        	select
--        	sum(data.debet)
--        	from
--        	(
            select 
                kpd.nomor,
                kpd.supplier,
                kpdd.total as debet,
--                dj.nominal,
--                kpdd.total - dj.nominal as selisih,
                0 as kredit,
                'DOC' as jenis,
                kpdd.kode_unit as unit
--                ,td.no_order
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
--            left join
--            	(select * from det_jurnal where tbl_name = 'terima_doc' and coa_asal = '21180.200' and unit = 'MLG' and tanggal between '2026-04-01' and '2026-04-30') dj
--            	on
--            		cast(td.id as varchar(10)) = dj.tbl_id
            where
                cast(td.datang as date) between '2026-04-01' and '2026-04-30'
--                and kpdd.kode_unit = 'LMG'
--        	) data

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
                kpp.tgl_bayar between '2026-04-01' and '2026-04-30'

            union all

            /* OA PAKAN */
            select
                oa.nomor,
                oa.supplier,
                sum(oa.total) as debet,
                0 as kredit,
                'OA PAKAN' as jenis,
                null as unit
            from (
                select kpop.nomor, kpop.ekspedisi_id as supplier, (kpop.total+kpop.potongan_pph_23) as total from konfirmasi_pembayaran_oa_pakan kpop
                where
                    kpop.tgl_bayar between '2026-04-01' and '2026-04-30'

                union all

                select tp.no_bbm as nomor, kp.ekspedisi_id as supplier, sum(dtp.jumlah)*kp.ongkos_angkut as total from det_terima_pakan dtp
                left join
                    terima_pakan tp
                    on
                        dtp.id_header = tp.id
                left join
                    kirim_pakan kp
                    on
                        tp.id_kirim_pakan = kp.id
                where
                    tp.tgl_terima between '2026-04-01' and '2026-04-30' and
                    kp.jenis_kirim = 'opkg' and
                    not exists (select * from konfirmasi_pembayaran_oa_pakan_det kpopd where no_sj = kp.no_sj)
                group by
                    tp.no_bbm, kp.ekspedisi_id, kp.ongkos_angkut

                union all

                select opp.no_sj as nomor, krm.ekspedisi_id as supplier, opp.ongkos_angkut as total from oa_pindah_pakan opp
                left join
                    (
                        select kp.no_sj, tp.no_bbm as kode_trans, kp.ekspedisi_id, tp.tgl_terima as tanggal from kirim_pakan kp
                        left join
                            terima_pakan tp 
                            on
                                kp.id = tp.id_kirim_pakan
                        group by
                            kp.no_sj, tp.no_bbm, kp.ekspedisi_id, tp.tgl_terima
                        
                        union all
                        
                        select no_retur as no_sj, no_retur as kode_trans, ekspedisi_id, tgl_retur as tanggal from retur_pakan rp 
                    ) krm
                    on
                        opp.no_sj = krm.no_sj
                where
                    krm.tanggal between '2026-04-01' and '2026-04-30' and
                    not exists (select * from konfirmasi_pembayaran_oa_pakan_det kpopd where no_sj = opp.no_sj)
            ) oa
            group by
                oa.nomor,
                oa.supplier
            /* END - OA PAKAN */

            union all

            select
                kpv.nomor, 
                kpv.supplier, 
                kpv.total as debet,
                0 as kredit,
                case
                    when kpv.supplier = '19B004' then
                        'OVK'
                    else
                        'OVK EXTERN'
                end as jenis,
--                'OVK' as jenis,
                kpvd.kode_unit as unit
            from konfirmasi_pembayaran_voadip_det kpvd
            left join
                konfirmasi_pembayaran_voadip kpv
                on
                    kpvd.id_header = kpv.id
            where
                kpv.tgl_bayar between '2026-04-01' and '2026-04-30'

            union all

            select
                kpp.nomor,
                kpp.mitra as supplier,
                kpp.total as debet,
                0 as kredit,
                'RHPP' as jenis,
                null as unit
            from konfirmasi_pembayaran_peternak kpp
            where
                kpp.tgl_bayar between '2026-04-01' and '2026-04-30'

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
                op.tgl_order between '2026-04-01' and '2026-04-30'

            union all

            select
                d.nomor,
                d.supplier,
                d.tot_dn as debet,
                0 as kredit,
                case
                    when d.jenis_dn = 'PKN' then
                        'PAKAN'
                    else
                        d.jenis_dn 
                end as jenis,
                null as unit
            from dn d
            where
                d.tanggal between '2026-04-01' and '2026-04-30'

            union all

            /* INVOICE LEWAT MEMO */
            select * from (
                select
                    mi.no_invoice as nomor,
                    m.no_supplier as supplier,
                    mi.nilai as debet,
                    0 as kredit,
                    case
                        when mi.coa_asal = '21180.300' then
                            'OVK'
                        when mi.coa_asal = '21174.000' then
                            'OVK EXTERN'
                    end as jenis,
                    m.unit
                from mmitem mi
                left join
                    mm m
                    on
                        mi.no_mm = m.no_mm
                where 
                    mi.coa_asal in ('21180.300', '21174.000') and
                    cast(mi.tgl_mm as date) between '2026-04-01' and '2026-04-30'
            ) inv_mm

            /*
            select * from 
            (
                select
                    dj.tbl_id as nomor,
                    dj.supplier as supplier,
                    dj.nominal as debet,
                    0 as kredit,
                    case
                        when dj.coa_asal = '21180.300' then
                            'OVK EXTERN'
                        when dj.coa_asal = '21174.000' then
                            'OVK'
                    end as jenis,
                    dj.unit
                from det_jurnal dj
                where
                    dj.tanggal between '2026-04-01' and '2026-04-30'
                    and dj.coa_asal in ('21180.300', '21174.000')
                    and dj.tbl_id like '%mm%'
            ) inv_mm
            */
            /* END - INVOICE LEWAT MEMO */
            /* END - DEBET */

            union all

            /* KREDIT */
            /*
            select
                c.nomor,
                c.supplier,
                0 as debet,
                c.tot_cn as kredit,
                case
                    when c.jenis_cn = 'PKN' then
                        'PAKAN'
                    else
                        c.jenis_cn 
                end as jenis,
                null as unit
            from cn c
            where
                c.tanggal between '2026-04-01' and '2026-04-30'
            */
            
            select
                cpd.nomor,
                kpp.supplier,
                0 as debet,
                cpd.pakai as kredit,
                case
                    when cp.jenis_cn = 'PKN' then
                        'PAKAN'
                    else
                        cp.jenis_cn 
                end as jenis,
                kppd.kode_unit as unit
            from cn_post_det cpd 
            left join
                cn_post cp
                on
                    cpd.id_header = cp.id
            left join
                konfirmasi_pembayaran_pakan kpp 
                on
                    cpd.nomor = kpp.nomor 
            left join
                konfirmasi_pembayaran_pakan_det kppd
                on
                    kpp.id = kppd.id_header
            where
                cp.tanggal between '2026-04-01' and '2026-04-30'

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
                bp.tgl_realisasi between '2026-04-01' and '2026-04-30'

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
                                -- rpd.transfer
                        end
                    else
                        rpd.transfer
                end as kredit,
                case
                    when rpd.transaksi = 'VOADIP' then
                        case
                            when rp.supplier = '19B004' then
                                'OVK'
                            else
                                'OVK EXTERN'
                        end
                    else
                        rpd.transaksi
                end as jenis,
--                rpd.transaksi as jenis,
                konfir.kode_unit as unit
            from realisasi_pembayaran_det rpd
            left join
                realisasi_pembayaran rp
                on
                    rpd.id_header = rp.id
            left join
                (
                    select kpdd.kode_unit, kpd.nomor, kpd.tgl_bayar as tanggal, (kpdd.total * (0.25/100)) as pph from konfirmasi_pembayaran_doc_det kpdd 
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
                ) konfir
                on
                    rpd.no_bayar = konfir.nomor
            where
                rp.tgl_bayar between '2026-04-01' and '2026-04-30'

            union all

            /* BAYAR LEWAT MEMO */
            select * from (
                select
                    mi.no_invoice as nomor,
                    konfir.supplier,
                    0 as debet,
                    mi.nilai as kredit,
                    case
                        when mi.coa_tujuan = '21180.300' then
                            'OVK'
                        when mi.coa_tujuan = '21174.000' then
                            'OVK EXTERN'
                    end as jenis,
                    m.unit
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
                where 
                    mi.coa_tujuan in ('21180.300', '21174.000') and
                    cast(mi.tgl_mm as date) between '2026-04-01' and '2026-04-30'
            ) byr_mm

            /*
            select * from 
            (
                select
                    dj.tbl_id as nomor,
                    dj.supplier as supplier,
                    dj.nominal as debet,
                    0 as kredit,
                    case
                        when dj.coa_tujuan = '21180.300' then
                            'OVK EXTERN'
                        when dj.coa_tujuan = '21174.000' then
                            'OVK'
                    end as jenis,
                    dj.unit
                from det_jurnal dj
                where
                    dj.tanggal between '2026-04-01' and '2026-04-30'
                    and dj.coa_tujuan in ('21180.300', '21174.000')
                    and dj.tbl_id like '%mm%'
            ) inv_mm
            */
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
where 
    supl.tipe = 'supplier'
    and data.supplier in (
    '19B004'
    )
    /*
    and data.jenis = 'PAKAN'
    */
group by
    -- data.jenis,
     data.unit
--    data.supplier,
--    supl.nama
--order by
--    supl.nama asc