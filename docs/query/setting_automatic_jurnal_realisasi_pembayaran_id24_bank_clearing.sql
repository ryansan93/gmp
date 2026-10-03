DECLARE @tgl_trans date, @jurnal_trans_id int, @supplier varchar(10), @perusahaan varchar(10), @keterangan varchar(200), @nominal decimal(13, 2), @saldo decimal(13, 2), @unit varchar(5), @noreg varchar(20), @kode_trans varchar(50), @kode_jurnal varchar(50), @no_bukti varchar(50)
DECLARE @det_jurnal_trans_id int, @urut int, @kode_voucher varchar(10), @asal varchar(100), @coa_asal varchar(10), @tujuan varchar(100), @coa_tujuan varchar(10)

DECLARE @jenis_transaksi varchar(15)

DECLARE @peternak varchar(10)
DECLARE @ekspedisi varchar(10)

DECLARE @coa_bank varchar(20)
DECLARE @nama_bank varchar(50)

DECLARE @unit_hutang varchar(5)
DECLARE @unit_bayar varchar(5)

DECLARE @jenis_supplier varchar(20)

DECLARE @nominal_pph23 decimal(13, 2)
DECLARE @nominal_pph22 decimal(13, 2)

DECLARE @jml_transfer decimal(13, 2), @jml_tf_hutang decimal(13, 2)
DECLARE @id_rpd int
DECLARE @jns_trans varchar(15)
DECLARE @selisih decimal(13, 2)
DECLARE @_kode_trans varchar(50)

DECLARE @kode_pengajuan varchar(50), @_kode_pengajuan varchar(50)

DECLARE @nominal_piutang_lain_extern decimal(13, 2), @tagihan decimal(13, 2)

/* KONSOLIDASI BANK -> CLEARING: tiap baris invoice tetap posting CLEARING -> HUTANG
   per invoice (banyak baris, spt sebelumnya), tapi sisi BANK cuma 1 baris di akhir
   (BANK -> CLEARING, nominal = akumulasi semua invoice dlm 1 bukti bayar). Selisih
   bank vs akumulasi hutang di-selisih kan ke @selisih setelah loop (menggantikan
   nilai @selisih dari select di bawah yg cuma valid utk 1 invoice per bukti bayar). */
DECLARE @bank_total decimal(13, 2) = 0
DECLARE @coa_clearing varchar(20) = '27001.000'
DECLARE @nama_clearing varchar(50) = 'Profit Center Zero Balance'

/* PCZB (cross-unit) dikonsolidasi 1 baris per unit_hutang, bukan 1 baris per invoice */
DECLARE @unit_pczb TABLE (unit varchar(5), total decimal(13, 2))
DECLARE @u_unit varchar(5), @u_total decimal(13, 2)

select
	@_kode_trans = cast(rp.nomor as varchar(50)),
	@jml_transfer = cast(rp.jml_transfer as decimal(13, 2)),
	@jml_tf_hutang = cast(rpd.jml_transfer as decimal(13, 2)),
	-- @jml_tf_hutang = cast(rpd.tagihan as decimal(13, 2)),
	@nominal_piutang_lain_extern = cast(IIF(rpd.jml_transfer > rpd.tagihan and abs(rpd.jml_transfer - rpd.tagihan) > 1, (rpd.jml_transfer - rpd.tagihan), 0) as decimal(13, 2)),
	@_kode_pengajuan = cast(_rpd.no_bayar as varchar(50)),
	@jns_trans = cast(rpd.transaksi as varchar(15)),
	@coa_bank = cast(rp.coa_bank as varchar(20))
from realisasi_pembayaran rp 
left join
	(
		select
			id_header, tagihan, sum(transfer) as jml_transfer, max(id) as id, transaksi
		from realisasi_pembayaran_det rpd
		group by
			id_header, tagihan, transaksi
	) rpd
	on
		rp.id = rpd.id_header
left join
	realisasi_pembayaran_det _rpd
	on
		rpd.id = _rpd.id
where 
	rp.id = @tbl_id
	
SET @selisih = @jml_transfer - (@jml_tf_hutang+@nominal_piutang_lain_extern)

