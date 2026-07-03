CREATE PROCEDURE [dbo].[hitung_stok_siklus] @jenis varchar(10), @tbl_name varchar(50), @tbl_id int, @_tgl_transaksi date, @delete int, @noreg1 varchar(15) = null, @noreg2 varchar(15) = null, @return int = 1, @_today date = null
AS 
/* 2026 */
SET NOCOUNT ON 
SET ANSI_WARNINGS OFF

create table #lnoreg (
	urut int,
	noreg varchar(15) COLLATE DATABASE_DEFAULT
)

create table #pp_pakan (
	id_trans int,
	no_sj_asal varchar(50) COLLATE DATABASE_DEFAULT,
	kode_barang varchar(20) COLLATE DATABASE_DEFAULT,
	jumlah int,
	status tinyint
)

DECLARE @noreg varchar(15)
DECLARE @id_header int

DECLARE @tgl_asal date

DECLARE @dk_tgl_trans date, @dk_kode_trans varchar(20), @dk_jumlah decimal(10, 2), @dk_kode_barang varchar(10), @dk_no_sj_asal varchar(20), @dk_no_order_asal varchar(20), @dk_tbl_name varchar(20)

DECLARE @ds_id int
DECLARE @ds_jml_stok decimal(13, 2)
DECLARE @ds_kode_brg varchar(10)
DECLARE @ds_hrg_jual decimal(12, 4), @ds_hrg_beli decimal(12, 4), @ds_oa decimal(12, 2)
DECLARE @ds_jml_stok_pindah decimal(13, 2)
--DECLARE @ds_hrg_beli decimal(12, 4)
--DECLARE @ds_hrg_jual decimal(12, 4)

DECLARE @rv_hrg_beli decimal(12, 4), @rv_jumlah decimal(13, 2)

DECLARE @_dk_jumlah decimal(10, 2)

DECLARE @dk_noreg_tujuan varchar(20)

DECLARE @jml_pp_pakan int
DECLARE @jml_pp_pakan_tercatat int
DECLARE @jml_pp_pakan_tersimpan int
DECLARE @jml_stok_pakan int
DECLARE @no_sj_asal varchar(50)

DECLARE @id_dss int

DECLARE @id_dss_dpp int, @no_sj_asal_dpp varchar(50), @kode_barang_dpp varchar(20), @jumlah_dpp int

DECLARE @tgl_docin date

DECLARE @today date, @tgl_transaksi date, @start_date varchar(25), @end_date varchar(25)
IF ( @_today is null )
BEGIN
	SET @today = GETDATE()
END
ELSE
BEGIN
	SET @today = @_today
END

IF ( @jenis like '%doc%' )
BEGIN
	delete from #lnoreg
	
	IF ( @tbl_name = 'lhk' )
	BEGIN
		IF ( EXISTS(
			select l.* from lhk l where l.id = @tbl_id
		) )
		BEGIN
			select
				@noreg1 = cast(data.noreg1 as varchar(15)),
				@noreg2 = cast(data.noreg2 as varchar(15))
			from
			(
				select
					l.noreg as noreg1,
					null as noreg2
				from lhk l
				where
					l.id = @tbl_id
			) data
		END
		
		insert into #lnoreg (urut, noreg)
		values
		(1, @noreg1),
		(2, @noreg2)
	END
	
	IF ( @tbl_name = 'terima_doc' )
	BEGIN		
		IF ( EXISTS(
			select td.* from terima_doc td where td.id = @tbl_id
		) )
		BEGIN
			select
				@noreg1 = cast(data.noreg1 as varchar(15)),
				@noreg2 = cast(data.noreg2 as varchar(15))
			from
			(
				select
					od.noreg as noreg1,
					null as noreg2
				from terima_doc td
				left join
					(
						select od1.* from order_doc od1
						right join
							(select max(id) as id, no_order from order_doc group by no_order) od2
							on
								od1.id = od2.id
					) od
					on
						td.no_order = od.no_order
				where
					td.id = @tbl_id
			) data
		END
		
		insert into #lnoreg (urut, noreg)
		values
		(1, @noreg1),
		(2, @noreg2)
	END
	
	IF ( @tbl_name = 'adjin_doc' )
	BEGIN		
		IF ( EXISTS(
			select td.* from terima_doc td where td.id = @tbl_id
		) )
		BEGIN
			select
				@noreg1 = cast(data.noreg1 as varchar(15)),
				@noreg2 = cast(data.noreg2 as varchar(15))
			from
			(
				select
					noreg as noreg1,
					null as noreg2
				from adjin_doc
				where
					id = @tbl_id
			) data
		END
		
		insert into #lnoreg (urut, noreg)
		values
		(1, @noreg1),
		(2, @noreg2)
	END
	
	DECLARE noreg CURSOR LOCAL FOR
		select noreg from #lnoreg where noreg is not null order by urut asc
			
	OPEN noreg
	
	FETCH NEXT FROM noreg INTO
	    @noreg
	
	WHILE @@FETCH_STATUS = 0
	BEGIN
		SET @tgl_transaksi = @_tgl_transaksi
		
		WHILE ( @tgl_transaksi <= @today )
		BEGIN
			IF ( NOT EXISTS ( select  * from stok where periode = @tgl_transaksi ) )
			BEGIN
				insert into stok (periode, tgl_proses) values
				(@tgl_transaksi, getdate())
				
				select 
					@id_header = cast(id as int) 
				from stok where periode = @tgl_transaksi
				order by id desc
			END
			ELSE
			BEGIN
				select 
					@id_header = cast(id as int) 
				from stok where periode = @tgl_transaksi
				order by id desc
			END
			
			/*
			update dss
			set
				dss.jml_stok = dss.jml_stok + dtrans.jumlah
			from det_stok_siklus dss
			right join
				(
					select 
						dsts.id_header,
						sum(dsts.jumlah) as jumlah
					from det_stok_trans_siklus dsts
					left join
						det_stok_siklus dss
						on
							dsts.id_header = dss.id
					where
						dsts.tgl_trans = @tgl_transaksi and
						dss.noreg = @noreg and
						dss.jenis_barang = @jenis
					group by
						dsts.id_header
				) dtrans
				on
					dss.id = dtrans.id_header
			*/
			update dss
			set
				dss.jml_stok = (dss.jumlah - isnull(dtrans.jumlah, 0))
	--		select
	--			(dss.jumlah - isnull(dtrans.jumlah, 0)) as jml_stok
			from det_stok_siklus dss
			left join
				(
					select dsts.id_header, sum(dsts.jumlah) as jumlah from det_stok_trans_siklus dsts
					left join
						det_stok_siklus dss
						on
							dsts.id_header = dss.id
					where
						dsts.tgl_trans < @tgl_transaksi and
						dss.noreg = @noreg and
						dss.jenis_barang = @jenis
					group by
						dsts.id_header
				) dtrans
				on
					dss.id = dtrans.id_header
			where
				dss.tgl_trans < @tgl_transaksi and
				dss.noreg = @noreg and
				dss.jenis_barang = @jenis
					
			delete det_stok_trans_siklus where id in (
				select 
					dsts.id
				from det_stok_trans_siklus dsts
				left join
					det_stok_siklus dss
					on
						dsts.id_header = dss.id
				where
					dsts.tgl_trans = @tgl_transaksi and
					dss.noreg = @noreg and
					dss.jenis_barang = @jenis
				group by
					dsts.id
			)
			
--			delete from det_stok_trans_siklus where id_header in (select id from det_stok_siklus where id_header = @id_header and noreg = @noreg and jenis_barang = @jenis)
			delete from det_stok_siklus where id_header = @id_header and noreg = @noreg and jenis_barang = @jenis
			
			/* DATA MASUK */
			SET @start_date = cast(@tgl_transaksi as varchar(10))+' 00:00:00.001'
			SET @end_date = cast(@tgl_transaksi as varchar(10))+' 23:59:59:999'
			
			insert into det_stok_siklus (id_header, tgl_trans, noreg, kode_barang, jumlah, hrg_jual, hrg_beli, oa, kode_trans, jenis_barang, jenis_trans, jml_stok)
			select * from
			(
				select
					@id_header as id_header,
					td.datang as tgl_trans,
					od.noreg as noreg,
					od.item as kode_barang,
					td.jml_ekor as jumlah,
					td.harga as hrg_jual,
					td.harga as hrg_beli,
					0 as oa,
					od.no_order as kode_trans,
					'doc' as jenis_barang,
					'ORDER' as jenis_trans,
					td.jml_ekor as jml_stok
				from (
					select td1.* from terima_doc td1
					right join
						(select max(id) as id, no_order from terima_doc group by no_order) td2
						on
							td1.id = td2.id
				) td
				left join
					(
						select od1.* from order_doc od1
						right join
							(select max(id) as id, no_order from order_doc group by no_order) od2
							on
								od1.id = od2.id
					) od
					on
						od.no_order = td.no_order
				where
					cast(td.datang as date) between @tgl_transaksi and @tgl_transaksi and
					od.noreg = @noreg
					
				union all
				
				select
					@id_header as id_header,
					tanggal as tgl_trans,
					noreg as noreg,
					kode_barang,
					jumlah,
					harga as hrg_jual,
					harga as hrg_beli,
					0 as oa,
					kode as kode_trans,
					'doc' as jenis_barang,
					'ADJIN' as jenis_trans,
					jumlah as jml_stok
				from adjin_doc
				where
					tanggal between @tgl_transaksi and @tgl_transaksi and
					noreg = @noreg
			) dm
			/* END - DATA MASUK */
			
			/* DATA KELUAR */
			DECLARE data_keluar CURSOR LOCAL FOR
				select
					dk.tgl_trans,
					dk.kode_trans,
					dk.jumlah,
					dk.kode_barang,
					dk.no_sj_asal,
					dk.tbl_name
				from (
					select
						l.tanggal as tgl_trans,
						cast(l.id as varchar(20)) as kode_trans,
						(l.ekor_mati - isnull(l_prev.ekor_mati, 0)) as jumlah,
						null as kode_barang,
						null as no_sj_asal,
						'lhk' as tbl_name,
						1 as urut
					from lhk l
					left join
						(select top 1 * from lhk where noreg = @noreg and tanggal < @tgl_transaksi and ekor_mati > 0 order by tanggal desc) l_prev
						on
							l_prev.noreg = l.noreg and
							l_prev.umur = l.umur
					where
						l.tanggal = @tgl_transaksi and
						l.noreg = @noreg
				) dk
				order by
					dk.urut asc
					
			OPEN data_keluar
			
			FETCH NEXT FROM data_keluar INTO
			    @dk_tgl_trans, @dk_kode_trans, @dk_jumlah, @dk_kode_barang, @dk_no_sj_asal, @dk_tbl_name
			
			WHILE @@FETCH_STATUS = 0
			BEGIN
				SET @dk_no_sj_asal = REPLACE(@dk_no_sj_asal, 'SJ', 'OP') 
				
				WHILE ( @dk_jumlah > 0 )
				BEGIN
					IF ( @tbl_name = 'lhk' )
					BEGIN
						IF ( EXISTS( select * from det_stok_siklus where noreg = @noreg and tgl_trans <= @tgl_transaksi and jenis_barang = @jenis and jml_stok > 0 ) )
						BEGIN
							 select top 1
							 	@ds_id = cast(id as int),
							 	@ds_jml_stok = cast(jml_stok as decimal(13, 2)),
							 	@ds_kode_brg = cast(kode_barang as varchar(10))
							 from det_stok_siklus 
							 where 
							 	noreg = @noreg and 
							 	tgl_trans <= @tgl_transaksi and 
							 	jenis_barang = @jenis and
							 	jml_stok > 0
							 order by
							 	tgl_trans asc,
							 	kode_trans asc
							 	
							 IF ( @dk_jumlah <= @ds_jml_stok )
							 BEGIN							 
								 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
								 values
								 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @dk_jumlah, @ds_kode_brg, @dk_tbl_name)
								 
								 SET @ds_jml_stok = @ds_jml_stok - @dk_jumlah
								 SET @dk_jumlah = 0
								 
								 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
							 END
							 ELSE
							 BEGIN							 
								 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
								 values
								 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @ds_jml_stok, @ds_kode_brg, @dk_tbl_name)
								 
								 SET @dk_jumlah = @dk_jumlah - @ds_jml_stok
								 SET @ds_jml_stok = 0
								 
								 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
							 END
						END
						ELSE
						BEGIN
							SET @dk_jumlah = 0
						END
					END
					ELSE
					BEGIN
						IF ( EXISTS( select * from det_stok_siklus where noreg = @noreg and tgl_trans <= @tgl_transaksi and jml_stok > 0 and kode_trans = @dk_no_sj_asal and kode_barang = @dk_kode_barang ) )
						BEGIN
							 select top 1
							 	@ds_id = cast(id as int),
							 	@ds_jml_stok = cast(jml_stok as decimal(13, 2))
							 from det_stok_siklus 
							 where 
							 	noreg = @noreg and 
							 	tgl_trans <= @tgl_transaksi and 
							 	jml_stok > 0 and 
							 	kode_trans = @dk_no_sj_asal and
								kode_barang = @dk_kode_barang
							 order by
							 	tgl_trans asc,
							 	kode_trans asc
							 	
							 IF ( @dk_jumlah <= @ds_jml_stok )
							 BEGIN							 
								 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
								 values
								 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @dk_jumlah, @dk_kode_barang, @dk_tbl_name)
								 
								 SET @ds_jml_stok = @ds_jml_stok - @dk_jumlah
								 SET @dk_jumlah = 0
								 
								 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
							 END
							 ELSE
							 BEGIN							 
								 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
								 values
								 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @ds_jml_stok, @dk_kode_barang, @dk_tbl_name)
								 
								 SET @dk_jumlah = @dk_jumlah - @ds_jml_stok
								 SET @ds_jml_stok = 0
								 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
							 END
						END
						ELSE
						BEGIN
							SET @dk_jumlah = 0
						END
					END
				END
				
				FETCH NEXT FROM data_keluar INTO
			    	@dk_tgl_trans, @dk_kode_trans, @dk_jumlah, @dk_kode_barang, @dk_no_sj_asal, @dk_tbl_name
			END
			CLOSE data_keluar
			DEALLOCATE data_keluar
			/* END - DATA KELUAR */
			
			SET @tgl_transaksi = DATEADD(DD, 1, @tgl_transaksi)
		END
		
		FETCH NEXT FROM noreg INTO
	    	@noreg
	END
	CLOSE noreg
	DEALLOCATE noreg
	
	IF ( @return = 1 )
	BEGIN
		select 'berhasil hitung doc'
	END
END

IF ( @jenis like '%pakan%' )
BEGIN
	delete from #lnoreg
	
	IF ( @tbl_name = 'lhk' )
	BEGIN
		IF ( EXISTS(
			select l.* from lhk l where l.id = @tbl_id
		) and @noreg1 is null and @noreg2 is null )
		BEGIN
			select
				@noreg1 = cast(data.noreg1 as varchar(15)),
				@noreg2 = cast(data.noreg2 as varchar(15))
			from
			(
				select
					l.noreg as noreg1,
					null as noreg2
				from lhk l
				where
					l.id = @tbl_id
			) data
		END
		
		insert into #lnoreg (urut, noreg)
		values
		(1, @noreg1),
		(2, @noreg2)
	END
	
	IF ( @tbl_name = 'terima_pakan' )
	BEGIN		
		IF ( EXISTS(
			select tp.* from terima_pakan tp where tp.id = @tbl_id
		) and @noreg1 is null and @noreg2 is null )
		BEGIN			
			select
				@noreg1 = cast(data.noreg1 as varchar(15)),
				@noreg2 = cast(data.noreg2 as varchar(15))
			from
			(
				select
					case
						when kp.jenis_kirim = 'opkp' then
							kp.asal
						else
							kp.tujuan
					end as noreg1,
					case
						when kp.jenis_kirim = 'opkp' then
							kp.tujuan
						else
							null
					end as noreg2
				from terima_pakan tp
				left join
					kirim_pakan kp
					on
						tp.id_kirim_pakan = kp.id
				where
					tp.id = @tbl_id
			) data
			
			select
				@tgl_asal = cast(min(kp_asal.tgl_kirim) as date)
			from terima_pakan tp
			left join
				det_kirim_pakan dkp
				on
					dkp.id_header = tp.id_kirim_pakan
			left join
				kirim_pakan kp_asal
				on
					dkp.no_sj_asal = kp_asal.no_sj
			where	
				tp.id = @tbl_id
			group by
				tp.id
				
			IF ( @tgl_asal is not null )
			BEGIN
				IF ( @tgl_asal < @_tgl_transaksi )
				BEGIN
					SET @_tgl_transaksi = @tgl_asal
				END
			END
		END
		
		insert into #lnoreg (urut, noreg)
		values
		(1, @noreg1),
		(2, @noreg2)
	END
	
	IF ( @tbl_name = 'retur_pakan' )
	BEGIN
		IF ( EXISTS(
			select rp.* from retur_pakan rp where rp.id = @tbl_id
		) and @noreg1 is null and @noreg2 is null )
		BEGIN
			select
				@noreg1 = cast(data.noreg1 as varchar(15)),
				@noreg2 = cast(data.noreg2 as varchar(15))
			from
			(
				select
					rp.id_asal as noreg1,
					null as noreg2
				from retur_pakan rp
				where
					rp.id = @tbl_id
			) data
		END
		
		insert into #lnoreg (urut, noreg)
		values
		(1, @noreg1),
		(2, @noreg2)
	END
	
	DECLARE noreg CURSOR LOCAL FOR
		select noreg from #lnoreg where noreg is not null order by urut asc
			
	OPEN noreg
	
	FETCH NEXT FROM noreg INTO
	    @noreg
	
	WHILE @@FETCH_STATUS = 0
	BEGIN
		SET @tgl_transaksi = @_tgl_transaksi
		
