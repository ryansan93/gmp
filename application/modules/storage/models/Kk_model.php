<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class Kk_model extends Conf{
	public $table = 'kk';
	protected $primaryKey = 'no_kk';
	public $timestamps = false;

	public function getKode($kode){
		$id = $this->whereRaw("SUBSTRING(".$this->primaryKey.", 0, ".((strlen($kode)+1)+6).") = '".$kode."'+cast(right(year(current_timestamp),2) as char(2))+replace(str(month(getdate()),2),' ',0)+replace(str(day(getdate()),2),' ',0)")
								->selectRaw("'".$kode."'+right(year(current_timestamp),2)+replace(str(month(getdate()),2),' ',0)+replace(str(day(getdate()),2),' ',0)+replace(str(substring(coalesce(max(".$this->primaryKey."),'0000'), ".((strlen($kode)+1)+6).", 4)+1, 4), ' ', '0') as nextId")
								->first();
		return $id->nextId;
	}

	public function getKk($id = null, $column = 'no_kk') {
		$data = null;
        
        $sql_id = "";
        if ( !empty($id) ) {
            $sql_id = "where k.".$column." = '".$id."'";
        }

		$sql = "
			select 
				k.*,
				jt.nama as jurnal_trans_nama,
				m.nama as nama_mitra,
				cast(SUBSTRING(k.noreg, 10, 2) as int) as kandang,
				case
					when td.datang is not null then
						td.datang
					else
						rs.tgl_docin
				end as tgl_docin
			from kk k
			left join
				(
					select jt1.* from jurnal_trans jt1
					right join
						(select max(id) as id, kode from jurnal_trans group by kode) jt2
						on
							jt1.id = jt2.id
				) jt
				on
					k.jurnal_trans = jt.kode
			left join
				rdim_submit rs
				on
					k.noreg = rs.noreg
			left join
				(
					select mm1.* from mitra_mapping mm1
					right join
						(select max(id) as id, nim from mitra_mapping group by nim) mm2
						on
							mm1.id = mm2.id
				) mm
				on
					mm.nim = rs.nim
			left join
				mitra m
				on
					m.id = mm.mitra
			left join
				(
					select od1.* from order_doc od1
					right join
						(select max(id) as id, no_order from order_doc group by no_order) od2
						on
							od1.id = od2.id
				) od
				on
					od.noreg = rs.noreg
			left join
				(
					select td1.* from terima_doc td1
					right join
						(select max(id) as id, no_order from terima_doc group by no_order) td2
						on
							td1.id = td2.id
				) td
				on
					td.no_order = od.no_order
			".$sql_id."
			order by
				k.tgl_kk desc,
				k.no_kk desc
		";
		$d_kk = $this->hydrateRaw($sql);

        if ( !empty($d_kk) && $d_kk->count() > 0 ) {
            $data = $d_kk->toArray();
        }

		return $data;
	}

	public function getBkByDate($start_date, $end_date, $bank) {
		$data = null;

		$sql = "
			select 
				k.*
			from kk k
			left join
				(
					select jt1.* from jurnal_trans jt1
					right join
						(select max(id) as id, kode from jurnal_trans group by kode) jt2
						on
							jt1.id = jt2.id
				) jt
				on
					k.jurnal_trans = jt.kode
			left join
				coa c
				on
					k.coa_bank = c.coa
			where
				k.tgl_kk between '".$start_date."' and '".$end_date."' and
				k.coa_bank = '".$bank."' and
				c.bank = 1
			order by
				k.tgl_kk desc,
				k.no_kk desc
		";
		$d_kk = $this->hydrateRaw($sql);

        if ( !empty($d_kk) && $d_kk->count() > 0 ) {
            $data = $d_kk->toArray();
        }

		return $data;
	}

	public function getKkByDate($start_date, $end_date, $bank) {
		$data = null;

		$sql = "
			select 
				k.*,
				m.nama as nama_mitra
			from kk k
			left join
				(
					select jt1.* from jurnal_trans jt1
					right join
						(select max(id) as id, kode from jurnal_trans group by kode) jt2
						on
							jt1.id = jt2.id
				) jt
				on
					k.jurnal_trans = jt.kode
			left join
				coa c
				on
					k.coa_bank = c.coa
			left join
				rdim_submit rs
				on
					k.noreg = rs.noreg
			left join
				(
					select mm1.* from mitra_mapping mm1
					right join
						(select max(id) as id, nim from mitra_mapping group by nim) mm2
						on
							mm1.id = mm2.id
				) mm
				on
					mm.nim = rs.nim
			left join
				mitra m
				on
					m.id = mm.mitra
			where
				k.tgl_kk between '".$start_date."' and '".$end_date."' and
				k.coa_bank = '".$bank."' and
				c.kas = 1
			order by
				k.tgl_kk desc,
				k.no_kk desc
		";
		$d_kk = $this->hydrateRaw($sql);

        if ( !empty($d_kk) && $d_kk->count() > 0 ) {
            $data = $d_kk->toArray();
        }

		return $data;
	}

	public function getDataLaporanKk($start_date, $end_date, $supplier, $urut, $kode) {
		$data = null;
		$params = null;
		
		$grand_total = 0;

		$sql_supplier = null;
		if ( !in_array('all', $supplier) ) {
			$sql_supplier = "and k.kode_supl in ('".implode("', '", $supplier)."')";
		}

		$sql = "
			select 
				k.tgl_kk,
				k.no_kk,
				k.kode_supl,
				supl.nama_supl,
				k.no_coa as no_coa_header,
				k.nama_bank,
				k.no_giro,
				k.tgl_cair,
				k.keterangan,
				k.nilai as grand_total,
				ki.no_kk,
				ki.tgl_kk,
				ki.no_urut,
				ki.no_coa,
				c.nama_coa,
				ki.keterangan,
				ki.no_lpb,
				ki.nilai_lpb,
				ki.nilai
			from kkitem ki
			left join
				kk k
				on
					ki.no_kk = k.no_kk
			left join
				supplier supl
				on
					k.kode_supl = supl.kode_supl
			left join
				coa c
				on
					ki.no_coa = c.no_coa
			where
				k.no_kk like '%".$kode."%' and
				k.tgl_kk between '".$start_date."' and '".$end_date."'
				".$sql_supplier."
		";
		$d_kk = $this->hydrateRaw($sql);

        if ( !empty($d_kk) && $d_kk->count() > 0 ) {
            $d_kk = $d_kk->toArray();

			foreach ($d_kk as $k_kk => $v_kk) {
				$key = null;
				$key_detail = null;
				if ( stristr($urut, 'tanggal') !== false ) {
					$key_tgl = str_replace('-', '', substr($v_kk['tgl_kk'], 0, 10));
					$key_kk = $v_kk['no_kk'];
					$key_detail = $v_kk['keterangan'].'-'.$v_kk['no_urut'].'-'.$v_kk['no_lpb'];

					if ( !isset( $data[ $key_tgl ] ) ) {
						$data[ $key_tgl ]['tgl_kk'] = $v_kk['tgl_kk'];
						$data[ $key_tgl ]['grand_total'] = $v_kk['grand_total'];

						$grand_total += $v_kk['grand_total'];

						$data[ $key_tgl ]['kk'][ $key_kk ] = array(
							'tgl_kk' => $v_kk['tgl_kk'],
							'no_kk' => $v_kk['no_kk'],
							'kode_supl' => $v_kk['kode_supl'],
							'nama_supl' => $v_kk['nama_supl'],
							'no_coa' => $v_kk['no_coa'],
							'nama_bank' => $v_kk['nama_bank'],
							'no_giro' => $v_kk['no_giro'],
							'tgl_cair' => $v_kk['tgl_cair'],
							'keterangan' => $v_kk['keterangan'],
							'grand_total' => $v_kk['grand_total']
						);
	
						$data[ $key_tgl ]['kk'][ $key_kk ]['detail'][ $key_detail ] = array(
							'no_urut' => $v_kk['no_urut'],
							'no_coa' => $v_kk['no_coa'],
							'nama_coa' => $v_kk['nama_coa'],
							'keterangan' => $v_kk['keterangan'],
							'no_lpb' => $v_kk['no_lpb'],
							'nilai_lpb' => $v_kk['nilai_lpb'],
							'nilai' => $v_kk['nilai']
						);
					} else {
						if ( !isset($data[ $key_tgl ]['kk'][ $key_kk ]) ) {
							$data[ $key_tgl ]['grand_total'] += $v_kk['grand_total'];

							$grand_total += $v_kk['grand_total'];

							$data[ $key_tgl ]['kk'][ $key_kk ] = array(
								'tgl_kk' => $v_kk['tgl_kk'],
								'no_kk' => $v_kk['no_kk'],
								'kode_supl' => $v_kk['kode_supl'],
								'nama_supl' => $v_kk['nama_supl'],
								'no_coa' => $v_kk['no_coa'],
								'nama_bank' => $v_kk['nama_bank'],
								'no_giro' => $v_kk['no_giro'],
								'tgl_cair' => $v_kk['tgl_cair'],
								'keterangan' => $v_kk['keterangan'],
								'grand_total' => $v_kk['grand_total']
							);
						}

						$data[ $key_tgl ]['kk'][ $key_kk ]['detail'][ $key_detail ] = array(
							'no_urut' => $v_kk['no_urut'],
							'no_coa' => $v_kk['no_coa'],
							'nama_coa' => $v_kk['nama_coa'],
							'keterangan' => $v_kk['keterangan'],
							'no_lpb' => $v_kk['no_lpb'],
							'nilai_lpb' => $v_kk['nilai_lpb'],
							'nilai' => $v_kk['nilai']
						);
					}
	
					ksort( $data[ $key_tgl ]['kk'][ $key_kk ]['detail'] );
					ksort( $data[ $key_tgl ]['kk'] );
					ksort( $data );

					$params = array(
						'data' => $data,
						'grand_total' => array(
							'grand_total' => $grand_total
						)
					);
				} else if ( stristr($urut, 'supplier') !== false ) {
					$key_supl = $v_kk['nama_supl'].' | '.$v_kk['kode_supl'];
					$key_kk = $v_kk['no_kk'];
					$key_detail = $v_kk['keterangan'].'-'.$v_kk['no_urut'].'-'.$v_kk['no_lpb'];

					if ( !isset( $data[ $key_supl ] ) ) {
						$data[ $key_supl ]['kode_supl'] = $v_kk['kode_supl'];
						$data[ $key_supl ]['nama_supl'] = $v_kk['nama_supl'];
						$data[ $key_supl ]['grand_total'] = $v_kk['grand_total'];

						$grand_total += $v_kk['grand_total'];

						$data[ $key_supl ]['kk'][ $key_kk ] = array(
							'tgl_kk' => $v_kk['tgl_kk'],
							'no_kk' => $v_kk['no_kk'],
							'kode_supl' => $v_kk['kode_supl'],
							'nama_supl' => $v_kk['nama_supl'],
							'no_coa' => $v_kk['no_coa'],
							'nama_bank' => $v_kk['nama_bank'],
							'no_giro' => $v_kk['no_giro'],
							'tgl_cair' => $v_kk['tgl_cair'],
							'keterangan' => $v_kk['keterangan'],
							'grand_total' => $v_kk['grand_total']
						);
	
						$data[ $key_supl ]['kk'][ $key_kk ]['detail'][ $key_detail ] = array(
							'no_urut' => $v_kk['no_urut'],
							'no_coa' => $v_kk['no_coa'],
							'nama_coa' => $v_kk['nama_coa'],
							'keterangan' => $v_kk['keterangan'],
							'no_lpb' => $v_kk['no_lpb'],
							'nilai_lpb' => $v_kk['nilai_lpb'],
							'nilai' => $v_kk['nilai']
						);
					} else {
						if ( !isset($data[ $key_supl ]['kk'][ $key_kk ]) ) {
							$data[ $key_supl ]['grand_total'] += $v_kk['grand_total'];

							$grand_total += $v_kk['grand_total'];

							$data[ $key_supl ]['kk'][ $key_kk ] = array(
								'tgl_kk' => $v_kk['tgl_kk'],
								'no_kk' => $v_kk['no_kk'],
								'kode_supl' => $v_kk['kode_supl'],
								'nama_supl' => $v_kk['nama_supl'],
								'no_coa' => $v_kk['no_coa'],
								'nama_bank' => $v_kk['nama_bank'],
								'no_giro' => $v_kk['no_giro'],
								'tgl_cair' => $v_kk['tgl_cair'],
								'keterangan' => $v_kk['keterangan'],
								'grand_total' => $v_kk['grand_total']
							);
						}

						$data[ $key_supl ]['kk'][ $key_kk ]['detail'][ $key_detail ] = array(
							'no_urut' => $v_kk['no_urut'],
							'no_coa' => $v_kk['no_coa'],
							'nama_coa' => $v_kk['nama_coa'],
							'keterangan' => $v_kk['keterangan'],
							'no_lpb' => $v_kk['no_lpb'],
							'nilai_lpb' => $v_kk['nilai_lpb'],
							'nilai' => $v_kk['nilai']
						);
					}
	
					ksort( $data[ $key_supl ]['kk'][ $key_kk ]['detail'] );
					ksort( $data[ $key_supl ]['kk'] );
					ksort( $data );

					$params = array(
						'data' => $data,
						'grand_total' => array(
							'grand_total' => $grand_total
						)
					);
				}
			}
        }

		return $params;
	}

	public function getKkCetak($id = null, $column = 'no_kk') {
		$data = null;
        
        $sql_id = "";
        if ( !empty($id) ) {
            $sql_id = "where k.".$column." = '".$id."'";
        }

		$sql = "
			select 
				k.*,
				c_header.nama_coa,
				supl.nama_supl,
				ki.no_urut,
				ki.no_coa as no_coa_item,
				c_item.nama_coa as nama_coa_item,
				-- ki.keterangan,
				case
					when ki.no_lpb is not null and ki.no_lpb <> '' then
						case
							when ki.keterangan is null or ki.keterangan = '' then
								'PELUNASAN HUTANG A.N '+supl.nama_supl+' / '+ki.no_lpb
							else
								ki.keterangan
						end
					else
						ki.keterangan
				end as keterangan,
				ki.no_lpb,
				ki.nilai_lpb,
				isnull(ki.nilai, 0) as nilai
			from kkitem ki
			left join
				kk k
				on
					k.no_kk = ki.no_kk
            left join
				coa c_header
				on
					k.no_coa = c_header.no_coa
			left join
				coa c_item
				on
					ki.no_coa = c_item.no_coa
			left join
				supplier supl
				on
					k.kode_supl = supl.kode_supl
			".$sql_id."
		";
		$d_kk = $this->hydrateRaw($sql);

        if ( !empty($d_kk) && $d_kk->count() > 0 ) {
            $data = $d_kk->toArray();
        }

		return $data;
	}
}
