<fieldset>
    <legend >Form Jenis Sewa</legend>

    <div class="row" style="padding:30px">
        <div class="col-xs-12 col-sm-12 col-md-12" style="padding-left:0; padding-right:0;">
            <form class="form-horizontal" role="form" style="max-width:100%; margin:0; padding:0;">
                <input type="hidden" id="id_jenis_sewa" value="<?php echo isset($data['id']) ? $data['id'] : ''; ?>">

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="kode_jenis_sewa" style="display:block; font-weight:600; margin-bottom:6px;">Kode Jenis Sewa</label>
                    <input type="text" class="form-control" id="kode_jenis_sewa" value="<?php echo isset($data['kode_jenis_sewa']) ? htmlspecialchars($data['kode_jenis_sewa']) : ''; ?>" placeholder="Masukkan kode jenis sewa" required>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="nama_jenis_sewa" style="display:block; font-weight:600; margin-bottom:6px;">Nama Jenis Sewa</label>
                    <input type="text" class="form-control" id="nama_jenis_sewa" value="<?php echo isset($data['nama_jenis_sewa']) ? htmlspecialchars($data['nama_jenis_sewa']) : ''; ?>" placeholder="Masukkan nama jenis sewa" required>
                </div>

                <!-- <div class="form-group" style="margin-bottom: 20px;">
                    <label for="keterangan" style="display:block; font-weight:600; margin-bottom:6px;">Keterangan</label>
                    <textarea class="form-control" id="keterangan" rows="3" placeholder="Masukkan keterangan"><?php echo isset($data['keterangan']) ? htmlspecialchars($data['keterangan']) : ''; ?></textarea>
                </div> -->

                <div class="form-group" style="margin-bottom: 0;">
                    <div style="padding-top: 5px;">
                        <?php if ( isset($data['id']) ) : ?>
                            <button type="button" class="btn btn-primary" onclick="mjs.edit_data()" style="margin-right:8px; margin-bottom:5px;">
                                <i class="fa fa-save"></i> Simpan Perubahan
                            </button>
                        <?php else : ?>
                            <button type="button" class="btn btn-primary" onclick="mjs.save_data()" style="margin-right:8px; margin-bottom:5px;">
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
