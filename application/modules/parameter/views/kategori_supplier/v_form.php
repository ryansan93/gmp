<fieldset>
    <legend>Form Kategori Supplier</legend>

    <div class="row" style="padding:30px">
        <div class="col-xs-12 col-sm-12 col-md-12" style="padding-left:0; padding-right:0;">
            <form class="form-horizontal" role="form" style="max-width:100%; margin:0; padding:0;">
                <input type="hidden" id="id_kategori_supplier" value="<?php echo isset($data['id']) ? $data['id'] : ''; ?>">

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="kode_kategori" style="display:block; font-weight:600; margin-bottom:6px;">Kode Kategori</label>
                    <input type="text" class="form-control" id="kode_kategori" value="<?php echo isset($data['kode_kategori']) ? htmlspecialchars($data['kode_kategori']) : ''; ?>" placeholder="Masukkan kode kategori" required>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="nama_kategori" style="display:block; font-weight:600; margin-bottom:6px;">Nama Kategori</label>
                    <input type="text" class="form-control" id="nama_kategori" value="<?php echo isset($data['nama_kategori']) ? htmlspecialchars($data['nama_kategori']) : ''; ?>" placeholder="Masukkan nama kategori" required>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <div style="padding-top: 5px;">
                        <?php if ( isset($data['id']) ) : ?>
                            <button type="button" class="btn btn-primary" onclick="ks.edit_data()" style="margin-right:8px; margin-bottom:5px;">
                                <i class="fa fa-save"></i> Simpan Perubahan
                            </button>
                        <?php else : ?>
                            <button type="button" class="btn btn-primary" onclick="ks.save_data()" style="margin-right:8px; margin-bottom:5px;">
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