--		print @id_header
--		print @noreg
		
		WHILE ( @tgl_transaksi <= @today )
		BEGIN
			IF ( NOT EXISTS ( select  * from stok where periode = @tgl_transaksi ) )
			BEGIN
				insert into stok (periode, tgl_proses) values
				(@tgl_transaksi, getdate())
			END
			
			select 
				@id_header = cast(id as int) 
			from stok where periode = @tgl_transaksi
			order by id desc
			
			IF ( @id_header is not null )
			BEGIN
--				select dsts.* from det_stok_trans_siklus dsts 
--				left join
--					det_stok_siklus dss
--					on
--						dsts.id_header = dss.id
--				where
--					dsts.tgl_trans >= @tgl_transaksi and
--					dss.noreg = @noreg and
--					dss.jenis_barang = @jenis
				
				delete det_stok_trans_siklus where id in (			
					select 
						dsts.id
					from det_stok_trans_siklus dsts
					left join
						det_stok_siklus dss
						on
							dsts.id_header = dss.id
					where
						dsts.tgl_trans >= @tgl_transaksi and
						dss.noreg = @noreg and
						dss.jenis_barang = @jenis
					group by
						dsts.id
				)
				
				update dss
				set
					dss.jml_stok = case 
						when isnull(dtrans.jumlah, 0) > dss.jumlah then
							dss.jumlah
						else
							(dss.jumlah - isnull(dtrans.jumlah, 0))
					end
				from det_stok_siklus dss
				left join
					(
						select dsts.id_header, sum(dsts.jumlah) as jumlah from det_stok_trans_siklus dsts
						left join
							det_stok_siklus dss
							on
								dsts.id_header = dss.id
						where
							dsts.tgl_trans < @tgl_transaksi and
							dss.noreg = @noreg and
							dss.jenis_barang = @jenis
						group by
							dsts.id_header
					) dtrans
					on
						dss.id = dtrans.id_header
				where
					dss.tgl_trans < @tgl_transaksi and
					dss.noreg = @noreg and
					dss.jenis_barang = @jenis
					
--				print '1--'+cast(@id_header as varchar(max))+' | '++cast(@noreg as varchar(max))+' | '+CONVERT(VARCHAR(10), @tgl_transaksi, 101)
					
				delete from det_stok_siklus where id_header = @id_header and noreg = @noreg and jenis_barang = @jenis
				
	--			IF ( @tgl_transaksi = '2026-01-31' )
	--			BEGIN
	--				insert into det_stok_siklus (id_header, tgl_trans, noreg, kode_barang, jumlah, hrg_jual, hrg_beli, oa, kode_trans, jenis_barang, jenis_trans, jml_stok)
	--				select
	--					@id_header as id_header,
	--					tp.tgl_terima as tgl_trans,
	--					kp.tujuan as noreg,
	--					dtp.item as kode_barang,
	--					dst.jumlah,
	--					ds.hrg_jual,
	--					ds.hrg_beli,
	--					kp.ongkos_angkut as oa,
	--					kp.no_order as kode_trans,
	--					'pakan' as jenis_barang,
	--					'ORDER' as jenis_trans,
	--					dst.jumlah as jml_stok
	--				from det_terima_pakan dtp
	--				left join
	--					terima_pakan tp
	--					on
	--						dtp.id_header = tp.id
	--				left join
	--					kirim_pakan kp
	--					on
	--						tp.id_kirim_pakan = kp.id
	--				left join
	--					det_stok_trans dst
	--					on
	--						dst.kode_trans = kp.no_order and
	--						dst.kode_barang = dtp.item
	--				left join
	--					det_stok ds
	--					on
	--						dst.id_header = ds.id
	--				where
	--					tp.tgl_terima = @tgl_transaksi and
	--					kp.tujuan = '25110300202' and
	--					kp.jenis_kirim = 'opkg'
	--					
	--				select * from det_stok_siklus where kode_trans = 'OP/BJN/26/01514'
	--			END
				
				/* GET TGL DOCIN */
				select
                    @tgl_docin = cast(td.datang as date)
                from (
                    select od1.* from order_doc od1
                    right join
                        (select max(id) as id, noreg from order_doc where noreg = @noreg group by noreg) od2
                        on
                            od1.id = od2.id
                    where
                        od1.noreg = @noreg
                ) od
                left join
                    (
                        select td1.* from terima_doc td1
                        right join
                            (select max(id) as id, no_order from terima_doc group by no_order) td2
                            on
                                td1.id = td2.id
                    ) td
                    on
                        od.no_order = td.no_order
				/* END - GET TGL DOCIN */
				
				/* DATA MASUK */
				insert into det_stok_siklus (id_header, tgl_trans, noreg, kode_barang, jumlah, hrg_jual, hrg_beli, oa, kode_trans, jenis_barang, jenis_trans, jml_stok)
				select * from
				(
					select
						@id_header as id_header,
						tp.tgl_terima as tgl_trans,
						kp.tujuan as noreg,
						dtp.item as kode_barang,
						sum(dst.jumlah) as jumlah,
						ds.hrg_jual,
						ds.hrg_beli,
						kp.ongkos_angkut as oa,
						kp.no_order as kode_trans,
						'pakan' as jenis_barang,
						'ORDER' as jenis_trans,
						sum(dst.jumlah) as jml_stok
					from det_terima_pakan dtp
					left join
						terima_pakan tp
						on
							dtp.id_header = tp.id
					left join
						kirim_pakan kp
						on
							tp.id_kirim_pakan = kp.id
					left join
						det_stok_trans dst
						on
							dst.kode_trans = kp.no_order and
							dst.kode_barang = dtp.item
					left join
						det_stok ds
						on
							dst.id_header = ds.id
					where
						tp.tgl_terima = @tgl_transaksi and
						kp.tujuan = @noreg and
						kp.jenis_kirim = 'opkg'
					group by
						tp.tgl_terima,
						kp.tujuan,
						dtp.item,
						ds.hrg_jual,
						ds.hrg_beli,
						kp.ongkos_angkut,
						kp.no_order
						
					union all
					
					select
						@id_header as id_header,
						tp.tgl_terima as tgl_trans,
						kp.tujuan as noreg,
						dtp.item as kode_barang,
						sum(dsts.jumlah) as jumlah,
						dss.hrg_jual,
						dss.hrg_beli,
						dss.oa,
						kp.no_order as kode_trans,
						'pakan' as jenis_barang,
						'MUTASI' as jenis_trans,
						sum(dsts.jumlah) as jml_stok
					from det_terima_pakan dtp 
					left join
						terima_pakan tp
						on
							dtp.id_header = tp.id
					left join
						kirim_pakan kp
						on
							tp.id_kirim_pakan = kp.id
					left join
						det_stok_trans_siklus dsts
						on
							dsts.kode_trans = kp.no_order and
							dsts.kode_barang = dtp.item
					left join
						det_stok_siklus dss
						on
							dsts.id_header = dss.id
					where
						tp.tgl_terima = @tgl_transaksi and
						kp.tujuan = @noreg and
						kp.jenis_kirim = 'opkp' and
						dss.id is not null
					group by
						tp.tgl_terima,
						kp.tujuan,
						dtp.item,
						dss.hrg_jual,
						dss.hrg_beli,
						dss.oa,
						kp.no_order
				) dm
				
				/* DATA KELUAR */
--				select
--					l.tanggal as tgl_trans,
--					cast(l.id as varchar(20)) as kode_trans,
--					lp.jumlah * 50 as jumlah,
--					lp.kode_barang as kode_barang,
--					null as no_sj_asal,
--					'lhk' as tbl_name,
--					1 as urut,
--					null as noreg_tujuan
--				from lhk l
--				left join
--					lhk_pakan lp
--					on
--						l.id = lp.id_header
--				where
--					l.tanggal = @tgl_transaksi and
--					l.noreg = @noreg and
--					lp.jumlah > 0
--					and (@tgl_docin >= '2026-05-08' or @noreg = '26010550301')
				
				DECLARE data_keluar CURSOR LOCAL FOR
					select
						dk.tgl_trans,
						dk.kode_trans,
						dk.jumlah,
						dk.kode_barang,
						dk.no_sj_asal,
						dk.tbl_name,
						dk.noreg_tujuan
					from (				
						select 
							l1.tanggal as tgl_trans,
							cast(l1.id as varchar(20)) as kode_trans,
							(l1.pakai_pakan-max(isnull(l2.pakai_pakan, 0))) * 50 as jumlah,
							null as kode_barang,
							null as no_sj_asal,
							'lhk' as tbl_name,
							2 as urut,
							null as noreg_tujuan
						from lhk l1
						right join
							(
								select max(id) as id, tanggal, noreg, umur from lhk group by tanggal, noreg, umur
							) l3
							on
								l1.id = l3.id
						left join
							(select tanggal, noreg, pakai_pakan, umur from lhk group by tanggal, noreg, pakai_pakan, umur) l2
							on
								l2.noreg = l1.noreg and
								l2.umur < l1.umur
						where
							l1.tanggal = @tgl_transaksi and
							l1.noreg = @noreg 
							and (@tgl_docin < '2026-05-01')
						group by
							l1.id,
							l1.noreg,
							l1.tanggal,
							l1.umur,
							l1.pakai_pakan
							
						union all
						
						select
							l.tanggal as tgl_trans,
							cast(l.id as varchar(20)) as kode_trans,
							lp.jumlah * 50 as jumlah,
							lp.kode_barang as kode_barang,
							null as no_sj_asal,
							'lhk' as tbl_name,
							2 as urut,
							null as noreg_tujuan
						from lhk l
						left join
							lhk_pakan lp
							on
								l.id = lp.id_header
						where
							l.tanggal = @tgl_transaksi and
							l.noreg = @noreg and
							lp.jumlah > 0
							and (@tgl_docin >= '2026-05-01')
							
						union all
					
						select
							tp.tgl_terima as tgl_trans,
							kp.no_order as kode_trans,
							dkp.jumlah,
							dkp.item as kode_barang,
							dkp.no_sj_asal,
							'terima_pakan' as tbl_name,
							1 as urut,
							kp.tujuan as noreg_tujuan
						from det_kirim_pakan dkp
						left join
							kirim_pakan kp
							on
								dkp.id_header = kp.id
						left join
							terima_pakan tp
							on
								tp.id_kirim_pakan = kp.id
						where
							tp.id is not null and
							tp.tgl_terima = @tgl_transaksi and
							kp.asal = @noreg
							
						union all
						
						select
							rp.tgl_retur as tgl_trans,
							rp.no_retur as kode_trans,
							drp.jumlah,
							drp.item as kode_barang,
							rp.no_order as no_sj_asal,
							'retur_pakan' as tbl_name,
							3 as urut,
							null as noreg_tujuan
						from det_retur_pakan drp
						left join
							retur_pakan rp
							on
								drp.id_header = rp.id
						where
							rp.tgl_retur = @tgl_transaksi and
							rp.id_asal = @noreg
					) dk
					order by
						dk.tgl_trans asc,
						dk.urut asc
						
				OPEN data_keluar
				
				FETCH NEXT FROM data_keluar INTO
				    @dk_tgl_trans, @dk_kode_trans, @dk_jumlah, @dk_kode_barang, @dk_no_sj_asal, @dk_tbl_name, @dk_noreg_tujuan
				
				WHILE @@FETCH_STATUS = 0
				BEGIN
					SET @dk_no_sj_asal = REPLACE(@dk_no_sj_asal, 'SJ', 'OP')
					
					SET @_dk_jumlah = @dk_jumlah
					
					WHILE ( @_dk_jumlah > 0 )
					BEGIN						
						IF ( @dk_tbl_name = 'lhk' )
						BEGIN
							IF ( @tgl_docin >= '2026-05-01' )
							BEGIN
								IF (
									EXISTS (
										select * 
										from det_stok_siklus dss
										where 
											dss.noreg = @noreg and 
											dss.jenis_barang = @jenis and 
											dss.kode_barang = @dk_kode_barang and
											dss.jml_stok > 0
									)
								)
								BEGIN
									select top 1
										@ds_id = cast(dss.id as int),
										@ds_jml_stok = cast(dss.jml_stok as int)
									from det_stok_siklus dss
									where 
										dss.noreg = @noreg and 
										dss.jenis_barang = @jenis and 
										dss.kode_barang = @dk_kode_barang and
										dss.jml_stok > 0
									order by
										dss.tgl_trans asc,
										dss.kode_trans asc
										
									IF ( @_dk_jumlah <= @ds_jml_stok )
									 BEGIN							 
										 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
										 values
										 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @_dk_jumlah, @dk_kode_barang, @dk_tbl_name)
										 
										 SET @ds_jml_stok = @ds_jml_stok - @_dk_jumlah
										 SET @_dk_jumlah = 0
										 
										 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
									 END
									 ELSE
									 BEGIN							 
										 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
										 values
										 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @ds_jml_stok, @dk_kode_barang, @dk_tbl_name)
										 
										 SET @_dk_jumlah = @_dk_jumlah - @ds_jml_stok
										 SET @ds_jml_stok = 0
										 
										 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
									 END
								END
							END
							ELSE
							BEGIN								
								/* 2026-04-27 */
								IF (
									EXISTS (
										select * 
										from det_stok_siklus dss 
										left join
											(
												select
													id_trans,
													no_sj_asal, 
													kode_barang,
													sum(jumlah) as jumlah
												from #pp_pakan
												where
													no_sj_asal <> @dk_no_sj_asal and
													kode_barang <> @dk_kode_barang
												group by
													id_trans, no_sj_asal, kode_barang
											) ppp
											on
												ppp.id_trans = dss.id and
												REPLACE(ppp.no_sj_asal, 'SJ', 'OP') = dss.kode_trans and
												ppp.kode_barang = dss.kode_barang
										where 
											dss.noreg = @noreg and 
											dss.jenis_barang = @jenis and 
											dss.jml_stok - isnull(ppp.jumlah, 0) > 0
									)
								)
								BEGIN								
									DECLARE data_dss CURSOR LOCAL FOR
										select 
											dss.id 
										from det_stok_siklus dss
										left join
											(
												select
													id_trans,
													no_sj_asal, 
													kode_barang,
													sum(jumlah) as jumlah
												from #pp_pakan
												where
													(@dk_no_sj_asal is null or no_sj_asal <> @dk_no_sj_asal) and
													(@dk_kode_barang is null or kode_barang <> @dk_kode_barang)
												group by
													id_trans, no_sj_asal, kode_barang
											) ppp
											on
												ppp.id_trans = dss.id and
												REPLACE(ppp.no_sj_asal, 'SJ', 'OP') = dss.kode_trans and
												ppp.kode_barang = dss.kode_barang
										where 
											dss.noreg = @noreg and 
											dss.jenis_barang = @jenis and 
											dss.jml_stok - isnull(ppp.jumlah, 0) > 0
										order by
										 	dss.tgl_trans asc,
										 	dss.kode_trans asc
										
									OPEN data_dss
									
									FETCH NEXT FROM data_dss INTO
									    @id_dss
									
									WHILE @@FETCH_STATUS = 0
									BEGIN										
										IF ( @_dk_jumlah > 0 )
										BEGIN
											select
												@no_sj_asal = cast(REPLACE(dss.kode_trans, 'OP', 'SJ') as varchar(50)),
												@ds_kode_brg = cast(dss.kode_barang as varchar(10)),
												@jml_stok_pakan = cast(dss.jumlah as int)
											from det_stok_siklus dss 
											where 
												dss.id = @id_dss
												
											select
												@jml_pp_pakan = cast(sum(dkp.jumlah) as int)
											from det_kirim_pakan dkp 
									 		left join
									 			kirim_pakan kp
									 			on
									 				dkp.id_header = kp.id
									 		where 
									 			dkp.no_sj_asal = @no_sj_asal and
									 			dkp.item = @ds_kode_brg
												
											IF (
												EXISTS (
													select * from
													(
														select dkp.no_sj_asal, dkp.item, sum(dkp.jumlah) as jumlah from det_kirim_pakan dkp 
												 		left join
												 			kirim_pakan kp
												 			on
												 				dkp.id_header = kp.id
												 		where 
												 			dkp.no_sj_asal = @no_sj_asal and
												 			dkp.item = @ds_kode_brg
												 		group by
												 			dkp.no_sj_asal, dkp.item
													) dkp
													left join
														(
															select
																REPLACE(dsts.kode_trans, 'OP', 'SJ') as no_sj_asal,
																dsts.kode_barang,
																sum(dsts.jumlah) as jumlah
															from det_stok_trans_siklus dsts 
															where
																dsts.kode_trans = REPLACE(@no_sj_asal, 'SJ', 'OP') and
																dsts.kode_barang = @ds_kode_brg
															group by
																dsts.kode_trans, dsts.kode_barang
														) dsts
														on
															dsts.no_sj_asal = dkp.no_sj_asal and
															dsts.kode_barang = dkp.item
													left join
														(
															select
																no_sj_asal, 
																kode_barang,
																sum(jumlah) as jumlah
															from #pp_pakan
--															where
--																(@dk_no_sj_asal is null or no_sj_asal <> @dk_no_sj_asal) and
--																(@dk_kode_barang is null or kode_barang <> @dk_kode_barang)
															group by
																no_sj_asal, kode_barang
														) ppp
														on
															ppp.no_sj_asal = dkp.no_sj_asal and
															ppp.kode_barang = dkp.item
													where
														dkp.jumlah > (isnull(ppp.jumlah, 0) + isnull(dsts.jumlah, 0))
												)
											)
											BEGIN											
												DECLARE data_pp CURSOR LOCAL FOR
													select
														dss.id,
														REPLACE(dss.kode_trans, 'OP', 'SJ') as no_sj_asal,
														dss.kode_barang,
														dss.jumlah
													from det_stok_siklus dss 
													where 
														dss.kode_trans = REPLACE(@no_sj_asal, 'SJ', 'OP') and
														dss.kode_barang = @ds_kode_brg
													order by
														dss.id desc
													
												OPEN data_pp
												
												FETCH NEXT FROM data_pp INTO
												    @id_dss_dpp, @no_sj_asal_dpp, @kode_barang_dpp, @jumlah_dpp
												
												WHILE @@FETCH_STATUS = 0
												BEGIN
													IF ( @jumlah_dpp <= @jml_pp_pakan )
													BEGIN
														SET @jml_pp_pakan = @jml_pp_pakan - @jumlah_dpp
														
														insert into #pp_pakan (id_trans, no_sj_asal, kode_barang, jumlah) values
														(@id_dss_dpp, @no_sj_asal_dpp, @kode_barang_dpp, @jumlah_dpp)
													END
													ELSE
													BEGIN												
														insert into #pp_pakan (id_trans, no_sj_asal, kode_barang, jumlah) values
														(@id_dss_dpp, @no_sj_asal_dpp, @kode_barang_dpp, @jml_pp_pakan)
														
														SET @jml_pp_pakan = 0
													END
													
													FETCH NEXT FROM data_pp INTO
											    		@id_dss_dpp, @no_sj_asal_dpp, @kode_barang_dpp, @jumlah_dpp
												END
												CLOSE data_pp
												DEALLOCATE data_pp
											END
											
									 		select
												@no_sj_asal = cast(REPLACE(dss.kode_trans, 'OP', 'SJ') as varchar(50)),
												@ds_kode_brg = cast(dss.kode_barang as varchar(10)),
												@ds_jml_stok = cast(dss.jml_stok - isnull(ppp.jumlah, 0) as int)
											from det_stok_siklus dss
											left join
												(
													select
														id_trans,
														no_sj_asal, 
														kode_barang,
														sum(jumlah) as jumlah
													from #pp_pakan
													where
														status is null or status <> 1
--														(@dk_no_sj_asal is null or no_sj_asal <> @dk_no_sj_asal) and
--														(@dk_kode_barang is null or kode_barang <> @dk_kode_barang)
													group by
														id_trans, no_sj_asal, kode_barang
												) ppp
												on
													ppp.id_trans = dss.id and
													REPLACE(ppp.no_sj_asal, 'SJ', 'OP') = dss.kode_trans and
													ppp.kode_barang = dss.kode_barang
											where 
												dss.id = @id_dss
												
											update #pp_pakan set status = 1 where no_sj_asal = @no_sj_asal and kode_barang = @ds_kode_brg
												
