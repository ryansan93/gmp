<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class JurnalTrans_model extends Conf{
    // public $incrementing = false;

    protected $table = 'jurnal_trans';
    protected $primaryKey = 'id';
    protected $kodeTable = 'TRJ';

    public function getNextId(){
        $id = $this->whereRaw("SUBSTRING(kode,4,4) = cast(right(year(current_timestamp),2) as char(2))+replace(str(month(getdate()),2),' ',0)")
                    ->selectRaw("'".$this->kodeTable."'+right(year(current_timestamp),2)+replace(str(month(getdate()),2),' ',0)+replace(str(substring(coalesce(max(kode),'000'),8,3)+1,3), ' ', '0') as nextId")
                    ->first();
        return $id->nextId;
    }

    public function fitur()
    {
      	return $this->hasMany('\Model\Storage\JurnalTransFitur_model', 'id_header', 'id');
    }

    public function detail()
    {
      	return $this->hasMany('\Model\Storage\DetJurnalTrans_model', 'id_header', 'id');
    }

    public function sumber_tujuan()
    {
      	return $this->hasMany('\Model\Storage\JurnalTransSumberTujuan_model', 'id_header', 'id');
    }

    public function getJurnalTransByUrl($url) {
        $sql = "
            select jt.* from
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
            left join
                detail_fitur df
                on
                    jt.det_fitur_id = df.id_detfitur
            where
                df.path_detfitur = '".substr($url, 1)."'
            order by
                jt.nama asc
        ";
        $d_jt = $this->hydrateRaw( $sql );

        $data = null;
        if ( $d_jt->count() > 0 ) {
            $data = $d_jt->toArray();
        }

        return $data;
    }
}
