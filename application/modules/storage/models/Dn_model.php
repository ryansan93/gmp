<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class Dn_model extends Conf {
	protected $table = 'dn';
	protected $primaryKey = 'id';
    public $timestamps = false;

    /*
     * TRUE bila realisasi pembayaran TIDAK boleh diposting jurnal otomatis:
     *  - SEMUA detailnya SEWA (jurnal otomatis SEWA belum ada; diposting accounting sendiri), atau
     *  - SEMUA detailnya SEWA / DN langsung, selama setting jurnal realisasi_pembayaran BELUM mengenali
     *    DN langsung (docs/setting_jurnal_realisasi_dn_langsung.sql). Tanpa setting itu DN langsung hanya
     *    menghasilkan kredit bank tanpa debet hutang. Begitu setting terpasang, DN langsung dijurnal
     *    otomatis mengikuti jenis DN-nya (DOC / PAKAN / OVK / RHPP / OA).
     */
    public static function tanpaJurnalOtomatis($id_header)
    {
        $m_conf = new \Model\Storage\Conf();

        $dn_dikenali = false;
        $d_saj = $m_conf->hydrateRaw("
            select top 1 case when _query like '%dnl.tipe_dn%' then 1 else 0 end as dikenali
            from setting_automatic_jurnal
            where tbl_name = 'realisasi_pembayaran'
            order by tgl_berlaku desc
        ");
        if ( $d_saj->count() > 0 ) {
            $dn_dikenali = ((int) $d_saj->toArray()[0]['dikenali']) == 1;
        }

        $jenis_khusus = $dn_dikenali ? "'SEWA'" : "'SEWA', 'DN'";

        $sql = "
            select
                sum(case when rpd.transaksi in (".$jenis_khusus.") then 1 else 0 end) as khusus,
                count(*) as semua
            from realisasi_pembayaran_det rpd
            where rpd.id_header = ".((int) $id_header)."
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        if ( $d_conf->count() > 0 ) {
            $row = $d_conf->toArray()[0];

            return ((int) $row['semua']) > 0 && ((int) $row['khusus']) == ((int) $row['semua']);
        }

        return false;
    }

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
				$sql_condition .= " and d.id = ".$id."";
			} else {
				$sql_condition = "where d.id = ".$id."";
			}
		}

		if ( !empty($start_date) && !empty($end_date) ) {
			if ( !empty($sql_condition) ) {
				$sql_condition .= " and d.tanggal between '".$start_date."' and '".$end_date."'";
			} else {
				$sql_condition = "where d.tanggal between '".$start_date."' and '".$end_date."'";
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
					d.*,
					case
						when d.jenis_dn like 'DOC' then 'supplier'
						when d.jenis_dn like 'PKN' then 'supplier'
						when d.jenis_dn like 'OVK' then 'supplier'
						when d.jenis_dn like 'RHPP' then 'mitra'
						when d.jenis_dn like 'OA' then 'ekspedisi'
						when d.jenis_dn like 'BKL' then 'bakul'
						when d.jenis_dn like 'NS' then 'supplier'
					end as jenis
				from dn d
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