--											select 'ppp' as tbl, * from #pp_pakan 
--											where
--												(@dk_no_sj_asal is null or no_sj_asal <> @dk_no_sj_asal) and
--												(@dk_kode_barang is null or kode_barang <> @dk_kode_barang)
												
--											SET @dk_no_sj_asal = @no_sj_asal
--											SET @dk_kode_barang = @ds_kode_brg
--												
--											print @id_dss
--									 		print @no_sj_asal
--									 		print @jml_stok_pakan
--									 		print @jml_pp_pakan
--									 		print @ds_jml_stok
--									 		print @dk_no_sj_asal
--									 		print @dk_kode_barang
--									 		print @_dk_jumlah
--									 		print '----------'
									 						
											IF ( @ds_jml_stok > 0 )
											BEGIN
												IF ( @_dk_jumlah <= @ds_jml_stok )
												BEGIN							 
													insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
													values
													(@id_dss, @dk_tgl_trans, @dk_kode_trans, @_dk_jumlah, @ds_kode_brg, @dk_tbl_name)
													 
													SET @ds_jml_stok = @ds_jml_stok - @_dk_jumlah
													SET @_dk_jumlah = 0
													
													IF ( @no_sj_asal like '%/MGT/26/05136%' )
													BEGIN
														print 1
														print @dk_kode_trans
														print @_dk_jumlah
														print @ds_jml_stok
														print '----------'
													END
													 
													update det_stok_siklus set jml_stok = @ds_jml_stok where id = @id_dss
													
													break;
												END
												ELSE
												BEGIN							 
													insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
													values
													(@id_dss, @dk_tgl_trans, @dk_kode_trans, @ds_jml_stok, @ds_kode_brg, @dk_tbl_name)
													 
													SET @_dk_jumlah = @_dk_jumlah - @ds_jml_stok
													SET @ds_jml_stok = 0
													
													IF ( @no_sj_asal like '%/MGT/26/05136%' )
													BEGIN
														print 2
														print @dk_kode_trans
														print @_dk_jumlah
														print @ds_jml_stok
														print '----------'
													END
													 
													update det_stok_siklus set jml_stok = @ds_jml_stok where id = @id_dss
												END
											END
										END
										ELSE
										BEGIN											
											BREAK;
										END
								 		
										FETCH NEXT FROM data_dss INTO
								    		@id_dss
									END
									CLOSE data_dss
									DEALLOCATE data_dss
								END
								ELSE
								BEGIN
									SET @_dk_jumlah = 0
								END
								/* 2026-04-27 */
							END
						END
						ELSE
						BEGIN							
							IF ( EXISTS( select * from det_stok_siklus where noreg = @noreg and tgl_trans <= @tgl_transaksi and jml_stok > 0 and kode_trans = @dk_no_sj_asal and kode_barang = @dk_kode_barang ) )
							BEGIN
