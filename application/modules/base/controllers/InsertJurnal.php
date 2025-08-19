<?php

defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );
class InsertJurnal extends Public_Controller {

  /**
  * Constructor
  */
  function __construct() {
    parent::__construct ();
  }

  public function exec($url, $id, $id_old = null, $action, $table = null) {
    try {
        $id_saj = null;
        $query = null;

        $sql_table = null;
        if ( !empty($table) ) {
            $sql_table = "and saj.tbl_name = '".$table."'";
        }

        $m_saj = new \Model\Storage\Conf();
        $sql = "
            select
                saj.id,
                saj._query,
                saj.tbl_name
            from setting_automatic_jurnal saj
            left join
                detail_fitur df
                on
                    saj.det_fitur_id = df.id_detfitur
            where
                df.path_detfitur = '".substr($url, 1)."'
                ".$sql_table."
        ";
        $d_saj = $m_saj->hydrateRaw( $sql );
        if ( $d_saj->count() > 0 ) {
            $d_saj = $d_saj->toArray()[0];

            $id_saj = $d_saj['id'];
            $query = $d_saj['_query'];
            $tbl_name = $d_saj['tbl_name'];

            $m_conf = new \Model\Storage\Conf();
            $sql = "
                SET NOCOUNT ON

                /* DEFAULT */
                DECLARE @tbl_id varchar(100), @tbl_id_old varchar(100), @action int, @tbl_name varchar(100)
        
                SET @tbl_id = '".$id."'
                SET @tbl_id_old = '".$id_old."'
                SET @action = ".$action."
                SET @tbl_name = '".$tbl_name."'
        
                DECLARE @id_saj int = ".$id_saj."
                /* END - DEFAULT */
                
                /* DEFAULT */
                /* INSERT, UPDATE, DELETE JURNAL */
                DECLARE @id_header int
                IF ( @action < 3 )
                BEGIN 
                    ".$query."

                    IF ( @action = 1 )
                    BEGIN 
                        /* INSERT */
                        insert into jurnal (tanggal, unit, jurnal_trans_id)
                        select top 1 GETDATE() as tanggal, @unit as unit, jurnal_trans_id from mapping_jurnal_trans where tbl_name = @tbl_name 
                        
                        SET @id_header = cast( SCOPE_IDENTITY() as int )
                        
                        insert into det_jurnal (id_header, tanggal, det_jurnal_trans_id, jurnal_trans_sumber_tujuan_id, supplier, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, gudang)
                        select @id_header as id_header, tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, gudang from mapping_jurnal_trans where tbl_name = @tbl_name
                    END
                    ELSE 
                    BEGIN 		
                        /* UPDATE */
                        select top 1
                            @id_header = cast(id_header as int)
                        from det_jurnal 
                        where 
                            tbl_name = @tbl_name and 
                            tbl_id = @tbl_id_old
                        group by
                            id_header
                            
                        delete from det_jurnal where tbl_name = @tbl_name and tbl_id = @tbl_id_old
                        delete from jurnal where id = @id_header
                        
                        IF ( EXISTS (select * from mapping_jurnal_trans where tbl_name = @tbl_name) )
                        BEGIN 
                            insert into jurnal (tanggal, unit, jurnal_trans_id)
                            select top 1 GETDATE() as tanggal, @unit as unit, jurnal_trans_id from mapping_jurnal_trans where tbl_name = @tbl_name 
                            
                            SET @id_header = cast( SCOPE_IDENTITY() as int )
                            
                            insert into det_jurnal (id_header, tanggal, det_jurnal_trans_id, jurnal_trans_sumber_tujuan_id, supplier, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, gudang)
                            select @id_header as id_header, tgl_trans, det_jurnal_trans_id, jurnal_trans_id, supplier, perusahaan, keterangan, nominal, saldo, asal, coa_asal, tujuan, coa_tujuan, unit, tbl_name, tbl_id, noreg, kode_trans, kode_jurnal, no_bukti, gudang from mapping_jurnal_trans where tbl_name = @tbl_name
                        END
                    END

                    delete mapping_jurnal_trans where tbl_name = @tbl_name and tbl_id = @tbl_id
        
                    select * from det_jurnal
                    where
                        tbl_name = @tbl_name and
                        tbl_id = @tbl_id
                END
                ELSE 
                BEGIN 
                    /* DELETE */
                    select
                        @id_header = cast(id_header as int)
                    from det_jurnal 
                    where 
                        tbl_name = @tbl_name and 
                        tbl_id = @tbl_id_old
                        
                    delete from det_jurnal where tbl_name = @tbl_name and tbl_id = @tbl_id_old
                    delete from jurnal where id = @id_header

                    select 'berhasil di hapus'
                END
                /* END - INSERT, UPDATE, DELETE JURNAL */
                /* END - DEFAULT */
            ";
            // cetak_r( $sql, 1 );
            $d_conf = $m_conf->hydrateRaw( $sql );
    
            $result['status'] = 1;
        } else {
            $result['message'] = 'Setting automatic jurnal belum tersedia, harap hubungi tim IT.';
        }
    } catch (Exception $e) {
        $result['message'] = $e->getMessage();
    }

    return $result;
  }
}