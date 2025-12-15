<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class DnPost_model extends Conf {
	protected $table = 'dn_post';
	protected $primaryKey = 'id';
    public $timestamps = false;

	public function getData( $id = null, $start_date = null, $end_date = null, $kode_supl = null, $jenis = null ) {
		$sql_condition = null;
		if ( !empty($id) ) {
			if ( !empty($sql_condition) ) {
				$sql_condition .= " and dp.id = ".$id."";
			} else {
				$sql_condition = "where dp.id = ".$id."";
			}
		}

		if ( !empty($start_date) && !empty($end_date) ) {
			if ( !empty($sql_condition) ) {
				$sql_condition .= " and dp.tanggal between '".$start_date."' and '".$end_date."'";
			} else {
				$sql_condition = "where dp.tanggal between '".$start_date."' and '".$end_date."'";
			}
		}

		$sql_condition2 = null;
		if ( !empty($kode_supl) ) {
			if ( !empty($sql_condition2) ) {
				$sql_condition2 .= " and data.supplier = '".$kode_supl."'";
			} else {
				$sql_condition2 = "where data.supplier = '".$kode_supl."'";
			}
		}

		if ( !empty($jenis) ) {
			if ( !empty($sql_condition2) ) {
				$sql_condition2 .= " and data.jenis_dn = '".$jenis."'";
			} else {
				$sql_condition2 = "where data.jenis_dn = '".$jenis."'";
			}
		}

		$sql = "
			select
				data.*,
				d.tanggal as tgl_dn,
				d.nomor as nomor_dn,
				d.no_dok,
				supl.nama as nama_supplier,
				(d.tot_dn - sum(isnull(_dpd.tot_pakai, 0))) as sisa
			from
			(
				select
					dp.*,
					case
						when dp.jenis_dn like 'DOC' then 'supplier'
						when dp.jenis_dn like 'PKN' then 'supplier'
						when dp.jenis_dn like 'OVK' then 'supplier'
						when dp.jenis_dn like 'RHPP' then 'mitra'
						when dp.jenis_dn like 'OA' then 'ekspedisi'
						when dp.jenis_dn like 'BKL' then 'bakul'
						when dp.jenis_dn like 'NS' then 'supplier'
					end as jenis
				from dn_post dp
				".$sql_condition."
			) data
			left join
                (
                    select 
                        dp.id, 
                        dp.tanggal, 
                        dp.no_dn, 
                        sum(dpd.pakai) as tot_pakai
                    from dn_post_det dpd
                    left join
                        dn_post dp 
                        on
                            dpd.id_header = dp.id
                    where
                        dp.id <> '".$id."'
                    group by
                        dp.id,
                        dp.tanggal,
                        dp.no_dn
                ) _dpd
                on
                    _dpd.no_dn = data.no_dn and
                    _dpd.tanggal <= data.tanggal and
                    _dpd.id < data.id
			left join
				dn d
				on
					data.no_dn = d.id
			left join
				(
					select
						p1.nomor, p1.nama, 'supplier' as jenis
					from pelanggan p1
					right join
						( select max(id) as id, nomor from pelanggan where tipe = 'supplier' and jenis <> 'ekspedisi' group by nomor ) p2
						on
							p1.id = p2.id

					union all

					select
						p1.nomor, p1.nama, 'bakul' as jenis
					from pelanggan p1
					right join
						( select max(id) as id, nomor from pelanggan where tipe='pelanggan' group by nomor ) p2
						on
							p1.id = p2.id

					union all

					select
						e1.nomor, e1.nama, 'ekspedisi' as jenis
					from ekspedisi e1
					right join
						( select max(id) as id, nomor from ekspedisi group by nomor ) e2
						on
							e1.id = e2.id

					union all

					select
						m1.nomor, m1.nama, 'mitra' as jenis
					from mitra m1
					right join
						( select max(id) as id, nomor from mitra group by nomor ) m2
						on
							m1.id = m2.id
				) supl
				on
					supl.nomor = d.supplier and
					supl.jenis = data.jenis
			".$sql_condition2."
			group by
				data.id,
				data.tanggal,
				data.jenis_dn,
				data.no_dn,
				data.tot_pakai,
				data.jenis,
				d.tanggal,
				d.nomor,
				d.no_dok,
				d.tot_dn,
				supl.nama
		";
		// cetak_r( $sql );
		$d_cp = $this->hydrateRaw( $sql );

		$data = null;
		if ( $d_cp->count() > 0 ) {
			$data = $d_cp->toArray();
		}

		return $data;
	}
}