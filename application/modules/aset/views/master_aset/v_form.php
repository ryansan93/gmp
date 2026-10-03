<!-- <input type="hidden" id="config-form" value="< ?php echo (($status_aset ?? 0) == 1) ? 1 : 0; ?>"> -->
<style>
    /* Memaksa background abu-abu saat Select2 di-disable */
    .select2-container--disabled .select2-selection--single {
        background-color: #e9ecef !important; /* Warna abu-abu */
        cursor: not-allowed !important;      /* Kursor jadi tanda larang */
        opacity: 0.8;
    }
    
    /* Menghilangkan tombol 'x' (clear) saat disabled */
    .select2-container--disabled .select2-selection__clear {
        display: none !important;
    }
</style>

<?php 
    $is_received = 0; 
    if (isset($data['data']['is_received'])) {
        $is_received = (int)$data['data']['is_received'];
    } elseif (isset($data['is_received'])) {
        $is_received = (int)$data['is_received'];
    }
?>

<fieldset>
    <legend>Form Aset <button type="button" class="pull-right" style=" display: none; width:100px; height:20px; font-size:10px; border:1px solid grey; border-radius:5px;" onclick="ma.triggerImportExcel()"><i class="fa fa-file-excel-o"></i> Import Excel</button></legend>
    
    <input type="file" id="file_excel" accept=".xls,.xlsx" style="display: none;" onchange="ma.processImportExcel(this)">


    <div class="row" style="padding-left:30px; padding-right:30px">
        <div class="col-xs-12 col-sm-12 col-md-12" style="padding-left:0; padding-right:0;">
            <form class="form-horizontal" role="form" style="max-width:100%; margin:0; padding:0;">
                <input type="hidden" id="id_aset" value="<?php echo isset($data['id']) ? $data['id'] : ''; ?>">

                <?php if ( isset($data['id']) ) : ?>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="kode_aset" style="display:block; font-weight:600; margin-bottom:6px;">Kode Aset</label>
                        <input type="text" class="form-control" id="kode_aset" value="<?php echo isset($data['kode_aset']) ? htmlspecialchars($data['kode_aset']) : ''; ?>" readonly>
                    </div>
                <?php endif; ?>

                <div class="row" style="margin:0 -20px;">
                    <div class="col-xs-12 col-sm-6" style="padding:0 6px; margin-bottom:15px;">
                        <label for="id_kategori" style="display:block; font-weight:600; margin-bottom:6px;">Kategori Aset <span class="text-danger">*</span></label>
                        <select <?php echo $is_received == 1 ? 'disabled' : ''; ?> id="id_kategori" class="form-control" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php if ( !empty($kategori_aset) ) : ?>
                                <?php foreach ( $kategori_aset as $row ) : ?>
                                    <?php $id   = isset($row['id']) ? $row['id'] : ''; ?>
                                    <?php $kode = isset($row['kategori_kode']) ? trim($row['kategori_kode']) : ''; ?>
                                    <?php $nama = isset($row['kategori_name']) ? trim($row['kategori_name']) : ''; ?>
                                    <option value="<?php echo htmlspecialchars($id); ?>" <?php echo (isset($data['id_kategori']) && $data['id_kategori'] == $id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($kode . ' - ' . $nama); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <?php if ($is_received == 1): ?>
                            <small class="text-warning" style="display: block; margin-top: 5px;">
                                <i class="fa fa-lock"></i> Kategori tidak dapat diubah karena aset sudah pernah diterima.
                            </small>
                        <?php endif; ?>
                    </div>
                    <div class="col-xs-12 col-sm-6" style="padding:0 6px; margin-bottom:15px;">
                        <label for="document_no" style="display:block; font-weight:600; margin-bottom:6px;">No. Faktur / Bukti</label>
                        <input type="text" autocomplete="off" class="form-control" id="document_no" value="<?php echo isset($data['document_no']) ? htmlspecialchars($data['document_no']) : ''; ?>" placeholder="Contoh: KP-0003, BCA125113058">
                    </div>
                </div>

                <div class="row" style="margin:0 -20px;">
                    <div class="col-xs-12 col-sm-6" style="padding:0 6px; margin-bottom:15px;">
                        <label for="tgl_perolehan" style="display:block; font-weight:600; margin-bottom:6px;">Tanggal Perolehan <span class="text-danger">*</span></label>
                        <div class="input-group date" id="tgl_perolehan_picker">
                            <input type="text" class="form-control" placeholder="Pilih Tanggal" id="tgl_perolehan" style="caret-color: transparent; background-color:#fff;" value="<?php echo isset($data['tgl_perolehan']) ? htmlspecialchars(date('Y-m-d', strtotime($data['tgl_perolehan']))) : ''; ?>" required onkeydown="return false;" onpaste="return false;" ondrop="return false;" autocomplete="off">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-6" style="padding:0 6px; margin-bottom:15px;">
                        <label for="nilai_perolehan" style="display:block; font-weight:600; margin-bottom:6px;">Nilai Perolehan (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-addon">Rp</span>
                            <input type="text" class="form-control" autocomplete="off" id="nilai_perolehan" value="<?php echo isset($data['nilai_perolehan']) ? htmlspecialchars($data['nilai_perolehan']) : ''; ?>" placeholder="Masukkan nilai perolehan" inputmode="numeric">
                        </div>
                    </div>
                </div>

                <div class="row" style="margin:0 -20px;">
                    <div class="col-xs-12 col-sm-6" style="padding:0 6px; margin-bottom:15px;">
                        <label for="unit_pengguna" style="display:block; font-weight:600; margin-bottom:6px;">Unit Pengguna </label>
                        <select name="unit_pengguna" id="unit_pengguna">
                            <option value="">-- Pilih Data --</option>
                            <option <?php echo isset($data['unit_pengguna']) && $data['unit_pengguna'] == 'Head Office' ? 'selected' : ''; ?> value="Head Office">Head Office (HO)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="deskripsi_aset" style="display:block; font-weight:600; margin-bottom:6px;">Deskripsi Aset <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="deskripsi_aset" rows="3" placeholder="Contoh: DELL LATITUDE 7490 Core I5-8350U 8GB/ SSD 238 GB + Windows 11 Pro" required><?php echo isset($data['deskripsi_aset']) ? htmlspecialchars($data['deskripsi_aset']) : ''; ?></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="keterangan" style="display:block; font-weight:600; margin-bottom:6px;">Keterangan / Catatan</label>
                    <textarea class="form-control" id="keterangan" rows="2" placeholder="Contoh: Sebelumnya dipakai Mas Irwan, Kendaraan dihapuskan terkena pencurian"><?php echo isset($data['keterangan']) ? htmlspecialchars($data['keterangan']) : ''; ?></textarea>
                </div>

                
                <div class="row" style="margin:0 -20px;">
                    <div class="col-xs-12 col-sm-6" style="padding:0 6px; margin-bottom:15px;">
                        <label for="file_dokumen" style="display:block; font-weight:600; margin-bottom:6px;">
                            <i class="fa fa-paperclip"></i> Dokumen Pendukung
                            <small style="font-weight:normal; color:#888;">(PDF / JPG / PNG, Maks 5MB)</small>
                        </label>
                        
                        <div class="input-group">
                            <input type="file" class="form-control" id="file_dokumen" accept=".pdf,.jpg,.jpeg,.png" onchange="ma.previewFile(this)">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default" onclick="ma.clearFile()" title="Hapus file">
                                    <i class="fa fa-times"></i>
                                </button>
                            </span>
                        </div>
                        
                        <div id="file_preview_area" style="margin-top:10px; display:none;">
                            <div class="alert alert-info" style="margin-bottom:0; padding:8px 12px;">
                                <i class="fa fa-file"></i> 
                                <span id="file_preview_name"></span>
                                <span id="file_preview_size" style="color:#666; font-size:12px; margin-left:8px;"></span>
                            </div>
                            <div id="file_preview_image" style="display:none; margin-top:8px;">
                                <img id="preview_img" src="" style="max-width:100%; max-height:200px; border:1px solid #ddd; border-radius:4px;">
                            </div>
                        </div>

                        <?php if ( isset($data['attachment']) && !empty($data['attachment']) ) : ?>
                            <div id="old_file_alert" style="margin-top:10px;">
                                <div class="alert alert-success" style="margin-bottom:0; padding:8px 12px;">
                                    <i class="fa fa-check-circle"></i> 
                                    File tersimpan: 
                                    <a href="<?php echo base_url('uploads/aset/' . $data['attachment']); ?>" target="_blank" style="font-weight:600;">
                                        Lihat / Download Dokumen
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <div style="padding-top: 5px; display: flex; justify-content: space-between; align-items: center;">
                        
                        <!-- <div>
                            < ?php if ( isset($data['id']) && $data['status'] == 'Aktif' ) : ?>
                                <button type="button" class="btn btn-info" onclick="ma.open_depreciation_bootbox()" style="margin-right: 8px;"> 
                                    <i class="fa fa-calculator"></i> Lihat Penyusutan
                                </button>
                            < ?php endif; ?>
                        </div> -->

                        <div>
                            <button type="button" class="btn btn-default" onclick="$('a[href=\'#history\']').trigger('click');" style="margin-right: 8px;">
                                Batal
                            </button>
                            
                            <?php if ( isset($data['id']) ) : ?>
                                <!-- < ?php if($data['is_locked'] == 0){?> -->
                                    <button type="button" class="btn btn-primary" onclick="ma.edit_data()">
                                        <i class="fa fa-save"></i> Simpan Perubahan
                                    </button>
                                <!-- < ?php } ?> -->
                            <?php else : ?>
                                <button type="button" class="btn btn-primary" onclick="ma.save_data()">
                                    <i class="fa fa-save"></i> Simpan
                                </button>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>

</fieldset>