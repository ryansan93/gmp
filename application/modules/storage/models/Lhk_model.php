<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class Lhk_model extends Conf {
	protected $table = 'lhk';

    public function lhk_sekat()
	{
		return $this->hasMany('\Model\Storage\LhkSekat_model', 'id_header', 'id');
	}

	public function lhk_nekropsi()
	{
		return $this->hasMany('\Model\Storage\LhkNekropsi_model', 'id_header', 'id')->with(['d_nekropsi', 'foto_nekropsi']);
	}

	public function lhk_solusi()
	{
		return $this->hasMany('\Model\Storage\LhkSolusi_model', 'id_header', 'id')->with(['d_solusi']);
	}

	public function foto_sisa_pakan()
	{
		return $this->hasMany('\Model\Storage\LhkFotoSisaPakan_model', 'id_header', 'id');
	}

	public function foto_ekor_mati()
	{
		return $this->hasMany('\Model\Storage\LhkFotoEkorMati_model', 'id_header', 'id');
	}

	public function getDataAck($nik, $status) {
		$sql_kry_unit = "
			select ku.unit as id_unit, w.kode as kode_unit from unit_karyawan ku
			left join
				(
					select k1.* from karyawan k1
					right join
						(select max(id) as id, nik from karyawan group by nik) k2
						on
							k1.id = k2.id
				) k
				on
					ku.id_karyawan = k.id
			left join
				wilayah w
				on
					cast(w.id as varchar(5)) = ku.unit
			where
				k.nik = '".$nik."'
		";
		$d_kry_unit = $this->hydrateRaw( $sql_kry_unit );

		$sql_unit = null;
		if ( $d_kry_unit->count() > 0 ) {
			$d_kry_unit = $d_kry_unit->toArray();

			$unit = null;
			if ( stristr($d_kry_unit[0]['id_unit'], 'all') === false ) {
				foreach ($d_kry_unit as $k_kry_unit => $v_kry_unit) {
					$unit[ $v_kry_unit['kode_unit'] ] = $v_kry_unit['kode_unit'];
				}
				
				$sql_unit = "and w.kode in ('".implode("', '", $unit)."')";
			}
		}

		$sql_lhk = "
			select
				data.*,
				data.terima_pakan - data.konsumsi_pakan as sisa_pakan
			from
			(
				select 
					l.*,
					k.kandang as no_kdg,
					mtr.nama as nama_mitra,
					kry.nama as nama_karyawan,
					l.pakai_pakan as konsumsi_pakan_zak,
					l.pakai_pakan * 50 as konsumsi_pakan,
					-- (select sum(pakai_pakan) as konsumsi_pakan from lhk where noreg = l.noreg and umur <= l.umur) as konsumsi_pakan_zak,
					-- (select sum(pakai_pakan) as konsumsi_pakan from lhk where noreg = l.noreg and umur <= l.umur) * 50 as konsumsi_pakan,
					(
						select
							sum(dtp.jumlah) as tot_kirim_pakan
						from det_terima_pakan dtp
						left join
							terima_pakan tp
							on
								dtp.id_header = tp.id
						left join
							kirim_pakan kp
							on
								kp.id = tp.id_kirim_pakan
						where
							tp.tgl_terima <= l.tanggal and
							kp.tujuan = l.noreg
					) as terima_pakan,
					mp.lat_long as lat_long_mitra
				from lhk l
				left join
					rdim_submit rs
					on
						l.noreg = rs.noreg
				left join
					kandang k
					on
						rs.kandang = k.id
				left join
					wilayah w
					on
						w.id = k.unit
				left join
					mitra_mapping mm
					on
						k.mitra_mapping = mm.id
				left join
					(
						select k1.* from karyawan k1
						right join
							(select max(id) as id, nik from karyawan group by nik) k2
							on
								k1.id = k2.id
					) kry
					on
						l.nik = kry.nik
				left join
					(
						select mtr1.* from mitra mtr1
						right join
							( select max(id) as id, nomor from mitra group by nomor ) mtr2
							on
								mtr1.id = mtr2.id
					) mtr
					on
						mm.nomor = mtr.nomor
				left join
					(
						select mp1.* from mitra_posisi mp1
						right join
							( select max(id) as id, nomor from mitra_posisi group by nomor ) mp2
							on
								mp1.id = mp2.id
					) mp
					on
						mp.nomor = mtr.nomor
				where
					mtr.nama is not null and
					l.noreg is not null and
					l.status = ".$status."
					".$sql_unit."
			) data
		";
		$d_lhk = $this->hydrateRaw( $sql_lhk );

		$data = null;
        if ( $d_lhk->count() ) {
            $d_lhk = $d_lhk->toArray();

			$json_kematian = null;
			$json_sisa_pakan = null;

			$idx = 0;
			foreach ($d_lhk as $key => $value) {
				$sql_kematian = "
					select * from lhk_foto_ekor_mati where id_header = ".$value['id']."
				";
				$d_kematian = $this->hydrateRaw( $sql_kematian );
				if ( $d_kematian->count() > 0 ) {
					$d_kematian = $d_kematian->toArray();

					$_url = array();
					foreach ($d_kematian as $k_fn => $v_fn) {
						array_push($_url, $v_fn['path']);
					}

					$json_kematian = json_encode($_url, JSON_FORCE_OBJECT);
				}

				$sql_sisa_pakan = "
					select * from lhk_foto_sisa_pakan where id_header = ".$value['id']."
				";
				$d_sisa_pakan = $this->hydrateRaw( $sql_sisa_pakan );
				if ( $d_sisa_pakan->count() > 0 ) {
					$d_sisa_pakan = $d_sisa_pakan->toArray();

					$_url = array();
					foreach ($d_sisa_pakan as $k_fn => $v_fn) {
						array_push($_url, $v_fn['path']);
					}

					$json_sisa_pakan = json_encode($_url, JSON_FORCE_OBJECT);
				}

				$data[ $idx ] = $value;
				$data[ $idx ]['json_kematian'] = $json_kematian;
				$data[ $idx ]['json_sisa_pakan'] = $json_sisa_pakan;

				$idx++;
			}
        }

        return $data;
	}

	public function notifData($nik, $status) {
		$d_lhk = $this->getDataAck($nik, $status);

		$data = null;
        if ( !empty($d_lhk) ) {
			$keterangan = null;
			foreach ($d_lhk as $key => $value) {
				if ( !empty( $keterangan ) ) {
					$keterangan .= '<br>';
				}

				$keterangan .= strtoupper( $value['nama_mitra'].' (KDG : '.$value['no_kdg'].')'.' | '.'UMUR : '.$value['umur'] );
			}

			$data[] = array(
				'deskripsi' => null,
				'waktu' => null,
				'gstatus' => $value['status'],
				'nama_status' => getStatus($value['status']),
				'nama_user' => null,
				'keterangan' => $keterangan,
				'key' => exEncrypt(json_encode(array('nik' => $nik, 'status' => $status, 'url_akses' => '/transaksi/LHK')))
			);
        }

        return $data;
	}
}