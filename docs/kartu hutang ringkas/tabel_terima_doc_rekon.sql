/* =====================================================================
   TABEL TERIMA DOC per invoice (BYD): total | pembayaran | sisa
                                       | jurnal kredit hutang | jurnal debet hutang
   ---------------------------------------------------------------------
   Grain  : per invoice DOC (nomor BYD). Periode terima_doc.datang 2025-09-01..2026-06-13.
   total       = nilai invoice (KHR debet / konfirmasi).
   pembayaran  = pengurang hutang operasional (KHR kredit: transfer+PPh+CN+DN+memo).
   sisa        = total - pembayaran (saldo hutang operasional).
   jurnal_kredit_hutang = GL NAIK hutang (coa_asal=21180.200) via BBM(no_bbm) + memo(invoice=BYD).
   jurnal_debet_hutang  = GL TURUN hutang (coa_tujuan=21180.200) diatribusikan ke BYD via:
                          (A) kolom invoice=BYD, (B) BYR no-invoice -> no_sj keterangan -> BYD,
                          (C) CN/memo -> BYD diparse dari keterangan 'CN DOC ATAS BYD/...'.
   gl_sisa     = jurnal_kredit_hutang - jurnal_debet_hutang.
   beda        = sisa - gl_sisa. ~0 = GL & operasional konsisten.
                 beda != 0 menandai invoice yg jurnal GL-nya ditambal memo tanpa
                 referensi invoice/no_sj (mis. KOREKSI DOC PLASMA di MJK).
   ===================================================================== */
SET NOCOUNT ON;

