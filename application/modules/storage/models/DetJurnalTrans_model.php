<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class DetJurnalTrans_model extends Conf{
    // public $incrementing = false;

    protected $table = 'det_jurnal_trans';
    protected $primaryKey = 'id';

    public function getNextIdDJT( $kode ){
        $id = $this->whereRaw("SUBSTRING(kode,0,".(strlen( $kode )+1).") = '".$kode."'")
                    ->selectRaw("'".$kode."'+'-'+replace(str(substring(coalesce(max(kode),'000'),12,3)+1,3), ' ', '0') as nextId")
                    ->first();
        return $id->nextId;
    }

    public function jurnal_trans()
    {
      	return $this->hasOne('\Model\Storage\JurnalTrans_model', 'id', 'id_header');
    }

    public function getDetJurnalTransByUrl($url) {
        $sql = "
            select djt.* from det_jurnal_trans djt
            left join
                (
                    select jt1.* from
                    (
                        select jt.id, jt.nama, jt.mstatus, jt.unit, jt.kode, jt.kode_voucher, jt.jurnal_manual, jtf.det_fitur_id from jurnal_trans_fitur jtf
                        left join
                            jurnal_trans jt
                            on
                                jt.id = jtf.id_header
                        group by
                            jt.id, jt.nama, jt.mstatus, jt.unit, jt.kode, jt.kode_voucher, jt.jurnal_manual, jtf.det_fitur_id
                    ) jt1
                    right join
                        (select max(id) as id, kode from jurnal_trans group by kode) jt2
                        on
                            jt1.id = jt2.id
                    where
                        jt1.id is not null
                ) jt
                on
                    djt.id_header = jt.id
            left join
                detail_fitur df
                on
                    jt.det_fitur_id = df.id_detfitur
            where
                df.path_detfitur = '".substr($url, 1)."'
            order by
                jt.nama asc
        ";
        $d_djt = $this->hydrateRaw( $sql );

        $data = null;
        if ( $d_djt->count() > 0 ) {
            $data = $d_djt->toArray();
        }

        return $data;
    }
}
