<fieldset>
    <legend>Form Kelompok Aset</legend>

    <div class="row" style="padding:30px">
        <div class="col-xs-12 col-sm-12 col-md-12" style="padding-left:0; padding-right:0;">
            <form class="form-horizontal" role="form" style="max-width:100%; margin:0; padding:0;">
                <input type="hidden" id="id" value="<?php echo isset($data['id']) ? $data['id'] : ''; ?>">

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="nama_kelompok" style="display:block; font-weight:600; margin-bottom:6px;">Nama Kelompok</label>
                    <input type="text" class="form-control" id="nama_kelompok" 
                           value="<?php echo isset($data['nama_kelompok']) ? htmlspecialchars($data['nama_kelompok']) : ''; ?>" 
                           placeholder="Contoh: Kelompok 1, Kelompok 2, Kelompok 3, Kelompok 4" required>
                    <small class="text-muted">Nama kelompok aset tetap</small>
                </div>

                <div class="row" style="margin:0 -20px;">
                    <div class="col-xs-12 col-sm-6" style="padding:0 6px; margin-bottom:15px;">
                        <label for="umur_ekonomis" style="display:block; font-weight:600; margin-bottom:6px;">
                            Umur Ekonomis <small class="text-muted">(dalam Tahun)</small>
                        </label>
                        <input type="number" class="form-control" id="umur_ekonomis" 
                               min="1" 
                               value="<?php echo isset($data['umur_ekonomis']) ? htmlspecialchars($data['umur_ekonomis']) : ''; ?>" 
                               placeholder="Contoh: 4, 8, 16, 20" required>
                        <small class="text-muted">Masa manfaat ekonomis aset (dalam tahun)</small>
                    </div>
                    <div class="col-xs-12 col-sm-6" style="padding:0 6px; margin-bottom:15px;">
                        <label for="deskripsi" style="display:block; font-weight:600; margin-bottom:6px;">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" rows="3"
                                  placeholder="Deskripsi kelompok aset"><?php echo isset($data['deskripsi']) ? htmlspecialchars($data['deskripsi']) : ''; ?></textarea>
                        <small class="text-muted">Keterangan tambahan mengenai kelompok aset</small>
                    </div>
                </div>

                <div class="row" style="margin:0 -20px;">
                    <div class="col-xs-12 col-sm-6" style="padding:0 6px; margin-bottom:15px;">
                        <label for="trf_garis_lurus" style="display:block; font-weight:600; margin-bottom:6px;">
                            Tarif Garis Lurus <small class="text-muted">(%)</small>
                        </label>
                        <input type="number" class="form-control" id="trf_garis_lurus" 
                               step="0.01" min="0" max="100"
                               value="<?php echo isset($data['trf_garis_lurus']) ? htmlspecialchars($data['trf_garis_lurus']) : ''; ?>" 
                               placeholder="Contoh: 12.5, 25, 10">
                        <small class="text-muted">Tarif penyusutan metode Garis Lurus</small>
                    </div>
                    <div class="col-xs-12 col-sm-6" style="padding:0 6px; margin-bottom:15px;">
                        <label for="trf_saldo_menurun" style="display:block; font-weight:600; margin-bottom:6px;">
                            Tarif Saldo Menurun <small class="text-muted">(%)</small>
                        </label>
                        <input type="number" class="form-control" id="trf_saldo_menurun" 
                               step="0.01" min="0" max="100"
                               value="<?php echo isset($data['trf_saldo_menurun']) ? htmlspecialchars($data['trf_saldo_menurun']) : ''; ?>" 
                               placeholder="Contoh: 25, 50">
                        <small class="text-muted">Tarif penyusutan metode Saldo Menurun Berganda</small>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <div style="padding-top: 5px;">
                        <?php if ( isset($data['id']) ) : ?>
                            <button type="button" class="btn btn-primary" onclick="mk.edit_data()" style="margin-right:8px; margin-bottom:5px;">
                                <i class="fa fa-save"></i> Simpan Perubahan
                            </button>
                        <?php else : ?>
                            <button type="button" class="btn btn-primary" onclick="mk.save_data()" style="margin-right:8px; margin-bottom:5px;">
                                <i class="fa fa-save"></i> Simpan
                            </button>
                        <?php endif; ?>
                        <button type="button" class="btn btn-default" onclick="$('a[href=\'#history\']').trigger('click');" style="margin-bottom:5px;">
                            Batal
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</fieldset>