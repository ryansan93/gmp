<?php

namespace Model\Storage;

defined('BASEPATH') OR exit('No direct script access allowed');

use \Model\Storage\Conf as Conf;

class AttachmentRealisasiPembayaran_model extends Conf
{
    protected $table = 'attachment_realisasi_pembayaran';
    protected $primaryKey = 'id';
    protected $fillable = [
        'realisasi_id',
        'file_name',
        'path',
        'created_at',
        'name_file_old',
        'tbl_name'
    ];


    public static function showAll($realisasi_id = null)
    {
        $query = self::query();
        if ($realisasi_id !== null) {
            $query->where('realisasi_id', $realisasi_id);
        }
        return $query->get()->toArray();
    }


    public static function showLastData($realisasi_id = null, $tbl_name = null)
    {
        $query = self::query();

        if ($realisasi_id !== null) {
            $query->where('realisasi_id', $realisasi_id)
                ->where('tbl_name', $tbl_name)
                ->whereRaw("
                        CAST(created_at AS DATE) = (
                            SELECT MAX(CAST(created_at AS DATE))
                            FROM attachment_realisasi_pembayaran
                            WHERE realisasi_id = ? AND tbl_name = ?
                        )
                ", [$realisasi_id, $tbl_name]);
        }

        return $query->get()->toArray();
    }

    public static function deleteNotInOldFile($realisasi_id, $old_file = [], $tbl_name)
    {
        $ids = [];

        if (!empty($old_file)) {
            foreach ($old_file as $row) {
                if (!empty($row['id_file'])) {
                    $ids[] = $row['id_file'];
                }
            }
        }

        $query = self::where('realisasi_id', $realisasi_id)->where('tbl_name', $tbl_name);

        if (!empty($ids)) {
            $query->whereNotIn('id', $ids);
        }

        $files = $query->get();

        foreach ($files as $file) {
            if (!empty($file->path) && file_exists($file->path)) {
                unlink($file->path);
            }
        }

        return $query->delete();
    }

    public static function deleteByRealisasiId($realisasi_id, $tbl_name)
    {
      
        $files = self::where('realisasi_id', $realisasi_id)->where('tbl_name', $tbl_name)->get();

        if ($files->count() > 0) {

            foreach ($files as $file) {
                if (!empty($file->path) && file_exists($file->path)) {
                    unlink($file->path);
                }
            }
            self::where('realisasi_id', $realisasi_id)->delete();
        }
    }


}