WITH td AS (
    SELECT t.no_order, t.no_sj, t.no_bbm, t.datang
    FROM terima_doc t
    JOIN (SELECT MAX(id) id FROM terima_doc GROUP BY no_order) m ON m.id=t.id
),
tgl AS (
    SELECT kpd.nomor, MIN(td.datang) tgl_terima, COUNT(DISTINCT kpdd.no_order) jml_doc
    FROM konfirmasi_pembayaran_doc_det kpdd
    JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id
    LEFT JOIN td ON td.no_order=kpdd.no_order
    GROUP BY kpd.nomor
    HAVING MIN(td.datang) BETWEEN '2025-09-01' AND '2026-06-13'
),
invbbm AS (
    SELECT DISTINCT kpd.nomor, td.no_bbm
    FROM konfirmasi_pembayaran_doc_det kpdd
    JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id
    JOIN td ON td.no_order=kpdd.no_order
),
sjmap AS (  -- no_sj -> BYD (untuk atribusi pembayaran tanpa kolom invoice)
    SELECT td.no_sj, MIN(kpd.nomor) byd
    FROM td
    JOIN konfirmasi_pembayaran_doc_det kpdd ON kpdd.no_order=td.no_order
    JOIN konfirmasi_pembayaran_doc kpd ON kpdd.id_header=kpd.id
    GROUP BY td.no_sj
),
glk_bbm AS (
    SELECT ib.nomor, SUM(dj.nominal) val
    FROM invbbm ib JOIN det_jurnal dj ON dj.kode_trans=ib.no_bbm
    WHERE dj.coa_asal='21180.200'
    GROUP BY ib.nomor
),
glk_memo AS (
    SELECT dj.invoice nomor, SUM(dj.nominal) val
    FROM det_jurnal dj
    WHERE dj.coa_asal='21180.200' AND dj.invoice LIKE 'BYD/%' AND dj.kode_trans NOT LIKE 'BBM%'
    GROUP BY dj.invoice
),
gld AS (  -- GL turun diatribusikan ke BYD (A invoice, B no_sj, C CN-memo)
    SELECT byd nomor, SUM(nominal) val FROM (
        -- (A) kolom invoice ber-BYD
        SELECT dj.invoice byd, dj.nominal
        FROM det_jurnal dj
        WHERE dj.coa_tujuan='21180.200' AND dj.invoice LIKE 'BYD/%' AND dj.tanggal<='2026-06-13'
        UNION ALL
        -- (B) BYR tanpa invoice -> no_sj keterangan
        SELECT s.byd, dj.nominal
        FROM det_jurnal dj
        JOIN sjmap s ON s.no_sj = LTRIM(RTRIM(REPLACE(CAST(dj.keterangan AS varchar(200)),'PEMBAYARAN DOC','')))
        WHERE dj.coa_tujuan='21180.200' AND dj.kode_trans LIKE 'BYR%'
          AND (dj.invoice IS NULL OR dj.invoice='' OR dj.invoice NOT LIKE 'BYD/%')
          AND CAST(dj.keterangan AS varchar(200)) LIKE 'PEMBAYARAN DOC%'
          AND dj.tanggal<='2026-06-13'
        UNION ALL
        -- (C) CN/memo tanpa invoice -> BYD diparse dari keterangan
        SELECT SUBSTRING(k, CHARINDEX('BYD/',k), 15) byd, dj.nominal
        FROM det_jurnal dj
        CROSS APPLY (SELECT CAST(dj.keterangan AS varchar(300)) k) x
        WHERE dj.coa_tujuan='21180.200' AND dj.kode_trans LIKE 'MM%'
          AND (dj.invoice IS NULL OR dj.invoice='' OR dj.invoice NOT LIKE 'BYD/%')
          AND x.k LIKE '%BYD/%' AND dj.tanggal<='2026-06-13'
    ) z GROUP BY byd
),
khr AS (
    SELECT data.kode_trans nomor, MIN(data.unit) unit,
           SUM(ISNULL(data.debet,0)) total,
           SUM(ISNULL(data.kredit,0)) pembayaran,
           SUM((ISNULL(data.saldo_awal,0)+ISNULL(data.debet,0))-ISNULL(data.kredit,0)) sisa
    FROM (
select
    	_data.kode_trans,
        _data.supplier,
        _data.jenis,
        _data.unit,
        isnull(sum(_data.saldo_awal), 0) as saldo_awal,
        isnull(sum(_data.debet), 0) as debet,
        isnull(sum(_data.kredit), 0) as kredit,
        (isnull(sum(_data.saldo_awal), 0)+isnull(sum(_data.debet), 0))-isnull(sum(_data.kredit), 0) as saldo_akhir
    from
    (
        /* TRANSAKSI BULAN BERJALAN */
        select
        	trans.nomor as kode_trans,
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
                cast(td.datang as date) <= '2026-06-13'

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
                kpp.tgl_bayar <= '2026-06-13'

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
                    kpop.tgl_bayar <= '2026-06-13'

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
                    tp.tgl_terima <= '2026-06-13' and
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
                    krm.tanggal <= '2026-06-13' and
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
                kpv.tgl_bayar <= '2026-06-13'

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
                kpp.tgl_bayar <= '2026-06-13'

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
                op.tgl_order <= '2026-06-13'

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
                dp.tanggal <= '2026-06-13'

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
                    pc.kode as jenis,
                    m.unit
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
                    cast(mi.tgl_mm as date) <= '2026-06-13'
            ) inv_mm
            /* END - INVOICE LEWAT MEMO */
            /* END - DEBET */

            union all

            /* KREDIT */
            select * from (
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
	                cp.tanggal <= '2026-06-13'

	            union all

	            select
	                bp.no_faktur as nomor,
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
	                bp.tgl_realisasi is not null and bp.tgl_realisasi <= '2026-06-13'

	            union all

	            select
	                rpd.no_bayar as nomor,
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
	                    /* ====== DOC: PPh berbasis tanggal + net setelah CN (FIX) ====== */
	                    select
	                        kpdd.kode_unit,
	                        kpd.nomor,
	                        kpd.tgl_bayar as tanggal,
	                        case
	                            when kpd.tgl_bayar >= '2026-01-01' then
	                                (sum(kpdd.total) - isnull(cn.pakai, 0)) * (0.25/100)
	                            else
	                                sum(kpdd.total) * (0.25/100)
	                        end as pph
	                    from konfirmasi_pembayaran_doc_det kpdd
	                    left join
	                        konfirmasi_pembayaran_doc kpd
	                        on
	                            kpdd.id_header = kpd.id
	                    left join
	                        (select nomor, sum(pakai) as pakai from cn_post_det group by nomor) cn
	                        on
	                            cn.nomor = kpd.nomor
	                    group by
	                        kpdd.kode_unit, kpd.nomor, kpd.tgl_bayar, cn.pakai
	                    /* ====== END FIX DOC ====== */

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

	                    select unit as kode_unit, mm.no_mm as nomor, null as tanggal, 0 as pph from mm
	                ) konfir
	                on
	                    rpd.no_bayar = konfir.nomor
	            left join
	                pelanggan_coa pc
	                on
	                    pc.no_pelanggan = rp.supplier and
	                    pc.kode like '%'+REPLACE(rpd.transaksi, 'VOADIP', 'OVK')+'%'
	            where
	                (rp.tgl_realisasi is not null and rp.tgl_realisasi <= '2026-06-13') and
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
	                (rp.tgl_realisasi is not null and rp.tgl_realisasi <= '2026-06-13')

	            union all

	            /* KREDIT OA PAKAN - pakai rp.ekspedisi sebagai supplier */
	            select
	                rpd.no_bayar as nomor,
	                rp.ekspedisi as supplier,
	                0 as debet,
	                rpd.transfer as kredit,
	                'OA PAKAN' as jenis,
	                null as unit
	            from realisasi_pembayaran_det rpd
	            left join
	                realisasi_pembayaran rp
	                on
	                    rpd.id_header = rp.id
	            where
	                rpd.transaksi = 'OA PAKAN' and
	                (rp.tgl_realisasi is not null and rp.tgl_realisasi <= '2026-06-13')

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
	                    pc.kode as jenis,
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
	                left join
	                    pelanggan_coa pc
	                    on
	                        pc.no_pelanggan = isnull(nullif(konfir.supplier,''), m.no_supplier) and
	                        pc.no_coa = mi.coa_tujuan
	                where
	                	mi.coa_asal not in ('71105.003') and
	                    mi.coa_tujuan in ('21180.300', '21174.000', '21180.200', '21180.100') and
	                    cast(mi.tgl_mm as date) <= '2026-06-13'
	            ) byr_mm
	            /* END - BAYAR LEWAT MEMO */
            ) kredit
            /* END - KREDIT */
        ) trans
        group by
        	trans.nomor,
            trans.supplier,
            trans.jenis,
            trans.unit
        /* END - TRANSAKSI BULAN BERJALAN */
    ) _data
    group by
    	_data.kode_trans,
        _data.supplier,
        _data.jenis,
        _data.unit
) data
    WHERE data.jenis='DOC'
    GROUP BY data.kode_trans
)
SELECT
    k.unit,
    CONVERT(varchar(10), g.tgl_terima, 23) AS tgl_terima,
    k.nomor AS invoice,
    g.jml_doc,
    CAST(k.total AS decimal(18,2))      total,
    CAST(k.pembayaran AS decimal(18,2)) pembayaran,
    CAST(k.sisa AS decimal(18,2))       sisa,
    CAST(ISNULL(kb.val,0)+ISNULL(km.val,0) AS decimal(18,2)) jurnal_kredit_hutang,
    CAST(ISNULL(d.val,0) AS decimal(18,2))                   jurnal_debet_hutang,
    CAST((ISNULL(kb.val,0)+ISNULL(km.val,0))-ISNULL(d.val,0) AS decimal(18,2)) gl_sisa,
    CAST(k.sisa-((ISNULL(kb.val,0)+ISNULL(km.val,0))-ISNULL(d.val,0)) AS decimal(18,2)) beda
FROM khr k
JOIN tgl g       ON g.nomor=k.nomor
LEFT JOIN glk_bbm  kb ON kb.nomor=k.nomor
LEFT JOIN glk_memo km ON km.nomor=k.nomor
LEFT JOIN gld      d  ON d.nomor=k.nomor
ORDER BY k.unit, g.tgl_terima, k.nomor;
