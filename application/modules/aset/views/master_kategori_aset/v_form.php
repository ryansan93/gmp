<fieldset>
    <legend>Form Kategori Aset</legend>

    <div class="row" style="padding:30px">
        <div class="col-xs-12 col-sm-12 col-md-12" style="padding-left:0; padding-right:0;">
            <form class="form-horizontal" role="form" style="max-width:100%; margin:0; padding:0;">
                <input type="hidden" id="id_kategori" value="<?php echo isset($data['id']) ? $data['id'] : ''; ?>">

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="kategori_kode" style="display:block; font-weight:600; margin-bottom:6px;">Kode Kategori</label>
                    <input type="text" class="form-control" id="kategori_kode" 
                           value="<?php echo isset($data['kategori_kode']) ? htmlspecialchars($data['kategori_kode']) : ''; ?>" 
                           placeholder="Contoh: INV, KMT, KMB" required>
                    <small class="text-muted">Kode singkat kategori (misal: INV = Inventaris, KMT = Kendaraan Motor, KMB = Kendaraan Mobil)</small>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="kategori_name" style="display:block; font-weight:600; margin-bottom:6px;">Nama Kategori</label>
                    <input type="text" class="form-control" id="kategori_name" value="<?php echo isset($data['kategori_name']) ? htmlspecialchars($data['kategori_name']) : ''; ?>" placeholder="Masukkan nama kategori aset" required>
                </div>

                <div class="row" style="margin:0 -20px;">
                    <div class="col-xs-12 col-sm-6" style="padding:0 6px; margin-bottom:15px;">
                        <label for="jumlah_bulan" style="display:block; font-weight:600; margin-bottom:6px;">Masa Manfaat Komersial <small class="text-muted">(dalam Bulan)</small></label>
                        <input type="number" class="form-control" id="masa_manfaat_komersial" min="1" value="<?php echo isset($data['masa_manfaat_komersial']) ? htmlspecialchars($data['masa_manfaat_komersial']) : ''; ?>"  placeholder="Contoh: 48 (4 tahun) atau 60 (5 tahun)" required>
                    </div>
                    <div class="col-xs-12 col-sm-6" style="padding:0 6px; margin-bottom:15px;">
                        <label for="jumlah_siklus" style="display:block; font-weight:600; margin-bottom:6px;"> Masa Manfaat Fiskal <small class="text-muted">(dalam Bulan)</small></label>
                         <input type="number" class="form-control" id="masa_manfaat_fiskal" min="1" value="<?php echo isset($data['masa_manfaat_fiskal']) ? htmlspecialchars($data['masa_manfaat_fiskal']) : ''; ?>"  placeholder="Contoh: 48 (4 tahun) atau 96 (8 tahun)" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="id_kelompok" style="display:block; font-weight:600; margin-bottom:6px;">
                        Kelompok Aset <small class="text-muted">(Wajib Dipilih)</small>
                    </label>
                    <select class="form-control" id="id_kelompok" name="id_kelompok" required>
                        <option value="">-- Pilih Kelompok --</option>
                        <?php if (!empty($kelompok)) : ?>
                            <?php foreach ($kelompok as $row) : ?>
                                <?php
                                    $val                = isset($row['id']) ? $row['id'] : (isset($row->id) ? $row->id : '');
                                    $nama               = isset($row['nama_kelompok']) ? $row['nama_kelompok'] : (isset($row->nama_kelompok) ? $row->nama_kelompok : '');                            
                                    $umur_ekonomis      = isset($row['umur_ekonomis']) ? $row['umur_ekonomis'] : (isset($row->umur_ekonomis) ? $row->umur_ekonomis : 0);
                                    $trf_garis_lurus    = isset($row['trf_garis_lurus']) ? $row['trf_garis_lurus'] : (isset($row->trf_garis_lurus) ? $row->trf_garis_lurus : 0);
                                    $trf_saldo_menurun  = isset($row['trf_saldo_menurun']) ? $row['trf_saldo_menurun'] : (isset($row->trf_saldo_menurun) ? $row->trf_saldo_menurun : 0);
                                    $label              = $nama . " (Umur: " . $umur_ekonomis . " Thn, GL: " . $trf_garis_lurus . "%, SM: " . $trf_saldo_menurun . "%)";                        
                                    $selected           = (isset($data['id_kelompok']) && $data['id_kelompok'] == $val) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $val; ?>" 
                                        data-umur-ekonomis="<?php echo $umur_ekonomis; ?>"
                                        data-trf-garis-lurus="<?php echo $trf_garis_lurus; ?>"
                                        data-trf-saldo-menurun="<?php echo $trf_saldo_menurun; ?>"
                                        <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($label); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <small class="text-muted">Pilih kelompok aset yang sesuai dengan kategori ini</small>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <div style="padding-top: 5px;">
                        <?php if ( isset($data['id']) ) : ?>
                            <button type="button" class="btn btn-primary" onclick="mka.edit_data()" style="margin-right:8px; margin-bottom:5px;">
                                <i class="fa fa-save"></i> Simpan Perubahan
                            </button>
                        <?php else : ?>
                            <button type="button" class="btn btn-primary" onclick="mka.save_data()" style="margin-right:8px; margin-bottom:5px;">
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