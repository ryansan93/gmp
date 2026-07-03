/* =====================================================================
   KARTU HUTANG RINGKAS - BREAKDOWN PER INVOICE (DOC)
   ---------------------------------------------------------------------
   Tujuan : Rekonsiliasi KHR DOC vs GL DOC (COA 21180.200) per invoice.
   Periode: kumulatif s/d 2026-06-13.

   FIX PPh (cabang DOC pada subquery `konfir` di sisi KREDIT realisasi):
     - PPh berbasis tanggal:
         tgl_bayar >= '2026-01-01' -> PPh atas NET  ((sum(total) - CN) * 0.25%)
         tgl_bayar <  '2026-01-01' -> PPh atas GROSS (sum(total) * 0.25%)  [perilaku lama]
     - Ditambah join ke cn_post_det (agregat `pakai` per `nomor`).
     - group by diganti dari kpdd.total -> cn.pakai (karena total di-sum).
     - join terima_doc di blok PPh dihapus (tidak dipakai untuk hitung PPh).

   Catatan: cutover 2026-01-01 disimpulkan dari 19 invoice ber-CN
   (Nov-Des 2025 = gross, Jan 2026+ = net). Konfirmasi ke tim akuntansi
   apakah memang ada perubahan kebijakan perhitungan PPh DOC per 1 Jan 2026.

   DESAIN (penting): KHR ini SENGAJA TIDAK menangkap jurnal pembulatan /
   band-aid GL (mmitem ke/dari 96010.000 tanpa supplier -> jenis NULL ->
   ter-filter). Alasan: KHR adalah kebenaran OPERASIONAL. Pembulatan &
   band-aid (termasuk yang besar spt 498,48) adalah penyesuaian sisi GL
   yang kadang salah-unit. Kalau di-capture, KHR justru ikut "kotor".
   Selisih akibat pembulatan/band-aid biar muncul di tools rekonsiliasi
   (rekonsiliasi_saldo_GL_vs_KHR_per_unit_DOC.sql & rekonsiliasi_transfer_*),
   BUKAN dipaksa masuk KHR. (Percobaan fallback jenis -> over-capture,
   sudah di-revert.)

   Residual sub-rupiah yang muncul di breakdown = sisa pembulatan kas yang
   NYATA (ada di GL juga) -> immaterial, BUKAN selisih vs GL.

   Filter contoh di bawah: data.jenis='DOC' dan data.unit='BJN'.
   ===================================================================== */

select
	data.kode_trans,
	data.unit,
    data.jenis,
    cast(isnull(sum(data.saldo_awal), 0) as decimal(15, 2)) as saldo_awal,
    cast(isnull(sum(data.debet), 0) as decimal(15, 2)) as debet,
    cast(isnull(sum(data.kredit), 0) as decimal(15, 2)) as kredit,
    cast(isnull(sum(data.saldo_akhir), 0) as decimal(15, 2)) as saldo_akhir
from
(
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
left join
	konfirmasi_pembayaran_doc kpd
	on
		kpd.nomor = data.kode_trans
where
	data.jenis in ('DOC')
	and data.unit in ('BJN')
group by
	data.kode_trans,
    data.jenis,
    data.unit
having
	isnull(sum(data.saldo_akhir), 0) <> 0
order by
	data.unit asc
