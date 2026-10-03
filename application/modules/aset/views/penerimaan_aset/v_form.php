<fieldset>
    <legend>Form Penerimaan Aset</legend>
    
    <div class="row" style="padding-left:30px; padding-right:30px">
        <div class="col-xs-12 col-sm-12 col-md-12" style="padding-left:0; padding-right:0;">
            <form class="form-horizontal" role="form" style="max-width:100%; margin:0; padding:0;">

                <input type="hidden" id="id_penerimaan" value="<?php echo isset($data['id']) ? $data['id'] : ''; ?>">

                <?php if ( isset($data['id']) ) : ?>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="kode_penerimaan" style="display:block; font-weight:600; margin-bottom:6px;">Kode Penerimaan</label>
                        <input type="text" class="form-control" id="kode_penerimaan" value="<?php echo isset($data['kode_penerimaan']) ? htmlspecialchars($data['kode_penerimaan']) : ''; ?>" readonly style="background-color: #f5f5f5;">
                    </div>
                <?php endif; ?>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="kode_aset" style="display:block; font-weight:600; margin-bottom:6px;">Kode Aset <span class="text-danger">*</span></label>
                    <select id="kode_aset" class="form-control select2" style="width:100%;" required>
                        <option value="">-- Pilih Kode Aset --</option>
                        <?php if ( !empty($list_kode_aset) ) : ?>
                            <?php foreach ( $list_kode_aset as $row ) : ?>
                                <?php 
                                    $kode       = isset($row['kode_aset']) ? trim($row['kode_aset']) : '';
                                    $kategori   = isset($row['kategori_name']) ? trim($row['kategori_name']) : '';
                                    $deskripsi  = isset($row['deskripsi_aset']) ? trim($row['deskripsi_aset']) : '';
                                    $selected   = (isset($data['kode_aset']) && $data['kode_aset'] == $kode) ? 'selected' : '';
                                    $tgl_perolehan   = isset($row['tgl_perolehan']) ? trim($row['tgl_perolehan']) : '';
                                    $document_no   = isset($row['document_no']) ? trim($row['document_no']) : '';
                                    
                                ?>
                                <option document_no="<?php echo $document_no; ?>" tgl_perolehan="<?php echo $tgl_perolehan; ?>" value="<?php echo htmlspecialchars($kode); ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($kode . ' | ' . $kategori . ' - ' . $deskripsi); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <small class="text-muted">
                        <i class="fa fa-info-circle"></i> 
                        Hanya menampilkan aset yang belum pernah diterima.
                    </small>
                </div>

                <div class="row" style="margin:0 -20px;">
                    <div class="col-xs-12 col-sm-6" style="padding:0 6px; margin-bottom:15px;">
                        <label for="lokasi_pengguna" style="display:block; font-weight:600; margin-bottom:6px;">Lokasi Digunakan</span></label>
                        <select id="lokasi_pengguna" class="form-control select2" style="width:100%;" >
                            <option value="">-- Pilih Lokasi --</option>
                            <?php if ( !empty($unit) ) : ?>
                                <?php foreach ( $unit as $row ) : ?>
                                    <?php $kode = isset($row['kode']) ? trim($row['kode']) : ''; ?>
                                    <?php $nama = isset($row['nama']) ? trim($row['nama']) : ''; ?>
                                    <option value="<?php echo htmlspecialchars($kode); ?>" <?php echo (isset($data['lokasi_pengguna']) && strtoupper(trim($data['lokasi_pengguna'])) == strtoupper($kode)) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($kode . ' - ' . str_replace(['KAB ', 'KOTA '], '', strtoupper($nama))); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="col-xs-12 col-sm-6" style="padding:0 6px; margin-bottom:15px;">
                        <label for="pic" style="display:block; font-weight:600; margin-bottom:6px;">Nama Pengguna (PIC)</label>
                        <select id="pic" class="form-control select2" style="width:100%;" >
                            <option value="">-- Pilih PIC --</option>
                            <?php if (!empty($pic)) { ?>
                                <?php foreach ( $pic as $row ) : ?>
                                    <?php $nik = isset($row['nik']) ? trim($row['nik']) : ''; ?>
                                    <?php $nama = isset($row['nama']) ? trim($row['nama']) : ''; ?>
                                    <?php $jabatan = isset($row['nama_jabatan']) ? trim($row['nama_jabatan']) : ''; ?>
                                    <option value="<?php echo htmlspecialchars($nik); ?>" <?php echo (isset($data['pic']) && strtoupper(trim($data['pic'])) == strtoupper($nik)) ? 'selected' : ''; ?>>
                                        <?php echo strtoupper($nama) . ' - ' . strtoupper($jabatan); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="row" style="margin:0 -20px;">
                    <div class="col-xs-12 col-sm-6" style="padding:0 6px; margin-bottom:15px;">
                        <label for="tgl_penerimaan" style="display:block; font-weight:600; margin-bottom:6px;">Tanggal Penerimaan <span class="text-danger">*</span></label>
                        <div class="input-group date" id="tgl_penerimaan_picker">
                            <input type="text" class="form-control" placeholder="Pilih Tanggal" id="tgl_penerimaan" style="caret-color: transparent; background-color:#fff;" value="<?php echo isset($data['tgl_penerimaan']) ? htmlspecialchars(date('Y-m-d', strtotime($data['tgl_penerimaan']))) : ''; ?>" required onkeydown="return false;" onpaste="return false;" ondrop="return false;" autocomplete="off">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        </div>
                    </div>
                </div>

                <hr style="margin: 20px 0; border-top: 1px dashed #ccc;">

                <div class="row" style="margin:0 -20px;">
                        
                    <div class="col-xs-12 col-sm-6" style="padding:0 6px; margin-bottom:15px;">
                        <label for="no_bukti_terima" style="display:block; font-weight:600; margin-bottom:6px;">No. Bukti Penerimaan <span class="text-danger">*</span></label>
                        <input type="text" autocomplete="off" class="form-control" id="no_bukti_terima" value="<?php echo isset($data['no_bukti_terima']) ? htmlspecialchars($data['no_bukti_terima']) : ''; ?>" placeholder="Contoh : No. Surat Jalan"> 
                    </div>

                    <div class="col-xs-12 col-sm-6" style="padding:0 6px; margin-bottom:15px;">
                        <label for="file_dokumen" style="display:block; font-weight:600; margin-bottom:6px;">
                            <i class="fa fa-paperclip"></i> Dokumen Pendukung (BAST / Foto Kondisi)
                            <small style="font-weight:normal; color:#888;">(PDF / JPG / PNG, Maks 5MB)</small>
                        </label>
                        
                        <div class="input-group">
                            <input type="file" class="form-control" id="file_dokumen" accept=".pdf,.jpg,.jpeg,.png" onchange="pa.previewFile(this)">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default" onclick="pa.clearFile()" title="Hapus file">
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
                                    <a href="<?php echo base_url('uploads/penerimaan_aset/' . $data['attachment']); ?>" target="_blank" style="font-weight:600;">
                                        Lihat / Download Dokumen
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                </div>

                <div class="row" style="margin:0 -20px;">
                    <div class="col-xs-12 col-sm-12" style="padding:0 6px; margin-bottom:15px;">
                        <label for="bukti_terima" style="display:block; font-weight:600; margin-bottom:6px;">Keterangan Bukti Penerimaan</label>
                        <textarea type="text" autocomplete="off" style="height:80px;" class="form-control" id="bukti_terima" placeholder="Keterangan bukti penerimaan"> <?php echo isset($data['keterangan_terima']) ? htmlspecialchars($data['keterangan_terima']) : ''; ?> </textarea>
                    </div>
                </div>

                <div class="row" style="margin:0 -20px;">
                    <div style="padding:0 6px; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <button type="button" class="btn btn-default" onclick="$('a[href=\'#history\']').trigger('click');" style="margin-right: 8px;">
                                <i class="fa fa-times"></i> Batal
                            </button>
                        </div>

                        <div>
                            <?php if ( isset($data['id']) ) : ?>
                                <button type="button" class="btn btn-primary" onclick="pa.edit_data()">
                                    <i class="fa fa-save"></i> Simpan Perubahan
                                </button>
                            <?php else : ?>
                                <button type="button" class="btn btn-primary" onclick="pa.save_data()">
                                    <i class="fa fa-save"></i> Simpan Data Penerimaan
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>


                

            </form>
        </div>
    </div>
</fieldset>