--								 delete from #pp_pakan 
--								 where 
--								 	no_sj_asal = @dk_no_sj_asal and
--								 	kode_barang = @dk_kode_barang
								
								 select top 1
								 	@ds_id = cast(id as int),
								 	@ds_jml_stok = cast(jml_stok as decimal(13, 2)),
								 	@ds_kode_brg = cast(kode_barang as varchar(10)),
								 	@ds_hrg_jual = cast(hrg_jual as decimal(12, 4)),
								 	@ds_hrg_beli = cast(hrg_beli as decimal(12, 4)),
								 	@ds_oa = cast(oa as decimal(12, 2))
								 from det_stok_siklus 
								 where 
								 	noreg = @noreg and 
								 	tgl_trans <= @tgl_transaksi and 
								 	jml_stok > 0 and 
								 	kode_trans = @dk_no_sj_asal and
									kode_barang = @dk_kode_barang
								 order by
								 	tgl_trans asc,
								 	kode_trans asc
								 	
								 IF ( @_dk_jumlah <= @ds_jml_stok )
								 BEGIN							 
									 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
									 values
									 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @_dk_jumlah, @dk_kode_barang, @dk_tbl_name)
									 
									 SET @ds_jml_stok = @ds_jml_stok - @_dk_jumlah
									 SET @_dk_jumlah = 0
									 
									 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
								 END
								 ELSE
								 BEGIN							 
									 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
									 values
									 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @ds_jml_stok, @dk_kode_barang, @dk_tbl_name)
									 
									 SET @_dk_jumlah = @_dk_jumlah - @ds_jml_stok
									 SET @ds_jml_stok = 0
									 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
								 END
							END
							ELSE
							BEGIN
								SET @_dk_jumlah = 0
							END
						END
							
							/* 2026-03-06
							IF ( EXISTS( 
								 select 
									*
								 from det_stok_siklus dss
								 left join
								 	(
								 		select id_header, sum(jumlah) as jumlah from det_stok_trans_siklus where tbl_name = 'terima_pakan' group by id_header
								 	) dsts
								 	on
								 		dss.id = dsts.id_header
								 left join
								 	(
								 		select dkp.no_sj_asal, dkp.item, sum(dkp.jumlah) as jumlah from det_kirim_pakan dkp 
								 		left join
								 			kirim_pakan kp
								 			on
								 				dkp.id_header = kp.id
								 		where 
								 			dkp.no_sj_asal is not null and
								 			kp.no_order <> @dk_kode_trans
								 		group by dkp.no_sj_asal, dkp.item
								 	) dkp
								 	on
								 		dss.kode_trans = REPLACE(dkp.no_sj_asal, 'SJ', 'OP') and
								 		dss.kode_barang = dkp.item
								 left join
						 			(select id_trans, sum(jumlah) as jumlah from #pp_pakan group by id_trans) pp
						 			on
						 				dss.id = pp.id_trans
						 		 left join
						 		 	(
						 		 		select dss.id, sum(dsts.jumlah) as jumlah from det_stok_trans_siklus dsts
						 		 		left join
						 		 			det_stok_siklus dss
						 		 			on
						 		 				dsts.id_header = dss.id
						 		 		where
						 		 			dss.noreg = @noreg and
						 		 			dss.jenis_barang = @jenis and
 											dsts.tbl_name = 'terima_pakan' and
 											dsts.tgl_trans < @tgl_transaksi
 										group by
 											dss.id
						 		 	) pp_sudah_tercatat
						 		 	on
						 		 		dss.id = pp_sudah_tercatat.id
								 where 
								 	dss.noreg = @noreg and 
								 	dss.jenis_barang = @jenis and
								 	(dss.jml_stok - (isnull(pp_sudah_tercatat.jumlah, 0) - isnull(dsts.jumlah, 0))) > 0
							) )
							BEGIN								
								select top 1
								 	@ds_id = cast(dss.id as int),
								 	@no_sj_asal = cast(REPLACE(dss.kode_trans, 'OP', 'SJ') as varchar(50)),
								 	@jml_pp_pakan = cast((isnull(dkp.jumlah, 0) - (isnull(pp_sudah_tercatat.jumlah, 0) - isnull(dsts.jumlah, 0))) as int),
								 	@jml_stok_pakan = cast((dss.jml_stok - (isnull(pp_sudah_tercatat.jumlah, 0) - isnull(dsts.jumlah, 0))) as int),
								 	@ds_jml_stok = cast((dss.jml_stok - (isnull(pp_sudah_tercatat.jumlah, 0) - isnull(dsts.jumlah, 0))) as decimal(13, 2)),
								 	@ds_kode_brg = cast(dss.kode_barang as varchar(10)),
								 	@ds_hrg_jual = cast(dss.hrg_jual as decimal(12, 4)),
								 	@ds_hrg_beli = cast(dss.hrg_beli as decimal(12, 4)),
								 	@ds_oa = cast(dss.oa as decimal(12, 2))
								 from det_stok_siklus dss
								 left join
								 	(
								 		select id_header, sum(jumlah) as jumlah from det_stok_trans_siklus where tbl_name = 'terima_pakan' group by id_header
								 	) dsts
								 	on
								 		dss.id = dsts.id_header
								 left join
								 	(
								 		select dkp.no_sj_asal, dkp.item, sum(dkp.jumlah) as jumlah from det_kirim_pakan dkp 
								 		left join
								 			kirim_pakan kp
								 			on
								 				dkp.id_header = kp.id
								 		where 
								 			dkp.no_sj_asal is not null and
								 			kp.no_order <> @dk_kode_trans
								 		group by dkp.no_sj_asal, dkp.item
								 	) dkp
								 	on
								 		dss.kode_trans = REPLACE(dkp.no_sj_asal, 'SJ', 'OP') and
								 		dss.kode_barang = dkp.item
								 left join
						 			(select id_trans, sum(jumlah) as jumlah from #pp_pakan group by id_trans) pp
						 			on
						 				dss.id = pp.id_trans
						 		 left join
						 		 	(
						 		 		select dss.id, sum(dsts.jumlah) as jumlah from det_stok_trans_siklus dsts
						 		 		left join
						 		 			det_stok_siklus dss
						 		 			on
						 		 				dsts.id_header = dss.id
						 		 		where
						 		 			dss.noreg = @noreg and
						 		 			dss.jenis_barang = @jenis and
 											dsts.tbl_name = 'terima_pakan' and
 											dsts.tgl_trans < @tgl_transaksi
 										group by
 											dss.id
						 		 	) pp_sudah_tercatat
						 		 	on
						 		 		dss.id = pp_sudah_tercatat.id
								 where 
								 	dss.noreg = @noreg and 
								 	dss.jenis_barang = @jenis and
								 	(dss.jml_stok - (isnull(pp_sudah_tercatat.jumlah, 0) - isnull(dsts.jumlah, 0))) > 0
								 order by
								 	dss.tgl_trans asc,
								 	dss.kode_trans asc
								 	
	--							 print @ds_id
	--							 print @jml_pp_pakan
								 	
								 IF ( @jml_pp_pakan > 0 )
								 BEGIN
									 IF ( @jml_pp_pakan <= @jml_stok_pakan )
									 BEGIN
										 insert into #pp_pakan (id_trans, no_sj_asal, kode_barang, jumlah) values
										 (@ds_id, @no_sj_asal, @ds_kode_brg, @jml_pp_pakan)
									 END
									 ELSE
									 BEGIN
										 insert into #pp_pakan (id_trans, no_sj_asal, kode_barang, jumlah) values
										 (@ds_id, @no_sj_asal, @ds_kode_brg, @jml_stok_pakan)
									 END
								 END
								 
	--							 select * from #pp_pakan
								 	
								 IF ( @_dk_jumlah <= @ds_jml_stok )
								 BEGIN							 
									 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
									 values
									 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @_dk_jumlah, @ds_kode_brg, @dk_tbl_name)
									 
									 SET @ds_jml_stok = @ds_jml_stok - @_dk_jumlah
									 SET @_dk_jumlah = 0
									 
									 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
								 END
								 ELSE
								 BEGIN							 
									 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
									 values
									 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @ds_jml_stok, @ds_kode_brg, @dk_tbl_name)
									 
									 SET @_dk_jumlah = @_dk_jumlah - @ds_jml_stok
									 SET @ds_jml_stok = 0
									 
									 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
								 END
							END
							ELSE
							BEGIN
								SET @_dk_jumlah = 0
							END
						END
						END - 2026-03-06 */
							
						/*
						IF ( EXISTS( 
								 select 
									*
								 from det_stok_siklus dss
								 left join
								 	(
								 		select id_header, sum(jumlah) as jumlah from det_stok_trans_siklus where tbl_name <> 'terima_pakan' group by id_header
								 	) dsts
								 	on
								 		dss.id = dsts.id_header
								 left join
								 	(
								 		select dkp.no_sj_asal, dkp.item, sum(dkp.jumlah) as jumlah from det_kirim_pakan dkp 
								 		left join
								 			kirim_pakan kp
								 			on
								 				dkp.id_header = kp.id
								 		where 
								 			dkp.no_sj_asal is not null and
								 			kp.no_order <> @dk_kode_trans
								 		group by dkp.no_sj_asal, dkp.item
								 	) dkp
								 	on
								 		dss.kode_trans = REPLACE(dkp.no_sj_asal, 'SJ', 'OP') and
								 		dss.kode_barang = dkp.item
						 		 left join
						 		 	(
						 		 		select dss.id, sum(dsts.jumlah) as jumlah from det_stok_trans_siklus dsts
						 		 		left join
						 		 			det_stok_siklus dss
						 		 			on
						 		 				dsts.id_header = dss.id
						 		 		where
						 		 			dss.noreg = @noreg and
						 		 			dss.jenis_barang = @jenis and
 											dsts.tbl_name = 'terima_pakan' and
 											dsts.tgl_trans < @tgl_transaksi
 										group by
 											dss.id
						 		 	) pp_sudah_tercatat
						 		 	on
						 		 		dss.id = pp_sudah_tercatat.id
						 		 left join
								 	(
								 		select REPLACE(dkp.no_sj_asal, 'SJ', 'OP') as no_sj_asal, dkp.item, sum(dkp.jumlah) as jumlah from det_kirim_pakan dkp 
								 		left join
								 			kirim_pakan kp
								 			on
								 				dkp.id_header = kp.id
								 		left join
								 			(
								 				select
													dsts.*
												from det_stok_trans_siklus dsts
												left join
													det_stok_siklus dss
													on
														dsts.id_header = dss.id
												where
													dss.noreg = @noreg and
													dss.jenis_barang = 'pakan' and
													dsts.tbl_name = 'terima_pakan'
								 			) dsts
								 			on
								 				dsts.kode_trans = kp.no_order and
								 				dsts.kode_barang = dkp.item
								 		where 
								 			dsts.id is null and
								 			kp.asal = @noreg
								 		group by dkp.no_sj_asal, dkp.item
								 	) pp_belum_tercatat
								 	on
								 		dss.kode_trans = pp_belum_tercatat.no_sj_asal and
								 		dss.kode_barang = pp_belum_tercatat.item
								 where 
								 	dss.noreg = @noreg and 
								 	dss.jenis_barang = @jenis and
								 	(isnull(dss.jumlah, 0) - isnull(pp_sudah_tercatat.jumlah, 0) - isnull(dsts.jumlah, 0) - isnull(pp_belum_tercatat.jumlah, 0)) > 0
							) )
							BEGIN								
								select top 1
								 	@ds_id = cast(dss.id as int),
								 	@no_sj_asal = cast(REPLACE(dss.kode_trans, 'OP', 'SJ') as varchar(50)),
								 	@jml_pp_pakan = cast((isnull(dss.jumlah, 0) - isnull(pp_sudah_tercatat.jumlah, 0) - isnull(dsts.jumlah, 0) - isnull(pp_belum_tercatat.jumlah, 0)) as int),
								 	@jml_stok_pakan = cast((dss.jml_stok - isnull(pp_sudah_tercatat.jumlah, 0) - isnull(dsts.jumlah, 0) - isnull(pp_belum_tercatat.jumlah, 0)) as int),
								 	@ds_jml_stok = cast((isnull(dss.jumlah, 0) - isnull(pp_sudah_tercatat.jumlah, 0) - isnull(dsts.jumlah, 0) - isnull(pp_belum_tercatat.jumlah, 0)) as decimal(13, 2)),
								 	@ds_kode_brg = cast(dss.kode_barang as varchar(10)),
								 	@ds_hrg_jual = cast(dss.hrg_jual as decimal(12, 4)),
								 	@ds_hrg_beli = cast(dss.hrg_beli as decimal(12, 4)),
								 	@ds_oa = cast(dss.oa as decimal(12, 2))
								 from det_stok_siklus dss
								 left join
								 	(
								 		select id_header, sum(jumlah) as jumlah from det_stok_trans_siklus where tbl_name <> 'terima_pakan' group by id_header
								 	) dsts
								 	on
								 		dss.id = dsts.id_header
								 left join
								 	(
								 		select dkp.no_sj_asal, dkp.item, sum(dkp.jumlah) as jumlah from det_kirim_pakan dkp 
								 		left join
								 			kirim_pakan kp
								 			on
								 				dkp.id_header = kp.id
								 		where 
								 			dkp.no_sj_asal is not null and
								 			kp.no_order <> @dk_kode_trans
								 		group by dkp.no_sj_asal, dkp.item
								 	) dkp
								 	on
								 		dss.kode_trans = REPLACE(dkp.no_sj_asal, 'SJ', 'OP') and
								 		dss.kode_barang = dkp.item
						 		 left join
						 		 	(
						 		 		select dss.id, sum(dsts.jumlah) as jumlah from det_stok_trans_siklus dsts
						 		 		left join
						 		 			det_stok_siklus dss
						 		 			on
						 		 				dsts.id_header = dss.id
						 		 		where
						 		 			dss.noreg = @noreg and
						 		 			dss.jenis_barang = @jenis and
 											dsts.tbl_name = 'terima_pakan' and
 											dsts.tgl_trans < @tgl_transaksi
 										group by
 											dss.id
						 		 	) pp_sudah_tercatat
						 		 	on
						 		 		dss.id = pp_sudah_tercatat.id
						 		 left join
								 	(
								 		select REPLACE(dkp.no_sj_asal, 'SJ', 'OP') as no_sj_asal, dkp.item, sum(dkp.jumlah) as jumlah from det_kirim_pakan dkp 
								 		left join
								 			kirim_pakan kp
								 			on
								 				dkp.id_header = kp.id
								 		left join
								 			(
								 				select
													dsts.*
												from det_stok_trans_siklus dsts
												left join
													det_stok_siklus dss
													on
														dsts.id_header = dss.id
												where
													dss.noreg = @noreg and
													dss.jenis_barang = 'pakan' and
													dsts.tbl_name = 'terima_pakan'
								 			) dsts
								 			on
								 				dsts.kode_trans = kp.no_order and
								 				dsts.kode_barang = dkp.item
								 		where 
								 			dsts.id is null and
								 			kp.asal = @noreg
								 		group by dkp.no_sj_asal, dkp.item
								 	) pp_belum_tercatat
								 	on
								 		dss.kode_trans = pp_belum_tercatat.no_sj_asal and
								 		dss.kode_barang = pp_belum_tercatat.item
								 where 
								 	dss.noreg = @noreg and 
								 	dss.jenis_barang = @jenis and
								 	(isnull(dss.jumlah, 0) - isnull(pp_sudah_tercatat.jumlah, 0) - isnull(dsts.jumlah, 0) - isnull(pp_belum_tercatat.jumlah, 0)) > 0
								 order by
								 	dss.tgl_trans asc,
								 	dss.kode_trans asc
								 	
								 IF ( @_dk_jumlah <= @ds_jml_stok )
								 BEGIN							 
									 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
									 values
									 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @_dk_jumlah, @ds_kode_brg, @dk_tbl_name)
									 
									 SET @ds_jml_stok = @ds_jml_stok - @_dk_jumlah
									 SET @_dk_jumlah = 0
									 
									 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
								 END
								 ELSE
								 BEGIN							 
									 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
									 values
									 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @ds_jml_stok, @ds_kode_brg, @dk_tbl_name)
									 
									 SET @_dk_jumlah = @_dk_jumlah - @ds_jml_stok
									 SET @ds_jml_stok = 0
									 
									 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
								 END
							END
							ELSE
							BEGIN
								SET @_dk_jumlah = 0
							END
						END
						ELSE
						BEGIN						
							IF ( EXISTS( select * from det_stok_siklus where noreg = @noreg and tgl_trans <= @tgl_transaksi and jml_stok > 0 and kode_trans = @dk_no_sj_asal and kode_barang = @dk_kode_barang ) )
							BEGIN
								 select top 1
								 	@ds_id = cast(id as int),
								 	@ds_jml_stok = cast(jml_stok as decimal(13, 2)),
								 	@ds_kode_brg = cast(kode_barang as varchar(10)),
								 	@ds_hrg_jual = cast(hrg_jual as decimal(12, 4)),
								 	@ds_hrg_beli = cast(hrg_beli as decimal(12, 4)),
								 	@ds_oa = cast(oa as decimal(12, 2))
								 from det_stok_siklus 
								 where 
								 	noreg = @noreg and 
								 	tgl_trans <= @tgl_transaksi and 
								 	jml_stok > 0 and 
								 	kode_trans = @dk_no_sj_asal and
									kode_barang = @dk_kode_barang
								 order by
								 	tgl_trans asc,
								 	kode_trans asc
								 	
								 IF ( @_dk_jumlah <= @ds_jml_stok )
								 BEGIN							 
									 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
									 values
									 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @_dk_jumlah, @dk_kode_barang, @dk_tbl_name)
									 
	--								 IF ( @dk_no_sj_asal is not null and @dk_no_sj_asal <> '' )
	--								 BEGIN
	----									 IF ( NOT EXISTS ( select * from det_stok_siklus where kode_trans = @dk_kode_trans ) )
	----									 BEGIN
	--										 insert into det_stok_siklus (id_header, tgl_trans, noreg, kode_barang, jumlah, hrg_jual, hrg_beli, oa, kode_trans, jenis_barang, jenis_trans, jml_stok) values
	--										 (@id_header, @dk_tgl_trans, @dk_noreg_tujuan, @dk_kode_barang, @_dk_jumlah, @ds_hrg_jual, @ds_hrg_beli, @ds_oa, @dk_kode_trans, @jenis, 'MUTASI', @_dk_jumlah)
	----									 END
	--								 END
									 
									 SET @ds_jml_stok = @ds_jml_stok - @_dk_jumlah
									 SET @_dk_jumlah = 0
									 
									 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
								 END
								 ELSE
								 BEGIN							 
									 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
									 values
									 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @ds_jml_stok, @dk_kode_barang, @dk_tbl_name)
									 
	--								 IF ( @dk_no_sj_asal is not null and @dk_no_sj_asal <> '' )
	--								 BEGIN
	----									 IF ( NOT EXISTS ( select * from det_stok_siklus where kode_trans = @dk_kode_trans ) )
	----									 BEGIN
	--										 insert into det_stok_siklus (id_header, tgl_trans, noreg, kode_barang, jumlah, hrg_jual, hrg_beli, oa, kode_trans, jenis_barang, jenis_trans, jml_stok) values
	--										 (@id_header, @dk_tgl_trans, @dk_noreg_tujuan, @dk_kode_barang, @ds_jml_stok, @ds_hrg_jual, @ds_hrg_beli, @ds_oa, @dk_kode_trans, @jenis, 'MUTASI', @ds_jml_stok)
	----									 END
	--								 END
									 
									 SET @_dk_jumlah = @_dk_jumlah - @ds_jml_stok
									 SET @ds_jml_stok = 0
									 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
								 END
							END
							ELSE
							BEGIN
								SET @_dk_jumlah = 0
							END
						END
						*/
					END
					
					FETCH NEXT FROM data_keluar INTO
				    	@dk_tgl_trans, @dk_kode_trans, @dk_jumlah, @dk_kode_barang, @dk_no_sj_asal, @dk_tbl_name, @dk_noreg_tujuan
				END
				CLOSE data_keluar
				DEALLOCATE data_keluar
				/* END - DATA KELUAR */
				
				/* DATA KELUAR AFTER MUTASI */
	--			DECLARE data_keluar CURSOR LOCAL FOR
	--				select
	--					dk.tgl_trans,
	--					dk.kode_trans,
	--					(dk.jumlah - dsts.jumlah) as jumlah,
	--					dk.kode_barang,
	--					dk.no_sj_asal,
	--					dk.tbl_name,
	--					dk.noreg_tujuan
	--				from (
	--					select
	--						l.tanggal as tgl_trans,
	--						cast(l.id as varchar(20)) as kode_trans,
	--						(l.pakai_pakan - isnull(l_prev.pakai_pakan, 0)) * 50 as jumlah,
	--						null as kode_barang,
	--						null as no_sj_asal,
	--						'lhk' as tbl_name,
	--						2 as urut,
	--						null as noreg_tujuan
	--					from lhk l
	--					left join
	--						(select top 1 * from lhk where noreg = @noreg and tanggal < @tgl_transaksi and pakai_pakan > 0 order by tanggal desc) l_prev
	--						on
	--							l_prev.noreg = l.noreg
	--					where
	--						l.tanggal = @tgl_transaksi and
	--						l.noreg = @noreg
	--						
	--					union all
	--				
	--					select
	--						tp.tgl_terima as tgl_trans,
	--						kp.no_order as kode_trans,
	--						dkp.jumlah,
	--						dkp.item as kode_barang,
	--						dkp.no_sj_asal,
	--						'terima_pakan' as tbl_name,
	--						1 as urut,
	--						kp.tujuan as noreg_tujuan
	--					from det_kirim_pakan dkp
	--					left join
	--						kirim_pakan kp
	--						on
	--							dkp.id_header = kp.id
	--					left join
	--						terima_pakan tp
	--						on
	--							tp.id_kirim_pakan = kp.id
	--					where
	--						tp.id is not null and
	--						tp.tgl_terima = @tgl_transaksi and
	--						kp.asal = @noreg
	--						
	--					union all
	--					
	--					select
	--						rp.tgl_retur as tgl_trans,
	--						rp.no_retur as kode_trans,
	--						drp.jumlah,
	--						drp.item as kode_barang,
	--						rp.no_order as no_sj_asal,
	--						'retur_pakan' as tbl_name,
	--						3 as urut,
	--						null as noreg_tujuan
	--					from det_retur_pakan drp
	--					left join
	--						retur_pakan rp
	--						on
	--							drp.id_header = rp.id
	--					where
	--						rp.tgl_retur = @tgl_transaksi and
	--						rp.id_asal = @noreg
	--				) dk
	--				left join
	--					(
	--						select
	--							dsts.kode_trans,
	--							dsts.tbl_name,
	--							dsts.kode_barang,
	--							sum(dsts.jumlah) as jumlah
	--						from det_stok_trans_siklus dsts
	--						group by
	--							dsts.kode_trans,
	--							dsts.tbl_name,
	--							dsts.kode_barang
	--					) dsts
	--					on
	--						dsts.kode_trans = dk.kode_trans and
	--						dsts.tbl_name = dk.tbl_name and
	--						dsts.kode_barang = dk.kode_barang
	--				where
	--					dk.jumlah > dsts.jumlah
	--				order by
	--					dk.urut asc
	--					
	--			OPEN data_keluar
	--			
	--			FETCH NEXT FROM data_keluar INTO
	--			    @dk_tgl_trans, @dk_kode_trans, @dk_jumlah, @dk_kode_barang, @dk_no_sj_asal, @dk_tbl_name, @dk_noreg_tujuan
	--			
	--			WHILE @@FETCH_STATUS = 0
	--			BEGIN
	--				SET @dk_no_sj_asal = REPLACE(@dk_no_sj_asal, 'SJ', 'OP')
	--				
	--				SET @_dk_jumlah = @dk_jumlah
	--				
	--				WHILE ( @_dk_jumlah > 0 )
	--				BEGIN					
	--					IF ( @dk_tbl_name = 'lhk' )
	--					BEGIN
	--						IF ( EXISTS(
	--							 select 
	--								*
	--							 from det_stok_siklus dss
	--							 left join
	--							 	(
	--							 		select dkp.no_sj_asal, dkp.item, sum(dkp.jumlah) as jumlah from det_kirim_pakan dkp 
	--							 		left join
	--							 			kirim_pakan kp
	--							 			on
	--							 				dkp.id_header = kp.id
	--							 		where 
	--							 			dkp.no_sj_asal is not null and
	--							 			kp.no_order <> @dk_kode_trans
	--							 		group by dkp.no_sj_asal, dkp.item
	--							 	) dkp
	--							 	on
	--							 		dss.kode_trans = REPLACE(dkp.no_sj_asal, 'SJ', 'OP') and
	--							 		dss.kode_barang = dkp.item
	--							 where 
	--							 	dss.noreg = @noreg and 
	--							 	dss.jenis_barang = @jenis and
	--							 	(dss.jml_stok - isnull(dkp.jumlah, 0)) > 0
	--						) )
	--						BEGIN							
	--							 select top 1
	--							 	@ds_id = cast(dss.id as int),
	--							 	@ds_jml_stok = cast((dss.jml_stok - isnull(dkp.jumlah, 0)) as decimal(13, 2)),
	--							 	@ds_kode_brg = cast(dss.kode_barang as varchar(10)),
	--							 	@ds_hrg_jual = cast(dss.hrg_jual as decimal(12, 4)),
	--							 	@ds_hrg_beli = cast(dss.hrg_beli as decimal(12, 4)),
	--							 	@ds_oa = cast(dss.oa as decimal(12, 2))
	--							 from det_stok_siklus dss
	--							 left join
	--							 	(
	--							 		select dkp.no_sj_asal, dkp.item, sum(dkp.jumlah) as jumlah from det_kirim_pakan dkp 
	--							 		left join
	--							 			kirim_pakan kp
	--							 			on
	--							 				dkp.id_header = kp.id
	--							 		where 
	--							 			dkp.no_sj_asal is not null and
	--							 			kp.no_order <> @dk_kode_trans
	--							 		group by dkp.no_sj_asal, dkp.item
	--							 	) dkp
	--							 	on
	--							 		dss.kode_trans = REPLACE(dkp.no_sj_asal, 'SJ', 'OP') and
	--							 		dss.kode_barang = dkp.item
	--							 where 
	--							 	dss.noreg = @noreg and 
	--							 	dss.jenis_barang = @jenis and
	--							 	(dss.jml_stok - isnull(dkp.jumlah, 0)) > 0
	--							 order by
	--							 	dss.tgl_trans asc,
	--							 	dss.kode_trans asc
	--							 	
	--							 IF ( @_dk_jumlah <= @ds_jml_stok )
	--							 BEGIN							 
	--								 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
	--								 values
	--								 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @_dk_jumlah, @ds_kode_brg, @dk_tbl_name)
	--								 
	--								 SET @ds_jml_stok = @ds_jml_stok - @_dk_jumlah
	--								 SET @_dk_jumlah = 0
	--								 
	--								 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
	--							 END
	--							 ELSE
	--							 BEGIN							 
	--								 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
	--								 values
	--								 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @ds_jml_stok, @ds_kode_brg, @dk_tbl_name)
	--								 
	--								 SET @_dk_jumlah = @_dk_jumlah - @ds_jml_stok
	--								 SET @ds_jml_stok = 0
	--								 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
	--							 END
	--						END
	--						ELSE
	--						BEGIN
	--							SET @_dk_jumlah = 0
	--						END
	--					END
	--					ELSE
	--					BEGIN						
	--						IF ( EXISTS( select * from det_stok_siklus where noreg = @noreg and tgl_trans <= @tgl_transaksi and jml_stok > 0 and kode_trans = @dk_no_sj_asal and kode_barang = @dk_kode_barang ) )
	--						BEGIN
	--							 select top 1
	--							 	@ds_id = cast(id as int),
	--							 	@ds_jml_stok = cast(jml_stok as decimal(13, 2)),
	--							 	@ds_hrg_jual = cast(hrg_jual as decimal(12, 4)),
	--							 	@ds_hrg_beli = cast(hrg_beli as decimal(12, 4)),
	--							 	@ds_oa = cast(oa as decimal(12, 2))
	--							 from det_stok_siklus 
	--							 where 
	--							 	noreg = @noreg and 
	--							 	tgl_trans <= @tgl_transaksi and 
	--							 	jml_stok > 0 and 
	--							 	kode_trans = @dk_no_sj_asal and
	--								kode_barang = @dk_kode_barang
	--							 order by
	--							 	tgl_trans asc,
	--							 	kode_trans asc
	--							 	
	--							 IF ( @_dk_jumlah <= @ds_jml_stok )
	--							 BEGIN							 
	--								 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
	--								 values
	--								 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @_dk_jumlah, @dk_kode_barang, @dk_tbl_name)
	--								 
	----								 IF ( @dk_no_sj_asal is not null and @dk_no_sj_asal <> '' )
	----								 BEGIN
	------									 IF ( NOT EXISTS ( select * from det_stok_siklus where kode_trans = @dk_kode_trans ) )
	------									 BEGIN
	----										 insert into det_stok_siklus (id_header, tgl_trans, noreg, kode_barang, jumlah, hrg_jual, hrg_beli, oa, kode_trans, jenis_barang, jenis_trans, jml_stok) values
	----										 (@id_header, @dk_tgl_trans, @dk_noreg_tujuan, @dk_kode_barang, @dk_jumlah, @ds_hrg_jual, @ds_hrg_beli, @ds_oa, @dk_kode_trans, @jenis, 'MUTASI', @dk_jumlah)
	------									 END
	----								 END
	--								 
	--								 SET @ds_jml_stok = @ds_jml_stok - @_dk_jumlah
	--								 SET @_dk_jumlah = 0
	--								 
	--								 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
	--							 END
	--							 ELSE
	--							 BEGIN							 
	--								 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
	--								 values
	--								 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @ds_jml_stok, @dk_kode_barang, @dk_tbl_name)
	--								 
	----								 IF ( @dk_no_sj_asal is not null and @dk_no_sj_asal <> '' )
	----								 BEGIN
	------									 IF ( NOT EXISTS ( select * from det_stok_siklus where kode_trans = @dk_kode_trans ) )
	------									 BEGIN
	----										 insert into det_stok_siklus (id_header, tgl_trans, noreg, kode_barang, jumlah, hrg_jual, hrg_beli, oa, kode_trans, jenis_barang, jenis_trans, jml_stok) values
	----										 (@id_header, @dk_tgl_trans, @dk_noreg_tujuan, @dk_kode_barang, @ds_jml_stok, @ds_hrg_jual, @ds_hrg_beli, @ds_oa, @dk_kode_trans, @jenis, 'MUTASI', @ds_jml_stok)
	------									 END
	----								 END
	--								 
	--								 SET @_dk_jumlah = @_dk_jumlah - @ds_jml_stok
	--								 SET @ds_jml_stok = 0
	--								 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
	--							 END
	--						END
	--						ELSE
	--						BEGIN
	--							SET @_dk_jumlah = 0
	--						END
	--					END
	--				END
	--				
	--				FETCH NEXT FROM data_keluar INTO
	--			    	@dk_tgl_trans, @dk_kode_trans, @dk_jumlah, @dk_kode_barang, @dk_no_sj_asal, @dk_tbl_name, @dk_noreg_tujuan
	--			END
	--			CLOSE data_keluar
	--			DEALLOCATE data_keluar
				/* END - DATA KELUAR AFTER MUTASI */
			END
			
			SET @tgl_transaksi = DATEADD(DD, 1, @tgl_transaksi)
		END
		
		FETCH NEXT FROM noreg INTO
	    	@noreg
	END
	CLOSE noreg
	DEALLOCATE noreg
	
	IF ( @return = 1 )
	BEGIN
		select 'berhasil hitung pakan'
	END
