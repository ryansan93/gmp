<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class Cn_model extends Conf {
	protected $table = 'cn';
	protected $primaryKey = 'id';
    public $timestamps = false;

    public function getNextNomor($kode)
	{
		$id = $this->whereRaw("SUBSTRING(nomor, LEN('".$kode."')+1, 7) = '/'+cast(right(year(current_timestamp),2) as char(2))+'/'+replace(str(month(getdate()),2),' ',0)+'/'")
                        ->selectRaw("'".$kode."'+'/'+right(year(current_timestamp),2)+'/'+replace(str(month(getdate()),2),' ',0)+'/'+replace(str(substring(coalesce(max(nomor),'000'),((LEN('".$kode."')+1)+(LEN('/'+cast(right(year(current_timestamp),2) as char(2))+'/'+replace(str(month(getdate()),2),' ',0)+'/'))),3)+1,3), ' ', '0') as nextId")
                        ->first();
		return $id->nextId;
	}

	public function getData( $id = null, $start_date = null, $end_date = null, $kode_supl = null, $jenis = null ) {
		$sql_condition = null;
		if ( !empty($id) ) {
			if ( !empty($sql_condition) ) {
				$sql_condition .= " and c.id = ".$id."";
			} else {
				$sql_condition = "where c.id = ".$id."";
			}
		}

		if ( !empty($start_date) && !empty($end_date) ) {
			if ( !empty($sql_condition) ) {
				$sql_condition .= " and c.tanggal between '".$start_date."' and '".$end_date."'";
			} else {
				$sql_condition = "where c.tanggal between '".$start_date."' and '".$end_date."'";
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
				$sql_condition2 .= " and data.jenis = '".$jenis."'";
			} else {
				$sql_condition2 = "where data.jenis = '".$jenis."'";
			}
		}

		$sql = "
			select
				data.*,
				supl.nama as nama_supplier
			from
			(
				select
					c.*,
					case
						when c.jenis_cn like 'DOC' then 'supplier'
						when c.jenis_cn like 'PKN' then 'supplier'
						when c.jenis_cn like 'OVK' then 'supplier'
						when c.jenis_cn like 'RHPP' then 'mitra'
						when c.jenis_cn like 'OA' then 'ekspedisi'
						when c.jenis_cn like 'BKL' then 'bakul'
						when c.jenis_cn like 'NS' then 'supplier'
					end as jenis
				from cn c
				".$sql_condition."
			) data
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
					supl.nomor = data.supplier and
					supl.jenis = data.jenis
			".$sql_condition2."
		";
		$d_supplier = $this->hydrateRaw( $sql );

		$data = null;
		if ( $d_supplier->count() > 0 ) {
			$data = $d_supplier->toArray();
		}

		return $data;
	}
}