<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class Km_model extends Conf{
	public $table = 'km';
	protected $primaryKey = 'no_km';
	public $timestamps = false;

	public function getKode($kode){
		$id = $this->whereRaw("SUBSTRING(".$this->primaryKey.", 0, ".((strlen($kode)+1)+6).") = '".$kode."'+cast(right(year(current_timestamp),2) as char(2))+replace(str(month(getdate()),2),' ',0)+replace(str(day(getdate()),2),' ',0)")
								->selectRaw("'".$kode."'+right(year(current_timestamp),2)+replace(str(month(getdate()),2),' ',0)+replace(str(day(getdate()),2),' ',0)+replace(str(substring(coalesce(max(".$this->primaryKey."),'0000'), ".((strlen($kode)+1)+6).", 4)+1, 4), ' ', '0') as nextId")
								->first();
		return $id->nextId;
	}

	public function getKm($id = null, $column = 'no_km') {
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
			from km k
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
				k.tgl_km desc,
				k.no_km desc
		";
		$d_km = $this->hydrateRaw($sql);

        if ( !empty($d_km) && $d_km->count() > 0 ) {
            $data = $d_km->toArray();
        }

		return $data;
	}

	public function getBmByDate($start_date, $end_date, $bank) {
		$data = null;

		$sql = "
			select 
				k.*
			from km k
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
				k.tgl_km between '".$start_date."' and '".$end_date."' and
				k.coa_bank = '".$bank."' and
                c.bank = 1
			order by
				k.tgl_km desc,
				k.no_km desc
		";
		$d_km = $this->hydrateRaw($sql);

        if ( !empty($d_km) && $d_km->count() > 0 ) {
            $data = $d_km->toArray();
        }

		return $data;
	}

	public function getKmByDate($start_date, $end_date, $bank) {
		$data = null;

		$sql = "
			select 
				k.*,
				m.nama as nama_mitra
			from km k
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
				k.tgl_km between '".$start_date."' and '".$end_date."' and
				k.coa_bank = '".$bank."' and
                c.kas = 1
			order by
				k.tgl_km desc,
				k.no_km desc
		";
		$d_km = $this->hydrateRaw($sql);

        if ( !empty($d_km) && $d_km->count() > 0 ) {
            $data = $d_km->toArray();
        }

		return $data;
	}

	public function getDataLaporanKm($start_date, $end_date, $customer, $urut, $kode) {
		$data = null;
		$params = null;
		
		$grand_total = 0;

		$sql_customer = null;
		if ( !in_array('all', $customer) ) {
			$sql_customer = "and k.kode_cust in ('".implode("', '", $customer)."')";
		}

		$sql = "
			select 
				k.tgl_km,
				k.no_km,
				k.kode_cust,
				cust.nama_cust,
				k.no_coa as no_coa_header,
				k.nama_bank,
				k.no_giro,
				k.tgl_cair,
				k.keterangan,
				k.nilai as grand_total,
				ki.no_km,
				ki.tgl_km,
				ki.no_urut,
				ki.no_coa,
				c.nama_coa,
				ki.keterangan,
				ki.no_faktur,
				ki.nilai_faktur,
				ki.nilai
			from kmitem ki
			left join
				km k
				on
					ki.no_km = k.no_km
			left join
				customer cust
				on
					k.kode_cust = cust.kode_cust
			left join
				coa c
				on
					ki.no_coa = c.no_coa
			where
				k.no_km like '%".$kode."%' and
				k.tgl_km between '".$start_date."' and '".$end_date."'
				".$sql_customer."
		";
		$d_km = $this->hydrateRaw($sql);

        if ( !empty($d_km) && $d_km->count() > 0 ) {
            $d_km = $d_km->toArray();

			foreach ($d_km as $k_km => $v_km) {
				$key = null;
				$key_detail = null;
				if ( stristr($urut, 'tanggal') !== false ) {
					$key_tgl = str_replace('-', '', substr($v_km['tgl_km'], 0, 10));
					$key_km = $v_km['no_km'];
					$key_detail = $v_km['keterangan'].'-'.$v_km['no_urut'].'-'.$v_km['no_faktur'];

					if ( !isset( $data[ $key_tgl ] ) ) {
						$data[ $key_tgl ]['tgl_km'] = $v_km['tgl_km'];
						$data[ $key_tgl ]['grand_total'] = $v_km['grand_total'];

						$grand_total += $v_km['grand_total'];

						$data[ $key_tgl ]['km'][ $key_km ] = array(
							'tgl_km' => $v_km['tgl_km'],
							'no_km' => $v_km['no_km'],
							'kode_cust' => $v_km['kode_cust'],
							'nama_cust' => $v_km['nama_cust'],
							'no_coa' => $v_km['no_coa'],
							'nama_bank' => $v_km['nama_bank'],
							'no_giro' => $v_km['no_giro'],
							'tgl_cair' => $v_km['tgl_cair'],
							'keterangan' => $v_km['keterangan'],
							'grand_total' => $v_km['grand_total']
						);
	
						$data[ $key_tgl ]['km'][ $key_km ]['detail'][ $key_detail ] = array(
							'no_urut' => $v_km['no_urut'],
							'no_coa' => $v_km['no_coa'],
							'nama_coa' => $v_km['nama_coa'],
							'keterangan' => $v_km['keterangan'],
							'no_faktur' => $v_km['no_faktur'],
							'nilai_faktur' => $v_km['nilai_faktur'],
							'nilai' => $v_km['nilai']
						);
					} else {
						if ( !isset($data[ $key_tgl ]['km'][ $key_km ]) ) {
							$data[ $key_tgl ]['grand_total'] += $v_km['grand_total'];

							$grand_total += $v_km['grand_total'];

							$data[ $key_tgl ]['km'][ $key_km ] = array(
								'tgl_km' => $v_km['tgl_km'],
								'no_km' => $v_km['no_km'],
								'kode_cust' => $v_km['kode_cust'],
								'nama_cust' => $v_km['nama_cust'],
								'no_coa' => $v_km['no_coa'],
								'nama_bank' => $v_km['nama_bank'],
								'no_giro' => $v_km['no_giro'],
								'tgl_cair' => $v_km['tgl_cair'],
								'keterangan' => $v_km['keterangan'],
								'grand_total' => $v_km['grand_total']
							);
						}

						$data[ $key_tgl ]['km'][ $key_km ]['detail'][ $key_detail ] = array(
							'no_urut' => $v_km['no_urut'],
							'no_coa' => $v_km['no_coa'],
							'nama_coa' => $v_km['nama_coa'],
							'keterangan' => $v_km['keterangan'],
							'no_faktur' => $v_km['no_faktur'],
							'nilai_faktur' => $v_km['nilai_faktur'],
							'nilai' => $v_km['nilai']
						);
					}
	
					ksort( $data[ $key_tgl ]['km'][ $key_km ]['detail'] );
					ksort( $data[ $key_tgl ]['km'] );
					ksort( $data );

					$params = array(
						'data' => $data,
						'grand_total' => array(
							'grand_total' => $grand_total
						)
					);
				} else if ( stristr($urut, 'customer') !== false ) {
					$key_cust = $v_km['nama_cust'].' | '.$v_km['kode_cust'];
					$key_km = $v_km['no_km'];
					$key_detail = $v_km['keterangan'].'-'.$v_km['no_urut'].'-'.$v_km['no_faktur'];

					if ( !isset( $data[ $key_cust ] ) ) {
						$data[ $key_cust ]['kode_cust'] = $v_km['kode_cust'];
						$data[ $key_cust ]['nama_cust'] = $v_km['nama_cust'];
						$data[ $key_cust ]['grand_total'] = $v_km['grand_total'];

						$grand_total += $v_km['grand_total'];

						$data[ $key_cust ]['km'][ $key_km ] = array(
							'tgl_km' => $v_km['tgl_km'],
							'no_km' => $v_km['no_km'],
							'kode_cust' => $v_km['kode_cust'],
							'nama_cust' => $v_km['nama_cust'],
							'no_coa' => $v_km['no_coa'],
							'nama_bank' => $v_km['nama_bank'],
							'no_giro' => $v_km['no_giro'],
							'tgl_cair' => $v_km['tgl_cair'],
							'keterangan' => $v_km['keterangan'],
							'grand_total' => $v_km['grand_total']
						);
	
						$data[ $key_cust ]['km'][ $key_km ]['detail'][ $key_detail ] = array(
							'no_urut' => $v_km['no_urut'],
							'no_coa' => $v_km['no_coa'],
							'nama_coa' => $v_km['nama_coa'],
							'keterangan' => $v_km['keterangan'],
							'no_faktur' => $v_km['no_faktur'],
							'nilai_faktur' => $v_km['nilai_faktur'],
							'nilai' => $v_km['nilai']
						);
					} else {
						if ( !isset($data[ $key_cust ]['km'][ $key_km ]) ) {
							$data[ $key_cust ]['grand_total'] += $v_km['grand_total'];

							$grand_total += $v_km['grand_total'];

							$data[ $key_cust ]['km'][ $key_km ] = array(
								'tgl_km' => $v_km['tgl_km'],
								'no_km' => $v_km['no_km'],
								'kode_cust' => $v_km['kode_cust'],
								'nama_cust' => $v_km['nama_cust'],
								'no_coa' => $v_km['no_coa'],
								'nama_bank' => $v_km['nama_bank'],
								'no_giro' => $v_km['no_giro'],
								'tgl_cair' => $v_km['tgl_cair'],
								'keterangan' => $v_km['keterangan'],
								'grand_total' => $v_km['grand_total']
							);
						}

						$data[ $key_cust ]['km'][ $key_km ]['detail'][ $key_detail ] = array(
							'no_urut' => $v_km['no_urut'],
							'no_coa' => $v_km['no_coa'],
							'nama_coa' => $v_km['nama_coa'],
							'keterangan' => $v_km['keterangan'],
							'no_faktur' => $v_km['no_faktur'],
							'nilai_faktur' => $v_km['nilai_faktur'],
							'nilai' => $v_km['nilai']
						);
					}
	
					ksort( $data[ $key_cust ]['km'][ $key_km ]['detail'] );
					ksort( $data[ $key_cust ]['km'] );
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

	public function getKmCetak($id = null, $column = 'no_km') {
		$data = null;
        
        $sql_id = "";
        if ( !empty($id) ) {
            $sql_id = "where k.".$column." = '".$id."'";
        }

		$sql = "
			select 
				k.*,
				c_header.nama_coa,
				cust.nama_cust,
				ki.no_urut,
				ki.no_coa as no_coa_item,
				c_item.nama_coa as nama_coa_item,
				-- ki.keterangan,
				case
					when ki.no_faktur is not null and ki.no_faktur <> '' then
						case
							when ki.keterangan is null or ki.keterangan = '' then
								'PELUNASAN PIUTANG A.N '+cust.nama_cust+' / '+ki.no_faktur
							else
								ki.keterangan
						end
					else
						ki.keterangan
				end as keterangan,
				ki.no_faktur,
				ki.nilai_faktur,
				isnull(ki.nilai , 0) as nilai
			from kmitem ki
			left join
				km k
				on
					k.no_km = ki.no_km
            left join
				coa c_header
				on
					k.no_coa = c_header.no_coa
			left join
				coa c_item
				on
					ki.no_coa = c_item.no_coa
			left join
				customer cust
				on
					k.kode_cust = cust.kode_cust
			".$sql_id."
			order by
				k.tgl_km desc,
				k.no_km desc
		";
		$d_km = $this->hydrateRaw($sql);

        if ( !empty($d_km) && $d_km->count() > 0 ) {
            $data = $d_km->toArray();
        }

		return $data;
	}
}