END

IF ( @jenis like '%voadip%' )
BEGIN
	delete from #lnoreg
	
	IF ( @tbl_name = 'terima_voadip' )
	BEGIN
		IF ( EXISTS(
				select 
					tv.* 
				from terima_voadip tv
				left join
					kirim_voadip kv
					on
						tv.id_kirim_voadip = kv.id
				where 
					tv.id = @tbl_id and
					kv.jenis_tujuan = 'peternak'
			) and 
			@noreg1 is null and 
			@noreg2 is null
		)
		BEGIN
			select
				@noreg1 = cast(data.noreg1 as varchar(15)),
				@noreg2 = cast(data.noreg2 as varchar(15))
			from
			(
				select
					case
						when kv.jenis_kirim = 'opkp' then
							kv.asal
						else
							kv.tujuan
					end as noreg1,
					case
						when kv.jenis_kirim = 'opkp' then
							kv.tujuan
						else
							null
					end as noreg2
				from terima_voadip tv
				left join
					kirim_voadip kv
					on
						tv.id_kirim_voadip = kv.id
				where
					tv.id = @tbl_id
			) data
		END
		
		insert into #lnoreg (urut, noreg)
		values
		(1, @noreg1),
		(2, @noreg2)
	END
	
	IF ( @tbl_name = 'retur_voadip' )
	BEGIN
		IF ( EXISTS(
			select rv.* from retur_voadip rv where rv.id = @tbl_id
		) and @noreg1 is null and @noreg2 is null )
		BEGIN
			select
				@noreg1 = cast(data.noreg1 as varchar(15)),
				@noreg2 = cast(data.noreg2 as varchar(15))
			from
			(
				select
					rv.id_asal as noreg1,
					null as noreg2
				from retur_voadip rv
				where
					rv.id = @tbl_id
			) data
		END
		
		insert into #lnoreg (urut, noreg)
		values
		(1, @noreg1),
		(2, @noreg2)
	END
	
	DECLARE noreg CURSOR LOCAL FOR
		select noreg from #lnoreg where noreg is not null order by urut asc
			
	OPEN noreg
	
	FETCH NEXT FROM noreg INTO
	    @noreg
	
	WHILE @@FETCH_STATUS = 0
	BEGIN
		SET @tgl_transaksi = @_tgl_transaksi
		
		WHILE ( @tgl_transaksi <= @today )
		BEGIN
			IF ( NOT EXISTS ( select  * from stok where periode = @tgl_transaksi ) )
			BEGIN
				insert into stok (periode, tgl_proses) values
				(@tgl_transaksi, getdate())
			END
			
			select 
				@id_header = cast(id as int) 
			from stok where periode = @tgl_transaksi
			order by id desc
			
			/*
			update dss
			set
				dss.jml_stok = dss.jml_stok + dtrans.jumlah
			from det_stok_siklus dss
			right join
				(
					select 
						dsts.id_header,
						sum(dsts.jumlah) as jumlah
					from det_stok_trans_siklus dsts
					left join
						det_stok_siklus dss
						on
							dsts.id_header = dss.id
					where
						dsts.tgl_trans = @tgl_transaksi and
						dss.noreg = @noreg and
						dss.jenis_barang = @jenis
					group by
						dsts.id_header
				) dtrans
				on
					dss.id = dtrans.id_header
			*/
			
			delete det_stok_trans_siklus where id in (			
				select 
					dsts.id
				from det_stok_trans_siklus dsts
				left join
					det_stok_siklus dss
					on
						dsts.id_header = dss.id
				where
					dsts.tgl_trans >= @tgl_transaksi and
					dss.noreg = @noreg and
					dss.jenis_barang = @jenis
				group by
					dsts.id
			)
			
			update dss
			set
				dss.jml_stok = case 
					when isnull(dtrans.jumlah, 0) > dss.jumlah then
						dss.jumlah
					else
						(dss.jumlah - isnull(dtrans.jumlah, 0))
				end
			from det_stok_siklus dss
			left join
				(
					select dsts.id_header, sum(dsts.jumlah) as jumlah from det_stok_trans_siklus dsts
					left join
						det_stok_siklus dss
						on
							dsts.id_header = dss.id
					where
						dsts.tgl_trans < @tgl_transaksi and
						dss.noreg = @noreg and
						dss.jenis_barang = @jenis
					group by
						dsts.id_header
				) dtrans
				on
					dss.id = dtrans.id_header
			where
				dss.tgl_trans < @tgl_transaksi and
				dss.noreg = @noreg and
				dss.jenis_barang = @jenis
			
--			delete from det_stok_trans_siklus where id_header in (select id from det_stok_siklus where id_header = @id_header and noreg = @noreg and jenis_barang = @jenis)
			delete from det_stok_siklus where id_header = @id_header and noreg = @noreg and jenis_barang = @jenis
			
			/* DATA MASUK */
			insert into det_stok_siklus (id_header, tgl_trans, noreg, kode_barang, jumlah, hrg_jual, hrg_beli, oa, kode_trans, jenis_barang, jenis_trans, jml_stok)
			select * from
			(
				select
					@id_header as id_header,
					tv.tgl_terima as tgl_trans,
					kv.tujuan as noreg,
					dtv.item as kode_barang,
					sum(dst.jumlah) as jumlah,
					ds.hrg_jual,
					ds.hrg_beli,
					kv.ongkos_angkut as oa,
					kv.no_order as kode_trans,
					'voadip' as jenis_barang,
					'ORDER' as jenis_trans,
					sum(dst.jumlah) as jml_stok
				from det_terima_voadip dtv
				left join
					terima_voadip tv
					on
						dtv.id_header = tv.id
				left join
					kirim_voadip kv
					on
						tv.id_kirim_voadip = kv.id
				left join
					det_stok_trans dst
					on
						dst.kode_trans = kv.no_order and
						dst.kode_barang = dtv.item
				left join
					det_stok ds
					on
						dst.id_header = ds.id
				where
					tv.tgl_terima = @tgl_transaksi and
					kv.tujuan = @noreg and
					kv.jenis_kirim = 'opkg'
				group by
					tv.tgl_terima,
					kv.tujuan,
					dtv.item,
					ds.hrg_jual,
					ds.hrg_beli,
					kv.ongkos_angkut,
					kv.no_order
					
--				union all
--				
--				select
--					@id_header as id_header,
--					tv.tgl_terima as tgl_trans,
--					kv.tujuan as noreg,
--					dtv.item as kode_barang,
--					dst.jumlah,
--					ds.hrg_jual,
--					ds.hrg_beli,
--					ds.oa,
--					kv.no_order as kode_trans,
--					'voadip' as jenis_barang,
--					'MUTASI' as jenis_trans,
--					dst.jumlah as jml_stok
--				from det_terima_voadip dtv
--				left join
--					terima_voadip tv
--					on
--						dtv.id_header = tv.id
--				left join
--					kirim_voadip kv
--					on
--						tv.id_kirim_voadip = kv.id
--				left join
--					det_stok_trans_siklus dst
--					on
--						dst.kode_trans = kv.no_order and
--						dst.kode_barang = dtv.item
--				left join
--					det_stok_siklus ds
--					on
--						dst.id_header = ds.id
--				where
--					tv.tgl_terima = @tgl_transaksi and
--					kv.tujuan = @noreg and
--					kv.jenis_kirim = 'opkp'
			) dm
			/* END - DATA MASUK */
			
			/* DATA KELUAR */
			DECLARE data_keluar CURSOR LOCAL FOR
				select
					dk.tgl_trans,
					dk.kode_trans,
					dk.jumlah,
					dk.kode_barang,
					dk.no_sj_asal,
					dk.tbl_name
				from (					
					select
						rv.tgl_retur as tgl_trans,
						rv.no_retur as kode_trans,
						drv.jumlah,
						drv.item as kode_barang,
						rv.no_order as no_sj_asal,
						'retur_voadip' as tbl_name,
						2 as urut
					from det_retur_voadip drv
					left join
						retur_voadip rv
						on
							drv.id_header = rv.id
					where
						rv.tgl_retur = @tgl_transaksi and
						rv.id_asal = @noreg
				) dk
				order by
					dk.urut asc
					
			OPEN data_keluar
			
			FETCH NEXT FROM data_keluar INTO
			    @dk_tgl_trans, @dk_kode_trans, @dk_jumlah, @dk_kode_barang, @dk_no_sj_asal, @dk_tbl_name
			
			WHILE @@FETCH_STATUS = 0
			BEGIN
				IF ( @dk_tbl_name = 'retur_voadip' )
				BEGIN
					DECLARE d_retur CURSOR LOCAL FOR
						select
							ds.jumlah,
							ds.hrg_beli
						from det_stok ds
						left join
							stok s
							on
								ds.id_header = s.id
						where
							ds.kode_trans = @dk_no_sj_asal and
							ds.kode_barang = @dk_kode_barang and
							ds.tgl_trans = s.periode
							
					OPEN d_retur
					
					FETCH NEXT FROM d_retur INTO
					    @rv_jumlah, @rv_hrg_beli
					
					WHILE @@FETCH_STATUS = 0
					BEGIN
						select top 1
						 	@ds_id = cast(id as int),
						 	@ds_jml_stok = cast(jml_stok as decimal(13, 2))
						from det_stok_siklus 
						where 
						 	noreg = @noreg and 
						 	tgl_trans <= @tgl_transaksi and 
						 	kode_trans = @dk_no_sj_asal and
							kode_barang = @dk_kode_barang and
							hrg_beli = @rv_hrg_beli
						order by
						 	tgl_trans asc,
						 	kode_trans asc
						
						insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
						values
						(@ds_id, @dk_tgl_trans, @dk_kode_trans, @rv_jumlah, @dk_kode_barang, @dk_tbl_name)
						
						
						SET @ds_jml_stok = @ds_jml_stok - @rv_jumlah
						update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
						
						FETCH NEXT FROM d_retur INTO
			    			@rv_jumlah, @rv_hrg_beli
					END
					CLOSE d_retur
					DEALLOCATE d_retur
				END
				
				/*
				SET @dk_no_sj_asal = REPLACE(@dk_no_sj_asal, 'SJ', 'OP')
				
				WHILE ( @dk_jumlah > 0 )
				BEGIN					
					IF ( EXISTS( select * from det_stok_siklus where noreg = @noreg and tgl_trans <= @tgl_transaksi and jml_stok > 0 and kode_trans = @dk_no_sj_asal and kode_barang = @dk_kode_barang ) )
					BEGIN
						 select top 1
						 	@ds_id = cast(id as int),
						 	@ds_jml_stok = cast(jml_stok as decimal(13, 2))
						 from det_stok_siklus 
						 where 
						 	noreg = @noreg and 
						 	tgl_trans <= @tgl_transaksi and 
						 	jml_stok > 0 and 
						 	kode_trans = @dk_no_sj_asal and
							kode_barang = @dk_kode_barang
						 order by
						 	tgl_trans asc,
						 	kode_trans asc
						 	
						 IF ( @dk_jumlah <= @ds_jml_stok )
						 BEGIN							 
							 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
							 values
							 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @dk_jumlah, @dk_kode_barang, @dk_tbl_name)
							 
							 SET @ds_jml_stok = @ds_jml_stok - @dk_jumlah
							 SET @dk_jumlah = 0
							 
							 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
						 END
						 ELSE
						 BEGIN							 
							 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
							 values
							 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @ds_jml_stok, @dk_kode_barang, @dk_tbl_name)
							 
							 SET @dk_jumlah = @dk_jumlah - @ds_jml_stok
							 SET @ds_jml_stok = 0
							 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
						 END
					END
					ELSE
					BEGIN
						SET @dk_jumlah = 0
					END
				END
				*/
				
				FETCH NEXT FROM data_keluar INTO
			    	@dk_tgl_trans, @dk_kode_trans, @dk_jumlah, @dk_kode_barang, @dk_no_sj_asal, @dk_tbl_name
			END
			CLOSE data_keluar
			DEALLOCATE data_keluar
			/* END - DATA KELUAR */
			
			SET @tgl_transaksi = DATEADD(DD, 1, @tgl_transaksi)
		END
		
		FETCH NEXT FROM noreg INTO
	    	@noreg
	END
	CLOSE noreg
	DEALLOCATE noreg
	
	IF ( @return = 1 )
	BEGIN
		select 'berhasil hitung voadip'
	END
END

