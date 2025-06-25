<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet as Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border as Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat as NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as Date;

class RekapDataRhpp extends Public_Controller {

    private $pathView = 'report/rekap_data_rhpp/';
    private $url;
    private $akses;

    function __construct()
    {
        parent::__construct();
        $this->url = $this->current_base_uri;
        $this->akses = hakAkses($this->url);
    }

    /**************************************************************************************
     * PUBLIC FUNCTIONS
     **************************************************************************************/
    /**
     * Default
     */
    public function index()
    {
        $akses = $this->akses;
        if ( $akses['a_view'] == 1 ) {
            $this->add_external_js(array(
                'assets/select2/js/select2.min.js',
                "assets/report/rekap_data_rhpp/js/rekap-data-rhpp.js",
            ));
            $this->add_external_css(array(
                'assets/select2/css/select2.min.css',
                "assets/report/rekap_data_rhpp/css/rekap-data-rhpp.css",
            ));

            $data = $this->includes;

            $content['akses'] = $akses;
            $content['perusahaan'] = $this->getPerusahaan();
            $content['unit'] = $this->getUnit();
            $content['title_menu'] = 'Rekap Data Rhpp';

            // Load Indexx
            $data['view'] = $this->load->view($this->pathView.'index', $content, TRUE);
            $this->load->view($this->template, $data);
        } else {
            showErrorAkses();
        }
    }

