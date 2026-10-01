<input type="hidden" id="config-form" value="<?php echo (($data['is_locked'] ?? 0) == 1 || $cek_amortisasi == 1) ? 1 : 0; ?>">

<fieldset>
    <legend>Form Sewa</legend>

    <div class="row" style="padding-left:30px; padding-right:30px">
        <div class="col-xs-12 col-sm-12 col-md-12" style="padding-left:0; padding-right:0;">
            <form class="form-horizontal" role="form" style="max-width:100%; margin:0; padding:0;">
                <input type="hidden" id="id_sewa" value="<?php echo isset($data['id']) ? $data['id'] : ''; ?>">

                <?php if ( isset($data['id']) ) : ?>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="no_sewa" style="display:block; font-weight:600; margin-bottom:6px;">No. Sewa</label>
                        <input type="text" class="form-control" id="no_sewa" value="<?php echo isset($data['no_sewa']) ? htmlspecialchars($data['no_sewa']) : ''; ?>" readonly>
                    </div>
                <?php endif; ?>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="no_kontrak" style="display:block; font-weight:600; margin-bottom:6px;">No. Kontrak</label>
                    <input type="text" class="form-control" autocomplete="off" id="no_kontrak" value="<?php echo isset($data['no_kontrak']) ? htmlspecialchars($data['no_kontrak']) : ''; ?>" placeholder="Masukkan nomor kontrak" required>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="no_supplier" style="display:block; font-weight:600; margin-bottom:6px;">No. Supplier</label>
                    <select id="no_supplier" class="form-control" style="width:100%;" required>
                        <option value="">Pilih</option>
                        <?php if ( !empty($supplier) ) : ?>
                            <?php foreach ( $supplier as $row ) : ?>
                                <?php $no_supplier = isset($row['nomor']) ? trim($row['nomor']) : ''; ?>
                                <?php $nama_supplier = isset($row['nama']) ? trim($row['nama']) : ''; ?>
                                <option value="<?php echo htmlspecialchars($no_supplier); ?>" <?php echo (isset($data['no_supplier']) && strtoupper(trim($data['no_supplier'])) == strtoupper($no_supplier)) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($no_supplier . ' - ' . $nama_supplier); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>


                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="unit" style="display:block; font-weight:600; margin-bottom:6px;">Unit</label>
                    <select id="unit" class="form-control" style="width:100%;" required>
                        <option value="">Pilih</option>
                        <?php if ( !empty($unit) ) : ?>
                            <?php foreach ( $unit as $row ) : ?>
                                <?php $kode_unit = isset($row['kode']) ? trim($row['kode']) : ''; ?>
                                <?php $nama_unit = isset($row['nama']) ? trim($row['nama']) : ''; ?>
                                <option value="<?php echo htmlspecialchars($kode_unit); ?>" <?php echo (isset($data['unit']) && strtoupper(trim($data['unit'])) == strtoupper($kode_unit)) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($kode_unit . ' - ' . str_replace(['KAB ', 'KOTA '], '', strtoupper($nama_unit))); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="row" style="margin:0 -20px;">
                    <div class="col-xs-12 col-sm-6" style="padding:0 6px; margin-bottom:15px;">
                        <label for="jenis_sewa" style="display:block; font-weight:600; margin-bottom:6px;">Jenis Sewa</label>
                        <select id="jenis_sewa" class="form-control" style="width:100%;" required>
                            <option value="">Pilih</option>
                            <?php if ( !empty($jenis_sewa) ) : ?>
                                <?php foreach ( $jenis_sewa as $row ) : ?>
                                    <?php $kode = isset($row['kode_jenis_sewa']) ? trim($row['kode_jenis_sewa']) : ''; ?>
                                    <?php $nama = isset($row['nama_jenis_sewa']) ? trim($row['nama_jenis_sewa']) : ''; ?>
                                    <option value="<?php echo htmlspecialchars($kode); ?>" data-nama-sewa="<?php echo htmlspecialchars($nama); ?>" <?php echo (isset($data['jenis_sewa']) && strtoupper(trim($data['jenis_sewa'])) == strtoupper($kode)) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($kode . ' - ' . $nama); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-xs-12 col-sm-6" style="padding:0 6px; margin-bottom:15px;">
                        <label for="nama_sewa" style="display:block; font-weight:600; margin-bottom:6px;">Nama Sewa</label>
                        <input type="text" autocomplete="off" class="form-control" id="nama_sewa" value="<?php echo isset($data['nama_sewa']) ? htmlspecialchars($data['nama_sewa']) : ''; ?>" placeholder="Pilih jenis sewa" required>
                    </div>
                </div>

                <div class="row config-jumlah" style="margin:0 -20px;">
                    <div class="col-xs-12 col-sm-3" style="padding:0 6px; margin-bottom:15px;">
                        <label for="jumlah_bulan" style="display:block; font-weight:600; margin-bottom:6px;">Jumlah Bulan</label>
                        <input onchange="ms.configJumlah(this, event)" type="number" class="form-control" id="jumlah_bulan" min="1" value="<?php echo isset($data['jumlah_bulan']) ? htmlspecialchars($data['jumlah_bulan']) : ''; ?>" placeholder="Bulan">
                    </div>
                    <div class="col-xs-12 col-sm-3" style="padding:0 6px; margin-bottom:15px;">
                        <label for="jumlah_siklus" style="display:block; font-weight:600; margin-bottom:6px;">Jumlah Siklus</label>
                        <input onchange="ms.configJumlah(this, event)" type="number" class="form-control" id="jumlah_siklus" min="1" value="<?php echo isset($data['jumlah_siklus']) ? htmlspecialchars($data['jumlah_siklus']) : ''; ?>" placeholder="Siklus">
                    </div>
                    <div class="col-xs-12 col-sm-3" style="padding:0 6px; margin-bottom:15px;">
                        <label for="tanggal_mulai" style="display:block; font-weight:600; margin-bottom:6px;">Tanggal Mulai Amortisasi</label>
                        <div class="input-group date" id="tanggal_mulai_picker">
                            <input type="text" class="form-control" placeholder="Pilih Tanggal" id="tanggal_mulai" style="caret-color: transparent; background-color:#E8E8E8;" value="<?php echo isset($data['tanggal_mulai']) ? htmlspecialchars(date('Y-m-d', strtotime($data['tanggal_mulai']))) : ''; ?>" required onkeydown="return false;" onpaste="return false;" ondrop="return false;" autocomplete="off">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-3" style="padding:0 6px; margin-bottom:15px;">
                        <label for="nominal_sewa" style="display:block; font-weight:600; margin-bottom:6px;">Nominal Perolehan</label>
                        <div class="input-group">
                            <span class="input-group-addon">Rp</span>
                            <input type="text" class="form-control" autocomplete="off" id="nominal_sewa" value="<?php echo isset($data['nominal_sewa']) ? htmlspecialchars($data['nominal_sewa']) : ''; ?>" placeholder="Masukkan nominal sewa" inputmode="numeric">
                        </div>
                    </div>
                </div>

                <div class="row" style="margin:0 -20px;">

                    <div class="col-xs-12 col-sm-3" style="padding:0 6px; margin-bottom:15px;">
                        <label for="durasi_cicilan" style="display:block; font-weight:600; margin-bottom:6px;">Durasi Cicilan (Bulan)</label>
                        <input type="number" onchange="ms.hitungCicilan();" class="form-control" id="durasi_cicilan"  value="<?php echo isset($data['durasi_cicilan']) ? htmlspecialchars($data['durasi_cicilan']) : ''; ?>" placeholder="Durasi cicilan">
                    </div>
                    <div class="col-xs-12 col-sm-3" style="padding:0 6px; margin-bottom:15px;">
                        <label for="dp" style="display:block; font-weight:600; margin-bottom:6px;">DP</label>
                        <div class="input-group">
                            <span class="input-group-addon">Rp</span>
                            <input type="text" onchange="ms.hitungCicilan();" class="form-control" autocomplete="off" id="dp" value="<?php echo isset($data['dp']) ? htmlspecialchars($data['dp']) : ''; ?>" placeholder="Masukkan DP" >
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-3" style="padding:0 6px; margin-bottom:15px;">
                        <label for="nominal_cicilan" style="display:block; font-weight:600; margin-bottom:6px;">Nominal Cicilan</label>
                        <div class="input-group">
                            <span class="input-group-addon">Rp</span>
                            <input type="text" class="form-control" autocomplete="off" id="nominal_cicilan" value="<?php echo isset($data['nominal_cicilan']) ? htmlspecialchars($data['nominal_cicilan']) : ''; ?>" placeholder="Masukkan nominal cicilan" inputmode="numeric">
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-3" style="padding:0 6px; margin-bottom:15px;">
                        <label for="tgl_jatuh_tempo" style="display:block; font-weight:600; margin-bottom:6px;">Tgl Jatuh Tempo (Angka)</label>
                        <input type="number" class="form-control" id="tgl_jatuh_tempo" min="1" max="31" value="<?php echo isset($data['tgl_jatuh_tempo']) ? htmlspecialchars($data['tgl_jatuh_tempo']) : ''; ?>" placeholder="Tanggal jatuh tempo">
                    </div>
                </div>

                <div class="row" style="margin:0 -20px;">
                    <div class="col-xs-12 col-sm-6" style="padding:0 6px; margin-bottom:15px;">
                        <label for="file_dokumen" style="display:block; font-weight:600; margin-bottom:6px;">
                            <i class="fa fa-paperclip"></i> Dokumen Pendukung
                            <small style="font-weight:normal; color:#888;">(PDF / JPG / PNG, Maks 5MB)</small>
                        </label>
                        
                        <div class="input-group">
                            <input type="file" class="form-control" id="file_dokumen" accept=".pdf,.jpg,.jpeg,.png" onchange="ms.previewFile(this)">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default" onclick="ms.clearFile()" title="Hapus file">
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
                                    <a href="<?php echo base_url('uploads/sewa/' . $data['attachment']); ?>" target="_blank" style="font-weight:600;">
                                        Lihat / Download Dokumen
                                    </a>
                                    <button type="button" class="close" onclick="ms.clearOldFile()" style="margin-left:10px;" title="Hapus file lama">
                                        <span>&times;</span>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <div style="padding-top: 5px; display: flex; justify-content: space-between; align-items: center;">
                        
                        <div>
                            <?php if ( isset($data['id']) ) : ?>
                                <button type="button" class="btn btn-warning" onclick="ms.open_amortisasi_bootbox()" style="margin-right: 8px;"> 
                                    <i class="fa fa-edit"></i> Edit Amortisasi
                                </button>
                            <?php endif; ?>
                        </div>

                        <div>
                            <button type="button" class="btn btn-default" onclick="$('a[href=\'#history\']').trigger('click');" style="margin-right: 8px;">
                                Batal
                            </button>
                            
                            <?php if ( isset($data['id']) ) : ?>
                                <?php if($data['is_locked'] == 0 && $cek_amortisasi == 0){?>
                                    <button type="button" class="btn btn-primary" onclick="ms.edit_data()">
                                        <i class="fa fa-save"></i> Simpan Perubahan
                                    </button>
                                <?php } ?>
                            <?php else : ?>
                                <button type="button" class="btn btn-primary" onclick="ms.save_data()">
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