/* 2025 */
/*
SET NOCOUNT ON 
SET ANSI_WARNINGS OFF

create table #lnoreg (
	urut int,
	noreg varchar(15) COLLATE DATABASE_DEFAULT
)

create table #pp_pakan (
	id_trans int,
	no_sj_asal varchar(50) COLLATE DATABASE_DEFAULT,
	kode_barang varchar(20) COLLATE DATABASE_DEFAULT,
	jumlah int
)

DECLARE @noreg varchar(15)
DECLARE @id_header int

DECLARE @tgl_asal date

DECLARE @dk_tgl_trans date, @dk_kode_trans varchar(20), @dk_jumlah decimal(10, 2), @dk_kode_barang varchar(10), @dk_no_sj_asal varchar(20), @dk_no_order_asal varchar(20), @dk_tbl_name varchar(20)

DECLARE @ds_id int
DECLARE @ds_jml_stok decimal(13, 2)
DECLARE @ds_kode_brg varchar(10)
DECLARE @ds_hrg_jual decimal(12, 4), @ds_hrg_beli decimal(12, 4), @ds_oa decimal(12, 2)
DECLARE @ds_jml_stok_pindah decimal(13, 2)
--DECLARE @ds_hrg_beli decimal(12, 4)
--DECLARE @ds_hrg_jual decimal(12, 4)

DECLARE @_dk_jumlah decimal(10, 2)

DECLARE @dk_noreg_tujuan varchar(20)

DECLARE @jml_pp_pakan int
DECLARE @jml_stok_pakan int
DECLARE @no_sj_asal varchar(50)

DECLARE @today date, @tgl_transaksi date, @start_date varchar(25), @end_date varchar(25)
IF ( @_today is null )
BEGIN
	SET @today = GETDATE()
END
ELSE
BEGIN
	SET @today = @_today
END

IF ( @jenis like '%doc%' )
BEGIN
	delete from #lnoreg
	
	IF ( @tbl_name = 'lhk' )
	BEGIN
		IF ( EXISTS(
			select l.* from lhk l where l.id = @tbl_id
		) )
		BEGIN
			select
				@noreg1 = cast(data.noreg1 as varchar(15)),
				@noreg2 = cast(data.noreg2 as varchar(15))
			from
			(
				select
					l.noreg as noreg1,
					null as noreg2
				from lhk l
				where
					l.id = @tbl_id
			) data
		END
		
		insert into #lnoreg (urut, noreg)
		values
		(1, @noreg1),
		(2, @noreg2)
	END
	
	IF ( @tbl_name = 'terima_doc' )
	BEGIN		
		IF ( EXISTS(
			select td.* from terima_doc td where td.id = @tbl_id
		) )
		BEGIN
			select
				@noreg1 = cast(data.noreg1 as varchar(15)),
				@noreg2 = cast(data.noreg2 as varchar(15))
			from
			(
				select
					od.noreg as noreg1,
					null as noreg2
				from terima_doc td
				left join
					(
						select od1.* from order_doc od1
						right join
							(select max(id) as id, no_order from order_doc group by no_order) od2
							on
								od1.id = od2.id
					) od
					on
						td.no_order = od.no_order
				where
					td.id = @tbl_id
			) data
		END
		
		insert into #lnoreg (urut, noreg)
		values
		(1, @noreg1),
		(2, @noreg2)
	END
	
	DECLARE noreg CURSOR LOCAL FOR
		select noreg from #lnoreg where noreg is not null order by urut asc
			
	OPEN noreg
	
	FETCH NEXT FROM noreg INTO
	    @noreg
	
	WHILE @@FETCH_STATUS = 0
	BEGIN
		SET @tgl_transaksi = @_tgl_transaksi
		
		WHILE ( @tgl_transaksi <= @today )
		BEGIN
			select 
				@id_header = cast(id as int) 
			from stok where periode = @tgl_transaksi
			
			/*
			update dss
			set
				dss.jml_stok = dss.jml_stok + dtrans.jumlah
			from det_stok_siklus dss
			right join
				(
					select 
						dsts.id_header,
						sum(dsts.jumlah) as jumlah
					from det_stok_trans_siklus dsts
					left join
						det_stok_siklus dss
						on
							dsts.id_header = dss.id
					where
						dsts.tgl_trans = @tgl_transaksi and
						dss.noreg = @noreg and
						dss.jenis_barang = @jenis
					group by
						dsts.id_header
				) dtrans
				on
					dss.id = dtrans.id_header
			*/
			update dss
			set
				dss.jml_stok = (dss.jumlah - isnull(dtrans.jumlah, 0))
	--		select
	--			(dss.jumlah - isnull(dtrans.jumlah, 0)) as jml_stok
			from det_stok_siklus dss
			left join
				(
					select dsts.id_header, sum(dsts.jumlah) as jumlah from det_stok_trans_siklus dsts
					left join
						det_stok_siklus dss
						on
							dsts.id_header = dss.id
					where
						dsts.tgl_trans < @tgl_transaksi and
						dss.noreg = @noreg and
						dss.jenis_barang = @jenis
					group by
						dsts.id_header
				) dtrans
				on
					dss.id = dtrans.id_header
			where
				dss.tgl_trans < @tgl_transaksi and
				dss.noreg = @noreg and
				dss.jenis_barang = @jenis
					
			delete det_stok_trans_siklus where id in (
				select 
					dsts.id
				from det_stok_trans_siklus dsts
				left join
					det_stok_siklus dss
					on
						dsts.id_header = dss.id
				where
					dsts.tgl_trans = @tgl_transaksi and
					dss.noreg = @noreg and
					dss.jenis_barang = @jenis
				group by
					dsts.id
			)
			
--			delete from det_stok_trans_siklus where id_header in (select id from det_stok_siklus where id_header = @id_header and noreg = @noreg and jenis_barang = @jenis)
			delete from det_stok_siklus where id_header = @id_header and noreg = @noreg and jenis_barang = @jenis
			
			/* DATA MASUK */
			SET @start_date = cast(@tgl_transaksi as varchar(10))+' 00:00:00.001'
			SET @end_date = cast(@tgl_transaksi as varchar(10))+' 23:59:59:999'
			
			insert into det_stok_siklus (id_header, tgl_trans, noreg, kode_barang, jumlah, hrg_jual, hrg_beli, oa, kode_trans, jenis_barang, jenis_trans, jml_stok)
			select * from
			(
				select
					@id_header as id_header,
					td.datang as tgl_trans,
					od.noreg as noreg,
					od.item as kode_barang,
					td.jml_ekor as jumlah,
					td.harga as hrg_jual,
					td.harga as hrg_beli,
					0 as oa,
					od.no_order as kode_trans,
					'doc' as jenis_barang,
					'ORDER' as jenis_trans,
					td.jml_ekor as jml_stok
				from (
					select td1.* from terima_doc td1
					right join
						(select max(id) as id, no_order from terima_doc group by no_order) td2
						on
							td1.id = td2.id
				) td
				left join
					(
						select od1.* from order_doc od1
						right join
							(select max(id) as id, no_order from order_doc group by no_order) od2
							on
								od1.id = od2.id
					) od
					on
						od.no_order = td.no_order
				where
					td.datang between @start_date and @end_date and
					od.noreg = @noreg
			) dm
			/* END - DATA MASUK */
			
			/* DATA KELUAR */
			DECLARE data_keluar CURSOR LOCAL FOR
				select
					dk.tgl_trans,
					dk.kode_trans,
					dk.jumlah,
					dk.kode_barang,
					dk.no_sj_asal,
					dk.tbl_name
				from (
					select
						l.tanggal as tgl_trans,
						cast(l.id as varchar(20)) as kode_trans,
						(l.ekor_mati - isnull(l_prev.ekor_mati, 0)) as jumlah,
						null as kode_barang,
						null as no_sj_asal,
						'lhk' as tbl_name,
						1 as urut
					from lhk l
					left join
						(select top 1 * from lhk where noreg = @noreg and tanggal < @tgl_transaksi and ekor_mati > 0 order by tanggal desc) l_prev
						on
							l_prev.noreg = l.noreg and
							l_prev.umur = l.umur
					where
						l.tanggal = @tgl_transaksi and
						l.noreg = @noreg
				) dk
				order by
					dk.urut asc
					
			OPEN data_keluar
			
			FETCH NEXT FROM data_keluar INTO
			    @dk_tgl_trans, @dk_kode_trans, @dk_jumlah, @dk_kode_barang, @dk_no_sj_asal, @dk_tbl_name
			
			WHILE @@FETCH_STATUS = 0
			BEGIN
				SET @dk_no_sj_asal = REPLACE(@dk_no_sj_asal, 'SJ', 'OP') 
				
				WHILE ( @dk_jumlah > 0 )
				BEGIN
					IF ( @tbl_name = 'lhk' )
					BEGIN
						IF ( EXISTS( select * from det_stok_siklus where noreg = @noreg and tgl_trans <= @tgl_transaksi and jenis_barang = @jenis and jml_stok > 0 ) )
						BEGIN
							 select top 1
							 	@ds_id = cast(id as int),
							 	@ds_jml_stok = cast(jml_stok as decimal(13, 2)),
							 	@ds_kode_brg = cast(kode_barang as varchar(10))
							 from det_stok_siklus 
							 where 
							 	noreg = @noreg and 
							 	tgl_trans <= @tgl_transaksi and 
							 	jenis_barang = @jenis and
							 	jml_stok > 0
							 order by
							 	tgl_trans asc,
							 	kode_trans asc
							 	
							 IF ( @dk_jumlah <= @ds_jml_stok )
							 BEGIN							 
								 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
								 values
								 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @dk_jumlah, @ds_kode_brg, @dk_tbl_name)
								 
								 SET @ds_jml_stok = @ds_jml_stok - @dk_jumlah
								 SET @dk_jumlah = 0
								 
								 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
							 END
							 ELSE
							 BEGIN							 
								 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
								 values
								 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @ds_jml_stok, @ds_kode_brg, @dk_tbl_name)
								 
								 SET @dk_jumlah = @dk_jumlah - @ds_jml_stok
								 SET @ds_jml_stok = 0
								 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
							 END
						END
						ELSE
						BEGIN
							SET @dk_jumlah = 0
						END
					END
					ELSE
					BEGIN
						IF ( EXISTS( select * from det_stok_siklus where noreg = @noreg and tgl_trans <= @tgl_transaksi and jml_stok > 0 and kode_trans = @dk_no_sj_asal and kode_barang = @dk_kode_barang ) )
						BEGIN
							 select top 1
							 	@ds_id = cast(id as int),
							 	@ds_jml_stok = cast(jml_stok as decimal(13, 2))
							 from det_stok_siklus 
							 where 
							 	noreg = @noreg and 
							 	tgl_trans <= @tgl_transaksi and 
							 	jml_stok > 0 and 
							 	kode_trans = @dk_no_sj_asal and
								kode_barang = @dk_kode_barang
							 order by
							 	tgl_trans asc,
							 	kode_trans asc
							 	
							 IF ( @dk_jumlah <= @ds_jml_stok )
							 BEGIN							 
								 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
								 values
								 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @dk_jumlah, @dk_kode_barang, @dk_tbl_name)
								 
								 SET @ds_jml_stok = @ds_jml_stok - @dk_jumlah
								 SET @dk_jumlah = 0
								 
								 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
							 END
							 ELSE
							 BEGIN							 
								 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
								 values
								 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @ds_jml_stok, @dk_kode_barang, @dk_tbl_name)
								 
								 SET @dk_jumlah = @dk_jumlah - @ds_jml_stok
								 SET @ds_jml_stok = 0
								 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
							 END
						END
						ELSE
						BEGIN
							SET @dk_jumlah = 0
						END
					END
				END
				
				FETCH NEXT FROM data_keluar INTO
			    	@dk_tgl_trans, @dk_kode_trans, @dk_jumlah, @dk_kode_barang, @dk_no_sj_asal, @dk_tbl_name
			END
			CLOSE data_keluar
			DEALLOCATE data_keluar
			/* END - DATA KELUAR */
			
			SET @tgl_transaksi = DATEADD(DD, 1, @tgl_transaksi)
		END
		
		FETCH NEXT FROM noreg INTO
	    	@noreg
	END
	CLOSE noreg
	DEALLOCATE noreg
	
	IF ( @return = 1 )
	BEGIN
		select 'berhasil hitung doc'
	END
END