    public function getUnit()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select
                REPLACE(REPLACE(w.nama, 'Kota ', ''), 'Kab ', '') as nama,
                w.kode
            from wilayah w
            where
                w.jenis = 'UN'
            group by
                REPLACE(REPLACE(w.nama, 'Kota ', ''), 'Kab ', ''),
                w.kode
            order by
                REPLACE(REPLACE(w.nama, 'Kota ', ''), 'Kab ', '') asc
        ";
        $d_unit = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_unit->count() > 0 ) {
            $data = $d_unit->toArray();
        }

        return $data;
    }

    public function getPerusahaan()
    {
        $m_conf = new \Model\Storage\Conf();
        $sql = "
            select p1.* from perusahaan p1
            right join
                (select max(id) as id, kode from perusahaan group by kode) p2
                on
                    p1.id = p2.id
        ";
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function getLists()
    {
        $params = $this->input->get('params');

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $perusahaan = $params['perusahaan'];
        $unit = $params['unit'];
        $jenis = $params['jenis'];

        $data = $this->getDataRekapRhpp($start_date, $end_date, $perusahaan, $unit, $jenis);

        $content['data'] = $data;
        $html = $this->load->view($this->pathView.'list', $content, TRUE);

        echo $html;
    }

    public function getDataRekapRhpp($start_date, $end_date, $perusahaan, $unit, $jenis) {
        $sql_perusahaan = "";
        if ( !in_array('all', $perusahaan) ) {
            $sql_perusahaan = "and d_rhpp.kode_perusahaan in ('".implode("', '", $perusahaan)."')";
        }

        $sql_unit = "";
        if ( !in_array('all', $unit) ) {
            $sql_unit = "and d_distribusi.unit in ('".implode("', '", $unit)."')";
        }

        $sql_jenis = "";
        if ( $jenis != 'all' ) {
            $sql_jenis = "and d_distribusi.jenis_trans = '".$jenis."'";
        }

        $m_conf = new \Model\Storage\Conf();
        $sql = "
            SET NOCOUNT ON 

            DECLARE @oa_id int, @oa_id_header int, @oa_tanggal date, @oa_nota varchar(20), @oa_nopol varchar(100), @oa_barang varchar(100), @oa_zak int, @oa_jumlah decimal(7, 2), @oa_harga decimal(9, 2), @oa_total decimal(15, 2)
            DECLARE @id_header int, @tanggal date, @nota varchar(20), @barang varchar(100), @zak int, @jumlah decimal(7, 2), @harga decimal(9, 2), @total decimal(15, 2)
            DECLARE @jumlah_simpan decimal(7, 2)

            /* NON GROUP */
            if OBJECT_ID('tempdb..#tmpRhppOaPakan') is not null
            BEGIN
                drop table #tmpRhppOaPakan
            END
            create table #tmpRhppOaPakan (
                id int,
                id_header int,
                tanggal date,
                nota varchar(20),
                nopol varchar(100),
                barang varchar(100),
                zak int,
                jumlah decimal(7, 2),
                harga decimal(9, 2),
                total decimal(15, 2)
            )

            if OBJECT_ID('tempdb..#tmpRhppPakan') is not null
            BEGIN
                drop table #tmpRhppPakan
            END
            create table #tmpRhppPakan (
                id_header int,
                tanggal date,
                nota varchar(20),
                barang varchar(100),
                zak int,
                jumlah decimal(7, 2),
                harga decimal(9, 2),
                total decimal(15, 2)
            )

            --if OBJECT_ID('tempdb..#tmpDataRhppPakan') is not null
            --BEGIN
            --    drop table #tmpDataRhppPakan
            --END
            IF (EXISTS (SELECT * 
                            FROM INFORMATION_SCHEMA.TABLES 
                            WHERE TABLE_SCHEMA = 'dbo' 
                            AND  TABLE_NAME = 'tmpDataRhppPakan'))
            BEGIN
                DROP TABLE tmpDataRhppPakan
            END
            create table tmpDataRhppPakan (
                id_header int,
                tanggal date,
                nota varchar(20),
                barang varchar(100),
                zak int,
                jumlah decimal(7, 2),
                harga decimal(9, 2),
                harga_oa decimal(9, 2),
                total decimal(15, 2)
            )

            if OBJECT_ID('tempdb..#tmpRhppOaPindahPakan') is not null
            BEGIN
                drop table #tmpRhppOaPindahPakan
            END
            create table #tmpRhppOaPindahPakan (
                id int,
                id_header int,
                tanggal date,
                nota varchar(20),
                nopol varchar(100),
                barang varchar(100),
                zak int,
                jumlah decimal(7, 2),
                harga decimal(9, 2),
                total decimal(15, 2)
            )

            if OBJECT_ID('tempdb..#tmpRhppPindahPakan') is not null
            BEGIN
                drop table #tmpRhppPindahPakan
            END
            create table #tmpRhppPindahPakan (
                id_header int,
                tanggal date,
                nota varchar(20),
                barang varchar(100),
                zak int,
                jumlah decimal(7, 2),
                harga decimal(9, 2),
                total decimal(15, 2)
            )

            --if OBJECT_ID('tempdb..#tmpDataRhppPindahPakan') is not null
            --BEGIN
            --    drop table #tmpDataRhppPindahPakan
            --END
            IF (EXISTS (SELECT * 
                            FROM INFORMATION_SCHEMA.TABLES 
                            WHERE TABLE_SCHEMA = 'dbo' 
                            AND  TABLE_NAME = 'tmpDataRhppPindahPakan'))
            BEGIN
                DROP TABLE tmpDataRhppPindahPakan
            END
            create table tmpDataRhppPindahPakan (
                id_header int,
                tanggal date,
                nota varchar(20),
                barang varchar(100),
                zak int,
                jumlah decimal(7, 2),
                harga decimal(9, 2),
                harga_oa decimal(9, 2),
                total decimal(15, 2)
            )

            if OBJECT_ID('tempdb..#tmpRhppOaReturPakan') is not null
            BEGIN
                drop table #tmpRhppOaReturPakan
            END
            create table #tmpRhppOaReturPakan (
                id int,
                id_header int,
                tanggal date,
                nota varchar(20),
                nopol varchar(100),
                barang varchar(100),
                zak int,
                jumlah decimal(7, 2),
                harga decimal(9, 2),
                total decimal(15, 2)
            )

            if OBJECT_ID('tempdb..#tmpRhppReturPakan') is not null
            BEGIN
                drop table #tmpRhppReturPakan
            END
            create table #tmpRhppReturPakan (
                id_header int,
                tanggal date,
                nota varchar(20),
                barang varchar(100),
                zak int,
                jumlah decimal(7, 2),
                harga decimal(9, 2),
                total decimal(15, 2)
            )

            --if OBJECT_ID('tempdb..#tmpDataRhppReturPakan') is not null
            --BEGIN
            --    drop table #tmpDataRhppReturPakan
            --END
            IF (EXISTS (SELECT * 
                            FROM INFORMATION_SCHEMA.TABLES 
                            WHERE TABLE_SCHEMA = 'dbo' 
                            AND  TABLE_NAME = 'tmpDataRhppReturPakan'))
            BEGIN
                DROP TABLE tmpDataRhppReturPakan
            END
            create table tmpDataRhppReturPakan (
                id_header int,
                tanggal date,
                nota varchar(20),
                barang varchar(100),
                zak int,
                jumlah decimal(7, 2),
                harga decimal(9, 2),
                harga_oa decimal(9, 2),
                total decimal(15, 2)
            )

            insert into #tmpRhppOaPakan (id, id_header, tanggal, nota, nopol, barang, zak, jumlah, harga, total)
            select rop.id, rop.id_header, rop.tanggal, rop.nota, rop.nopol, rop.barang, rop.zak, rop.jumlah, rop.harga, rop.total from rhpp_oa_pakan rop
            left join
                rhpp r
                on
                    r.id = rop.id_header
            left join
                tutup_siklus ts
                on
                    r.id_ts = ts.id
            where
                r.jenis = 'rhpp_inti' and
                ts.tgl_tutup between '".$start_date."' and '".$end_date."' and
                not exists (select * from rhpp_group_noreg where noreg = r.noreg)
                
            insert into #tmpRhppPakan (id_header, tanggal, nota, barang, zak, jumlah, harga, total)
            select rp.id_header, rp.tanggal, rp.nota, rp.barang, rp.zak, rp.jumlah, rp.harga, rp.total from rhpp_pakan rp
            left join
                rhpp r
                on
                    r.id = rp.id_header
            left join
                tutup_siklus ts
                on
                    r.id_ts = ts.id
            where
                r.jenis = 'rhpp_inti' and
                ts.tgl_tutup between '".$start_date."' and '".$end_date."' and
                not exists (select * from rhpp_group_noreg where noreg = r.noreg)
                
            insert into #tmpRhppOaPindahPakan (id, id_header, tanggal, nota, nopol, barang, zak, jumlah, harga, total)
            select ropp.id, ropp.id_header, ropp.tanggal, ropp.nota, ropp.nopol, ropp.barang, ropp.zak, ropp.jumlah, ropp.harga, ropp.total from rhpp_oa_pindah_pakan ropp
            left join
                rhpp r
                on
                    r.id = ropp.id_header
            left join
                tutup_siklus ts
                on
                    r.id_ts = ts.id
            where
                r.jenis = 'rhpp_inti' and
                ts.tgl_tutup between '".$start_date."' and '".$end_date."' and
                not exists (select * from rhpp_group_noreg where noreg = r.noreg)
                
            insert into #tmpRhppPindahPakan (id_header, tanggal, nota, barang, zak, jumlah, harga, total)
            select rpp.id_header, rpp.tanggal, rpp.nota, rpp.barang, rpp.zak, rpp.jumlah, rpp.harga, rpp.total from rhpp_pindah_pakan rpp
            left join
                rhpp r
                on
                    r.id = rpp.id_header
            left join
                tutup_siklus ts
                on
                    r.id_ts = ts.id
            where
                r.jenis = 'rhpp_inti' and
                ts.tgl_tutup between '".$start_date."' and '".$end_date."' and
                not exists (select * from rhpp_group_noreg where noreg = r.noreg)
                
            insert into #tmpRhppOaReturPakan (id, id_header, tanggal, nota, nopol, barang, zak, jumlah, harga, total)
            select rorp.id, rorp.id_header, rorp.tanggal, rorp.nota, rorp.nopol, rorp.barang, rorp.zak, rorp.jumlah, rorp.harga, rorp.total from rhpp_oa_retur_pakan rorp
            left join
                rhpp r
                on
                    r.id = rorp.id_header
            left join
                tutup_siklus ts
                on
                    r.id_ts = ts.id
            where
                r.jenis = 'rhpp_inti' and
                ts.tgl_tutup between '".$start_date."' and '".$end_date."' and
                not exists (select * from rhpp_group_noreg where noreg = r.noreg)
                
            insert into #tmpRhppReturPakan (id_header, tanggal, nota, barang, zak, jumlah, harga, total)
            select rrp.id_header, rrp.tanggal, kp.no_sj as nota, rrp.barang, rrp.zak, rrp.jumlah, rrp.harga, rrp.total from rhpp_retur_pakan rrp
            left join
                retur_pakan rp
                on
                    rp.no_retur = rrp.nota
            left join
                kirim_pakan kp
                on
                    kp.no_order = rp.no_order
            left join
                rhpp r
                on
                    r.id = rrp.id_header
            left join
                tutup_siklus ts
                on
                    r.id_ts = ts.id
            where
                r.jenis = 'rhpp_inti' and
                ts.tgl_tutup between '".$start_date."' and '".$end_date."' and
                not exists (select * from rhpp_group_noreg where noreg = r.noreg)
                
            /* PAKAN */
            DECLARE pakan_cursor CURSOR LOCAL FOR
                select id_header, tanggal, nota, barang, zak, jumlah, harga, total from #tmpRhppPakan

            OPEN pakan_cursor
                
            FETCH NEXT FROM pakan_cursor INTO
                @id_header, @tanggal, @nota, @barang, @zak, @jumlah, @harga, @total
                
            WHILE @@FETCH_STATUS = 0
            BEGIN
                WHILE ( @jumlah > 0 )
                BEGIN
                    select top 1
                        @oa_id = cast(id as int),
                        @oa_id_header = cast(id_header as int),
                        @oa_tanggal = cast(tanggal as date),
                        @oa_nota = cast(nota as varchar(20)),
                        @oa_nopol = cast(nopol as varchar(100)),
                        @oa_barang = cast(barang as varchar(100)),
                        @oa_zak = cast(zak as int),
                        @oa_jumlah = cast(jumlah as decimal(7, 2)),
                        @oa_harga = cast(harga as decimal(9, 2)),
                        @oa_total = cast(total as decimal(15, 2))
                    from #tmpRhppOaPakan
                    where
                        nota = @nota and
                        barang = @barang and
                        jumlah > 0
                        
                    IF ( @jumlah >= @oa_jumlah )
                    BEGIN
                        SET @jumlah_simpan = @oa_jumlah
                        SET @jumlah = @jumlah - @oa_jumlah
                        
                        update #tmpRhppOaPakan set jumlah = 0 where id = @oa_id
                    END
                    ELSE
                    BEGIN
                        SET @jumlah_simpan = @jumlah
                        SET @oa_jumlah = @oa_jumlah - @jumlah
                        SET @jumlah = 0
                        
                        update #tmpRhppOaPakan set jumlah = @oa_jumlah where id = @oa_id
                    END
                    
                    insert into tmpDataRhppPakan (id_header, tanggal, nota, barang, zak, jumlah, harga, harga_oa, total) values
                    (@id_header, @tanggal, @nota, @barang, @zak, @jumlah_simpan, @harga, @oa_harga, ((@harga+@oa_harga) * @jumlah_simpan))
                END
                
                FETCH NEXT FROM pakan_cursor INTO
                    @id_header, @tanggal, @nota, @barang, @zak, @jumlah, @harga, @total
            END

            CLOSE pakan_cursor
            DEALLOCATE pakan_cursor

            /* PINDAH PAKAN */
            DECLARE pindah_pakan_cursor CURSOR LOCAL FOR
                select id_header, tanggal, nota, barang, zak, jumlah, harga, total from #tmpRhppPindahPakan

            OPEN pindah_pakan_cursor
                
            FETCH NEXT FROM pindah_pakan_cursor INTO
                @id_header, @tanggal, @nota, @barang, @zak, @jumlah, @harga, @total
                
            WHILE @@FETCH_STATUS = 0
            BEGIN
                WHILE ( @jumlah > 0 )
                BEGIN
                    select top 1
                        @oa_id = cast(id as int),
                        @oa_id_header = cast(id_header as int),
                        @oa_tanggal = cast(tanggal as date),
                        @oa_nota = cast(nota as varchar(20)),
                        @oa_nopol = cast(nopol as varchar(100)),
                        @oa_barang = cast(barang as varchar(100)),
                        @oa_zak = cast(zak as int),
                        @oa_jumlah = cast(jumlah as decimal(7, 2)),
                        @oa_harga = cast(harga as decimal(9, 2)),
                        @oa_total = cast(total as decimal(15, 2))
                    from #tmpRhppOaPindahPakan
                    where
                        nota = @nota and
                        barang = @barang and
                        jumlah > 0
                        
                    IF ( @jumlah >= @oa_jumlah )
                    BEGIN
                        SET @jumlah_simpan = @oa_jumlah
                        SET @jumlah = @jumlah - @oa_jumlah
                        
                        update #tmpRhppOaPindahPakan set jumlah = 0 where id = @oa_id
                    END
                    ELSE
                    BEGIN
                        SET @jumlah_simpan = @jumlah
                        SET @oa_jumlah = @oa_jumlah - @jumlah
                        SET @jumlah = 0
                        
                        update #tmpRhppOaPindahPakan set jumlah = @oa_jumlah where id = @oa_id
                    END
                    
                    insert into tmpDataRhppPindahPakan (id_header, tanggal, nota, barang, zak, jumlah, harga, harga_oa, total) values
                    (@id_header, @tanggal, @nota, @barang, @zak, @jumlah_simpan, @harga, @oa_harga, ((@harga+@oa_harga) * @jumlah_simpan))
                END
                
                FETCH NEXT FROM pindah_pakan_cursor INTO
                    @id_header, @tanggal, @nota, @barang, @zak, @jumlah, @harga, @total
            END

            CLOSE pindah_pakan_cursor
            DEALLOCATE pindah_pakan_cursor

            /* RETUR PAKAN */
            DECLARE retur_pakan_cursor CURSOR LOCAL FOR
                select id_header, tanggal, nota, barang, zak, jumlah, harga, total from #tmpRhppReturPakan

            OPEN retur_pakan_cursor
                
            FETCH NEXT FROM retur_pakan_cursor INTO
                @id_header, @tanggal, @nota, @barang, @zak, @jumlah, @harga, @total
                
            WHILE @@FETCH_STATUS = 0
            BEGIN
                WHILE ( @jumlah > 0 )
                BEGIN
                    select top 1
                        @oa_id = cast(id as int),
                        @oa_id_header = cast(id_header as int),
                        @oa_tanggal = cast(tanggal as date),
                        @oa_nota = cast(nota as varchar(20)),
                        @oa_nopol = cast(nopol as varchar(100)),
                        @oa_barang = cast(barang as varchar(100)),
                        @oa_zak = cast(zak as int),
                        @oa_jumlah = cast(jumlah as decimal(7, 2)),
                        @oa_harga = cast(harga as decimal(9, 2)),
                        @oa_total = cast(total as decimal(15, 2))
                    from #tmpRhppOaReturPakan
                    where
                        nota = @nota and
                        barang = @barang and
                        jumlah > 0
                        
                    IF ( @jumlah >= @oa_jumlah )
                    BEGIN
                        SET @jumlah_simpan = @oa_jumlah
                        SET @jumlah = @jumlah - @oa_jumlah
                        
                        update #tmpRhppOaReturPakan set jumlah = 0 where id = @oa_id
                    END
                    ELSE
                    BEGIN
                        SET @jumlah_simpan = @jumlah
                        SET @oa_jumlah = @oa_jumlah - @jumlah
                        SET @jumlah = 0
                        
                        update #tmpRhppOaReturPakan set jumlah = @oa_jumlah where id = @oa_id
                    END
                    
                    insert into tmpDataRhppReturPakan (id_header, tanggal, nota, barang, zak, jumlah, harga, harga_oa, total) values
                    (@id_header, @tanggal, @nota, @barang, @zak, @jumlah_simpan, @harga, @oa_harga, ((@harga+@oa_harga) * @jumlah_simpan))
                END
                
                FETCH NEXT FROM retur_pakan_cursor INTO
                    @id_header, @tanggal, @nota, @barang, @zak, @jumlah, @harga, @total
            END

            CLOSE retur_pakan_cursor
            DEALLOCATE retur_pakan_cursor
            /* END - NON GROUP */

            /* GROUP */
            if OBJECT_ID('tempdb..#tmpRhppGroupOaPakan') is not null
            BEGIN
                drop table #tmpRhppGroupOaPakan
            END
            create table #tmpRhppGroupOaPakan (
                id int,
                id_header int,
                tanggal date,
                nota varchar(20),
                nopol varchar(100),
                barang varchar(100),
                zak int,
                jumlah decimal(7, 2),
                harga decimal(9, 2),
                total decimal(15, 2)
            )

            if OBJECT_ID('tempdb..#tmpRhppGroupPakan') is not null
            BEGIN
                drop table #tmpRhppGroupPakan
            END
            create table #tmpRhppGroupPakan (
                id_header int,
                tanggal date,
                nota varchar(20),
                barang varchar(100),
                zak int,
                jumlah decimal(7, 2),
                harga decimal(9, 2),
                total decimal(15, 2)
            )

            --if OBJECT_ID('tempdb..#tmpDataRhppGroupPakan') is not null
            --BEGIN
            --    drop table #tmpDataRhppGroupPakan
            --END
            IF (EXISTS (SELECT * 
                            FROM INFORMATION_SCHEMA.TABLES 
                            WHERE TABLE_SCHEMA = 'dbo' 
                            AND  TABLE_NAME = 'tmpDataRhppGroupPakan'))
            BEGIN
                DROP TABLE tmpDataRhppGroupPakan
            END
            create table tmpDataRhppGroupPakan (
                id_header int,
                tanggal date,
                nota varchar(20),
                barang varchar(100),
                zak int,
                jumlah decimal(7, 2),
                harga decimal(9, 2),
                harga_oa decimal(9, 2),
                total decimal(15, 2)
            )

            if OBJECT_ID('tempdb..#tmpRhppGroupOaPindahPakan') is not null
            BEGIN
                drop table #tmpRhppGroupOaPindahPakan
            END
            create table #tmpRhppGroupOaPindahPakan (
                id int,
                id_header int,
                tanggal date,
                nota varchar(20),
                nopol varchar(100),
                barang varchar(100),
                zak int,
                jumlah decimal(7, 2),
                harga decimal(9, 2),
                total decimal(15, 2)
            )

            if OBJECT_ID('tempdb..#tmpRhppGroupPindahPakan') is not null
            BEGIN
                drop table #tmpRhppGroupPindahPakan
            END
            create table #tmpRhppGroupPindahPakan (
                id_header int,
                tanggal date,
                nota varchar(20),
                barang varchar(100),
                zak int,
                jumlah decimal(7, 2),
                harga decimal(9, 2),
                total decimal(15, 2)
            )

            --if OBJECT_ID('tempdb..#tmpDataRhppGroupPindahPakan') is not null
            --BEGIN
            --    drop table #tmpDataRhppGroupPindahPakan
            --END
            IF (EXISTS (SELECT * 
                            FROM INFORMATION_SCHEMA.TABLES 
                            WHERE TABLE_SCHEMA = 'dbo' 
                            AND  TABLE_NAME = 'tmpDataRhppGroupPindahPakan'))
            BEGIN
                DROP TABLE tmpDataRhppGroupPindahPakan
            END
            create table tmpDataRhppGroupPindahPakan (
                id_header int,
                tanggal date,
                nota varchar(20),
                barang varchar(100),
                zak int,
                jumlah decimal(7, 2),
                harga decimal(9, 2),
                harga_oa decimal(9, 2),
                total decimal(15, 2)
            )

            if OBJECT_ID('tempdb..#tmpRhppGroupOaReturPakan') is not null
            BEGIN
                drop table #tmpRhppGroupOaReturPakan
            END
            create table #tmpRhppGroupOaReturPakan (
                id int,
                id_header int,
                tanggal date,
                nota varchar(20),
                nopol varchar(100),
                barang varchar(100),
                zak int,
                jumlah decimal(7, 2),
                harga decimal(9, 2),
                total decimal(15, 2)
            )

            if OBJECT_ID('tempdb..#tmpRhppGroupReturPakan') is not null
            BEGIN
                drop table #tmpRhppGroupReturPakan
            END
            create table #tmpRhppGroupReturPakan (
                id_header int,
                tanggal date,
                nota varchar(20),
                barang varchar(100),
                zak int,
                jumlah decimal(7, 2),
                harga decimal(9, 2),
                total decimal(15, 2)
            )

            --if OBJECT_ID('tempdb..#tmpDataRhppGroupReturPakan') is not null
            --BEGIN
            --    drop table #tmpDataRhppGroupReturPakan
            --END
            IF (EXISTS (SELECT * 
                            FROM INFORMATION_SCHEMA.TABLES 
                            WHERE TABLE_SCHEMA = 'dbo' 
                            AND  TABLE_NAME = 'tmpDataRhppGroupReturPakan'))
            BEGIN
                DROP TABLE tmpDataRhppGroupReturPakan
            END
            create table tmpDataRhppGroupReturPakan (
                id_header int,
                tanggal date,
                nota varchar(20),
                barang varchar(100),
                zak int,
                jumlah decimal(7, 2),
                harga decimal(9, 2),
                harga_oa decimal(9, 2),
                total decimal(15, 2)
            )

            insert into #tmpRhppGroupOaPakan (id, id_header, tanggal, nota, nopol, barang, zak, jumlah, harga, total)
            select r_non_group.id, r_non_group.id_header, r_non_group.tanggal, r_non_group.nota, r_non_group.nopol, r_non_group.barang, r_non_group.zak, r_non_group.jumlah, r_non_group.harga, r_non_group.total from rhpp_group_noreg rgn
            left join
                rhpp_group r
                on
                    r.id = rgn.id_header
            left join
                rhpp_group_header rgh
                on
                    r.id_header = rgh.id
            left join
                (
                    select r.noreg, rop.* from rhpp_oa_pakan rop
                    left join
                        rhpp r
                        on
                            rop.id_header = r.id
                    where 
                        r.jenis = 'rhpp_inti'
                ) r_non_group
                on
                    r_non_group.noreg = rgn.noreg
            where
                r.jenis = 'rhpp_inti' and
                rgh.tgl_submit between '".$start_date."' and '".$end_date."'
                
            insert into #tmpRhppGroupPakan (id_header, tanggal, nota, barang, zak, jumlah, harga, total)
            select rp.id_header, rp.tanggal, rp.nota, rp.barang, rp.zak, rp.jumlah, rp.harga, rp.total from rhpp_group_pakan rp
            left join
                rhpp_group r
                on
                    r.id = rp.id_header
            left join
                rhpp_group_header rgh
                on
                    r.id_header = rgh.id
            where
                r.jenis = 'rhpp_inti' and
                rgh.tgl_submit between '".$start_date."' and '".$end_date."'
                
            insert into #tmpRhppGroupOaPindahPakan (id, id_header, tanggal, nota, nopol, barang, zak, jumlah, harga, total)
            select r_non_group.id, r_non_group.id_header, r_non_group.tanggal, r_non_group.nota, r_non_group.nopol, r_non_group.barang, r_non_group.zak, r_non_group.jumlah, r_non_group.harga, r_non_group.total from rhpp_group_noreg rgn
            left join
                rhpp_group r
                on
                    r.id = rgn.id_header
            left join
                rhpp_group_header rgh
                on
                    r.id_header = rgh.id
            left join
                (
                    select r.noreg, ropp.* from rhpp_oa_pindah_pakan ropp
                    left join
                        rhpp r
                        on
                            ropp.id_header = r.id
                    where 
                        r.jenis = 'rhpp_inti'
                ) r_non_group
                on
                    r_non_group.noreg = rgn.noreg
            where
                r.jenis = 'rhpp_inti' and
                rgh.tgl_submit between '".$start_date."' and '".$end_date."'
                
            insert into #tmpRhppGroupPindahPakan (id_header, tanggal, nota, barang, zak, jumlah, harga, total)
            select rpp.id_header, rpp.tanggal, rpp.nota, rpp.barang, rpp.zak, rpp.jumlah, rpp.harga, rpp.total from rhpp_group_pindah_pakan rpp
            left join
                rhpp_group r
                on
                    r.id = rpp.id_header
            left join
                rhpp_group_header rgh
                on
                    r.id_header = rgh.id
            where
                r.jenis = 'rhpp_inti' and
                rgh.tgl_submit between '".$start_date."' and '".$end_date."'
                
            insert into #tmpRhppGroupOaReturPakan (id, id_header, tanggal, nota, nopol, barang, zak, jumlah, harga, total)
            select r_non_group.id, r_non_group.id_header, r_non_group.tanggal, r_non_group.nota, r_non_group.nopol, r_non_group.barang, r_non_group.zak, r_non_group.jumlah, r_non_group.harga, r_non_group.total from rhpp_group_noreg rgn
            left join
                rhpp_group r
                on
                    r.id = rgn.id_header
            left join
                rhpp_group_header rgh
                on
                    r.id_header = rgh.id
            left join
                (
                    select 
                        r.noreg,
                        rorp.id,
                        rorp.id_header,
                        rorp.tanggal,
                        rp.no_retur as nota,
                        rorp.nopol,
                        rorp.barang,
                        rorp.zak,
                        rorp.jumlah,
                        rorp.harga,
                        rorp.total
                    from rhpp_oa_retur_pakan rorp
                    left join
                        rhpp r
                        on
                            rorp.id_header = r.id
                    left join
                        kirim_pakan kp
                        on
                            kp.no_sj = rorp.nota
                    left join
                        retur_pakan rp
                        on
                            rp.tgl_retur = rorp.tanggal and
                            rp.no_order = kp.no_order
                    where 
                        r.jenis = 'rhpp_inti'
                ) r_non_group
                on
                    r_non_group.noreg = rgn.noreg
            where
                r.jenis = 'rhpp_inti' and
                rgh.tgl_submit between '".$start_date."' and '".$end_date."'
                
            insert into #tmpRhppGroupReturPakan (id_header, tanggal, nota, barang, zak, jumlah, harga, total)
            select rrp.id_header, rrp.tanggal, rrp.nota, rrp.barang, rrp.zak, rrp.jumlah, rrp.harga, rrp.total from rhpp_group_retur_pakan rrp
            left join
                rhpp_group r
                on
                    r.id = rrp.id_header
            left join
                rhpp_group_header rgh
                on
                    r.id_header = rgh.id
            where
                r.jenis = 'rhpp_inti' and
                rgh.tgl_submit between '".$start_date."' and '".$end_date."'
                
            /* PAKAN */
            DECLARE group_pakan_cursor CURSOR LOCAL FOR
                select id_header, tanggal, nota, barang, zak, jumlah, harga, total from #tmpRhppGroupPakan

            OPEN group_pakan_cursor
                
            FETCH NEXT FROM group_pakan_cursor INTO
                @id_header, @tanggal, @nota, @barang, @zak, @jumlah, @harga, @total
                
            WHILE @@FETCH_STATUS = 0
            BEGIN
                WHILE ( @jumlah > 0 )
                BEGIN
                    select top 1
                        @oa_id = cast(id as int),
                        @oa_id_header = cast(id_header as int),
                        @oa_tanggal = cast(tanggal as date),
                        @oa_nota = cast(nota as varchar(20)),
                        @oa_nopol = cast(nopol as varchar(100)),
                        @oa_barang = cast(barang as varchar(100)),
                        @oa_zak = cast(zak as int),
                        @oa_jumlah = cast(jumlah as decimal(7, 2)),
                        @oa_harga = cast(harga as decimal(9, 2)),
                        @oa_total = cast(total as decimal(15, 2))
                    from #tmpRhppGroupOaPakan
                    where
                        nota = @nota and
                        barang = @barang and
                        jumlah > 0
                        
                    IF ( @jumlah >= @oa_jumlah )
                    BEGIN
                        SET @jumlah_simpan = @oa_jumlah
                        SET @jumlah = @jumlah - @oa_jumlah
                        
                        update #tmpRhppGroupOaPakan set jumlah = 0 where id = @oa_id
                    END
                    ELSE
                    BEGIN
                        SET @jumlah_simpan = @jumlah
                        SET @oa_jumlah = @oa_jumlah - @jumlah
                        SET @jumlah = 0
                        
                        update #tmpRhppGroupOaPakan set jumlah = @oa_jumlah where id = @oa_id
                    END
                    
                    insert into tmpDataRhppGroupPakan (id_header, tanggal, nota, barang, zak, jumlah, harga, harga_oa, total) values
                    (@id_header, @tanggal, @nota, @barang, @zak, @jumlah_simpan, @harga, @oa_harga, ((@harga+@oa_harga) * @jumlah_simpan))
                END
                
                FETCH NEXT FROM group_pakan_cursor INTO
                    @id_header, @tanggal, @nota, @barang, @zak, @jumlah, @harga, @total
            END

            CLOSE group_pakan_cursor
            DEALLOCATE group_pakan_cursor

            /* PINDAH PAKAN */
            DECLARE group_pindah_pakan_cursor CURSOR LOCAL FOR
                select id_header, tanggal, nota, barang, zak, jumlah, harga, total from #tmpRhppGroupPindahPakan

            OPEN group_pindah_pakan_cursor
                
            FETCH NEXT FROM group_pindah_pakan_cursor INTO
                @id_header, @tanggal, @nota, @barang, @zak, @jumlah, @harga, @total
                
            WHILE @@FETCH_STATUS = 0
            BEGIN
                WHILE ( @jumlah > 0 )
                BEGIN
                    select top 1
                        @oa_id = cast(id as int),
                        @oa_id_header = cast(id_header as int),
                        @oa_tanggal = cast(tanggal as date),
                        @oa_nota = cast(nota as varchar(20)),
                        @oa_nopol = cast(nopol as varchar(100)),
                        @oa_barang = cast(barang as varchar(100)),
                        @oa_zak = cast(zak as int),
                        @oa_jumlah = cast(jumlah as decimal(7, 2)),
                        @oa_harga = cast(harga as decimal(9, 2)),
                        @oa_total = cast(total as decimal(15, 2))
                    from #tmpRhppGroupOaPindahPakan
                    where
                        nota = @nota and
                        barang = @barang and
                        jumlah > 0
                        
                    IF ( @jumlah >= @oa_jumlah )
                    BEGIN
                        SET @jumlah_simpan = @oa_jumlah
                        SET @jumlah = @jumlah - @oa_jumlah
                        
                        update #tmpRhppGroupOaPindahPakan set jumlah = 0 where id = @oa_id
                    END
                    ELSE
                    BEGIN
                        SET @jumlah_simpan = @jumlah
                        SET @oa_jumlah = @oa_jumlah - @jumlah
                        SET @jumlah = 0
                        
                        update #tmpRhppGroupOaPindahPakan set jumlah = @oa_jumlah where id = @oa_id
                    END
                    
                    insert into tmpDataRhppGroupPindahPakan (id_header, tanggal, nota, barang, zak, jumlah, harga, harga_oa, total) values
                    (@id_header, @tanggal, @nota, @barang, @zak, @jumlah_simpan, @harga, @oa_harga, ((@harga+@oa_harga) * @jumlah_simpan))
                END
                
                FETCH NEXT FROM group_pindah_pakan_cursor INTO
                    @id_header, @tanggal, @nota, @barang, @zak, @jumlah, @harga, @total
            END

            CLOSE group_pindah_pakan_cursor
            DEALLOCATE group_pindah_pakan_cursor

            /* RETUR PAKAN */
            DECLARE group_retur_pakan_cursor CURSOR LOCAL FOR
                select id_header, tanggal, nota, barang, zak, jumlah, harga, total from #tmpRhppGroupReturPakan

            OPEN group_retur_pakan_cursor
                
            FETCH NEXT FROM group_retur_pakan_cursor INTO
                @id_header, @tanggal, @nota, @barang, @zak, @jumlah, @harga, @total
                
            WHILE @@FETCH_STATUS = 0
            BEGIN
                WHILE ( @jumlah > 0 )
                BEGIN
                    select top 1
                        @oa_id = cast(id as int),
                        @oa_id_header = cast(id_header as int),
                        @oa_tanggal = cast(tanggal as date),
                        @oa_nota = cast(nota as varchar(20)),
                        @oa_nopol = cast(nopol as varchar(100)),
                        @oa_barang = cast(barang as varchar(100)),
                        @oa_zak = cast(zak as int),
                        @oa_jumlah = cast(jumlah as decimal(7, 2)),
                        @oa_harga = cast(harga as decimal(9, 2)),
                        @oa_total = cast(total as decimal(15, 2))
                    from #tmpRhppGroupOaReturPakan
                    where
                        nota = @nota and
                        barang = @barang and
                        jumlah > 0
                        
                    IF ( @jumlah >= @oa_jumlah )
                    BEGIN
                        SET @jumlah_simpan = @oa_jumlah
                        SET @jumlah = @jumlah - @oa_jumlah
                        
                        update #tmpRhppGroupOaReturPakan set jumlah = 0 where id = @oa_id
                    END
                    ELSE
                    BEGIN
                        SET @jumlah_simpan = @jumlah
                        SET @oa_jumlah = @oa_jumlah - @jumlah
                        SET @jumlah = 0
                        
                        update #tmpRhppGroupOaReturPakan set jumlah = @oa_jumlah where id = @oa_id
                    END
                    
                    insert into tmpDataRhppGroupReturPakan (id_header, tanggal, nota, barang, zak, jumlah, harga, harga_oa, total) values
                    (@id_header, @tanggal, @nota, @barang, @zak, @jumlah_simpan, @harga, @oa_harga, ((@harga+@oa_harga) * @jumlah_simpan))
                END
                
                FETCH NEXT FROM group_retur_pakan_cursor INTO
                    @id_header, @tanggal, @nota, @barang, @zak, @jumlah, @harga, @total
            END

            CLOSE group_retur_pakan_cursor
            DEALLOCATE group_retur_pakan_cursor
            /* END - GROUP */

            select 
                d_rhpp.id,
                d_rhpp.nama_peternak,
                d_distribusi.kandang,
                d_distribusi.periode,
                d_rhpp.tgl_docin,
                d_distribusi.jenis_trans as jenis,
                d_distribusi.jenis as ketegori,
                d_distribusi.tanggal as tgl_distribusi,
                -- d_distribusi.nota,
                d_distribusi.barang,
                sum(d_distribusi.box_sak) as box_sak,
                sum(d_distribusi.jumlah) as jumlah,
                d_distribusi.harga,
                sum(d_distribusi.total) as total,
                sum(d_distribusi.mutasi_barang) as mutasi_barang,
                sum(d_distribusi.nominal) as nominal,
                sum(d_distribusi.mutasi_box_sak) as mutasi_box_sak,
                d_rhpp.nama_perusahaan as do,
                d_distribusi.unit,
                d_rhpp.tgl_awal_panen,
                d_rhpp.tgl_akhir_panen
            from (
                select * from
                (
                    select 
                        rd.id_header, 
                        'distribusi' as jenis,
                        rd.tanggal,
                        rd.nota,
                        rd.barang,
                        rd.box as box_sak,
                        rd.jumlah,
                        rd.harga,
                        rd.total,
                        rd.jumlah as mutasi_barang,
                        rd.total as nominal,
                        rd.box as mutasi_box_sak,
                        w.kode as unit,
                        'rhpp' as jenis_rhpp,
                        'doc' as jenis_trans,
                        k.kandang,
                        cast(SUBSTRING(rs.noreg, 8, 2) as int) as periode
                    from rhpp_doc rd
                    right join
                        rhpp r
                        on
                            rd.id_header = r.id
                    left join
                        (
                            select td1.*, od.noreg from terima_doc td1
                            right join
                                (select max(id) as id, no_order from terima_doc where no_order is not null group by no_order) td2
                                on
                                    td1.id = td2.id
                            left join
                                (
                                    select od1.* from order_doc od1
                                    right join
                                        (select max(id) as id, noreg from order_doc group by noreg) od2
                                        on
                                            od1.id = od2.id
                                ) od
                                on
                                    td1.no_order = od.no_order
                        ) td
                        on
                            rd.nota = td.no_sj and
                            r.noreg = td.noreg
                    left join
                        rdim_submit rs 
                        on
                            rs.noreg = td.noreg
                    left join
                        kandang k
                        on
                            rs.kandang = k.id
                    left join
                        wilayah w 
                        on
                            k.unit = w.id
                    where
                        not exists (select * from rhpp_group_noreg where noreg = r.noreg)
                            
                    union all
                    
                    select 
                        rgd.id_header, 
                        'distribusi' as jenis,
                        rgd.tanggal,
                        rgd.nota,
                        rgd.barang,
                        rgd.box as box_sak,
                        rgd.jumlah,
                        rgd.harga,
                        rgd.total,
                        rgd.jumlah as mutasi_barang,
                        rgd.total as nominal,
                        rgd.box as mutasi_box_sak,
                        w.kode as unit,
                        'rhpp_group' as jenis_rhpp,
                        'doc' as jenis_trans,
                        k.kandang,
                        cast(SUBSTRING(rs.noreg, 8, 2) as int) as periode
                    from rhpp_group_doc rgd
                    left join
                        rhpp_group rg
                        on
                            rgd.id_header = rg.id
                    left join
                        rhpp_group_header rgh 
                        on
                            rg.id_header = rgh.id
                    left join
                        (
                            select nomor, nim from mitra_mapping mm group by nomor, nim
                        ) mm
                        on
                            mm.nomor = rgh.nomor 
                    left join
                        (
                            select td1.*, od.noreg, SUBSTRING(od.noreg, 1, 7) as nim from terima_doc td1
                            right join
                                (select max(id) as id, no_order from terima_doc where no_order is not null group by no_order) td2
                                on
                                    td1.id = td2.id
                            left join
                                (
                                    select od1.* from order_doc od1
                                    right join
                                        (select max(id) as id, noreg from order_doc group by noreg) od2
                                        on
                                            od1.id = od2.id
                                ) od
                                on
                                    td1.no_order = od.no_order
                        ) td
                        on
                            rgd.nota = td.no_sj and
                            mm.nim = td.nim
                    left join
                        rdim_submit rs 
                        on
                            rs.noreg = td.noreg
                    left join
                        kandang k
                        on
                            rs.kandang = k.id
                    left join
                        wilayah w 
                        on
                            k.unit = w.id
                ) d_doc

                union all

                select * from (
                    select * from
                    (
                        select 
                            rv.id_header, 
                            'distribusi' as jenis,
                            rv.tanggal,
                            rv.nota,
                            rv.barang,
                            0 as box_sak,
                            rv.jumlah,
                            rv.harga,
                            rv.total,
                            rv.jumlah as mutasi_barang,
                            rv.total as nominal,
                            0 as mutasi_box_sak,
                            w.kode as unit,
                            'rhpp' as jenis_rhpp,
                            'ovk' as jenis_trans,
                            k.kandang,
                            cast(SUBSTRING(rs.noreg, 8, 2) as int) as periode
                        from rhpp_voadip rv
                        left join
                            kirim_voadip kv 
                            on
                                rv.nota = kv.no_sj
                        left join
                            rdim_submit rs 
                            on
                                rs.noreg = kv.tujuan
                        left join
                            kandang k
                            on
                                rs.kandang = k.id
                        left join
                            wilayah w 
                            on
                                k.unit = w.id
                                
                        union all
                        
                        select 
                            rgv.id_header,
                            'distribusi' as jenis,
                            rgv.tanggal,
                            rgv.nota,
                            rgv.barang,
                            0 as box_sak,
                            rgv.jumlah,
                            rgv.harga,
                            rgv.total,
                            rgv.jumlah as mutasi_barang,
                            rgv.total as nominal,
                            0 as mutasi_box_sak,
                            w.kode as unit,
                            'rhpp_group' as jenis_rhpp,
                            'ovk' as jenis_trans,
                            k.kandang,
                            cast(SUBSTRING(rs.noreg, 8, 2) as int) as periode
                        from rhpp_group_voadip rgv 
                        left join
                            kirim_voadip kv 
                            on
                                rgv.nota = kv.no_sj
                        left join
                            rdim_submit rs 
                            on
                                rs.noreg = kv.tujuan
                        left join
                            kandang k
                            on
                                rs.kandang = k.id
                        left join
                            wilayah w 
                            on
                                k.unit = w.id
                    ) d_ovk

                    union all

                    select * from
                    (
                        select 
                            rrv.id_header, 
                            'retur' as jenis,
                            rrv.tanggal,
                            rrv.nota,
                            rrv.barang,
                            0 as box_sak,
                            isnull(rrv.jumlah, 0) as jumlah,
                            rrv.harga,
                            isnull(rrv.total, 0) as total,
                            0-isnull(rrv.jumlah, 0) as mutasi_barang,
                            0-isnull(rrv.total, 0) as nominal,
                            0 as mutasi_box_sak,
                            w.kode as unit,
                            'rhpp' as jenis_rhpp,
                            'ovk' as jenis_trans,
                            k.kandang,
                            cast(SUBSTRING(rs.noreg, 8, 2) as int) as periode
                        from rhpp_retur_voadip rrv
                        left join
                            retur_voadip rv
                            on
                                rrv.nota = rv.no_retur
                        left join
                            kirim_voadip kv 
                            on
                                rv.no_order = kv.no_order
                        left join
                            rdim_submit rs 
                            on
                                rs.noreg = kv.tujuan
                        left join
                            kandang k
                            on
                                rs.kandang = k.id
                        left join
                            wilayah w 
                            on
                                k.unit = w.id
                                
                        union all
                        
                        select 
                            rgrv.id_header,
                            'retur' as jenis,
                            rgrv.tanggal,
                            rgrv.nota,
                            rgrv.barang,
                            0 as box_sak,
                            isnull(rgrv.jumlah, 0) as jumlah,
                            rgrv.harga,
                            isnull(rgrv.total, 0) as total,
                            0-isnull(rgrv.jumlah, 0) as mutasi_barang,
                            0-isnull(rgrv.total, 0) as nominal,
                            0 as mutasi_box_sak,
                            w.kode as unit,
                            'rhpp_group' as jenis_rhpp,
                            'ovk' as jenis_trans,
                            k.kandang,
                            cast(SUBSTRING(rs.noreg, 8, 2) as int) as periode
                        from rhpp_group_retur_voadip rgrv
                        left join
                            retur_voadip rv
                            on
                                rgrv.nota = rv.no_retur
                        left join
                            kirim_voadip kv 
                            on
                                rv.no_order = kv.no_order
                        left join
                            rdim_submit rs 
                            on
                                rs.noreg = kv.tujuan
                        left join
                            kandang k
                            on
                                rs.kandang = k.id
                        left join
                            wilayah w 
                            on
                                k.unit = w.id
                    ) d_retur_ovk
                ) d_ovk

                union all

                -- select * from tmpDataRhppPakan
                -- select * from tmpDataRhppPindahPakan
                -- select * from tmpDataRhppReturPakan
                -- select * from tmpDataRhppGroupPakan
                -- select * from tmpDataRhppGroupPindahPakan
                -- select * from tmpDataRhppGroupReturPakan

                select * from (
                    select * from
                    (
                        select 
                            rp.id_header, 
                            case
                                when kp.jenis_kirim = 'opkg' then
                                    'distribusi'
                                else
                                    'mutasi_masuk'
                            end as jenis,
                            rp.tanggal,
                            rp.nota,
                            rp.barang,
                            (rp.jumlah/50) as box_sak,
                            rp.jumlah,
                            (rp.harga+rp.harga_oa) as harga,
                            (rp.jumlah * (rp.harga+rp.harga_oa)) as total,
                            rp.jumlah as mutasi_barang,
                            (rp.jumlah * (rp.harga+rp.harga_oa)) as nominal,
                            (rp.jumlah/50) as mutasi_box_sak,
                            w.kode as unit,
                            'rhpp' as jenis_rhpp,
                            'pakan' as jenis_trans,
                            k.kandang,
                            cast(SUBSTRING(rs.noreg, 8, 2) as int) as periode
                        from tmpDataRhppPakan rp
                        left join
                            kirim_pakan kp
                            on
                                rp.nota = kp.no_sj
                        left join
                            rdim_submit rs 
                            on
                                rs.noreg = kp.tujuan
                        left join
                            kandang k
                            on
                                rs.kandang = k.id
                        left join
                            wilayah w 
                            on
                                k.unit = w.id
                                
                        union all
                        
                        select 
                            rgp.id_header,
                            case
                                when kp.jenis_kirim = 'opkg' then
                                    'distribusi'
                                else
                                    'mutasi_masuk'
                            end as jenis,
                            rgp.tanggal,
                            rgp.nota,
                            rgp.barang,
                            (rgp.jumlah/50) as box_sak,
                            rgp.jumlah,
                            (rgp.harga+rgp.harga_oa) as harga,
                            (rgp.jumlah * (rgp.harga+rgp.harga_oa)) as total,
                            rgp.jumlah as mutasi_barang,
                            (rgp.jumlah * (rgp.harga+rgp.harga_oa)) as nominal,
                            (rgp.jumlah/50) as mutasi_box_sak,
                            w.kode as unit,
                            'rhpp_group' as jenis_rhpp,
                            'pakan' as jenis_trans,
                            k.kandang,
                            cast(SUBSTRING(rs.noreg, 8, 2) as int) as periode
                        from tmpDataRhppGroupPakan rgp 
                        left join
                            kirim_pakan kp
                            on
                                rgp.nota = kp.no_sj
                        left join
                            rdim_submit rs 
                            on
                                rs.noreg = kp.tujuan
                        left join
                            kandang k
                            on
                                rs.kandang = k.id
                        left join
                            wilayah w 
                            on
                                k.unit = w.id
                    ) d_pakan

                    union all

                    select * from
                    (
                        select 
                            rpp.id_header, 
                            'mutasi_keluar' as jenis,
                            rpp.tanggal,
                            rpp.nota,
                            rpp.barang,
                            isnull((rpp.jumlah/50), 0) as box_sak,
                            isnull(rpp.jumlah, 0) as jumlah,
                            (rpp.harga+rpp.harga_oa) as harga,
                            isnull((rpp.jumlah * (rpp.harga+rpp.harga_oa)), 0) as total,
                            0-isnull(rpp.jumlah, 0) as mutasi_barang,
                            0-isnull((rpp.jumlah * (rpp.harga+rpp.harga_oa)), 0) as nominal,
                            0-isnull((rpp.jumlah/50), 0) as mutasi_box_sak,
                            w.kode as unit,
                            'rhpp' as jenis_rhpp,
                            'pakan' as jenis_trans,
                            k.kandang,
                            cast(SUBSTRING(rs.noreg, 8, 2) as int) as periode
                        from tmpDataRhppPindahPakan rpp
                        left join
                            kirim_pakan kp
                            on
                                rpp.nota = kp.no_sj
                        left join
                            rdim_submit rs 
                            on
                                rs.noreg = kp.asal
                        left join
                            kandang k
                            on
                                rs.kandang = k.id
                        left join
                            wilayah w 
                            on
                                k.unit = w.id
                                
                        union all
                        
                        select 
                            rgpp.id_header,
                            'mutasi_keluar' as jenis,
                            rgpp.tanggal,
                            rgpp.nota,
                            rgpp.barang,
                            isnull((rgpp.jumlah/50), 0) as box_sak,
                            isnull(rgpp.jumlah, 0) as jumlah,
                            (rgpp.harga+rgpp.harga_oa) as harga,
                            isnull((rgpp.jumlah * (rgpp.harga+rgpp.harga_oa)), 0) as total,
                            0-isnull(rgpp.jumlah, 0) as mutasi_barang,
                            0-isnull((rgpp.jumlah * (rgpp.harga+rgpp.harga_oa)), 0) as nominal,
                            0-isnull((rgpp.jumlah/50), 0) as mutasi_box_sak,
                            w.kode as unit,
                            'rhpp_group' as jenis_rhpp,
                            'pakan' as jenis_trans,
                            k.kandang,
                            cast(SUBSTRING(rs.noreg, 8, 2) as int) as periode
                        from tmpDataRhppGroupPindahPakan rgpp 
                        left join
                            kirim_pakan kp
                            on
                                rgpp.nota = kp.no_sj
                        left join
                            rdim_submit rs 
                            on
                                rs.noreg = kp.asal
                        left join
                            kandang k
                            on
                                rs.kandang = k.id
                        left join
                            wilayah w 
                            on
                                k.unit = w.id
                    ) d_pakan_mutasi_keluar

                    union all

                    select * from
                    (
                        select 
                            rrp.id_header, 
                            'retur' as jenis,
                            rrp.tanggal,
                            rrp.nota,
                            rrp.barang,
                            isnull((rrp.jumlah/50), 0) as box_sak,
                            isnull(rrp.jumlah, 0) as jumlah,
                            (rrp.harga+rrp.harga_oa) as harga,
                            isnull((rrp.jumlah * (rrp.harga+rrp.harga_oa)), 0) as total,
                            0-isnull(rrp.jumlah, 0) as mutasi_barang,
                            0-isnull((rrp.jumlah * (rrp.harga+rrp.harga_oa)), 0) as nominal,
                            0-isnull((rrp.jumlah/50), 0) as mutasi_box_sak,
                            w.kode as unit,
                            'rhpp' as jenis_rhpp,
                            'pakan' as jenis_trans,
                            k.kandang,
                            cast(SUBSTRING(rs.noreg, 8, 2) as int) as periode
                        from tmpDataRhppReturPakan rrp
                        left join
                            kirim_pakan kp
                            on
                                rrp.nota = kp.no_sj
                        left join
                            rdim_submit rs 
                            on
                                rs.noreg = kp.tujuan
                        left join
                            kandang k
                            on
                                rs.kandang = k.id
                        left join
                            wilayah w 
                            on
                                k.unit = w.id
                                
                        union all
                        
                        select 
                            rgrp.id_header,
                            'retur' as jenis,
                            rgrp.tanggal,
                            rgrp.nota,
                            rgrp.barang,
                            isnull((rgrp.jumlah/50), 0) as box_sak,
                            isnull(rgrp.jumlah, 0) as jumlah,
                            (rgrp.harga+rgrp.harga_oa) as harga,
                            isnull((rgrp.jumlah * (rgrp.harga+rgrp.harga_oa)), 0) as total,
                            0-isnull(rgrp.jumlah, 0) as mutasi_barang,
                            0-isnull((rgrp.jumlah * (rgrp.harga+rgrp.harga_oa)), 0) as nominal,
                            0-isnull((rgrp.jumlah/50), 0) as mutasi_box_sak,
                            w.kode as unit,
                            'rhpp_group' as jenis_rhpp,
                            'pakan' as jenis_trans,
                            k.kandang,
                            cast(SUBSTRING(rs.noreg, 8, 2) as int) as periode
                        from tmpDataRhppGroupReturPakan rgrp 
                        left join
                            kirim_pakan kp
                            on
                                rgrp.nota = kp.no_sj
                        left join
                            rdim_submit rs 
                            on
                                rs.noreg = kp.tujuan
                        left join
                            kandang k
                            on
                                rs.kandang = k.id
                        left join
                            wilayah w 
                            on
                                k.unit = w.id
                    ) d_pakan_retur
                ) d_pakan
            ) d_distribusi
            right join
                (
                    select
                        r.id,
                        m.nama as nama_peternak,
                        cast(SUBSTRING(r.noreg, 10, 2) as int) as kandang,
                        cast(SUBSTRING(r.noreg, 8, 2) as int) as periode,
                        r.tgl_docin,
                        ts.tgl_tutup,
                        'rhpp' as jenis,
                        rp.tgl_awal_panen,
                        rp.tgl_akhir_panen,
                        prs.perusahaan as nama_perusahaan,
                        prs.kode as kode_perusahaan
                    from rhpp r
                    left join
                        (select min(tanggal) as tgl_awal_panen, max(tanggal) as tgl_akhir_panen, id_header from rhpp_penjualan group by id_header) rp
                        on
                            r.id = rp.id_header
                    left join
                        (select max(id) as id, noreg, tgl_tutup from tutup_siklus group by noreg, tgl_tutup) ts
                        on
                            r.id_ts = ts.id
                    left join
                        (
                            select mm1.* from mitra_mapping mm1
                            right join
                                (select max(id) as id, nim from mitra_mapping group by nim) mm2
                                on
                                    mm1.id = mm2.id
                        ) mm
                        on
                            SUBSTRING(r.noreg, 1, 7) = mm.nim
                    left join
                        mitra m
                        on
                            m.id = mm.mitra
                    left join
                        (
                            select prs1.* from perusahaan prs1
                            right join
                                (select max(id) as id, kode from perusahaan group by kode) prs2
                                on
                                    prs1.id = prs2.id
                        ) prs
                        on
                            prs.kode = m.perusahaan
                    where
                        r.jenis = 'rhpp_inti' and
                        not exists (select * from rhpp_group_noreg where noreg = r.noreg)

                    union all

                    select
                        rg.id,
                        rgh.mitra as nama_peternak,
                        0 as kandang,
                        0 as periode,
                        rgn.tgl_docin,
                        rgh.tgl_submit as tgl_tutup,
                        'rhpp_group' as jenis,
                        rgp.tgl_awal_panen,
                        rgp.tgl_akhir_panen,
                        prs.perusahaan as nama_perusahaan,
                        prs.kode as kode_perusahaan
                    from rhpp_group rg
                    left join
                        rhpp_group_header rgh
                        on
                            rg.id_header = rgh.id
                    left join
                        (select min(tgl_docin) as tgl_docin, id_header from rhpp_group_noreg group by id_header) rgn
                        on
                            rg.id = rgn.id_header
                    left join
                        (select min(tanggal) as tgl_awal_panen, max(tanggal) as tgl_akhir_panen, id_header from rhpp_group_penjualan group by id_header) rgp
                        on
                            rg.id = rgp.id_header
                    left join
                        (
                            select mtr1.* from mitra mtr1
                            right join
                                (select max(id) as id, nomor from mitra group by nomor) mtr2
                                on
                                    mtr1.id = mtr2.id
                        ) m
                        on
                            m.nomor = rgh.nomor 
                    left join
                        (
                            select prs1.* from perusahaan prs1
                            right join
                                (select max(id) as id, kode from perusahaan group by kode) prs2
                                on
                                    prs1.id = prs2.id
                        ) prs
                        on
                            prs.kode = m.perusahaan
                    where
                        rg.jenis = 'rhpp_inti'
                ) d_rhpp
                on
                    d_rhpp.id = d_distribusi.id_header and
                    d_rhpp.jenis = d_distribusi.jenis_rhpp
            where
                d_rhpp.tgl_tutup between '".$start_date."' and '".$end_date."'
                ".$sql_perusahaan."
                ".$sql_unit."
                ".$sql_jenis."
            group by
                d_rhpp.id,
                d_rhpp.nama_peternak,
                d_distribusi.kandang,
                d_distribusi.periode,
                d_rhpp.tgl_docin,
                d_distribusi.jenis_trans,
                d_distribusi.jenis,
                d_distribusi.tanggal,
                -- d_distribusi.nota,
                d_distribusi.barang,
                d_distribusi.harga,
                d_rhpp.nama_perusahaan,
                d_distribusi.unit,
                d_rhpp.tgl_awal_panen,
                d_rhpp.tgl_akhir_panen
            order by
                d_rhpp.tgl_docin asc,
                d_rhpp.nama_peternak asc,
                d_distribusi.jenis_trans asc,
                d_distribusi.barang asc
        ";
        // cetak_r( $sql, 1 );
        $d_conf = $m_conf->hydrateRaw( $sql );

        $data = null;
        if ( $d_conf->count() > 0 ) {
            $data = $d_conf->toArray();
        }

        return $data;
    }

    public function excryptParams()
    {
        $params = $this->input->post('params');

        try {
            $params_encrypt = exEncrypt( json_encode($params) );

            $this->result['status'] = 1;
            $this->result['content'] = $params_encrypt;
        } catch (Exception $e) {
            $this->result['message'] = $e->getMessage();
        }

        display_json( $this->result );
    }

    public function exportExcel($params_encrypt) {
        $params = json_decode( exDecrypt($params_encrypt), true );

        $start_date = $params['start_date'];
        $end_date = $params['end_date'];
        $perusahaan = $params['perusahaan'];
        $unit = $params['unit'];
        $jenis = $params['jenis'];

        $data = $this->getDataRekapRhpp($start_date, $end_date, $perusahaan, $unit, $jenis);

        $filename = "REKAP_DATA_RHPP_";
        $filename = $filename.str_replace('-', '', $params['start_date']).'_'.str_replace('-', '', $params['end_date']).'.xls';

        $arr_header = array('Urutan', 'Peternak', 'Kandang', 'Periode', 'Tgl Chick-in', 'Jenis', 'Kategori', 'Tgl Distribusi', 'Nota', 'Barang', 'Box/Sak', 'Jumlah', 'Harga', 'Total', 'Mutasi Barang', 'Nominal', 'Mutasi Box/Sak', 'D.O', 'Unit', 'Tgl. Panen Awal', 'Tgl. Panen Akhir');
        $arr_column = null;
        if ( !empty($data) ) {
            $idx = 0;
            foreach ($data as $key => $value) {
                $arr_column[ $idx ] = array(
                    'Urutan' => array('value' => ($idx+1), 'data_type' => 'integer'),
                    'Peternak' => array('value' => strtoupper($value['nama_peternak']), 'data_type' => 'string'),
                    'Kandang' => array('value' => strtoupper($value['kandang']), 'data_type' => 'string'),
                    'Periode' => array('value' => $value['periode'], 'data_type' => 'integer'),
                    'Tgl Chick-in' => array('value' => $value['tgl_docin'], 'data_type' => 'date'),
                    'Jenis' => array('value' => strtoupper($value['jenis']), 'data_type' => 'string'),
                    'Kategori' => array('value' => strtoupper(str_replace('_', ' ', $value['ketegori'])), 'data_type' => 'string'),
                    'Tgl Distribusi' => array('value' => $value['tgl_distribusi'], 'data_type' => 'date'),
                    'Nota' => array('value' => '', 'data_type' => 'string'),
                    'Barang' => array('value' => strtoupper($value['barang']), 'data_type' => 'string'),
                    'Box/Sak' => array('value' => $value['box_sak'], 'data_type' => 'decimal2'),
                    'Jumlah' => array('value' => $value['jumlah'], 'data_type' => 'decimal2'),
                    'Harga' => array('value' => $value['harga'], 'data_type' => 'decimal2'),
                    'Total' => array('value' => $value['total'], 'data_type' => 'decimal2'),
                    'Mutasi Barang' => array('value' => $value['mutasi_barang'], 'data_type' => 'decimal2'),
                    'Nominal' => array('value' => $value['nominal'], 'data_type' => 'decimal2'),
                    'Mutasi Box/Sak' => array('value' => $value['mutasi_box_sak'], 'data_type' => 'decimal2'),
                    'D.O' => array('value' => strtoupper($value['do']), 'data_type' => 'string'),
                    'Unit' => array('value' => strtoupper($value['unit']), 'data_type' => 'string'),
                    'Tgl. Panen Awal' => array('value' => $value['tgl_awal_panen'], 'data_type' => 'date'),
                    'Tgl. Panen Akhir' => array('value' => $value['tgl_akhir_panen'], 'data_type' => 'date')
                );

                $idx++;
            }
        }

        $this->exportExcelUsingSpreadSheet( $filename, $arr_header, $arr_column );
    }

    public function exportExcelUsingSpreadSheet( $file_name, $arr_header, $arr_column ) {
        /* Spreadsheet Init */
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        /* Excel Header */
        for ($i=0; $i < count($arr_header); $i++) { 
            $huruf = toAlpha($i+1);

            $posisi = $huruf.'2';
            $sheet->setCellValue($posisi, $arr_header[$i]);

            $styleBold = [
                'font' => [
                    'bold' => true,
                ]
            ];
            $spreadsheet->getActiveSheet()->getStyle($posisi)->applyFromArray($styleBold);
        }

        $baris = 3;
        if ( !empty($arr_column) && count($arr_column) ) {
            for ($i=0; $i < count($arr_column); $i++) {
                for ($j=0; $j < count($arr_header); $j++) {
                    $huruf = toAlpha($j+1);

                    $data = $arr_column[ $i ][ $arr_header[ $j ] ];

                    if ( $data['data_type'] == 'string' ) {
                        $sheet->setCellValue($huruf.$baris, strtoupper($data['value']));
                    }

                    if ( $data['data_type'] == 'nik' ) {
                        $sheet->getCell($huruf.$baris)->setValueExplicit($data['value'], DataType::TYPE_STRING);
                        // $sheet->setCellValue($huruf.$baris, strtoupper($data['value']));
                        // $spreadsheet->getActiveSheet()->getStyle('A9')
                        //             ->getNumberFormat()
                        //             ->setFormatCode(
                        //                 '00000000000'
                        //             );
                    }

                    if ( $data['data_type'] == 'text' ) {
                        $sheet->setCellValue($huruf.$baris, strtoupper($data['value']));
                        $spreadsheet->getActiveSheet()->getStyle($huruf.$baris)
                                    ->getNumberFormat()
                                    ->setFormatCode(NumberFormat::FORMAT_GENERAL);
                    }

                    if ( $data['data_type'] == 'date' ) {
                        $dt = Date::PHPToExcel(DateTime::createFromFormat('!Y-m-d', substr($data['value'], 0, 10)));
                        $sheet->setCellValue($huruf.$baris, $dt);
                        $spreadsheet->getActiveSheet()->getStyle($huruf.$baris)
                                    ->getNumberFormat()
                                    ->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
                    }

                    if ( $data['data_type'] == 'integer' ) {
                        $sheet->setCellValue($huruf.$baris, $data['value']);
                        $spreadsheet->getActiveSheet()->getStyle($huruf.$baris)
                                    ->getNumberFormat()
                                    ->setFormatCode(NumberFormat::FORMAT_NUMBER);
                    }

                    if ( $data['data_type'] == 'decimal2' ) {
                        $sheet->setCellValue($huruf.$baris, $data['value']);
                        $spreadsheet->getActiveSheet()->getStyle($huruf.$baris)
                                    ->getNumberFormat()
                                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);
                    }
                }

                $baris++;
            }

            /* Excel Total */
            $sheet->setCellValue('J1', "TOTAL");
            $spreadsheet->getActiveSheet()->mergeCells('A1:J1');
            $sheet->setCellValue('K1', "=SUBTOTAL(9,K3:K".$baris.")");
            $sheet->setCellValue('L1', "=SUBTOTAL(9,L3:L".$baris.")");
            $sheet->setCellValue('N1', "=SUBTOTAL(9,N3:N".$baris.")");
            $sheet->setCellValue('O1', "=SUBTOTAL(9,O3:O".$baris.")");
            $sheet->setCellValue('P1', "=SUBTOTAL(9,P3:P".$baris.")");
            $sheet->setCellValue('Q1', "=SUBTOTAL(9,Q3:Q".$baris.")");
            $styleBold = [
                'font' => [
                    'bold' => true,
                ]
            ];
            $spreadsheet->getActiveSheet()->getStyle('A1:U1')->applyFromArray($styleBold);
            $spreadsheet->getActiveSheet()->getStyle('K1:Q1')
                        ->getNumberFormat()
                        ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);
        } else {
            $range1 = 'A'.$baris;
            $range2 = toAlpha(count($arr_header)).$baris;

            $spreadsheet->getActiveSheet()->mergeCells("$range1:$range2");
            $sheet->setCellValue($range1, 'Data tidak ditemukan.');
        }

        $styleArray = [
            'borders' => [
                'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']],
                'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']],
                'right' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']],
                'left' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']],
            ],
        ];
        
        $spreadsheet->getActiveSheet()->getStyle('A1:'.toAlpha(count($arr_header)).$baris)->applyFromArray($styleArray, false);

        /* Excel File Format */
        $writer = new Xlsx($spreadsheet);
        $filename = $file_name;
        $writer->save('export_excel/'.$filename);

        $this->load->helper('download');
        force_download('export_excel/'.$filename, NULL);
    }

    public function tes() {
        $this->getDataRekapRhpp('2024-06-01', '2024-06-15', array('P001'), array('BWI', 'LMJ'), 'ovk');
    }
}