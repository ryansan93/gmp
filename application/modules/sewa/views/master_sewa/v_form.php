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
                    <div class="col-xs-12 col-sm-4" style="padding:0 6px; margin-bottom:15px;">
                        <label for="jumlah_bulan" style="display:block; font-weight:600; margin-bottom:6px;">Jumlah Bulan</label>
                        <input onchange="ms.configJumlah(this, event)" type="number" class="form-control" id="jumlah_bulan" min="1" value="<?php echo isset($data['jumlah_bulan']) ? htmlspecialchars($data['jumlah_bulan']) : ''; ?>" placeholder="Bulan">
                    </div>
                    <div class="col-xs-12 col-sm-4" style="padding:0 6px; margin-bottom:15px;">
                        <label for="jumlah_siklus" style="display:block; font-weight:600; margin-bottom:6px;">Jumlah Siklus</label>
                        <input onchange="ms.configJumlah(this, event)" type="number" class="form-control" id="jumlah_siklus" min="1" value="<?php echo isset($data['jumlah_siklus']) ? htmlspecialchars($data['jumlah_siklus']) : ''; ?>" placeholder="Siklus">
                    </div>
                    <div class="col-xs-12 col-sm-4" style="padding:0 6px; margin-bottom:15px;">
                        <label for="tanggal_mulai" style="display:block; font-weight:600; margin-bottom:6px;">Tanggal Mulai</label>
                        <div class="input-group date" id="tanggal_mulai_picker">
                            <input type="text" class="form-control" placeholder="Pilih Tanggal" id="tanggal_mulai" style="caret-color: transparent; background-color:#E8E8E8;" value="<?php echo isset($data['tanggal_mulai']) ? htmlspecialchars(date('Y-m-d', strtotime($data['tanggal_mulai']))) : ''; ?>" required onkeydown="return false;" onpaste="return false;" ondrop="return false;" autocomplete="off">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                        </div>
                    </div>
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

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="nominal_sewa" style="display:block; font-weight:600; margin-bottom:6px;">Nominal Sewa</label>
                    <div class="input-group">
                        <span class="input-group-addon">Rp</span>
                        <input type="text" class="form-control" autocomplete="off" id="nominal_sewa" value="<?php echo isset($data['nominal_sewa']) ? htmlspecialchars($data['nominal_sewa']) : ''; ?>" placeholder="Masukkan nominal sewa" inputmode="numeric">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <div style="padding-top: 5px;">
                        <?php if ( isset($data['id']) ) : ?>
                            <button type="button" class="btn btn-primary" onclick="ms.edit_data()" style="margin-right:8px; margin-bottom:5px;">
                                <i class="fa fa-save"></i> Simpan Perubahan
                            </button>
                        <?php else : ?>
                            <button type="button" class="btn btn-primary" onclick="ms.save_data()" style="margin-right:8px; margin-bottom:5px;">
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