IF ( @jenis like '%pakan%' )
BEGIN
	delete from #lnoreg
	
	IF ( @tbl_name = 'lhk' )
	BEGIN
		IF ( EXISTS(
			select l.* from lhk l where l.id = @tbl_id
		) and @noreg1 is null and @noreg2 is null )
		BEGIN
			select
				@noreg1 = cast(data.noreg1 as varchar(15)),
				@noreg2 = cast(data.noreg2 as varchar(15))
			from
			(
				select
					l.noreg as noreg1,
					null as noreg2
				from lhk l
				where
					l.id = @tbl_id
			) data
		END
		
		insert into #lnoreg (urut, noreg)
		values
		(1, @noreg1),
		(2, @noreg2)
	END
	
	IF ( @tbl_name = 'terima_pakan' )
	BEGIN		
		IF ( EXISTS(
			select tp.* from terima_pakan tp where tp.id = @tbl_id
		) and @noreg1 is null and @noreg2 is null )
		BEGIN			
			select
				@noreg1 = cast(data.noreg1 as varchar(15)),
				@noreg2 = cast(data.noreg2 as varchar(15))
			from
			(
				select
					case
						when kp.jenis_kirim = 'opkp' then
							kp.asal
						else
							kp.tujuan
					end as noreg1,
					case
						when kp.jenis_kirim = 'opkp' then
							kp.tujuan
						else
							null
					end as noreg2
				from terima_pakan tp
				left join
					kirim_pakan kp
					on
						tp.id_kirim_pakan = kp.id
				where
					tp.id = @tbl_id
			) data
			
			select
				@tgl_asal = cast(min(kp_asal.tgl_kirim) as date)
			from terima_pakan tp
			left join
				det_kirim_pakan dkp
				on
					dkp.id_header = tp.id_kirim_pakan
			left join
				kirim_pakan kp_asal
				on
					dkp.no_sj_asal = kp_asal.no_sj
			where	
				tp.id = @tbl_id
			group by
				tp.id
				
			IF ( @tgl_asal is not null )
			BEGIN
				IF ( @tgl_asal < @_tgl_transaksi )
				BEGIN
					SET @_tgl_transaksi = @tgl_asal
				END
			END
		END
		
		insert into #lnoreg (urut, noreg)
		values
		(1, @noreg1),
		(2, @noreg2)
	END
	
	IF ( @tbl_name = 'retur_pakan' )
	BEGIN
		IF ( EXISTS(
			select rp.* from retur_pakan rp where rp.id = @tbl_id
		) and @noreg1 is null and @noreg2 is null )
		BEGIN
			select
				@noreg1 = cast(data.noreg1 as varchar(15)),
				@noreg2 = cast(data.noreg2 as varchar(15))
			from
			(
				select
					rp.id_asal as noreg1,
					null as noreg2
				from retur_pakan rp
				where
					rp.id = @tbl_id
			) data
		END
		
		insert into #lnoreg (urut, noreg)
		values
		(1, @noreg1),
		(2, @noreg2)
	END
	
	DECLARE noreg CURSOR LOCAL FOR
		select noreg from #lnoreg where noreg is not null order by urut asc
			
	OPEN noreg
	
	FETCH NEXT FROM noreg INTO
	    @noreg
	
	WHILE @@FETCH_STATUS = 0
	BEGIN
		SET @tgl_transaksi = @_tgl_transaksi
		
		WHILE ( @tgl_transaksi <= @today )
		BEGIN
			select 
				@id_header = cast(id as int) 
			from stok where periode = @tgl_transaksi
			
			delete det_stok_trans_siklus where id in (			
				select 
					dsts.id
				from det_stok_trans_siklus dsts
				left join
					det_stok_siklus dss
					on
						dsts.id_header = dss.id
				where
					dsts.tgl_trans >= @tgl_transaksi and
					dss.noreg = @noreg and
					dss.jenis_barang = @jenis
				group by
					dsts.id
			)
			
			update dss
			set
				dss.jml_stok = case 
					when isnull(dtrans.jumlah, 0) > dss.jumlah then
						dss.jumlah
					else
						(dss.jumlah - isnull(dtrans.jumlah, 0))
				end
			from det_stok_siklus dss
			left join
				(
					select dsts.id_header, sum(dsts.jumlah) as jumlah from det_stok_trans_siklus dsts
					left join
						det_stok_siklus dss
						on
							dsts.id_header = dss.id
					where
						dsts.tgl_trans < @tgl_transaksi and
						dss.noreg = @noreg and
						dss.jenis_barang = @jenis
					group by
						dsts.id_header
				) dtrans
				on
					dss.id = dtrans.id_header
			where
				dss.tgl_trans < @tgl_transaksi and
				dss.noreg = @noreg and
				dss.jenis_barang = @jenis
				
			delete from det_stok_siklus where id_header = @id_header and noreg = @noreg and jenis_barang = @jenis
			
			/* DATA MASUK */
			insert into det_stok_siklus (id_header, tgl_trans, noreg, kode_barang, jumlah, hrg_jual, hrg_beli, oa, kode_trans, jenis_barang, jenis_trans, jml_stok)
			select * from
			(
				select
					@id_header as id_header,
					tp.tgl_terima as tgl_trans,
					kp.tujuan as noreg,
					dtp.item as kode_barang,
					dst.jumlah,
					ds.hrg_jual,
					ds.hrg_beli,
					kp.ongkos_angkut as oa,
					kp.no_order as kode_trans,
					'pakan' as jenis_barang,
					'ORDER' as jenis_trans,
					dst.jumlah as jml_stok
				from det_terima_pakan dtp
				left join
					terima_pakan tp
					on
						dtp.id_header = tp.id
				left join
					kirim_pakan kp
					on
						tp.id_kirim_pakan = kp.id
				left join
					det_stok_trans dst
					on
						dst.kode_trans = kp.no_order and
						dst.kode_barang = dtp.item
				left join
					det_stok ds
					on
						dst.id_header = ds.id
				where
					tp.tgl_terima = @tgl_transaksi and
					kp.tujuan = @noreg and
					kp.jenis_kirim = 'opkg'
					
				union all
				
				select
					@id_header as id_header,
					tp.tgl_terima as tgl_trans,
					kp.tujuan as noreg,
					dtp.item as kode_barang,
					dsts.jumlah,
					dss.hrg_jual,
					dss.hrg_beli,
					dss.oa,
					kp.no_order as kode_trans,
					'pakan' as jenis_barang,
					'MUTASI' as jenis_trans,
					dsts.jumlah as jml_stok
				from det_terima_pakan dtp 
				left join
					terima_pakan tp
					on
						dtp.id_header = tp.id
				left join
					kirim_pakan kp
					on
						tp.id_kirim_pakan = kp.id
				left join
					det_stok_trans_siklus dsts
					on
						dsts.kode_trans = kp.no_order and
						dsts.kode_barang = dtp.item
				left join
					det_stok_siklus dss
					on
						dsts.id_header = dss.id
				where
					tp.tgl_terima = @tgl_transaksi and
					kp.tujuan = @noreg and
					kp.jenis_kirim = 'opkp' and
					dss.id is not null
			) dm
			
			/* DATA KELUAR */
			DECLARE data_keluar CURSOR LOCAL FOR
				select
					dk.tgl_trans,
					dk.kode_trans,
					dk.jumlah,
					dk.kode_barang,
					dk.no_sj_asal,
					dk.tbl_name,
					dk.noreg_tujuan
				from (				
					select 
						l1.tanggal as tgl_trans,
						cast(l1.id as varchar(20)) as kode_trans,
						(l1.pakai_pakan-max(isnull(l2.pakai_pakan, 0))) * 50 as jumlah,
						null as kode_barang,
						null as no_sj_asal,
						'lhk' as tbl_name,
						2 as urut,
						null as noreg_tujuan
					from lhk l1
					left join
						(select tanggal, noreg, pakai_pakan, umur from lhk) l2
						on
							l2.noreg = l1.noreg and
							l2.umur < l1.umur
					where
						l1.tanggal = @tgl_transaksi and
						l1.noreg = @noreg
					group by
						l1.id,
						l1.noreg,
						l1.tanggal,
						l1.umur,
						l1.pakai_pakan
						
					union all
				
					select
						tp.tgl_terima as tgl_trans,
						kp.no_order as kode_trans,
						dkp.jumlah,
						dkp.item as kode_barang,
						dkp.no_sj_asal,
						'terima_pakan' as tbl_name,
						1 as urut,
						kp.tujuan as noreg_tujuan
					from det_kirim_pakan dkp
					left join
						kirim_pakan kp
						on
							dkp.id_header = kp.id
					left join
						terima_pakan tp
						on
							tp.id_kirim_pakan = kp.id
					where
						tp.id is not null and
						tp.tgl_terima = @tgl_transaksi and
						kp.asal = @noreg
						
					union all
					
					select
						rp.tgl_retur as tgl_trans,
						rp.no_retur as kode_trans,
						drp.jumlah,
						drp.item as kode_barang,
						rp.no_order as no_sj_asal,
						'retur_pakan' as tbl_name,
						3 as urut,
						null as noreg_tujuan
					from det_retur_pakan drp
					left join
						retur_pakan rp
						on
							drp.id_header = rp.id
					where
						rp.tgl_retur = @tgl_transaksi and
						rp.id_asal = @noreg
				) dk
				order by
					dk.urut asc
					
			OPEN data_keluar
			
			FETCH NEXT FROM data_keluar INTO
			    @dk_tgl_trans, @dk_kode_trans, @dk_jumlah, @dk_kode_barang, @dk_no_sj_asal, @dk_tbl_name, @dk_noreg_tujuan
			
			WHILE @@FETCH_STATUS = 0
			BEGIN
				SET @dk_no_sj_asal = REPLACE(@dk_no_sj_asal, 'SJ', 'OP')
				
				SET @_dk_jumlah = @dk_jumlah
				
				WHILE ( @_dk_jumlah > 0 )
				BEGIN					
					IF ( @dk_tbl_name = 'lhk' )
					BEGIN						
						IF ( EXISTS( 
							 select 
								*
							 from det_stok_siklus dss
							 left join
							 	(
							 		select dkp.no_sj_asal, dkp.item, (sum(dkp.jumlah) - isnull(sum(pp.jumlah), 0)) as jumlah from det_kirim_pakan dkp 
							 		left join
							 			kirim_pakan kp
							 			on
							 				dkp.id_header = kp.id
							 		left join
							 			(select no_sj_asal, kode_barang, sum(jumlah) as jumlah from #pp_pakan group by no_sj_asal, kode_barang) pp
							 			on
							 				pp.no_sj_asal = dkp.no_sj_asal and
							 				pp.kode_barang = dkp.item
							 		where 
							 			dkp.no_sj_asal is not null and
							 			kp.no_order <> @dk_kode_trans and
							 			not exists (select * from det_stok_trans_siklus where kode_trans = kp.no_order and kode_barang = dkp.item)
							 		group by dkp.no_sj_asal, dkp.item
							 	) dkp
							 	on
							 		dss.kode_trans = REPLACE(dkp.no_sj_asal, 'SJ', 'OP') and
							 		dss.kode_barang = dkp.item
							 left join
					 			(select id_trans, sum(jumlah) as jumlah from #pp_pakan group by id_trans) pp
					 			on
					 				dss.id = pp.id_trans
							 where 
							 	dss.noreg = @noreg and 
							 	dss.jenis_barang = @jenis and
							 	((dss.jml_stok - isnull(pp.jumlah, 0)) - isnull(dkp.jumlah, 0)) > 0
						) )
						BEGIN							
							select top 1
							 	@ds_id = cast(dss.id as int),
							 	@no_sj_asal = cast(REPLACE(dss.kode_trans, 'OP', 'SJ') as varchar(50)),
							 	@jml_pp_pakan = cast(isnull(dkp.jumlah, 0) as int),
							 	@jml_stok_pakan = cast((dss.jml_stok - isnull(pp.jumlah, 0)) as int),
							 	@ds_jml_stok = cast(((dss.jml_stok - isnull(pp.jumlah, 0)) - isnull(dkp.jumlah, 0)) as decimal(13, 2)),
							 	@ds_kode_brg = cast(dss.kode_barang as varchar(10)),
							 	@ds_hrg_jual = cast(dss.hrg_jual as decimal(12, 4)),
							 	@ds_hrg_beli = cast(dss.hrg_beli as decimal(12, 4)),
							 	@ds_oa = cast(dss.oa as decimal(12, 2))
							 from det_stok_siklus dss
							 left join
							 	(
							 		select dkp.no_sj_asal, dkp.item, (sum(dkp.jumlah) - isnull(sum(pp.jumlah), 0)) as jumlah from det_kirim_pakan dkp 
							 		left join
							 			kirim_pakan kp
							 			on
							 				dkp.id_header = kp.id
							 		left join
							 			(select no_sj_asal, kode_barang, sum(jumlah) as jumlah from #pp_pakan group by no_sj_asal, kode_barang) pp
							 			on
							 				pp.no_sj_asal = dkp.no_sj_asal and
							 				pp.kode_barang = dkp.item
							 		where 
							 			dkp.no_sj_asal is not null and
							 			kp.no_order <> @dk_kode_trans and
							 			not exists (select * from det_stok_trans_siklus where kode_trans = kp.no_order and kode_barang = dkp.item)
							 		group by dkp.no_sj_asal, dkp.item
							 	) dkp
							 	on
							 		dss.kode_trans = REPLACE(dkp.no_sj_asal, 'SJ', 'OP') and
							 		dss.kode_barang = dkp.item
							 left join
					 			(select id_trans, sum(jumlah) as jumlah from #pp_pakan group by id_trans) pp
					 			on
					 				dss.id = pp.id_trans
							 where 
							 	dss.noreg = @noreg and 
							 	dss.jenis_barang = @jenis and
							 	((dss.jml_stok - isnull(pp.jumlah, 0)) - isnull(dkp.jumlah, 0)) > 0
							 order by
							 	dss.tgl_trans asc,
							 	dss.kode_trans asc
							 	
							 print 1
							 print @jml_pp_pakan
							 	
							 IF ( @jml_pp_pakan > 0 )
							 BEGIN
								 IF ( @jml_pp_pakan <= @jml_stok_pakan )
								 BEGIN
									 insert into #pp_pakan (id_trans, no_sj_asal, kode_barang, jumlah) values
									 (@ds_id, @no_sj_asal, @ds_kode_brg, @jml_pp_pakan)
								 END
								 ELSE
								 BEGIN
									 insert into #pp_pakan (id_trans, no_sj_asal, kode_barang, jumlah) values
									 (@ds_id, @no_sj_asal, @ds_kode_brg, @jml_stok_pakan)
								 END
							 END
							 
							 select * from #pp_pakan
							 	
							 IF ( @_dk_jumlah <= @ds_jml_stok )
							 BEGIN							 
								 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
								 values
								 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @_dk_jumlah, @ds_kode_brg, @dk_tbl_name)
								 
								 SET @ds_jml_stok = @ds_jml_stok - @_dk_jumlah
								 SET @_dk_jumlah = 0
								 
								 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
							 END
							 ELSE
							 BEGIN							 
								 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
								 values
								 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @ds_jml_stok, @ds_kode_brg, @dk_tbl_name)
								 
								 SET @_dk_jumlah = @_dk_jumlah - @ds_jml_stok
								 SET @ds_jml_stok = 0
								 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
							 END
						END
						ELSE
						BEGIN
							SET @_dk_jumlah = 0
						END
					END
					ELSE
					BEGIN						
						IF ( EXISTS( select * from det_stok_siklus where noreg = @noreg and tgl_trans <= @tgl_transaksi and jml_stok > 0 and kode_trans = @dk_no_sj_asal and kode_barang = @dk_kode_barang ) )
						BEGIN
							 select top 1
							 	@ds_id = cast(id as int),
							 	@ds_jml_stok = cast(jml_stok as decimal(13, 2)),
							 	@ds_kode_brg = cast(kode_barang as varchar(10)),
							 	@ds_hrg_jual = cast(hrg_jual as decimal(12, 4)),
							 	@ds_hrg_beli = cast(hrg_beli as decimal(12, 4)),
							 	@ds_oa = cast(oa as decimal(12, 2))
							 from det_stok_siklus 
							 where 
							 	noreg = @noreg and 
							 	tgl_trans <= @tgl_transaksi and 
							 	jml_stok > 0 and 
							 	kode_trans = @dk_no_sj_asal and
								kode_barang = @dk_kode_barang
							 order by
							 	tgl_trans asc,
							 	kode_trans asc
							 	
							 IF ( @_dk_jumlah <= @ds_jml_stok )
							 BEGIN							 
								 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
								 values
								 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @_dk_jumlah, @dk_kode_barang, @dk_tbl_name)
								 
--								 IF ( @dk_no_sj_asal is not null and @dk_no_sj_asal <> '' )
--								 BEGIN
----									 IF ( NOT EXISTS ( select * from det_stok_siklus where kode_trans = @dk_kode_trans ) )
----									 BEGIN
--										 insert into det_stok_siklus (id_header, tgl_trans, noreg, kode_barang, jumlah, hrg_jual, hrg_beli, oa, kode_trans, jenis_barang, jenis_trans, jml_stok) values
--										 (@id_header, @dk_tgl_trans, @dk_noreg_tujuan, @dk_kode_barang, @_dk_jumlah, @ds_hrg_jual, @ds_hrg_beli, @ds_oa, @dk_kode_trans, @jenis, 'MUTASI', @_dk_jumlah)
----									 END
--								 END
								 
								 SET @ds_jml_stok = @ds_jml_stok - @_dk_jumlah
								 SET @_dk_jumlah = 0
								 
								 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
							 END
							 ELSE
							 BEGIN							 
								 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
								 values
								 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @ds_jml_stok, @dk_kode_barang, @dk_tbl_name)
								 
--								 IF ( @dk_no_sj_asal is not null and @dk_no_sj_asal <> '' )
--								 BEGIN
----									 IF ( NOT EXISTS ( select * from det_stok_siklus where kode_trans = @dk_kode_trans ) )
----									 BEGIN
--										 insert into det_stok_siklus (id_header, tgl_trans, noreg, kode_barang, jumlah, hrg_jual, hrg_beli, oa, kode_trans, jenis_barang, jenis_trans, jml_stok) values
--										 (@id_header, @dk_tgl_trans, @dk_noreg_tujuan, @dk_kode_barang, @ds_jml_stok, @ds_hrg_jual, @ds_hrg_beli, @ds_oa, @dk_kode_trans, @jenis, 'MUTASI', @ds_jml_stok)
----									 END
--								 END
								 
								 SET @_dk_jumlah = @_dk_jumlah - @ds_jml_stok
								 SET @ds_jml_stok = 0
								 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
							 END
						END
						ELSE
						BEGIN
							SET @_dk_jumlah = 0
						END
					END
				END
				
				FETCH NEXT FROM data_keluar INTO
			    	@dk_tgl_trans, @dk_kode_trans, @dk_jumlah, @dk_kode_barang, @dk_no_sj_asal, @dk_tbl_name, @dk_noreg_tujuan
			END
			CLOSE data_keluar
			DEALLOCATE data_keluar
			/* END - DATA KELUAR */
			
			/* DATA KELUAR AFTER MUTASI */