--DECLARE saj_cursor CURSOR LOCAL FOR
--select
--    djt.id,
--    sajd.urut,
--    jt.kode_voucher,
--    c_asal.nama_coa as sumber,
--    sajd.coa_asal as sumber_coa,
--    c_tujuan.nama_coa as tujuan,
--    sajd.coa_tujuan as tujuan_coa
--from setting_automatic_jurnal_det sajd
--left join
--    (   select djt1.* from det_jurnal_trans djt1
--        right join
--            (select max(id) as id, kode from det_jurnal_trans group by kode) djt2
--            on
--                djt1.id = djt2.id
--    ) djt
--    on
--        djt.kode = sajd.det_jurnal_trans_kode
--left join
--    jurnal_trans jt
--    on
--        djt.id_header = jt.id
--left join
--	coa c_asal
--	on
--		c_asal.coa = sajd.coa_asal
--left join
--	coa c_tujuan
--	on
--		c_tujuan.coa = sajd.coa_tujuan
--where
--    sajd.id_header = @id_saj
--        
--OPEN saj_cursor
--
--FETCH NEXT FROM saj_cursor INTO
--    @det_jurnal_trans_id, @urut, @kode_voucher, @asal, @coa_asal, @tujuan, @coa_tujuan
--
--WHILE @@FETCH_STATUS = 0
--BEGIN	
	DECLARE drs_cursor CURSOR LOCAL FOR
	select
		data.tgl_trans,
		data.perusahaan,
		data.keterangan,
		data.unit,
		data.kode_trans,
		data.nominal,
		data.nominal_piutang_lain_extern,
		data.tagihan,
		data.jenis_transaksi,
		data.unit_hutang,
		data.unit_bayar,
		data.coa_bank,
		data.nama_bank,
		data.supplier,
		data.peternak,
		data.ekspedisi,
		data.jenis_supplier,
		data.pph23,
        data.pph22,
        data.no_bayar
	from
	(
		select
			rp.tgl_realisasi as tgl_trans,
			rp.perusahaan,
			'PEMBAYARAN '+rpd.transaksi+' '+pengajuan.kode_trans as keterangan,
			pengajuan.kode_unit as unit,
			rp.nomor as kode_trans,
			rpd.transfer as nominal,
			case
				when rpd.transfer > rpd.tagihan then
					rpd.transfer - rpd.tagihan
				else
					0
			end as nominal_piutang_lain_extern,
			rpd.tagihan,
			rpd.transaksi as jenis_transaksi,
			pengajuan.kode_unit as unit_hutang,
			c.unit as unit_bayar,
			rp.coa_bank,
			rp.nama_bank,
			rp.supplier,
			rp.peternak,
			rp.ekspedisi,
			j.nama as jenis_supplier,
			pengajuan.pph23,
	        pengajuan.pph22,
	        rpd.no_bayar,
	        'realisasi_pembayaran' as tbl_name
		from realisasi_pembayaran_det rpd
		left join
			(
				-- select kpd.nomor, kpdd.kode_unit, td.no_sj as kode_trans, 0 as pph23, (((kpd.total + isnull(_dn.nilai, 0)) - isnull(_cn.nilai, 0)) * (0.25/100)) as pph22 from konfirmasi_pembayaran_doc_det kpdd
				select kpd.nomor, kpdd.kode_unit, td.no_sj as kode_trans, 0 as pph23, 0 as pph22 from konfirmasi_pembayaran_doc_det kpdd
				left join
					(
						select td1.* from terima_doc td1
						right join
							(select max(id) as id, no_order from terima_doc group by no_order) td2
							on
								td1.id = td2.id
					) td
					on
						kpdd.no_order = td.no_order
				left join
					konfirmasi_pembayaran_doc kpd 
					on
						kpdd.id_header = kpd.id
				left join
					(select nomor, sum(pakai) as nilai from cn_post_det group by nomor) _cn
					on
						_cn.nomor = kpd.nomor
				left join
					(select nomor, sum(pakai) as nilai from dn_post_det group by nomor) _dn
					on
						_dn.nomor = kpd.nomor
						
				union all
				
				select kpp.nomor, kppd.kode_unit, kppd.no_sj as kode_trans, 0 as pph23, 0 as pph22 from konfirmasi_pembayaran_pakan_det kppd
				left join
					konfirmasi_pembayaran_pakan kpp 
					on
						kppd.id_header = kpp.id
						
				union all
				
				select * from (
					select kpv.nomor, kpvd.kode_unit, kpvd.no_sj as kode_trans, 0 as pph23, 0 as pph22 from konfirmasi_pembayaran_voadip_det kpvd
					left join
						konfirmasi_pembayaran_voadip kpv 
						on
							kpvd.id_header = kpv.id
					union all
					
					select
                                                m.no_mm as nomor,
                                                m.unit as kode_unit,
                                                m.no_mm as kode_trans,
                                                0 as pph23, 
                                                0 as pph22
                                        from mmitem mi
                                        left join
                                                mm m
                                                on
                                                        mi.no_mm = m.no_mm
                                        where
                                                mi.coa_tujuan in ('21174.000', '21180.300')
				) ovk
						
				union all
				
				select kpop.nomor, sj.kode_unit, kpop.invoice as kode_trans, kpop.potongan_pph_23 as pph23, 0 as pph22 from konfirmasi_pembayaran_oa_pakan_det kpopd
				left join
					(
						select kp.no_sj, SUBSTRING(REPLACE(REPLACE(kp.no_order, 'OPK/', ''), 'OP/', ''), 1, 3) as kode_unit from kirim_pakan kp
						
						union all
						
						select rp.no_retur as no_sj, SUBSTRING(REPLACE(REPLACE(rp.no_order, 'OPK/', ''), 'OP/', ''), 1, 3) as kode_unit from retur_pakan rp 
					) sj
					on
						kpopd.no_sj = sj.no_sj
				left join
					konfirmasi_pembayaran_oa_pakan kpop 
					on
						kpop.id = kpopd.id_header
				group by
					kpop.nomor, sj.kode_unit, kpop.invoice, kpop.potongan_pph_23
						
				union all
				
				select kpp.nomor, rhpp.kode_unit, rhpp.kode_trans, 0 as pph23, 0 as pph22 from konfirmasi_pembayaran_peternak_det kppd
				left join
					(
						select r.id as id_trans, r.invoice as kode_trans, w.kode as kode_unit, 'RHPP' as jenis from rhpp r 
						left join
							rdim_submit rs 
							on
								rs.noreg = r.noreg
						left join
							kandang k 
							on
								rs.kandang = k.id
						left join
							wilayah w
							on
								w.id = k.unit
						where 
							NOT EXISTS (select * from rhpp_group_noreg where noreg = r.noreg) 
							and r.jenis = 'rhpp_plasma'
						
						union all
						
						select rg.id as id_trans, rg.invoice as kode_trans, w.kode as kode_unit, 'RHPP GROUP' as jenis from rhpp_group rg 
						left join
							rhpp_group_noreg rgn 
							on
								rg.id = rgn.id_header
						left join
							rdim_submit rs 
							on
								rs.noreg = rgn.noreg
						left join
							kandang k 
							on
								rs.kandang = k.id
						left join
							wilayah w
							on
								w.id = k.unit
						where
							rg.jenis = 'rhpp_plasma'
						group by
							rg.id, rg.invoice, w.kode
					) rhpp
					on
						rhpp.id_trans = kppd.id_trans and
						rhpp.jenis = kppd.jenis
				left join
					konfirmasi_pembayaran_peternak kpp 
					on
						kpp.id = kppd.id_header
			) pengajuan
			on
				rpd.no_bayar = pengajuan.nomor
		left join
			realisasi_pembayaran rp
			on
				rpd.id_header = rp.id
		left join
			coa c
			on
				c.coa = rp.coa_bank
		left join
			(
	            select plg1.* from pelanggan plg1
	            right join
	                (select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor) plg2
	                on
	                    plg1.id = plg2.id
	            where
	                plg1.mstatus = 1
	        ) supl
	        on
	            rp.supplier = supl.nomor
	    left join
	    	jenis j 
	    	on
	    		supl.jenis = j.kode
		where
			(rp.status = 2 or rp.status is null) and
			rp.id = @tbl_id
			
		union all
		
		select
			p.tgl_realisasi as tgl_trans,
			'P001' as perusahaan,
			'PEMBAYARAN PIUTANG PLASMA '+UPPER(mtr.nama) as keterangan,
			c.unit as unit,
			p.kode as kode_trans,
			p.nominal as nominal,
			0 as nominal_piutang_lain_extern,
			0 as tagihan,
			'PIUTANG PLASMA' as jenis_transaksi,
			c.unit as unit_hutang,
			c.unit as unit_bayar,
			p.tf_bank as coa_bank,
			c.nama_coa as nama_bank,
			null as supplier,
			p.mitra as peternak,
			null as ekspedisi,
			null as jenis_supplier,
			0 as pph23,
	        0 as pph22,
	        p.kode as no_bayar,
	        'piutang' as tbl_name
		from piutang p
		left join
			coa c
			on
				c.coa = p.tf_bank
		left join
			(
				select mtr1.* from mitra mtr1
				right join
					(select max(id) as id, nomor from mitra group by nomor) mtr2
					on
						mtr1.id = mtr2.id
			) mtr
			on
				p.mitra = mtr.nomor
		where
			p.id = @tbl_id
			
		union all
		
		select
			bp.tgl_realisasi as tgl_trans,
			'P001' as perusahaan,
			'PEMBAYARAN HUTANG PERALATAN '+UPPER(supl.nama) as keterangan,
			'PST' as unit,
--			case
--				when op.unit is not null then
--					op.unit
--				else
--					mtr.kode_unit
--			end as unit,
			bp.no_faktur as kode_trans,
			bp.jml_bayar as nominal,
			0 as nominal_piutang_lain_extern,
			0 as tagihan,
			'PERALATAN' as jenis_transaksi,
			case
				when op.unit is not null then
					op.unit
				else
					mtr.kode_unit
			end as unit_hutang,
			c.unit as unit_bayar,
			bp.coa_bank as coa_bank,
			bp.nama_bank as nama_bank,
			op.supplier as supplier,
			null as peternak,
			null as ekspedisi,
			j.nama as jenis_supplier,
			0 as pph23,
	        0 as pph22,
	        bp.no_faktur as no_bayar,
	        'bayar_peralatan' as tbl_name
		from bayar_peralatan bp
		left join
			order_peralatan op
			on
				bp.no_order = op.no_order
		left join
			(
				select plg1.* from pelanggan plg1
				right join
					(select max(id) as id, nomor from pelanggan where tipe = 'supplier' group by nomor) plg2
					on
						plg1.id = plg2.id
			) supl
			on
				op.supplier = supl.nomor
		left join
			(
                select
                    m.nomor,
                    m.nama,
                    w.kode as kode_unit
                from kandang k
                right join
                    (
                        select mm1.* from mitra_mapping mm1
                        right join
                            (select max(id) as id, nim from mitra_mapping group by nim) mm2
                            on
                                mm1.id = mm2.id
                    ) mm
                    on
                        mm.id = k.mitra_mapping
                left join
                    mitra m 
                    on
                        mm.mitra = m.id
                left join
                    wilayah w
                    on
                        w.id = k.unit
                where
                    m.mstatus = 1
                group by
                    m.nomor,
                    m.nama,
                    w.kode
            ) mtr
            on
            	op.mitra = mtr.nomor
        left join
	    	jenis j 
	    	on
	    		supl.jenis = j.kode
	    left join
			coa c
			on
				c.coa = bp.coa_bank 
		where
			bp.id = @tbl_id
	) data
	where
		data.tbl_name = @tbl_name
	        
	OPEN drs_cursor
	
	FETCH NEXT FROM drs_cursor INTO
	    @tgl_trans, @perusahaan, @keterangan, @unit, @kode_trans, @nominal, @nominal_piutang_lain_extern, @tagihan, @jenis_transaksi, @unit_hutang, @unit_bayar, @coa_bank, @nama_bank, @supplier, @peternak, @ekspedisi, @jenis_supplier, @nominal_pph23, @nominal_pph22, @kode_pengajuan
	
	WHILE @@FETCH_STATUS = 0
	BEGIN
		IF ( @tbl_id_old IS NULL or @tbl_id_old = '' )
	    BEGIN
	        select
	            @kode_jurnal = cast(@kode_voucher+'/'+@unit+'/'+right(year(current_timestamp),2)+'/'+replace(str(month(getdate()),2),' ',0)+'/'+replace(str(substring(coalesce(max(kode_jurnal),'00000'),(LEN(@kode_voucher+'/'+@unit)+1+7),5)+1,5), ' ', '0') as varchar(50))
	        from det_jurnal
	        where
	            SUBSTRING(kode_jurnal,0,(LEN(@kode_voucher+'/'+@unit)+1+6)) = @kode_voucher+'/'+@unit+'/'+cast(right(year(current_timestamp),2) as char(2))+'/'+replace(str(month(getdate()),2),' ',0)
	    END
	    ELSE
	    BEGIN
	        select
	            @kode_jurnal = cast(dj.kode_jurnal as varchar(50))
	        from det_jurnal dj
	        where
	            dj.tbl_name = @tbl_name and
	            dj.tbl_id = @tbl_id_old
	            
			IF ( @kode_jurnal is null or @kode_jurnal = '' )
			BEGIN
				select
		            @kode_jurnal = cast(@kode_voucher+'/'+@unit+'/'+right(year(current_timestamp),2)+'/'+replace(str(month(getdate()),2),' ',0)+'/'+replace(str(substring(coalesce(max(kode_jurnal),'00000'),(LEN(@kode_voucher+'/'+@unit)+1+7),5)+1,5), ' ', '0') as varchar(50))
		        from det_jurnal
		        where
		            SUBSTRING(kode_jurnal,0,(LEN(@kode_voucher+'/'+@unit)+1+6)) = @kode_voucher+'/'+@unit+'/'+cast(right(year(current_timestamp),2) as char(2))+'/'+replace(str(month(getdate()),2),' ',0)
			END
	    END
		
	    IF ( @tbl_name like 'realisasi_pembayaran' )
	    BEGIN
		    IF ( @selisih < 1 and @selisih > 0 )
			BEGIN
				IF ( @_kode_pengajuan = @kode_pengajuan )
				BEGIN
					SET @nominal = @nominal + @selisih
				END
			END
			
			IF ( @jenis_transaksi like 'doc' )
			BEGIN
				select	
					@det_jurnal_trans_id = cast(null as int), 
					@urut = cast(sajd.urut as int), 
					@kode_voucher = cast(null as varchar(10)),
					@asal = cast(c_asal.nama_coa as varchar(100)), 
					@coa_asal = cast(sajd.coa_asal as varchar(10)), 
					@tujuan = cast(c_tujuan.nama_coa as varchar(100)), 
					@coa_tujuan = cast(sajd.coa_tujuan as varchar(10))
				from setting_automatic_jurnal_det sajd
				left join
					coa c_asal
					on
						c_asal.coa = sajd.coa_asal
				left join
					coa c_tujuan
					on
						c_tujuan.coa = sajd.coa_tujuan
				where
				    sajd.id_header = @id_saj and
				    sajd.urut in (1)

				SET @bank_total = @bank_total + @nominal

				insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
			    values
			    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @nominal, @saldo, NULL, NULL, @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, NULL)
			    
				/* PCZB dikonsolidasi per unit_hutang (1 baris per unit, bukan per invoice) - lihat blok sesudah loop */
				IF EXISTS (SELECT 1 FROM @unit_pczb WHERE unit = @unit_hutang)
					UPDATE @unit_pczb SET total = total + (@nominal) WHERE unit = @unit_hutang
				ELSE
					INSERT INTO @unit_pczb (unit, total) VALUES (@unit_hutang, (@nominal))
				/* top-up transfer vs tagihan: kalau tagihan > transfer, tambah D.Hutang / K.Pembulatan sebesar selisihnya (blm pernah kejadian sebaliknya di histori) */
				IF ( isnull(@tagihan, 0) > isnull(@nominal, 0) and (@tagihan - @nominal) < 1 )
				BEGIN
					insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
				    values
				    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, (@tagihan - @nominal), @saldo, 'Pembulatan Rupiah Penuh', '96010.000', @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
				END
			    
			    select	
					@det_jurnal_trans_id = cast(null as int), 
					@urut = cast(sajd.urut as int), 
					@kode_voucher = cast(null as varchar(10)),
					@asal = cast(c_asal.nama_coa as varchar(100)), 
					@coa_asal = cast(sajd.coa_asal as varchar(10)), 
					@tujuan = cast(c_tujuan.nama_coa as varchar(100)), 
					@coa_tujuan = cast(sajd.coa_tujuan as varchar(10))
				from setting_automatic_jurnal_det sajd
				left join
					coa c_asal
					on
						c_asal.coa = sajd.coa_asal
				left join
					coa c_tujuan
					on
						c_tujuan.coa = sajd.coa_tujuan
				where
				    sajd.id_header = @id_saj and
				    sajd.urut in (9)
				
				insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
			    values
			    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @nominal_pph22, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
				
			    /*
				IF ( @coa_asal = @coa_bank )
				BEGIN
					insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
				    values
				    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @nominal, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_bayar, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
				END
				
				IF ( @urut = 13 )
				BEGIN
					insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
				    values
				    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @nominal, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, @unit_bayar)
				END
				
				IF ( @urut = 15 )
				BEGIN				
					insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
				    values
				    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @nominal_pph22, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
				END
				*/
			END
			
			IF ( @jenis_transaksi like 'pakan' )
			BEGIN
				select	
					@det_jurnal_trans_id = cast(null as int), 
					@urut = cast(sajd.urut as int), 
					@kode_voucher = cast(null as varchar(10)),
					@asal = cast(c_asal.nama_coa as varchar(100)), 
					@coa_asal = cast(sajd.coa_asal as varchar(10)), 
					@tujuan = cast(c_tujuan.nama_coa as varchar(100)), 
					@coa_tujuan = cast(sajd.coa_tujuan as varchar(10))
				from setting_automatic_jurnal_det sajd
				left join
					coa c_asal
					on
						c_asal.coa = sajd.coa_asal
				left join
					coa c_tujuan
					on
						c_tujuan.coa = sajd.coa_tujuan
				where
				    sajd.id_header = @id_saj and
				    sajd.urut in (2)

				SET @bank_total = @bank_total + @nominal

				insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
			    values
			    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @nominal, @saldo, NULL, NULL, @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, NULL)
			    
				/* PCZB dikonsolidasi per unit_hutang (1 baris per unit, bukan per invoice) - lihat blok sesudah loop */
				IF EXISTS (SELECT 1 FROM @unit_pczb WHERE unit = @unit_hutang)
					UPDATE @unit_pczb SET total = total + (@nominal) WHERE unit = @unit_hutang
				ELSE
					INSERT INTO @unit_pczb (unit, total) VALUES (@unit_hutang, (@nominal))
				/* top-up transfer vs tagihan: kalau tagihan > transfer, tambah D.Hutang / K.Pembulatan sebesar selisihnya (blm pernah kejadian sebaliknya di histori) */
				IF ( isnull(@tagihan, 0) > isnull(@nominal, 0) and (@tagihan - @nominal) < 1 )
				BEGIN
					insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
				    values
				    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, (@tagihan - @nominal), @saldo, 'Pembulatan Rupiah Penuh', '96010.000', @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
				END
				
				/*
				IF ( @coa_asal = @coa_bank )
				BEGIN
					insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
				    values
				    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @nominal, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_bayar, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
				END
				
				IF ( @urut = 13 )
				BEGIN
					insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
				    values
				    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @nominal, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, @unit_bayar)
				END
				*/
			END
			
			IF ( @jenis_transaksi like 'voadip' )
			BEGIN
				IF ( @jenis_supplier like '%EKSTERNAL%')
				BEGIN
					select	
						@det_jurnal_trans_id = cast(null as int), 
						@urut = cast(sajd.urut as int), 
						@kode_voucher = cast(null as varchar(10)),
						@asal = cast(c_asal.nama_coa as varchar(100)), 
						@coa_asal = cast(sajd.coa_asal as varchar(10)), 
						@tujuan = cast(c_tujuan.nama_coa as varchar(100)), 
						@coa_tujuan = cast(sajd.coa_tujuan as varchar(10))
					from setting_automatic_jurnal_det sajd
					left join
						coa c_asal
						on
							c_asal.coa = sajd.coa_asal
					left join
						coa c_tujuan
						on
							c_tujuan.coa = sajd.coa_tujuan
					where
					    sajd.id_header = @id_saj and
					    sajd.urut in (4)

					SET @bank_total = @bank_total + @nominal

					insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
				    values
				    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @nominal, @saldo, NULL, NULL, @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, NULL)
					/* top-up transfer vs tagihan: kalau tagihan > transfer, tambah D.Hutang / K.Pembulatan sebesar selisihnya (blm pernah kejadian sebaliknya di histori) */
					IF ( isnull(@tagihan, 0) > isnull(@nominal, 0) and (@tagihan - @nominal) < 1 )
					BEGIN
						insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
					    values
					    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, (@tagihan - @nominal), @saldo, 'Pembulatan Rupiah Penuh', '96010.000', @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
					END
				END
				
				IF ( @jenis_supplier like '%INTERNAL%')
				BEGIN
					select	
						@det_jurnal_trans_id = cast(null as int), 
						@urut = cast(sajd.urut as int), 
						@kode_voucher = cast(null as varchar(10)),
						@asal = cast(c_asal.nama_coa as varchar(100)), 
						@coa_asal = cast(sajd.coa_asal as varchar(10)), 
						@tujuan = cast(c_tujuan.nama_coa as varchar(100)), 
						@coa_tujuan = cast(sajd.coa_tujuan as varchar(10))
					from setting_automatic_jurnal_det sajd
					left join
						coa c_asal
						on
							c_asal.coa = sajd.coa_asal
					left join
						coa c_tujuan
						on
							c_tujuan.coa = sajd.coa_tujuan
					where
					    sajd.id_header = @id_saj and
					    sajd.urut in (3)

					SET @bank_total = @bank_total + @nominal

					insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
				    values
				    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @nominal, @saldo, NULL, NULL, @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, NULL)
					/* top-up transfer vs tagihan: kalau tagihan > transfer, tambah D.Hutang / K.Pembulatan sebesar selisihnya (blm pernah kejadian sebaliknya di histori) */
					IF ( isnull(@tagihan, 0) > isnull(@nominal, 0) and (@tagihan - @nominal) < 1 )
					BEGIN
						insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
					    values
					    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, (@tagihan - @nominal), @saldo, 'Pembulatan Rupiah Penuh', '96010.000', @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
					END
				END
				
				/* PCZB dikonsolidasi per unit_hutang (1 baris per unit, bukan per invoice) - lihat blok sesudah loop */
				IF EXISTS (SELECT 1 FROM @unit_pczb WHERE unit = @unit_hutang)
					UPDATE @unit_pczb SET total = total + (@nominal) WHERE unit = @unit_hutang
				ELSE
					INSERT INTO @unit_pczb (unit, total) VALUES (@unit_hutang, (@nominal))
				
				/*
				IF ( @jenis_supplier like '%EKSTERNAL%' and (@urut = 7 or @urut = 8))
				BEGIN
					IF ( @coa_asal = @coa_bank )
					BEGIN
						insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
					    values
					    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @nominal, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_bayar, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
					END
				END
				
				IF ( @jenis_supplier like '%INTERNAL%' and (@urut = 5 or @urut = 6))
				BEGIN
					IF ( @coa_asal = @coa_bank )
					BEGIN
						insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
					    values
					    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @nominal, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_bayar, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
					END
				END
				
				IF ( @urut = 13 )
				BEGIN
					insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
				    values
				    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @nominal, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, @unit_bayar)
				END
				*/
			END
			
			IF ( @jenis_transaksi like 'oa pakan' )
			BEGIN
				select	
					@det_jurnal_trans_id = cast(null as int), 
					@urut = cast(sajd.urut as int), 
					@kode_voucher = cast(null as varchar(10)),
					@asal = cast(c_asal.nama_coa as varchar(100)), 
					@coa_asal = cast(sajd.coa_asal as varchar(10)), 
					@tujuan = cast(c_tujuan.nama_coa as varchar(100)), 
					@coa_tujuan = cast(sajd.coa_tujuan as varchar(10))
				from setting_automatic_jurnal_det sajd
				left join
					coa c_asal
					on
						c_asal.coa = sajd.coa_asal
				left join
					coa c_tujuan
					on
						c_tujuan.coa = sajd.coa_tujuan
				where
				    sajd.id_header = @id_saj and
				    sajd.urut in (5)

				SET @bank_total = @bank_total + @nominal

				insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
			    values
			    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @nominal, @saldo, NULL, NULL, @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, NULL)
			    
				/* PCZB dikonsolidasi per unit_hutang (1 baris per unit, bukan per invoice) - lihat blok sesudah loop */
				IF EXISTS (SELECT 1 FROM @unit_pczb WHERE unit = @unit_hutang)
					UPDATE @unit_pczb SET total = total + (@nominal) WHERE unit = @unit_hutang
				ELSE
					INSERT INTO @unit_pczb (unit, total) VALUES (@unit_hutang, (@nominal))
				/* top-up transfer vs tagihan: kalau tagihan > transfer, tambah D.Hutang / K.Pembulatan sebesar selisihnya (blm pernah kejadian sebaliknya di histori) */
				IF ( isnull(@tagihan, 0) > isnull(@nominal, 0) and (@tagihan - @nominal) < 1 )
				BEGIN
					insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
				    values
				    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, (@tagihan - @nominal), @saldo, 'Pembulatan Rupiah Penuh', '96010.000', @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
				END
			    
			    select	
					@det_jurnal_trans_id = cast(null as int), 
					@urut = cast(sajd.urut as int), 
					@kode_voucher = cast(null as varchar(10)),
					@asal = cast(c_asal.nama_coa as varchar(100)), 
					@coa_asal = cast(sajd.coa_asal as varchar(10)), 
					@tujuan = cast(c_tujuan.nama_coa as varchar(100)), 
					@coa_tujuan = cast(sajd.coa_tujuan as varchar(10))
				from setting_automatic_jurnal_det sajd
				left join
					coa c_asal
					on
						c_asal.coa = sajd.coa_asal
				left join
					coa c_tujuan
					on
						c_tujuan.coa = sajd.coa_tujuan
				where
				    sajd.id_header = @id_saj and
				    sajd.urut in (8)
				
				insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
			    values
			    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @nominal_pph23, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
				
				/*
				IF ( @coa_asal = @coa_bank )
				BEGIN
					insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
				    values
				    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @nominal, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_bayar, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
				END
				
				IF ( @urut = 13 )
				BEGIN
					insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
				    values
				    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @nominal, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, @unit_bayar)
				END
				
				IF ( @urut = 14 )
				BEGIN
					insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
				    values
				    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @nominal_pph23, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
				END
				*/
			END
			
			IF ( @jenis_transaksi like 'plasma'  )
			BEGIN
				/* BANK -> HUTANG */
				select	
					@det_jurnal_trans_id = cast(null as int), 
					@urut = cast(sajd.urut as int), 
					@kode_voucher = cast(null as varchar(10)),
					@asal = cast(c_asal.nama_coa as varchar(100)), 
					@coa_asal = cast(sajd.coa_asal as varchar(10)), 
					@tujuan = cast(c_tujuan.nama_coa as varchar(100)), 
					@coa_tujuan = cast(sajd.coa_tujuan as varchar(10))
				from setting_automatic_jurnal_det sajd
				left join
					coa c_asal
					on
						c_asal.coa = sajd.coa_asal
				left join
					coa c_tujuan
					on
						c_tujuan.coa = sajd.coa_tujuan
				where
				    sajd.id_header = @id_saj and
				    sajd.urut in (6)

				SET @bank_total = @bank_total + IIF(@nominal >= @tagihan, @tagihan, @nominal)

				insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
			    values
			    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, IIF(@nominal >= @tagihan, @tagihan, @nominal), @saldo, NULL, NULL, @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, NULL)
				/* top-up transfer vs tagihan: kalau tagihan > transfer, tambah D.Hutang / K.Pembulatan sebesar selisihnya (blm pernah kejadian sebaliknya di histori) */
				IF ( isnull(@tagihan, 0) > isnull(@nominal, 0) and (@tagihan - @nominal) < 1 )
				BEGIN
					insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
				    values
				    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, (@tagihan - @nominal), @saldo, 'Pembulatan Rupiah Penuh', '96010.000', @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
				END
			    
			    /* BANK -> PIUTANG LAIN EXTERN */
			    IF ( isnull(@nominal_piutang_lain_extern, 0) > 0 )
			    BEGIN
				    select	
						@det_jurnal_trans_id = cast(null as int), 
						@urut = cast(sajd.urut as int), 
						@kode_voucher = cast(null as varchar(10)),
						@asal = cast(c_asal.nama_coa as varchar(100)), 
						@coa_asal = cast(sajd.coa_asal as varchar(10)), 
						@tujuan = cast(c_tujuan.nama_coa as varchar(100)), 
						@coa_tujuan = cast(sajd.coa_tujuan as varchar(10))
					from setting_automatic_jurnal_det sajd
					left join
						coa c_asal
						on
							c_asal.coa = sajd.coa_asal
					left join
						coa c_tujuan
						on
							c_tujuan.coa = sajd.coa_tujuan
					where
					    sajd.id_header = @id_saj and
					    sajd.urut in (15)

					SET @bank_total = @bank_total + isnull(@nominal_piutang_lain_extern, 0)

					insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
				    values
				    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, isnull(@nominal_piutang_lain_extern, 0), @saldo, NULL, NULL, @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, NULL)
			    END
			    
			    
				/* PCZB dikonsolidasi per unit_hutang (1 baris per unit, bukan per invoice) - lihat blok sesudah loop */
				IF EXISTS (SELECT 1 FROM @unit_pczb WHERE unit = @unit_hutang)
					UPDATE @unit_pczb SET total = total + (IIF(@nominal >= @tagihan, (@tagihan+isnull(@nominal_piutang_lain_extern, 0)), (@nominal+isnull(@nominal_piutang_lain_extern, 0)))) WHERE unit = @unit_hutang
				ELSE
					INSERT INTO @unit_pczb (unit, total) VALUES (@unit_hutang, (IIF(@nominal >= @tagihan, (@tagihan+isnull(@nominal_piutang_lain_extern, 0)), (@nominal+isnull(@nominal_piutang_lain_extern, 0)))))
				
				/*
				IF ( @coa_asal = @coa_bank )
				BEGIN
					insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
				    values
				    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @nominal, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_bayar, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
				END
				
				IF ( @urut = 13 )
				BEGIN
					insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
				    values
				    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @nominal, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, @unit_bayar)
				END
				*/
			END
	    END
	    ELSE
	    BEGIN
		    IF ( @tbl_name like 'piutang' )
		    BEGIN
			    select	
					@det_jurnal_trans_id = cast(null as int), 
					@urut = cast(sajd.urut as int), 
					@kode_voucher = cast(null as varchar(10)),
					@asal = cast(c_asal.nama_coa as varchar(100)), 
					@coa_asal = cast(sajd.coa_asal as varchar(10)), 
					@tujuan = cast(c_tujuan.nama_coa as varchar(100)), 
					@coa_tujuan = cast(sajd.coa_tujuan as varchar(10))
				from setting_automatic_jurnal_det sajd
				left join
					coa c_asal
					on
						c_asal.coa = sajd.coa_asal
				left join
					coa c_tujuan
					on
						c_tujuan.coa = sajd.coa_tujuan
				where
				    sajd.id_header = @id_saj and
				    sajd.urut in (15)
				    
				insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
			    values
			    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @nominal, @saldo, @nama_bank, @coa_bank, @tujuan, @coa_tujuan, @unit_bayar, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
			    
			    /*
			    IF ( @urut = 21 or @urut = 22 )
			    BEGIN
				    IF ( @coa_asal = @coa_bank )
					BEGIN				
						insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
					    values
					    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @nominal, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_bayar, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
					END
			    END
			    */
			END
			
			IF ( @tbl_name like 'bayar_peralatan' )
		    BEGIN
			    IF ( @jenis_supplier like '%EKSTERNAL%' )
			    BEGIN
				    select	
						@det_jurnal_trans_id = cast(null as int), 
						@urut = cast(sajd.urut as int), 
						@kode_voucher = cast(null as varchar(10)),
						@asal = cast(c_asal.nama_coa as varchar(100)), 
						@coa_asal = cast(sajd.coa_asal as varchar(10)), 
						@tujuan = cast(c_tujuan.nama_coa as varchar(100)), 
						@coa_tujuan = cast(sajd.coa_tujuan as varchar(10))
					from setting_automatic_jurnal_det sajd
					left join
						coa c_asal
						on
							c_asal.coa = sajd.coa_asal
					left join
						coa c_tujuan
						on
							c_tujuan.coa = sajd.coa_tujuan
					where
					    sajd.id_header = @id_saj and
					    sajd.urut in (16)
					    
					insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
				    values
				    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @nominal, @saldo, @nama_bank, @coa_bank, @tujuan, @coa_tujuan, @unit_bayar, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
				    
				    IF ( @unit_hutang <> @unit_bayar )
					BEGIN
					    select	
							@det_jurnal_trans_id = cast(null as int), 
							@urut = cast(sajd.urut as int), 
							@kode_voucher = cast(null as varchar(10)),
							@asal = cast(c_asal.nama_coa as varchar(100)), 
							@coa_asal = cast(sajd.coa_asal as varchar(10)), 
							@tujuan = cast(c_tujuan.nama_coa as varchar(100)), 
							@coa_tujuan = cast(sajd.coa_tujuan as varchar(10))
						from setting_automatic_jurnal_det sajd
						left join
							coa c_asal
							on
								c_asal.coa = sajd.coa_asal
						left join
							coa c_tujuan
							on
								c_tujuan.coa = sajd.coa_tujuan
						where
						    sajd.id_header = @id_saj and
						    sajd.urut in (13)
						
						insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
					    values
					    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @nominal, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, @unit_bayar)
					END
				    
				    /*
				    IF ( @coa_asal = @coa_bank )
					BEGIN				
						IF ( @urut = 23 or @urut = 24 )
						BEGIN 
							insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
						    values
						    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @nominal, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_bayar, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
						END
					END
					
					IF ( @unit_hutang <> @unit_bayar )
					BEGIN
						IF ( @urut = 13 )
						BEGIN 
							insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
						    values
						    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @nominal, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, @unit_bayar)
						END
					END
					*/
			    END
			    
			    IF ( @jenis_supplier like '%INTERNAL%' )
			    BEGIN
				    select	
						@det_jurnal_trans_id = cast(null as int), 
						@urut = cast(sajd.urut as int), 
						@kode_voucher = cast(null as varchar(10)),
						@asal = cast(c_asal.nama_coa as varchar(100)), 
						@coa_asal = cast(sajd.coa_asal as varchar(10)), 
						@tujuan = cast(c_tujuan.nama_coa as varchar(100)), 
						@coa_tujuan = cast(sajd.coa_tujuan as varchar(10))
					from setting_automatic_jurnal_det sajd
					left join
						coa c_asal
						on
							c_asal.coa = sajd.coa_asal
					left join
						coa c_tujuan
						on
							c_tujuan.coa = sajd.coa_tujuan
					where
					    sajd.id_header = @id_saj and
					    sajd.urut in (17)
					    
					insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
				    values
				    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @nominal, @saldo, @nama_bank, @coa_bank, @tujuan, @coa_tujuan, @unit_bayar, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
				    
				    IF ( @unit_hutang <> @unit_bayar )
					BEGIN
					    select	
							@det_jurnal_trans_id = cast(null as int), 
							@urut = cast(sajd.urut as int), 
							@kode_voucher = cast(null as varchar(10)),
							@asal = cast(c_asal.nama_coa as varchar(100)), 
							@coa_asal = cast(sajd.coa_asal as varchar(10)), 
							@tujuan = cast(c_tujuan.nama_coa as varchar(100)), 
							@coa_tujuan = cast(sajd.coa_tujuan as varchar(10))
						from setting_automatic_jurnal_det sajd
						left join
							coa c_asal
							on
								c_asal.coa = sajd.coa_asal
						left join
							coa c_tujuan
							on
								c_tujuan.coa = sajd.coa_tujuan
						where
						    sajd.id_header = @id_saj and
						    sajd.urut in (13)
						
						insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
					    values
					    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @nominal, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, @unit_bayar)
					END
				    
				    /*
				    IF ( @coa_asal = @coa_bank )
					BEGIN
						IF ( @urut = 25 or @urut = 26 )
						BEGIN 
							insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
						    values
						    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @nominal, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_bayar, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
						END
					END
					
					IF ( @unit_hutang <> @unit_bayar )
					BEGIN
						IF ( @urut = 13 )
						BEGIN 
							insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
						    values
						    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @nominal, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @kode_trans, @kode_jurnal, @no_bukti, @unit_bayar)
						END
					END
					*/
			    END
			END
	    END
		
		FETCH NEXT FROM drs_cursor INTO
        	@tgl_trans, @perusahaan, @keterangan, @unit, @kode_trans, @nominal, @nominal_piutang_lain_extern, @tagihan, @jenis_transaksi, @unit_hutang, @unit_bayar, @coa_bank, @nama_bank, @supplier, @peternak, @ekspedisi, @jenis_supplier, @nominal_pph23, @nominal_pph22, @kode_pengajuan
	END
	
	CLOSE drs_cursor
	DEALLOCATE drs_cursor

	IF ( @tbl_name like 'realisasi_pembayaran' )
	BEGIN
		/* pakai total akumulasi semua invoice dlm bukti bayar ini (bukan @selisih
		   dari select awal, yg cuma valid kalau 1 invoice per bukti bayar) */
		SET @selisih = @jml_transfer - @bank_total

		IF ( isnull(@jml_transfer, 0) <> 0 )
		BEGIN
			insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
		    values
		    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, IIF(@jml_transfer < @bank_total, @jml_transfer, @bank_total), @saldo, @nama_bank, @coa_bank, NULL, NULL, @unit_bayar, @tbl_name, @tbl_id, @noreg, @_kode_trans, @kode_jurnal, @no_bukti, NULL)
		END

		/* PCZB per unit_hutang (1 baris per unit, bukan per invoice) */
		WHILE EXISTS (SELECT 1 FROM @unit_pczb)
		BEGIN
			SELECT TOP 1 @u_unit = unit, @u_total = total FROM @unit_pczb

			IF ( isnull(@u_total, 0) <> 0 and @u_unit is not null )
			BEGIN
				insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
			    values
			    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @u_total, @saldo, @nama_clearing, @coa_clearing, @nama_clearing, @coa_clearing, @u_unit, @tbl_name, @tbl_id, @noreg, @_kode_trans, @kode_jurnal, @no_bukti, @unit_bayar)
			END

			DELETE FROM @unit_pczb WHERE unit = @u_unit or (unit is null and @u_unit is null)
		END
	END

	IF ( @selisih < 1  )
	BEGIN
		IF ( @selisih < 0 and abs(@selisih) <= 1 )
		BEGIN
			select	
				@det_jurnal_trans_id = cast(null as int), 
				@urut = cast(sajd.urut as int), 
				@kode_voucher = cast(null as varchar(10)),
				@asal = cast(c_asal.nama_coa as varchar(100)), 
				@coa_asal = cast(sajd.coa_asal as varchar(10)), 
				@tujuan = cast(c_tujuan.nama_coa as varchar(100)), 
				@coa_tujuan = cast(sajd.coa_tujuan as varchar(10)),
				@urut = cast(sajd.urut as int)
			from setting_automatic_jurnal_det sajd
			left join
				coa c_asal
				on
					c_asal.coa = sajd.coa_asal
			left join
				coa c_tujuan
				on
					c_tujuan.coa = sajd.coa_tujuan
			where
			    sajd.id_header = @id_saj and
			    (
				    (@jns_trans like 'doc' and sajd.urut in (10)) 
				    or
				    (@jns_trans like 'voadip' and sajd.urut in (11))
				    or
				    (@jns_trans like 'pakan' and sajd.urut in (12))
				    or
				    (@jns_trans like 'oa pakan' and sajd.urut in (13))
				    or
				    (@jns_trans like 'plasma' and sajd.urut in (14))
		        )
		        
			IF ( @jns_trans like 'doc' and @urut = 10 )
			BEGIN
				IF ( @selisih < 1  )
				BEGIN
					insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
				    values
				    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @selisih, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @_kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
				END
			END
			
			IF ( @jns_trans like 'voadip' and @urut = 11 )
			BEGIN
				IF ( @selisih < 1  )
				BEGIN
					insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
				    values
				    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @selisih, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @_kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
				END
			END
			
			IF ( @jns_trans like 'pakan' and @urut = 12 )
			BEGIN
				IF ( @selisih < 1  )
				BEGIN
					insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
				    values
				    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @selisih, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @_kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
				END
			END
			
			IF ( @jns_trans like 'oa pakan' and @urut = 13 )
			BEGIN
				IF ( @selisih < 1  )
				BEGIN
					insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
				    values
				    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @selisih, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @_kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
				END
			END
			
			IF ( @jns_trans like 'plasma' and @urut = 14 )
			BEGIN
				IF ( @selisih < 1  )
				BEGIN
					insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
				    values
				    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, abs(@selisih), @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @_kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
				END
			END

