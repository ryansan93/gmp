<?php
namespace Model\Storage;
use \Model\Storage\Conf as Conf;

class CnPostDet_model extends Conf {
	protected $table = 'cn_post_det';
    public $timestamps = false;

    public function getData( $id = null ) {
		$sql_condition = null;
		if ( !empty($id) ) {
			if ( !empty($sql_condition) ) {
				$sql_condition .= " and cp.id = ".$id."";
			} else {
				$sql_condition = "where cp.id = ".$id."";
			}
		}

		$sql = "
			select 
                cpd.*,
                REPLACE(sj.tgl_sj, '-', '/')+' | '+sj.no_sj as no_sj,
                sj.tagihan,
                sum(isnull(_cpd.tot_pakai, 0)) as tot_pakai,
                sum(isnull(rpd.tot_tf, 0)) as tot_tf,
                (sj.tagihan - (sum(isnull(_cpd.tot_pakai, 0)) + sum(isnull(rpd.tot_tf, 0)))) as sisa
            from cn_post_det cpd
            left join
                cn_post cp
                on
                    cpd.id_header = cp.id
            left join
                (
                    select
                        sj.id as id,
                        sj.total as tagihan,
                        sj.no_sj,
                        sj.tgl_sj
                    from (
                        select
                            kpdd.tgl_order as tgl_sj,
                            kpdd.no_order, 
                            td.no_sj, 
                            kpdd.total, 
                            'DOC' as jenis_cn,
                            kpd.nomor as id
                        from konfirmasi_pembayaran_doc_det kpdd
                        left join
                            konfirmasi_pembayaran_doc kpd 
                            on
                                kpdd.id_header = kpd.id
                        left join
                            (
                                select td1.* from terima_doc td1
                                right join
                                    (select max(id) as id, no_order from terima_doc group by no_order) td2
                                    on
                                        td1.id = td2.id
                            ) td
                            on
                                td.no_order = kpdd.no_order
                
                        union all
                
                        select
                            kppd.tgl_sj,
                            kppd.no_order, 
                            kppd.no_sj, 
                            kppd.total, 
                            'PKN' as jenis_cn,
                            kpp.nomor as id
                        from konfirmasi_pembayaran_pakan_det kppd 
                        left join
                            konfirmasi_pembayaran_pakan kpp 
                            on
                                kppd.id_header = kpp.id
                    ) sj
                ) sj
                on
                    sj.id = cpd.nomor
            left join
                (
                    select 
                        cp.id, 
                        cp.tanggal, 
                        cpd.nomor, 
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
                        cpd.nomor
                ) _cpd
                on
                    cpd.nomor = _cpd.nomor and
                    _cpd.tanggal < cp.tanggal and
                    _cpd.id < cp.id
            left join
                (
                    select
                        rp.tgl_bayar as tanggal,
                        rpd.no_bayar as nomor, 
                        sum(rpd.transfer) as tot_tf 
                    from realisasi_pembayaran_det rpd
                    left join
                        realisasi_pembayaran rp 
                        on
                            rpd.id_header = rp.id
                    group by 
                        rp.tgl_bayar,
                        rpd.no_bayar
                ) rpd
                on
                    cpd.nomor = rpd.nomor and
                    rpd.tanggal < cp.tanggal
            ".$sql_condition."
            group by
                cpd.id_header,
                cpd.nomor,
                cpd.pakai,
                sj.tagihan,
                sj.no_sj,
                sj.tgl_sj
		";
		$d_cpd = $this->hydrateRaw( $sql );

		$data = null;
		if ( $d_cpd->count() > 0 ) {
			$data = $d_cpd->toArray();
		}

		return $data;
	}
}