--			DECLARE data_keluar CURSOR LOCAL FOR
--				select
--					dk.tgl_trans,
--					dk.kode_trans,
--					(dk.jumlah - dsts.jumlah) as jumlah,
--					dk.kode_barang,
--					dk.no_sj_asal,
--					dk.tbl_name,
--					dk.noreg_tujuan
--				from (
--					select
--						l.tanggal as tgl_trans,
--						cast(l.id as varchar(20)) as kode_trans,
--						(l.pakai_pakan - isnull(l_prev.pakai_pakan, 0)) * 50 as jumlah,
--						null as kode_barang,
--						null as no_sj_asal,
--						'lhk' as tbl_name,
--						2 as urut,
--						null as noreg_tujuan
--					from lhk l
--					left join
--						(select top 1 * from lhk where noreg = @noreg and tanggal < @tgl_transaksi and pakai_pakan > 0 order by tanggal desc) l_prev
--						on
--							l_prev.noreg = l.noreg
--					where
--						l.tanggal = @tgl_transaksi and
--						l.noreg = @noreg
--						
--					union all
--				
--					select
--						tp.tgl_terima as tgl_trans,
--						kp.no_order as kode_trans,
--						dkp.jumlah,
--						dkp.item as kode_barang,
--						dkp.no_sj_asal,
--						'terima_pakan' as tbl_name,
--						1 as urut,
--						kp.tujuan as noreg_tujuan
--					from det_kirim_pakan dkp
--					left join
--						kirim_pakan kp
--						on
--							dkp.id_header = kp.id
--					left join
--						terima_pakan tp
--						on
--							tp.id_kirim_pakan = kp.id
--					where
--						tp.id is not null and
--						tp.tgl_terima = @tgl_transaksi and
--						kp.asal = @noreg
--						
--					union all
--					
--					select
--						rp.tgl_retur as tgl_trans,
--						rp.no_retur as kode_trans,
--						drp.jumlah,
--						drp.item as kode_barang,
--						rp.no_order as no_sj_asal,
--						'retur_pakan' as tbl_name,
--						3 as urut,
--						null as noreg_tujuan
--					from det_retur_pakan drp
--					left join
--						retur_pakan rp
--						on
--							drp.id_header = rp.id
--					where
--						rp.tgl_retur = @tgl_transaksi and
--						rp.id_asal = @noreg
--				) dk
--				left join
--					(
--						select
--							dsts.kode_trans,
--							dsts.tbl_name,
--							dsts.kode_barang,
--							sum(dsts.jumlah) as jumlah
--						from det_stok_trans_siklus dsts
--						group by
--							dsts.kode_trans,
--							dsts.tbl_name,
--							dsts.kode_barang
--					) dsts
--					on
--						dsts.kode_trans = dk.kode_trans and
--						dsts.tbl_name = dk.tbl_name and
--						dsts.kode_barang = dk.kode_barang
--				where
--					dk.jumlah > dsts.jumlah
--				order by
--					dk.urut asc
--					
--			OPEN data_keluar
--			
--			FETCH NEXT FROM data_keluar INTO
--			    @dk_tgl_trans, @dk_kode_trans, @dk_jumlah, @dk_kode_barang, @dk_no_sj_asal, @dk_tbl_name, @dk_noreg_tujuan
--			
--			WHILE @@FETCH_STATUS = 0
--			BEGIN
--				SET @dk_no_sj_asal = REPLACE(@dk_no_sj_asal, 'SJ', 'OP')
--				
--				SET @_dk_jumlah = @dk_jumlah
--				
--				WHILE ( @_dk_jumlah > 0 )
--				BEGIN					
--					IF ( @dk_tbl_name = 'lhk' )
--					BEGIN
--						IF ( EXISTS(
--							 select 
--								*
--							 from det_stok_siklus dss
--							 left join
--							 	(
--							 		select dkp.no_sj_asal, dkp.item, sum(dkp.jumlah) as jumlah from det_kirim_pakan dkp 
--							 		left join
--							 			kirim_pakan kp
--							 			on
--							 				dkp.id_header = kp.id
--							 		where 
--							 			dkp.no_sj_asal is not null and
--							 			kp.no_order <> @dk_kode_trans
--							 		group by dkp.no_sj_asal, dkp.item
--							 	) dkp
--							 	on
--							 		dss.kode_trans = REPLACE(dkp.no_sj_asal, 'SJ', 'OP') and
--							 		dss.kode_barang = dkp.item
--							 where 
--							 	dss.noreg = @noreg and 
--							 	dss.jenis_barang = @jenis and
--							 	(dss.jml_stok - isnull(dkp.jumlah, 0)) > 0
--						) )
--						BEGIN							
--							 select top 1
--							 	@ds_id = cast(dss.id as int),
--							 	@ds_jml_stok = cast((dss.jml_stok - isnull(dkp.jumlah, 0)) as decimal(13, 2)),
--							 	@ds_kode_brg = cast(dss.kode_barang as varchar(10)),
--							 	@ds_hrg_jual = cast(dss.hrg_jual as decimal(12, 4)),
--							 	@ds_hrg_beli = cast(dss.hrg_beli as decimal(12, 4)),
--							 	@ds_oa = cast(dss.oa as decimal(12, 2))
--							 from det_stok_siklus dss
--							 left join
--							 	(
--							 		select dkp.no_sj_asal, dkp.item, sum(dkp.jumlah) as jumlah from det_kirim_pakan dkp 
--							 		left join
--							 			kirim_pakan kp
--							 			on
--							 				dkp.id_header = kp.id
--							 		where 
--							 			dkp.no_sj_asal is not null and
--							 			kp.no_order <> @dk_kode_trans
--							 		group by dkp.no_sj_asal, dkp.item
--							 	) dkp
--							 	on
--							 		dss.kode_trans = REPLACE(dkp.no_sj_asal, 'SJ', 'OP') and
--							 		dss.kode_barang = dkp.item
--							 where 
--							 	dss.noreg = @noreg and 
--							 	dss.jenis_barang = @jenis and
--							 	(dss.jml_stok - isnull(dkp.jumlah, 0)) > 0
--							 order by
--							 	dss.tgl_trans asc,
--							 	dss.kode_trans asc
--							 	
--							 IF ( @_dk_jumlah <= @ds_jml_stok )
--							 BEGIN							 
--								 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
--								 values
--								 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @_dk_jumlah, @ds_kode_brg, @dk_tbl_name)
--								 
--								 SET @ds_jml_stok = @ds_jml_stok - @_dk_jumlah
--								 SET @_dk_jumlah = 0
--								 
--								 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
--							 END
--							 ELSE
--							 BEGIN							 
--								 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
--								 values
--								 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @ds_jml_stok, @ds_kode_brg, @dk_tbl_name)
--								 
--								 SET @_dk_jumlah = @_dk_jumlah - @ds_jml_stok
--								 SET @ds_jml_stok = 0
--								 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
--							 END
--						END
--						ELSE
--						BEGIN
--							SET @_dk_jumlah = 0
--						END
--					END
--					ELSE
--					BEGIN						
--						IF ( EXISTS( select * from det_stok_siklus where noreg = @noreg and tgl_trans <= @tgl_transaksi and jml_stok > 0 and kode_trans = @dk_no_sj_asal and kode_barang = @dk_kode_barang ) )
--						BEGIN
--							 select top 1
--							 	@ds_id = cast(id as int),
--							 	@ds_jml_stok = cast(jml_stok as decimal(13, 2)),
--							 	@ds_hrg_jual = cast(hrg_jual as decimal(12, 4)),
--							 	@ds_hrg_beli = cast(hrg_beli as decimal(12, 4)),
--							 	@ds_oa = cast(oa as decimal(12, 2))
--							 from det_stok_siklus 
--							 where 
--							 	noreg = @noreg and 
--							 	tgl_trans <= @tgl_transaksi and 
--							 	jml_stok > 0 and 
--							 	kode_trans = @dk_no_sj_asal and
--								kode_barang = @dk_kode_barang
--							 order by
--							 	tgl_trans asc,
--							 	kode_trans asc
--							 	
--							 IF ( @_dk_jumlah <= @ds_jml_stok )
--							 BEGIN							 
--								 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
--								 values
--								 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @_dk_jumlah, @dk_kode_barang, @dk_tbl_name)
--								 
----								 IF ( @dk_no_sj_asal is not null and @dk_no_sj_asal <> '' )
----								 BEGIN
------									 IF ( NOT EXISTS ( select * from det_stok_siklus where kode_trans = @dk_kode_trans ) )
------									 BEGIN
----										 insert into det_stok_siklus (id_header, tgl_trans, noreg, kode_barang, jumlah, hrg_jual, hrg_beli, oa, kode_trans, jenis_barang, jenis_trans, jml_stok) values
----										 (@id_header, @dk_tgl_trans, @dk_noreg_tujuan, @dk_kode_barang, @dk_jumlah, @ds_hrg_jual, @ds_hrg_beli, @ds_oa, @dk_kode_trans, @jenis, 'MUTASI', @dk_jumlah)
------									 END
----								 END
--								 
--								 SET @ds_jml_stok = @ds_jml_stok - @_dk_jumlah
--								 SET @_dk_jumlah = 0
--								 
--								 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
--							 END
--							 ELSE
--							 BEGIN							 
--								 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
--								 values
--								 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @ds_jml_stok, @dk_kode_barang, @dk_tbl_name)
--								 
----								 IF ( @dk_no_sj_asal is not null and @dk_no_sj_asal <> '' )
----								 BEGIN
------									 IF ( NOT EXISTS ( select * from det_stok_siklus where kode_trans = @dk_kode_trans ) )
------									 BEGIN
----										 insert into det_stok_siklus (id_header, tgl_trans, noreg, kode_barang, jumlah, hrg_jual, hrg_beli, oa, kode_trans, jenis_barang, jenis_trans, jml_stok) values
----										 (@id_header, @dk_tgl_trans, @dk_noreg_tujuan, @dk_kode_barang, @ds_jml_stok, @ds_hrg_jual, @ds_hrg_beli, @ds_oa, @dk_kode_trans, @jenis, 'MUTASI', @ds_jml_stok)
------									 END
----								 END
--								 
--								 SET @_dk_jumlah = @_dk_jumlah - @ds_jml_stok
--								 SET @ds_jml_stok = 0
--								 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
--							 END
--						END
--						ELSE
--						BEGIN
--							SET @_dk_jumlah = 0
--						END
--					END
--				END
--				
--				FETCH NEXT FROM data_keluar INTO
--			    	@dk_tgl_trans, @dk_kode_trans, @dk_jumlah, @dk_kode_barang, @dk_no_sj_asal, @dk_tbl_name, @dk_noreg_tujuan
--			END
--			CLOSE data_keluar
--			DEALLOCATE data_keluar
			/* END - DATA KELUAR AFTER MUTASI */
			
			SET @tgl_transaksi = DATEADD(DD, 1, @tgl_transaksi)
		END
		
		FETCH NEXT FROM noreg INTO
	    	@noreg
	END
	CLOSE noreg
	DEALLOCATE noreg
	
	IF ( @return = 1 )
	BEGIN
		select 'berhasil hitung pakan'
	END
END

IF ( @jenis like '%voadip%' )
BEGIN
	delete from #lnoreg
	
	IF ( @tbl_name = 'terima_voadip' )
	BEGIN
		IF ( EXISTS(
			select tv.* from terima_voadip tv where tv.id = @tbl_id
		) and @noreg1 is null and @noreg2 is null )
		BEGIN
			select
				@noreg1 = cast(data.noreg1 as varchar(15)),
				@noreg2 = cast(data.noreg2 as varchar(15))
			from
			(
				select
					case
						when kv.jenis_kirim = 'opkp' then
							kv.asal
						else
							kv.tujuan
					end as noreg1,
					case
						when kv.jenis_kirim = 'opkp' then
							kv.tujuan
						else
							null
					end as noreg2
				from terima_voadip tv
				left join
					kirim_voadip kv
					on
						tv.id_kirim_voadip = kv.id
				where
					tv.id = @tbl_id
			) data
		END
		
		insert into #lnoreg (urut, noreg)
		values
		(1, @noreg1),
		(2, @noreg2)
	END
	
	IF ( @tbl_name = 'retur_voadip' )
	BEGIN
		IF ( EXISTS(
			select rv.* from retur_voadip rv where rv.id = @tbl_id
		) and @noreg1 is null and @noreg2 is null )
		BEGIN
			select
				@noreg1 = cast(data.noreg1 as varchar(15)),
				@noreg2 = cast(data.noreg2 as varchar(15))
			from
			(
				select
					rv.id_asal as noreg1,
					null as noreg2
				from retur_voadip rv
				where
					rv.id = @tbl_id
			) data
		END
		
		insert into #lnoreg (urut, noreg)
		values
		(1, @noreg1),
		(2, @noreg2)
	END
	
	DECLARE noreg CURSOR LOCAL FOR
		select noreg from #lnoreg where noreg is not null order by urut asc
			
	OPEN noreg
	
	FETCH NEXT FROM noreg INTO
	    @noreg
	
	WHILE @@FETCH_STATUS = 0
	BEGIN
		SET @tgl_transaksi = @_tgl_transaksi
		
		WHILE ( @tgl_transaksi <= @today )
		BEGIN
			select 
				@id_header = cast(id as int) 
			from stok where periode = @tgl_transaksi
			
			/*
			update dss
			set
				dss.jml_stok = dss.jml_stok + dtrans.jumlah
			from det_stok_siklus dss
			right join
				(
					select 
						dsts.id_header,
						sum(dsts.jumlah) as jumlah
					from det_stok_trans_siklus dsts
					left join
						det_stok_siklus dss
						on
							dsts.id_header = dss.id
					where
						dsts.tgl_trans = @tgl_transaksi and
						dss.noreg = @noreg and
						dss.jenis_barang = @jenis
					group by
						dsts.id_header
				) dtrans
				on
					dss.id = dtrans.id_header
			*/
			
			delete det_stok_trans_siklus where id in (			
				select 
					dsts.id
				from det_stok_trans_siklus dsts
				left join
					det_stok_siklus dss
					on
						dsts.id_header = dss.id
				where
					dsts.tgl_trans >= @tgl_transaksi and
					dss.noreg = @noreg and
					dss.jenis_barang = @jenis
				group by
					dsts.id
			)
			
			update dss
			set
				dss.jml_stok = case 
					when isnull(dtrans.jumlah, 0) > dss.jumlah then
						dss.jumlah
					else
						(dss.jumlah - isnull(dtrans.jumlah, 0))
				end
			from det_stok_siklus dss
			left join
				(
					select dsts.id_header, sum(dsts.jumlah) as jumlah from det_stok_trans_siklus dsts
					left join
						det_stok_siklus dss
						on
							dsts.id_header = dss.id
					where
						dsts.tgl_trans < @tgl_transaksi and
						dss.noreg = @noreg and
						dss.jenis_barang = @jenis
					group by
						dsts.id_header
				) dtrans
				on
					dss.id = dtrans.id_header
			where
				dss.tgl_trans < @tgl_transaksi and
				dss.noreg = @noreg and
				dss.jenis_barang = @jenis
			
--			delete from det_stok_trans_siklus where id_header in (select id from det_stok_siklus where id_header = @id_header and noreg = @noreg and jenis_barang = @jenis)
			delete from det_stok_siklus where id_header = @id_header and noreg = @noreg and jenis_barang = @jenis
			
			/* DATA MASUK */
			insert into det_stok_siklus (id_header, tgl_trans, noreg, kode_barang, jumlah, hrg_jual, hrg_beli, oa, kode_trans, jenis_barang, jenis_trans, jml_stok)
			select * from
			(
				select
					@id_header as id_header,
					tv.tgl_terima as tgl_trans,
					kv.tujuan as noreg,
					dtv.item as kode_barang,
					dst.jumlah,
					ds.hrg_jual,
					ds.hrg_beli,
					kv.ongkos_angkut as oa,
					kv.no_order as kode_trans,
					'voadip' as jenis_barang,
					'ORDER' as jenis_trans,
					dst.jumlah as jml_stok
				from det_terima_voadip dtv
				left join
					terima_voadip tv
					on
						dtv.id_header = tv.id
				left join
					kirim_voadip kv
					on
						tv.id_kirim_voadip = kv.id
				left join
					det_stok_trans dst
					on
						dst.kode_trans = kv.no_order and
						dst.kode_barang = dtv.item
				left join
					det_stok ds
					on
						dst.id_header = ds.id
				where
					tv.tgl_terima = @tgl_transaksi and
					kv.tujuan = @noreg and
					kv.jenis_kirim = 'opkg'
					
--				union all
--				
--				select
--					@id_header as id_header,
--					tv.tgl_terima as tgl_trans,
--					kv.tujuan as noreg,
--					dtv.item as kode_barang,
--					dst.jumlah,
--					ds.hrg_jual,
--					ds.hrg_beli,
--					ds.oa,
--					kv.no_order as kode_trans,
--					'voadip' as jenis_barang,
--					'MUTASI' as jenis_trans,
--					dst.jumlah as jml_stok
--				from det_terima_voadip dtv
--				left join
--					terima_voadip tv
--					on
--						dtv.id_header = tv.id
--				left join
--					kirim_voadip kv
--					on
--						tv.id_kirim_voadip = kv.id
--				left join
--					det_stok_trans_siklus dst
--					on
--						dst.kode_trans = kv.no_order and
--						dst.kode_barang = dtv.item
--				left join
--					det_stok_siklus ds
--					on
--						dst.id_header = ds.id
--				where
--					tv.tgl_terima = @tgl_transaksi and
--					kv.tujuan = @noreg and
--					kv.jenis_kirim = 'opkp'
			) dm
			/* END - DATA MASUK */
			
			/* DATA KELUAR */
			DECLARE data_keluar CURSOR LOCAL FOR
				select
					dk.tgl_trans,
					dk.kode_trans,
					dk.jumlah,
					dk.kode_barang,
					dk.no_sj_asal,
					dk.tbl_name
				from (					
					select
						rv.tgl_retur as tgl_trans,
						rv.no_retur as kode_trans,
						drv.jumlah,
						drv.item as kode_barang,
						rv.no_order as no_sj_asal,
						'retur_voadip' as tbl_name,
						2 as urut
					from det_retur_voadip drv
					left join
						retur_voadip rv
						on
							drv.id_header = rv.id
					where
						rv.tgl_retur = @tgl_transaksi and
						rv.id_asal = @noreg
				) dk
				order by
					dk.urut asc
					
			OPEN data_keluar
			
			FETCH NEXT FROM data_keluar INTO
			    @dk_tgl_trans, @dk_kode_trans, @dk_jumlah, @dk_kode_barang, @dk_no_sj_asal, @dk_tbl_name
			
			WHILE @@FETCH_STATUS = 0
			BEGIN
				SET @dk_no_sj_asal = REPLACE(@dk_no_sj_asal, 'SJ', 'OP') 
				
				WHILE ( @dk_jumlah > 0 )
				BEGIN
					IF ( EXISTS( select * from det_stok_siklus where noreg = @noreg and tgl_trans <= @tgl_transaksi and jml_stok > 0 and kode_trans = @dk_no_sj_asal and kode_barang = @dk_kode_barang ) )
					BEGIN
						 select top 1
						 	@ds_id = cast(id as int),
						 	@ds_jml_stok = cast(jml_stok as decimal(13, 2))
						 from det_stok_siklus 
						 where 
						 	noreg = @noreg and 
						 	tgl_trans <= @tgl_transaksi and 
						 	jml_stok > 0 and 
						 	kode_trans = @dk_no_sj_asal and
							kode_barang = @dk_kode_barang
						 order by
						 	tgl_trans asc,
						 	kode_trans asc
						 	
						 IF ( @dk_jumlah <= @ds_jml_stok )
						 BEGIN							 
							 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
							 values
							 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @dk_jumlah, @dk_kode_barang, @dk_tbl_name)
							 
							 SET @ds_jml_stok = @ds_jml_stok - @dk_jumlah
							 SET @dk_jumlah = 0
							 
							 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
						 END
						 ELSE
						 BEGIN							 
							 insert into det_stok_trans_siklus (id_header, tgl_trans, kode_trans, jumlah, kode_barang, tbl_name)
							 values
							 (@ds_id, @dk_tgl_trans, @dk_kode_trans, @ds_jml_stok, @dk_kode_barang, @dk_tbl_name)
							 
							 SET @dk_jumlah = @dk_jumlah - @ds_jml_stok
							 SET @ds_jml_stok = 0
							 update det_stok_siklus set jml_stok = @ds_jml_stok where id = @ds_id
						 END
					END
					ELSE
					BEGIN
						SET @dk_jumlah = 0
					END
				END
				
				FETCH NEXT FROM data_keluar INTO
			    	@dk_tgl_trans, @dk_kode_trans, @dk_jumlah, @dk_kode_barang, @dk_no_sj_asal, @dk_tbl_name
			END
			CLOSE data_keluar
			DEALLOCATE data_keluar
			/* END - DATA KELUAR */
			
			SET @tgl_transaksi = DATEADD(DD, 1, @tgl_transaksi)
		END
		
		FETCH NEXT FROM noreg INTO
	    	@noreg
	END
	CLOSE noreg
	DEALLOCATE noreg
	
	IF ( @return = 1 )
	BEGIN
		select 'berhasil hitung voadip'
	END
END
*/