--			insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
--		    values
--		    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @selisih, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_bayar, @tbl_name, @tbl_id, @noreg, @_kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
		END
		ELSE
		BEGIN
			select	
				@det_jurnal_trans_id = cast(null as int), 
				@urut = cast(sajd.urut as int), 
				@kode_voucher = cast(null as varchar(10)),
				@asal = cast(c_asal.nama_coa as varchar(100)), 
				@coa_asal = cast(sajd.coa_asal as varchar(10)), 
				@tujuan = cast(c_tujuan.nama_coa as varchar(100)), 
				@coa_tujuan = cast(sajd.coa_tujuan as varchar(10))
			from setting_automatic_jurnal_det sajd
			left join
				coa c_asal
				on
					c_asal.coa = sajd.coa_asal
			left join
				coa c_tujuan
				on
					c_tujuan.coa = sajd.coa_tujuan
			where
			    sajd.id_header = @id_saj and
			    sajd.urut in (18)

			IF ( isnull(@selisih, 0) <> 0 )
			BEGIN
				insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
			    values
			    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @selisih, @saldo, @nama_bank, @coa_bank, @tujuan, @coa_tujuan, @unit_bayar, @tbl_name, @tbl_id, @noreg, @_kode_trans, @kode_jurnal, @no_bukti, @unit_bayar)
			END
		END
	END
	
    /*
    select	
		@det_jurnal_trans_id = cast(djt.id as int), 
		@urut = cast(sajd.urut as int), 
		@kode_voucher = cast(jt.kode_voucher as varchar(10)),
		@asal = cast(c_asal.nama_coa as varchar(100)), 
		@coa_asal = cast(sajd.coa_asal as varchar(10)), 
		@tujuan = cast(c_tujuan.nama_coa as varchar(100)), 
		@coa_tujuan = cast(sajd.coa_tujuan as varchar(10)),
		@urut = cast(sajd.urut as int)
	from setting_automatic_jurnal_det sajd
	left join
	    (   select djt1.* from det_jurnal_trans djt1
	        right join
	            (select max(id) as id, kode from det_jurnal_trans group by kode) djt2
	            on
	                djt1.id = djt2.id
	    ) djt
	    on
	        djt.kode = sajd.det_jurnal_trans_kode
	left join
	    jurnal_trans jt
	    on
	        djt.id_header = jt.id
	left join
		coa c_asal
		on
			c_asal.coa = sajd.coa_asal
	left join
		coa c_tujuan
		on
			c_tujuan.coa = sajd.coa_tujuan
	where
	    sajd.id_header = @id_saj and
	    (
		    (@jns_trans like 'doc' and sajd.urut in (16)) 
		    or
		    (@jns_trans like 'voadip' and sajd.urut in (17))
		    or
		    (@jns_trans like 'pakan' and sajd.urut in (18))
		    or
		    (@jns_trans like 'oa pakan' and sajd.urut in (19))
		    or
		    (@jns_trans like 'plasma' and sajd.urut in (20))
        )
        
	IF ( @jns_trans like 'doc' and @urut = 16 )
	BEGIN
		IF ( @selisih < 1  )
		BEGIN
			insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
		    values
		    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @selisih, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @_kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
		END
	END
	
	IF ( @jns_trans like 'voadip' and @urut = 17 )
	BEGIN
		IF ( @selisih < 1  )
		BEGIN
			insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
		    values
		    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @selisih, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @_kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
		END
	END
	
	IF ( @jns_trans like 'pakan' and @urut = 18 )
	BEGIN
		IF ( @selisih < 1  )
		BEGIN
			insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
		    values
		    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @selisih, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @_kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
		END
	END
	
	IF ( @jns_trans like 'oa pakan' and @urut = 19 )
	BEGIN
		IF ( @selisih < 1  )
		BEGIN
			insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
		    values
		    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @selisih, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @_kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
		END
	END
	
	IF ( @jns_trans like 'plasma' and @urut = 20 )
	BEGIN
		IF ( @selisih < 1  )
		BEGIN
			insert into mapping_jurnal_trans (tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, mitra, ekspedisi, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, unit_tujuan)
		    values
		    (@tgl_trans, @det_jurnal_trans_id, @jurnal_trans_id, @supplier, @peternak, @ekspedisi, @perusahaan, @keterangan, @selisih, @saldo, @asal, @coa_asal, @tujuan, @coa_tujuan, @unit_hutang, @tbl_name, @tbl_id, @noreg, @_kode_trans, @kode_jurnal, @no_bukti, @unit_hutang)
		END
	END
	*/
	
--    FETCH NEXT FROM saj_cursor INTO
--        @det_jurnal_trans_id, @urut, @kode_voucher, @asal, @coa_asal, @tujuan, @coa_tujuan
--END
--
--CLOSE saj_cursor 
--DEALLOCATE saj_cursor

