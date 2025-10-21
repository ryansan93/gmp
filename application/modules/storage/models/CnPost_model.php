<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class CnPost_model extends Conf {
	protected $table = 'cn_post';
	protected $primaryKey = 'id';
    public $timestamps = false;

	public function getData( $id = null, $start_date = null, $end_date = null, $kode_supl = null, $jenis = null ) {
		$sql_condition = null;
		if ( !empty($id) ) {
			if ( !empty($sql_condition) ) {
				$sql_condition .= " and cp.id = ".$id."";
			} else {
				$sql_condition = "where cp.id = ".$id."";
			}
		}

		if ( !empty($start_date) && !empty($end_date) ) {
			if ( !empty($sql_condition) ) {
				$sql_condition .= " and cp.tanggal between '".$start_date."' and '".$end_date."'";
			} else {
				$sql_condition = "where cp.tanggal between '".$start_date."' and '".$end_date."'";
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
				$sql_condition2 .= " and data.jenis_cn = '".$jenis."'";
			} else {
				$sql_condition2 = "where data.jenis_cn = '".$jenis."'";
			}
		}

		$sql = "
			select
				data.*,
				c.tanggal as tgl_cn,
				c.nomor as nomor_cn,
				c.no_dok,
				supl.nama as nama_supplier,
				(c.tot_cn - sum(isnull(_cpd.tot_pakai, 0))) as sisa
			from
			(
				select
					cp.*,
					case
						when cp.jenis_cn like 'DOC' then 'supplier'
						when cp.jenis_cn like 'PKN' then 'supplier'
						when cp.jenis_cn like 'OVK' then 'supplier'
						when cp.jenis_cn like 'RHPP' then 'mitra'
						when cp.jenis_cn like 'OA' then 'ekspedisi'
						when cp.jenis_cn like 'BKL' then 'bakul'
						when cp.jenis_cn like 'NS' then 'supplier'
					end as jenis
				from cn_post cp
				".$sql_condition."
			) data
			left join
                (
                    select 
                        cp.id, 
                        cp.tanggal, 
                        cp.no_cn, 
                        sum(cpd.pakai) as tot_pakai
                    from cn_post_det cpd
                    left join
                        cn_post cp 
                        on
                            cpd.id_header = cp.id
                    where
                        cp.id <> '".$id."'
                    group by
                        cp.id,
                        cp.tanggal,
                        cp.no_cn
                ) _cpd
                on
                    _cpd.no_cn = data.no_cn and
                    _cpd.tanggal <= data.tanggal and
                    _cpd.id < data.id
			left join
				cn c
				on
					data.no_cn = c.id
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
					supl.nomor = c.supplier and
					supl.jenis = data.jenis
			".$sql_condition2."
			group by
				data.id,
				data.tanggal,
				data.jenis_cn,
				data.no_cn,
				data.tot_pakai,
				data.jenis,
				c.tanggal,
				c.nomor,
				c.no_dok,
				c.tot_cn,
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