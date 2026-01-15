<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class Wilayah_model extends Conf{
  protected $table = 'wilayah';
  protected $primaryKey = 'id';

  public function scopePerwakilan($query)
  {
    return $query->where('jenis','PW');
  }
  public function unit()
  {
    return $this->hasMany('\Model\Storage\Wilayah_model','induk', 'id');
  }

  public function mitra_mapping()
  {
    return $this->hasMany('\Model\Storage\MitraMapping_model','perwakilan', 'id')->with(['dMitra']);
  }

  public function hitung_budidaya()
  {
    return $this->hasMany('\Model\Storage\HitungBudidaya_model','perwakilan', 'id')->where('g_status', 3)->with(['details']);
  }

  public function dPerwakilanMapping()
  {
    return $this->hasMany('\Model\Storage\PerwakilanMaping_model','id_pwk', 'id')->with(['dHitungBudidayaItem']);
  }

  public function getDataUnit($byUser = 0, $userId = null) {
    $sql_unit = null;

    if ( $byUser == 1 ) {
      $sql_det_user = "
        select * from detail_user where id_user = '".$userId."'
      ";
      $d_det_user = $this->hydrateRaw( $sql_det_user );
      
      if ( $d_det_user->count() > 0 ) {
        $d_det_user = $d_det_user->toArray()[0];
  
        $m_karyawan = new \Model\Storage\Karyawan_model();
        $d_karyawan = $m_karyawan->where('nama', 'like', strtolower(trim($d_det_user['nama_detuser'])).'%')->orderBy('id', 'desc')->first();

        $sql_karyawan = "
          select * from karyawan where nama like '".strtolower(trim(str_replace("'", "''", $d_det_user['nama_detuser'])))."%' order by id desc
        ";
        $d_karyawan = $this->hydrateRaw( $sql_karyawan );
  
        if ( $d_karyawan->count() > 0 ) {
          $d_karyawan = $d_karyawan->toArray()[0];

          $m_conf = new \Model\Storage\Conf();
          $sql = "
              select
                  uk.unit
              from unit_karyawan uk
              where 
                  uk.id_karyawan = '".$d_karyawan['id']."' and
                  uk.unit like '%all%'
          ";
          $d_conf = $m_conf->hydrateRaw( $sql );
  
          if ( $d_conf->count() == 0 ) {
            $kode_unit = array();
  
            $m_conf = new \Model\Storage\Conf();
            $sql = "
              select
                  w.kode
              from unit_karyawan uk
              left join
                  wilayah w
                  on
                      uk.unit = w.id
              where 
                  uk.id_karyawan = '".$d_karyawan['id']."'
              group by
                  w.kode
            ";
            $d_uk = $m_conf->hydrateRaw( $sql );
  
            if ( $d_uk->count() > 0 ) {
              $d_uk = $d_uk->toArray();
  
              foreach ($d_uk as $k_uk => $v_uk) {
                $kode_unit[] = $v_uk['kode'];
              }
  
              $sql_unit = "and w1.kode in ('".implode("', '", $kode_unit)."')";
            }
          }
        }
      }
    }

    $sql = "
      select *
      from
      (
        select UPPER(REPLACE(REPLACE(w1.nama, 'Kota ', ''), 'Kab ', '')) as nama, w1.kode from wilayah w1
        right join
          (select max(id) as id, kode from wilayah group by kode) w2
          on
            w1.id = w2.id
        where
          w1.kode is not null
          ".$sql_unit."
      ) data
      group by
        data.nama,
        data.kode
      order by
        data.nama asc
    ";
    $d_conf = $this->hydrateRaw( $sql );

    $data = null;
    if ( $d_conf->count() > 0 ) {
      $data = $d_conf->toArray();
    }

    return $data;
  }
}
