<div class="row">

  <!-- ============ KOLOM KIRI: INDIKATOR TIMBANGAN ============ -->
  <div class="col-md-5">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><strong>Indikator Timbangan (RS-232)</strong></h3>
      </div>

      <div class="panel-body text-center">

        <div id="tile_berat" class="tile tile-default" style="padding: 25px 15px;">
          <span id="span_berat" style="font-size: 52px; font-weight: 700; line-height: 1;">0</span>
          <span style="font-size: 20px; font-weight: 600;"> g</span>
          <p style="margin-top: 8px;"><small id="span_berat_kg">0 kg</small></p>
        </div>

        <div id="span_status" class="label label-default" style="display:inline-block; margin-top:10px; font-size:14px; padding:6px 14px;">STANDBY</div>
        <div id="sisa_waktu" class="text-muted" style="display:none; margin-top:6px; font-size:12px;"></div>

        <hr />

        <div class="form-group">
          <label class="control-label">Baud Rate</label>
          <select id="baud_rate" class="form-control">
            <?php foreach (array(2400, 4800, 9600, 19200, 38400, 57600, 115200) as $baud) : ?>
              <option value="<?= $baud ?>" <?= $setting['BAUD_RATE'] == $baud ? 'selected' : '' ?>><?= $baud ?> bps</option>
            <?php endforeach; ?>
          </select>
        </div>

        <button type="button" id="btn_connect" class="btn btn-primary"><i class="fa fa-plug"></i> Hubungkan Port</button>
        <button type="button" id="btn_disconnect" class="btn btn-danger" disabled><i class="fa fa-times"></i> Putuskan</button>

        <div class="tile tile-default" style="margin-top:15px; text-align:left;">
          <small class="text-muted">Data mentah terakhir:</small><br />
          <code id="span_raw">-</code>
        </div>

        <div id="alert_serial" class="alert alert-warning" style="display:none; text-align:left; margin-top:15px;"></div>

      </div>
    </div>

    <!-- Jenis kemasan paket.
         Pilihan ini berlaku per paket dan tidak mengubah pengaturan bersama:
         beberapa meja timbang bekerja bersamaan, dan pilihan satu meja tidak
         boleh menggeser hitungan meja lain. -->
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title">Jenis Kemasan</h3>
      </div>
      <div class="panel-body">
        <div class="checkbox" style="margin-top:0;">
          <label style="font-size:15px;">
            <input type="checkbox" id="pakai_kardus" /> <strong>Pakai kotak / kardus</strong>
          </label>
        </div>
        <p style="margin-bottom:0; font-weight:700; color:#333;">
          <small>Centang bila barang dimasukkan ke kotaknya dulu, lalu kotak itu masuk ke zipper.</small>
        </p>

        <div id="box_kardus" style="display:none; margin-top:8px;">
          <select id="berat_kardus" class="form-control">
            <?php foreach ($pilihan_kemasan as $kemasan) : ?>
              <?php if (empty($kemasan['lengkap'])) : ?>
                <option value="" disabled><?= html_escape($kemasan['nama']) ?> &mdash; belum didata</option>
              <?php else : ?>
                <option value="<?= html_escape($kemasan['kode']) ?>">
                  <?= html_escape($kemasan['nama']) ?>
                  &mdash; <?= rtrim(rtrim(number_format($kemasan['berat'], 2, ',', '.'), '0'), ',') ?> gram<?= $kemasan['perlu_jumlah'] ? ' / box' : '' ?>
                </option>
              <?php endif; ?>
            <?php endforeach; ?>
          </select>

          <div id="ringkas_kemasan" class="text-muted" style="display:none; margin-top:6px; font-size:12px;"></div>
        </div>

        <p style="margin-top:10px; margin-bottom:0; font-weight:700; color:#333;">
          <small>Tanpa dicentang, berat kemasan memakai nilai bawaan
            zipper <?= rtrim(rtrim(number_format((float) $setting['BERAT_KEMASAN'], 2, ',', '.'), '0'), ',') ?> gram.</small>
        </p>
      </div>
    </div>

    <!-- Input manual bila indikator tidak tersambung -->
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title">Input Berat Manual</h3>
      </div>
      <div class="panel-body">
        <p class="text-muted"><small>Dipakai bila indikator tidak bisa dibaca otomatis. Nonaktif selama port serial terhubung.</small></p>
        <div class="input-group">
          <input type="number" step="0.01" min="0" id="berat_manual" class="form-control" placeholder="Berat sesuai indikator" />
          <span class="input-group-addon"><?= $setting['SATUAN_INDIKATOR'] ?></span>
        </div>
      </div>
    </div>
  </div>

  <!-- ============ KOLOM KANAN: RESI ============ -->
  <div class="col-md-7">
    <!-- class `nojs`: pengiriman form ditangani skrip halaman ini sendiri.
         Tanpa penanda itu, handler global di plugins.js ikut menyambar submit,
         mengganti seluruh isi halaman dengan gambar loading, lalu menunggu
         balasan yang tidak pernah datang. -->
    <form action="timbangan/save-timbangan" class="form-horizontal nojs" id="form_timbangan" autocomplete="off">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title"><strong>Timbangan Resi</strong></h3>
        </div>

        <div class="panel-body">

          <div class="form-group">
            <label class="col-md-3 col-xs-12 control-label">Total timbang hari ini</label>
            <div class="col-md-8 col-xs-12">
              <input type="text" id="total_scan" value="<?= $total_scan ?>" class="form-control" disabled />
            </div>
          </div>

          <div class="form-group">
            <label class="col-md-3 col-xs-12 control-label">Komputer</label>
            <div class="col-md-8 col-xs-12">
              <input type="text" class="form-control" value="<?= $nama_komputer ?>" readonly />
            </div>
          </div>

          <div class="form-group">
            <label class="col-md-3 col-xs-12 control-label">Resi</label>
            <div class="col-md-8 col-xs-12">
              <input type="text" name="noresi" id="noresi" class="form-control" placeholder="Scan / ketik nomor resi lalu Enter" />
            </div>
          </div>

          <!-- Penanda tahap untuk resi campuran PLCA. Tersembunyi pada resi
               biasa supaya layar tidak ramai tanpa perlu. -->
          <div id="box_tahap" class="alert alert-info" style="display:none; font-size:16px; margin-bottom:12px;">
            <strong id="teks_tahap"></strong>
            <div id="teks_tahap_detail" style="font-size:13px; margin-top:4px;"></div>
          </div>

          <div class="tile tile-default" id="tile_pesan">
            <span id="span_pesan">-</span>
            <p><small id="span_pesan_detail">Scan nomor resi untuk memuat berat standar paket</small></p>
          </div>

          <div id="box_resi" style="display:none;">
            <table class="table table-condensed" style="margin-bottom:8px;">
              <tbody>
                <tr>
                  <th style="width:35%;">Marketplace / Kurir</th>
                  <td id="td_marketplace">-</td>
                </tr>
                <tr>
                  <th>Berat standar</th>
                  <td><strong id="td_berat_standar">-</strong> <small class="text-muted" id="td_berat_kemasan"></small></td>
                </tr>
                <tr>
                  <th>Rentang ACCEPT</th>
                  <td id="td_rentang">-</td>
                </tr>
              </tbody>
            </table>

            <table class="table table-striped table-condensed">
              <thead>
                <tr>
                  <th>SKU</th>
                  <th class="text-right">Qty</th>
                  <th class="text-right">Berat/pcs</th>
                  <th class="text-right">Subtotal</th>
                  <th>Sumber</th>
                </tr>
              </thead>
              <tbody id="tbody_item"></tbody>
            </table>

            <div id="alert_master" class="alert alert-warning" style="display:none;"></div>
          </div>

        </div>

        <div class="panel-footer">
          <button type="submit" id="btn_simpan" class="btn btn-success" disabled><i class="fa fa-save"></i> Simpan Hasil Timbang</button>
          <button type="button" id="btn_reset" class="btn btn-default pull-right">Reset</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- ============ MODAL JUMLAH BOX ============ -->
<!-- Sebagian kemasan dipakai lebih dari satu per paket, dan jumlahnya hanya
     operator yang tahu. Tanpa angka ini berat kemasan cuma tebakan. -->
<div class="modal" id="modal_jumlah_box" tabindex="-1" role="dialog" data-backdrop="static">
  <div class="modal-dialog modal-sm" role="document">
    <form id="form_jumlah_box" class="nojs" autocomplete="off">
      <div class="modal-content">
        <div class="modal-header" style="background-color:#2e75b6; color:#fff;">
          <h4 class="modal-title"><i class="fa fa-cube"></i> Jumlah Box Dipakai</h4>
        </div>

        <div class="modal-body">
          <p id="jb_nama" style="font-weight:700; margin-bottom:12px;"></p>
          <div id="jb_isian"></div>
          <div id="jb_total" class="alert alert-info" style="margin-bottom:0;"></div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-default" id="jb_batal">Batal</button>
          <button type="submit" class="btn btn-primary">Pakai <small>(Enter)</small></button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- ============ MODAL PERINGATAN BERAT TIDAK SESUAI ============ -->
<!-- Layar operator tidak pernah dikunci dan tidak pernah meminta kode. Hasilnya
     sudah tercatat, dan resinya masuk daftar "Berat Resi Tidak Sesuai" untuk
     ditindaklanjuti admin. Warna serta pesannya berubah pada percobaan kedua
     dan seterusnya. -->
<style>
  /* Jendela digeser ke kiri supaya panel detail resi di kolom kanan tetap
     terbaca saat peringatan muncul -- operator perlu membandingkan bacaan
     indikator dengan rincian berat per SKU tanpa menutup jendela ini. */
  #modal_warn .modal-dialog {
    margin-left: 30px;
    margin-right: auto;
    width: 620px;
    max-width: 46%;
  }

  /* Layar sempit tidak punya ruang untuk digeser: kembalikan ke tengah. */
  @media (max-width: 991px) {
    #modal_warn .modal-dialog {
      margin-left: auto;
      width: auto;
      max-width: none;
    }
  }

  /* Rincian selisih adalah angka yang paling dicari operator, jadi dibuat
     merah tebal dan cukup besar untuk dibaca sambil berdiri di meja timbang. */
  #warn_detail {
    color: #d9534f;
    font-weight: 700;
    font-size: 17px;
    line-height: 1.5;
    margin-bottom: 12px;
  }
</style>

<div class="modal" id="modal_warn" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" id="warn_header" style="background-color:#f0ad4e; color:#fff;">
        <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> <span id="warn_judul">BERAT TIDAK SESUAI</span></h4>
      </div>

      <div class="modal-body">
        <p style="font-size:18px;"><strong id="warn_pesan">Berat tidak sesuai, konfirmasi ke admin.</strong></p>

        <p>Resi <strong id="warn_noresi">-</strong></p>

        <div id="warn_detail">
          <div id="warn_detail_status">-</div>
          <div id="warn_detail_berat">-</div>
          <div id="warn_detail_selisih">-</div>
        </div>

        <div class="alert" id="warn_catatan" style="margin-bottom:0;"></div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-warning" id="warn_tombol" data-dismiss="modal">Mengerti, Timbang Ulang <small>(Enter)</small></button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  (function() {
    // ------------------------------------------------------------------
    // Pengaturan dari server
    // ------------------------------------------------------------------
    var SATUAN = <?= json_encode($setting['SATUAN_INDIKATOR']) ?>;      // 'g' atau 'kg'
    // Catatan: pengaturan INTERLOCK tidak dipakai lagi. Sejak alur 2
    // percobaan, hasil yang meleset justru WAJIB tersimpan supaya muncul di
    // menu "Berat Resi Tidak Sesuai".
    var AUTO_SIMPAN = <?= $setting['AUTO_SIMPAN'] == '1' ? 'true' : 'false' ?>;
    var STABIL_MS = <?= (int) $setting['STABIL_MS'] ?>;

    // Alur dua kali scan. Hasil hanya tersimpan setelah operator men-scan resi
    // yang sama untuk kedua kalinya, jadi tidak ada berat yang tercatat tanpa
    // tindakan sadar. Set SCAN_GANDA = 0 di pengaturan untuk kembali ke alur
    // lama (tersimpan sendiri begitu angka diam).
    var SCAN_GANDA = <?= $setting['SCAN_GANDA'] == '1' ? 'true' : 'false' ?>;
    var JEDA_SCAN_MS = <?= (int) $setting['JEDA_SCAN_MS'] ?>;
    var TIMEOUT_RESI_MS = <?= (int) $setting['TIMEOUT_RESI_MENIT'] * 60000 ?>;

    // Goyangan wajar indikator. Selama bacaan masih bergerak lebih besar dari
    // ini, paket dianggap belum diam di atas timbangan.
    var EPS_GRAM = 2;

    // Di bawah angka ini timbangan dianggap kosong -- pertanda paket sudah
    // diangkat dan penimbangan berikutnya boleh dinilai.
    var AMBANG_KOSONG_GRAM = 5;

    // ------------------------------------------------------------------
    // Bunyi penanda
    // ------------------------------------------------------------------
    // Berkasnya didaftarkan sebagai <audio> di main.php. Mau menukar bunyi
    // suatu kejadian? Cukup ganti id di bawah ini -- tidak perlu menyentuh
    // baris lain.
    //
    // Bunyi sengaja hanya dipasang pada dua kejadian: berat sesuai dan berat
    // tidak sesuai. Kejadian lain (resi termuat, gagal simpan, kode salah)
    // cukup ditandai lewat teks di layar, supaya bunyi di ruang packing tidak
    // kehilangan artinya.
    var BUNYI_LOLOS = 'audio-benar';       // berat masuk rentang ACCEPT
    var BUNYI_BERAT_BEDA = 'audio-beda';   // berat di luar rentang (UNDER/OVER)

    var setpoint = null;   // hasil hitungan server untuk resi aktif
    var resiAktif = null;
    var beratIndikator = null;  // angka apa adanya dari indikator
    var rawTerakhir = '';

    // Status verifikasi otomatis
    var sibuk = false;          // sedang kirim simpan/kunci ke server
    var sudahDiputus = false;   // hasil resi aktif sudah diputuskan lolos/tidak
    var peringatanTerbuka = false;  // jendela peringatan berat sedang tampil
    var stabilTimer = null;
    var beratAcuan = null;

    // ------------------------------------------------------------------
    // Jenis kemasan
    // ------------------------------------------------------------------
    /**
     * Berat kemasan pilihan operator untuk paket ini, atau null bila memakai
     * nilai bawaan dari pengaturan. Dikirim ke server pada pemuatan resi dan
     * penyimpanan, lalu server yang menghitung ulang berat standarnya.
     */
    // Rincian tiap jenis kemasan: komponen, beratnya, dan mana yang jumlahnya
    // harus ditanyakan ke operator.
    var KEMASAN = <?= json_encode($pilihan_kemasan) ?>;

    // Jumlah box yang sedang berlaku: id_komponen -> banyaknya.
    var jumlahKemasan = {};

    function infoKemasan(kode) {
      for (var i = 0; i < KEMASAN.length; i++) {
        if (KEMASAN[i].kode === kode) return KEMASAN[i];
      }
      return null;
    }

    /**
     * Berat kemasan yang sedang berlaku, dihitung dari komponen dan jumlah box.
     * Dipakai hanya untuk ditampilkan di layar -- angka yang menentukan tetap
     * dihitung ulang di server.
     */
    function beratKemasanSekarang() {
      var info = infoKemasan(kemasanTerpilih());

      if (!info) return null;

      var total = 0;

      for (var i = 0; i < info.komponen.length; i++) {
        var k = info.komponen[i];
        var n = k.tanya_jumlah ? (parseInt(jumlahKemasan[k.id], 10) || 0) : 1;

        total += parseFloat(k.berat) * n;
      }

      return total;
    }

    function kemasanTerpilih() {
      return $("#pakai_kardus").is(':checked') ? ($("#berat_kardus").val() || '') : '';
    }

    /**
     * Tampilkan tahap yang sedang dikerjakan pada resi campuran PLCA.
     * Pada resi biasa penandanya disembunyikan.
     */
    function tampilkanTahap() {
      if (!duaTahap) {
        $("#box_tahap").hide();
        return;
      }

      var tahap1 = tahapAktif === 1;

      $("#box_tahap")
        .removeClass('alert-info alert-success')
        .addClass(tahap1 ? 'alert-info' : 'alert-success')
        .show();

      $("#teks_tahap").text('TAHAP ' + tahapAktif + ' DARI 2 — '
        + (tahap1 ? 'timbang SKU PLCA saja' : 'timbang SKU selain PLCA'));

      // Kemasan tiap tahap ditentukan operator lewat panel di sebelah kiri:
      // PLCA berdus tinggal dipilih "Dus PLCA", tanpa dus biarkan zipper.
      $("#teks_tahap_detail").text(tahap1
        ? 'Pisahkan barang PLCA dari isi resi lainnya. Pilih "Dus PLCA" di panel kiri bila PLCA-nya pakai dus.'
        : 'Sekarang timbang sisa isi resi. Sesuaikan lagi jenis kemasan di panel kiri bila berbeda dari tahap 1.');
    }

    // --- Penimbangan dua tahap (resi campuran PLCA) ---
    // Resi yang memuat PLCA bersama SKU lain ditimbang dua kali: tahap 1 hanya
    // PLCA, tahap 2 sisanya. Masing-masing memakai kemasannya sendiri.
    var duaTahap = false;
    var tahapAktif = 0;          // 0 = alur biasa, 1 = PLCA, 2 = sisanya
    var lanjutTahap = 0;         // tahap yang harus dibuka setelah hasil ini

    /**
     * Beralih ke tahap berikutnya pada resi yang sama tanpa scan ulang:
     * resi dimuat kembali dengan kelompok SKU yang berbeda.
     */
    function lanjutkanTahap() {
      var noresi = resiAktif ? resiAktif.noresi : '';
      var tujuan = lanjutTahap;

      lanjutTahap = 0;

      if (!noresi || !tujuan) return;

      resetResi();
      muatResi(noresi, tujuan);
    }

    // --- Keadaan alur dua kali scan ---
    var beratStabil = false;     // angka indikator sudah diam, siap dikunci
    var waktuScanTerakhir = 0;   // penangkal scanner yang menembak ganda
    var lepasTimer = null;       // pelepasan resi aktif setelah menganggur
    var hitungMundurTimer = null;
    var batasLepas = 0;          // titik waktu resi aktif dilepas

    // ------------------------------------------------------------------
    // Helper
    // ------------------------------------------------------------------
    // Satuan bacaan yang sedang berlaku. Diisi ulang tiap baris data masuk:
    // satuan yang tertulis di data indikator menang atas SATUAN_INDIKATOR,
    // supaya indikator yang mengirim "kg" tidak terbaca sebagai gram (0,104
    // kg pernah tampil sebagai "0 g" karena ini).
    var satuanAktif = SATUAN;

    function keGram(nilai) {
      return satuanAktif === 'kg' ? nilai * 1000 : nilai;
    }

    // Bunyi yang sama bisa terpicu berkali-kali berturut-turut, jadi posisi
    // putarnya perlu dikembalikan ke awal supaya benar-benar terdengar ulang.
    function bunyi(id) {
      var el = document.getElementById(id);

      if (!el) return;

      // currentTime melempar InvalidStateError bila metadata berkas belum
      // sempat dimuat. Dulu baris ini satu blok try dengan play(), sehingga
      // sekali gagal, bunyinya ikut batal sama sekali. Sekarang dipisah:
      // gagal memundurkan posisi tidak boleh membatalkan pemutaran.
      try {
        el.pause();
        el.currentTime = 0;
      } catch (e) {}

      // play() mengembalikan Promise pada browser modern. Penolakannya tidak
      // tertangkap try/catch, jadi harus ditangani lewat .catch() -- tanpa itu
      // kegagalan autoplay hanya muncul diam-diam di console.
      try {
        var main = el.play();

        if (main && typeof main.catch === 'function') {
          main.catch(function(err) {
            if (err && err.name === 'NotAllowedError') {
              // Chrome menolak memutar suara sebelum halaman pernah disentuh
              // pengguna. Tunggu interaksi berikutnya, lalu putar sekali.
              tungguSentuhan(el);
              return;
            }

            console.warn('Bunyi ' + id + ' gagal diputar:', err);
          });
        }
      } catch (e) {
        console.warn('Bunyi ' + id + ' gagal diputar:', e);
      }
    }

    /**
     * Satu kali pemutaran tertunda sampai operator menyentuh halaman
     * (klik / tombol / scan barcode), untuk melewati kebijakan autoplay.
     */
    function tungguSentuhan(el) {
      var putar = function() {
        document.removeEventListener('click', putar, true);
        document.removeEventListener('keydown', putar, true);

        var lagi = el.play();

        if (lagi && typeof lagi.catch === 'function') {
          lagi.catch(function() {});
        }
      };

      document.addEventListener('click', putar, true);
      document.addEventListener('keydown', putar, true);
    }

    function fmtGram(gram) {
      return Number(gram).toLocaleString('id-ID', { maximumFractionDigits: 0 }) + ' g';
    }

    function fmtKg(gram) {
      return (Number(gram) / 1000).toLocaleString('id-ID', { maximumFractionDigits: 3 }) + ' kg';
    }

    function setPesan(teks, detail, tipe) {
      $("#span_pesan").text(teks);
      $("#span_pesan_detail").text(detail || '');
      $("#tile_pesan").removeClass('tile-default tile-success tile-danger tile-warning').addClass('tile-' + (tipe || 'default'));
    }

    // ------------------------------------------------------------------
    // Evaluasi set point
    // ------------------------------------------------------------------
    function statusDari(gram) {
      if (!setpoint) return null;
      if (gram < setpoint.berat_min) return 'UNDER';
      if (gram > setpoint.berat_max) return 'OVER';

      return 'ACCEPT';
    }

    function evaluasi() {
      var tile = $("#tile_berat");
      var badge = $("#span_status");
      var gram = beratIndikator === null ? null : keGram(beratIndikator);

      tile.removeClass('tile-default tile-success tile-danger tile-warning');
      badge.removeClass('label-default label-success label-danger label-warning');

      if (gram !== null) {
        $("#span_berat").text(Number(gram).toLocaleString('id-ID', { maximumFractionDigits: 0 }));
        $("#span_berat_kg").text(fmtKg(gram));
      }

      $("#btn_simpan").prop('disabled', true);

      if (gram === null) {
        tile.addClass('tile-default');
        // Resi sudah dimuat tapi timbangan masih kosong: sebutkan langkah
        // berikutnya, jangan cuma "STANDBY".
        badge.addClass('label-default').text(setpoint && SCAN_GANDA ? 'TARUH PAKET DI TIMBANGAN' : 'STANDBY');
        return;
      }

      if (!setpoint) {
        tile.addClass('tile-default');
        badge.addClass('label-default').text('MENUNGGU RESI');
        return;
      }

      // Pada alur dua kali scan, layar tidak mengumumkan lolos/tidak sebelum
      // operator mengunci hasilnya -- yang ditampilkan adalah tahap kerjanya.
      if (SCAN_GANDA) {
        if (!beratStabil) {
          tile.addClass('tile-default');
          badge.addClass('label-default').text('MENIMBANG...');
          return;
        }

        tile.addClass('tile-warning');
        badge.addClass('label-warning').text('SCAN RESI SEKALI LAGI');
        return;
      }

      var status = statusDari(gram);

      if (status === 'UNDER') {
        tile.addClass('tile-warning');
        badge.addClass('label-warning').text('UNDER (KURANG)');
      } else if (status === 'OVER') {
        tile.addClass('tile-danger');
        badge.addClass('label-danger').text('OVER (LEBIH)');
      } else {
        tile.addClass('tile-success');
        badge.addClass('label-success').text('ACCEPT (SESUAI)');
      }

      // Semua status boleh disimpan: yang meleset pun harus tercatat.
      $("#btn_simpan").prop('disabled', false);
    }

    // ------------------------------------------------------------------
    // Keputusan lolos / tidak lolos
    // ------------------------------------------------------------------
    function batalStabil() {
      if (stabilTimer) {
        clearTimeout(stabilTimer);
        stabilTimer = null;
      }
    }

    // ------------------------------------------------------------------
    // Pelepasan resi aktif setelah menganggur
    //
    // Resi yang dimuat lalu ditinggalkan tidak boleh menggantung: paket
    // berikutnya bisa tanpa sengaja tercatat atas namanya.
    // ------------------------------------------------------------------
    function mulaiHitungMundur() {
      hentikanHitungMundur();

      batasLepas = Date.now() + TIMEOUT_RESI_MS;

      lepasTimer = setTimeout(function() {
        setPesan('-', 'Resi dilepas otomatis karena didiamkan terlalu lama. Silakan scan ulang.', 'warning');

        resetResi();

        $("#noresi").val('').focus();
      }, TIMEOUT_RESI_MS);

      hitungMundurTimer = setInterval(tampilkanSisaWaktu, 1000);

      tampilkanSisaWaktu();
    }

    function hentikanHitungMundur() {
      if (lepasTimer) { clearTimeout(lepasTimer); lepasTimer = null; }
      if (hitungMundurTimer) { clearInterval(hitungMundurTimer); hitungMundurTimer = null; }

      $("#sisa_waktu").text('').hide();
    }

    function tampilkanSisaWaktu() {
      var sisa = Math.max(0, Math.round((batasLepas - Date.now()) / 1000));
      var menit = Math.floor(sisa / 60);
      var detik = sisa % 60;

      $("#sisa_waktu")
        .text('Sisa waktu ' + menit + ':' + (detik < 10 ? '0' : '') + detik)
        .show();
    }

    /**
     * Keputusan hanya diambil setelah angka indikator berhenti bergerak selama
     * STABIL_MS. Tanpa jeda ini paket yang masih berayun sempat terbaca UNDER
     * padahal belum diam di atas timbangan.
     */
    function pantauStabil() {
      if (sibuk || peringatanTerbuka) return;
      if (!setpoint || beratIndikator === null) return;

      var gram = keGram(beratIndikator);

      // Satu paket = satu keputusan. Keputusan berikutnya baru dibuka setelah
      // paketnya benar-benar diangkat dari timbangan (bacaan kembali kosong).
      //
      // Tanpa syarat ini, paket yang tetap tergeletak setelah hasil meleset
      // akan dinilai stabil lagi setiap STABIL_MS dan tersimpan berulang kali
      // -- satu resi pernah tercatat 187 baris dalam hitungan menit.
      if (sudahDiputus) {
        if (gram <= AMBANG_KOSONG_GRAM) {
          sudahDiputus = false;
          beratAcuan = null;
          batalStabil();
        }

        return;
      }

      if (beratAcuan === null || Math.abs(gram - beratAcuan) > EPS_GRAM) {
        beratAcuan = gram;
        beratStabil = false;
        batalStabil();
      }

      if (!stabilTimer) {
        stabilTimer = setTimeout(function() {
          beratStabil = true;

          // Pada alur dua kali scan, angka yang sudah diam TIDAK langsung
          // disimpan -- ia hanya menunggu scan kedua dari operator. Inilah
          // yang membuat tidak ada berat tercatat tanpa tindakan sadar.
          if (SCAN_GANDA) {
            evaluasi();
            return;
          }

          putuskanHasil(false);
        }, STABIL_MS);
      }
    }

    /**
     * @param {boolean} paksaSimpan tekan tombol Simpan = simpan walau
     *        AUTO_SIMPAN dimatikan.
     */
    function putuskanHasil(paksaSimpan) {
      batalStabil();

      if (sibuk || sudahDiputus || peringatanTerbuka) return;
      if (!setpoint || resiAktif === null || beratIndikator === null) return;

      var gram = keGram(beratIndikator);

      // Timbangan masih kosong: jangan diperlakukan sebagai paket kurang berat.
      if (gram <= 0) return;

      sudahDiputus = true;

      if (statusDari(gram) === 'ACCEPT') {
        bunyi(BUNYI_LOLOS);

        setPesan(resiAktif.noresi, 'LOLOS - berat sesuai berat standar SKU', 'success');

        if (AUTO_SIMPAN || paksaSimpan) {
          simpanHasil();
        }

        return;
      }

      bunyi(BUNYI_BERAT_BEDA);

      setPesan(resiAktif.noresi, 'Berat tidak sesuai berat standar SKU', 'danger');

      // Hasil yang meleset tetap disimpan supaya masuk daftar "Berat Resi
      // Tidak Sesuai". Server yang menentukan apakah kali ini cukup
      // peringatan atau sudah harus minta kode atasan.
      simpanHasil();
    }

    function simpanHasil() {
      if (sibuk || !resiAktif || beratIndikator === null) return;

      var noresi = resiAktif.noresi;

      sibuk = true;
      $("#btn_simpan").prop('disabled', true);

      $.post('timbangan/save-timbangan', {
        noresi: noresi,
        berat_indikator: beratIndikator,
        // Satuan bacaan HARUS ikut dikirim. Indikator yang mengirim "kg"
        // sementara pengaturan SATUAN_INDIKATOR berisi "g" pernah membuat
        // 0,102 kg tersimpan sebagai 0 g.
        satuan_indikator: satuanAktif,
        // Jenis kemasan ikut dikirim supaya server menghitung berat standar
        // dengan kemasan yang sama seperti saat resi dimuat.
        kode_kemasan: kemasanTerpilih(),
        // Jumlah box per komponen; server yang mengalikannya dengan berat.
        jumlah_kemasan: jumlahKemasan,
        // Tahap ikut dikirim supaya server menimbang kelompok SKU yang sama
        // dengan yang sedang ditampilkan di layar.
        tahap: tahapAktif,
        raw_data: rawTerakhir
      })
        .done(function(res) {
          var d = res.data;

          $("#total_scan").val(d.total_scan);

          // Bunyi sudah dibunyikan putuskanHasil() begitu status diketahui,
          // jadi di sini tidak diulang.
          // Tahap 1 yang selesai selalu dilanjutkan ke tahap 2, baik hasilnya
          // lolos maupun meleset -- yang meleset tetap tercatat dan menunggu
          // ditindaklanjuti admin.
          lanjutTahap = d.tahap_berikutnya || 0;

          if (d.status === 'ACCEPT') {
            setPesan(noresi,
              'LOLOS - tersimpan ' + fmtGram(d.berat_aktual)
              + ' (selisih ' + (d.selisih > 0 ? '+' : '') + fmtGram(d.selisih) + ')'
              + (lanjutTahap ? '. Lanjut ke tahap 2.' : ''),
              'success');

            if (lanjutTahap) {
              lanjutkanTahap();
              return;
            }

            // Langsung siap untuk resi berikutnya tanpa operator menghapus
            // nomor resi sebelumnya.
            resetResi();

            $("#noresi").val('').focus();

            return;
          }

          setPesan(noresi,
            d.status + ' - tersimpan ' + fmtGram(d.berat_aktual)
            + ' (selisih ' + (d.selisih > 0 ? '+' : '') + fmtGram(d.selisih) + ')'
            + ', percobaan ke-' + d.percobaan_ke,
            'danger');

          tampilkanPeringatan(d);
        })
        .fail(function(xhr) {
          setPesan(noresi, bacaGagal(xhr, 'Gagal menyimpan'), 'danger');

          // Keputusan sengaja tidak dibuka kembali di sini. Kalau server
          // menolak terus-menerus (mis. resi sudah pernah lolos), membukanya
          // membuat percobaan berulang tanpa henti selama paket masih di atas
          // timbangan. Angkat paketnya, atau tekan Simpan, untuk mencoba lagi.
          evaluasi();
        })
        .always(function() {
          sibuk = false;
        });
    }

    // ------------------------------------------------------------------
    // Peringatan berat tidak sesuai
    //
    // Tidak pernah mengunci layar dan tidak pernah meminta kode. Hasilnya
    // sudah tersimpan; resi bersangkutan menunggu ditindaklanjuti admin di
    // menu "Berat Resi Tidak Sesuai". Pada percobaan kedua dan seterusnya,
    // peringatannya dinaikkan supaya operator segera melapor.
    // ------------------------------------------------------------------
    function tampilkanPeringatan(info) {
      var ulang = info.percobaan_ke > 1;

      $("#warn_judul").text(ulang ? 'CEK ULANG BERAT SKU' : 'BERAT TIDAK SESUAI');
      $("#warn_header").css('background-color', ulang ? '#d9534f' : '#f0ad4e');

      $("#warn_pesan").text(ulang
        ? 'Verifikasi berat masih tidak sesuai, harap melapor segera!'
        : 'Berat tidak sesuai, konfirmasi ke admin.');

      $("#warn_catatan")
        .removeClass('alert-warning alert-danger')
        .addClass(ulang ? 'alert-danger' : 'alert-warning')
        .text(ulang
          ? 'Ini percobaan ke-' + info.percobaan_ke + ' pada resi yang sama dan hasilnya masih meleset. '
            + 'Hasil sudah tercatat dan menunggu ditindaklanjuti admin di menu "Berat Resi Tidak Sesuai".'
          : 'Hasil ini sudah tercatat. Silakan cek isi paket, lalu timbang ulang resi yang sama.');

      $("#warn_tombol")
        .removeClass('btn-warning btn-danger')
        .addClass(ulang ? 'btn-danger' : 'btn-warning');

      $("#warn_noresi").text(info.noresi || '-');

      // Dipecah tiga baris supaya terbaca sekilas dari jarak berdiri:
      // status, perbandingan berat, lalu selisihnya.
      $("#warn_detail_status").text('STATUS ' + info.status);
      $("#warn_detail_berat").text(
        'Terbaca ' + fmtGram(info.berat_aktual)
        + ', seharusnya ' + fmtGram(info.berat_standar)
        + ' (rentang ' + fmtGram(info.berat_min) + ' s/d ' + fmtGram(info.berat_max) + ')');
      $("#warn_detail_selisih").text(
        'Selisih ' + (info.selisih > 0 ? '+' : '') + fmtGram(info.selisih));

      // Panel detail resi sengaja DIBIARKAN tampil selama peringatan terbuka,
      // supaya operator bisa membandingkan bacaan indikator dengan rincian
      // berat per SKU. Pembersihannya menyusul saat jendela ini ditutup.
      //
      // Selama jendela terbuka, penilaian otomatis dihentikan: resi masih
      // aktif, dan tanpa penjaga ini paket yang diangkat lalu diganti bisa
      // tersimpan lagi diam-diam di belakang jendela peringatan.
      peringatanTerbuka = true;

      munculkanModal($("#modal_warn"));

      evaluasi();
    }

    /**
     * Bootstrap mengabaikan show() bila status internalnya masih menganggap
     * jendela ini terbuka. Kalau status itu tertinggal padahal jendelanya
     * tidak tampak, kembalikan dulu supaya show() di bawah dituruti.
     */
    function munculkanModal(modal) {
      var data = modal.data('bs.modal');

      if (data && data.isShown && !modal.hasClass('in')) {
        data.isShown = false;

        $('.modal-backdrop').remove();
      }

      modal.modal('show');
    }

    /**
     * Ambil pesan error dari respons server.
     */
    function bacaGagal(xhr, pesanDefault) {
      var res = {};

      try { res = JSON.parse(xhr.responseText); } catch (e) {}

      return res.message || pesanDefault;
    }

    // ------------------------------------------------------------------
    // Web Serial
    // ------------------------------------------------------------------
    // Port disimpan di window supaya tidak bocor ketika halaman dimuat ulang
    // lewat navigasi menu. Instance lama ditutup dulu sebelum yang baru jalan.
    if (typeof window.__timbanganDisconnect === 'function') {
      try { window.__timbanganDisconnect(); } catch (e) {}
    }

    window.__timbanganPort = null;
    window.__timbanganKeepReading = false;

    var reader = null;
    var readableStreamClosed = null;

    if (!('serial' in navigator)) {
      var pesanSerial = window.isSecureContext
        ? 'Browser ini tidak mendukung Web Serial API. Gunakan Google Chrome atau Microsoft Edge versi terbaru di PC/laptop.'
        : 'Web Serial API hanya aktif pada koneksi aman (https:// atau localhost). Halaman ini dibuka lewat ' + window.location.protocol + '//' + window.location.host + ', jadi port timbangan tidak bisa dibuka. Gunakan HTTPS atau daftarkan alamat ini sebagai origin aman di Chrome. Sementara itu pakai Input Berat Manual.';

      $("#alert_serial").text(pesanSerial).show();
      $("#btn_connect").prop('disabled', true);
    }

    /**
     * Baca angka sekaligus satuannya dari satu baris data indikator.
     *
     * Indikator RS-232 mengirim bermacam format, mis. "wn000.104kg",
     * "ST,GS,+000.104 kg", "+0104g", atau angka polos "104". Huruf di depan
     * (status stabil/tare) diabaikan; yang dipakai adalah angka pertama.
     *
     * Satuan diambil dari barisnya sendiri bila ditulis, karena inilah sumber
     * yang paling bisa dipercaya. Pengaturan SATUAN_INDIKATOR hanya dipakai
     * sebagai cadangan saat indikator mengirim angka tanpa satuan.
     *
     * @return {?{nilai: number, satuan: ?string}}
     */
    function parseBerat(baris) {
      var cocok = baris.match(/([-+]?\d+(?:[.,]\d+)?)\s*(kgs?|kilogram|gr?|gram|lbs?)?/i);

      if (!cocok) return null;

      var nilai = parseFloat(cocok[1].replace(',', '.'));

      if (isNaN(nilai)) return null;

      var satuan = null;
      var unitMentah = (cocok[2] || '').toLowerCase();

      if (unitMentah.indexOf('k') === 0) {
        satuan = 'kg';
      } else if (unitMentah.indexOf('lb') === 0) {
        // 1 lb = 453,592 g. Dikonversi di sini supaya sisa program cukup
        // mengenal 'g' dan 'kg' saja.
        nilai = nilai * 453.59237;
        satuan = 'g';
      } else if (unitMentah !== '') {
        satuan = 'g';
      }

      return { nilai: nilai, satuan: satuan };
    }

    async function connectSerial() {
      try {
        var port = await navigator.serial.requestPort();
        await port.open({ baudRate: parseInt($("#baud_rate").val(), 10) });

        window.__timbanganPort = port;
        window.__timbanganKeepReading = true;

        $("#btn_connect").prop('disabled', true);
        $("#btn_disconnect").prop('disabled', false);
        $("#baud_rate").prop('disabled', true);
        $("#berat_manual").prop('disabled', true).val('');
        $("#alert_serial").hide();

        readLoop(port);
      } catch (e) {
        if (e && e.name !== 'NotFoundError') {
          $("#alert_serial").text('Gagal terhubung: ' + e.message).show();
        }
      }
    }

    async function readLoop(port) {
      var decoder = new TextDecoderStream();
      readableStreamClosed = port.readable.pipeTo(decoder.writable);
      reader = decoder.readable.getReader();

      var buffer = '';

      try {
        while (window.__timbanganKeepReading) {
          var hasil = await reader.read();
          if (hasil.done) break;
          if (!hasil.value) continue;

          buffer += hasil.value;

          var idx;
          while ((idx = buffer.indexOf('\n')) >= 0) {
            var baris = buffer.slice(0, idx).trim();
            buffer = buffer.slice(idx + 1);

            if (!baris.length) continue;

            rawTerakhir = baris;
            $("#span_raw").text(baris);

            var bacaan = parseBerat(baris);
            if (bacaan !== null) {
              beratIndikator = bacaan.nilai;
              satuanAktif = bacaan.satuan || SATUAN;

              evaluasi();

              // Hanya bacaan dari indikator yang diputuskan otomatis. Input
              // manual menunggu operator menekan Enter / tombol Simpan.
              pantauStabil();
            }
          }
        }
      } catch (e) {
        console.error('Gagal membaca data timbangan:', e);
      } finally {
        try { reader.releaseLock(); } catch (e) {}
      }
    }

    async function disconnectSerial() {
      window.__timbanganKeepReading = false;

      try { if (reader) await reader.cancel(); } catch (e) {}
      try { if (readableStreamClosed) await readableStreamClosed; } catch (e) {}
      try { if (window.__timbanganPort) await window.__timbanganPort.close(); } catch (e) {}

      window.__timbanganPort = null;
      reader = null;
      readableStreamClosed = null;

      beratIndikator = null;
      rawTerakhir = '';
      satuanAktif = SATUAN;

      $("#btn_connect").prop('disabled', !('serial' in navigator));
      $("#btn_disconnect").prop('disabled', true);
      $("#baud_rate").prop('disabled', false);
      $("#berat_manual").prop('disabled', false);
      $("#span_berat").text('0');
      $("#span_berat_kg").text('0 kg');
      $("#span_raw").text('-');

      evaluasi();
    }

    window.__timbanganDisconnect = disconnectSerial;

    $("#btn_connect").on('click', connectSerial);
    $("#btn_disconnect").on('click', disconnectSerial);

    $("#berat_manual").on('input', function() {
      var nilai = $(this).val();

      beratIndikator = (nilai === '' || isNaN(parseFloat(nilai))) ? null : parseFloat(nilai);
      rawTerakhir = 'MANUAL:' + nilai;

      // Kolom manual memakai satuan yang tertulis di sebelah kolomnya, yaitu
      // SATUAN_INDIKATOR -- bukan satuan bacaan serial terakhir.
      satuanAktif = SATUAN;

      // Angka yang sedang diketik belum boleh diputuskan: "500" dalam
      // perjalanan mengetik "5000" jangan sampai ikut mengunci layar.
      batalStabil();
      sudahDiputus = false;

      evaluasi();
    });

    $("#berat_manual").on('keydown', function(e) {
      if (e.which !== 13) return;

      e.preventDefault();

      $("#form_timbangan").submit();
    });

    // ------------------------------------------------------------------
    // Muat resi
    // ------------------------------------------------------------------
    function resetResi() {
      resiAktif = null;
      setpoint = null;

      sudahDiputus = false;
      beratAcuan = null;
      beratStabil = false;
      duaTahap = false;
      tahapAktif = 0;
      batalStabil();
      hentikanHitungMundur();

      $("#box_tahap").hide();
      $("#box_resi").hide();
      $("#tbody_item").empty();
      $("#alert_master").hide();

      evaluasi();
    }

    /**
     * @param {number} tahapDiminta 0 = biarkan server yang menentukan dari
     *        riwayat resi; 1 atau 2 = paksa tahap tertentu, dipakai saat
     *        operator meneruskan ke tahap 2 setelah tahap 1 selesai.
     */
    function muatResi(noresi, tahapDiminta) {
      if (!noresi) return;

      tahapDiminta = tahapDiminta || 0;

      $.post('timbangan/get-resi-timbangan', {
        noresi: noresi,
        kode_kemasan: kemasanTerpilih(),
        jumlah_kemasan: jumlahKemasan,
        tahap: tahapDiminta
      })
        .done(function(res) {
          resiAktif = res.data.resi;
          setpoint = res.data.setpoint;
          duaTahap = !!res.data.dua_tahap;
          tahapAktif = res.data.tahap || 0;

          tampilkanTahap();

          $("#td_marketplace").text((resiAktif.nama_marketplace || '-') + ' / ' + (resiAktif.nama_kurir || '-'));
          $("#td_berat_standar").text(fmtGram(setpoint.berat_standar) + ' (' + fmtKg(setpoint.berat_standar) + ')');
          $("#td_berat_kemasan").text(setpoint.berat_kemasan > 0 ? 'termasuk kemasan ' + fmtGram(setpoint.berat_kemasan) : '');
          $("#td_rentang").text(fmtGram(setpoint.berat_min) + ' s/d ' + fmtGram(setpoint.berat_max) + ' (toleransi ' + setpoint.toleransi_persen + '%)');

          var html = '';
          $.each(res.data.items, function(i, item) {
            var berat = parseFloat(item.berat_standar) || 0;
            var sumber = item.sumber_berat === 'MASTER'
              ? '<span class="label label-success">Master</span>'
              : (item.sumber_berat === 'TBLSKU'
                ? '<span class="label label-info">tblsku</span>'
                : '<span class="label label-danger">Belum ada</span>');

            html += '<tr>'
              + '<td>' + item.sku + '<br><small class="text-muted">' + (item.nama_sku || '-') + '</small></td>'
              + '<td class="text-right">' + item.jumlah + '</td>'
              + '<td class="text-right">' + fmtGram(berat) + '</td>'
              + '<td class="text-right">' + fmtGram(berat * item.jumlah) + '</td>'
              + '<td>' + sumber + '</td>'
              + '</tr>';
          });

          $("#tbody_item").html(html);

          if (setpoint.sku_tanpa_master > 0) {
            $("#alert_master")
              .text('Ada ' + setpoint.sku_tanpa_master + ' baris SKU yang belum punya berat standar. Berat standar paket jadi lebih kecil dari seharusnya — lengkapi di menu Master Berat SKU.')
              .show();
          } else {
            $("#alert_master").hide();
          }

          $("#box_resi").show();

          // Resi yang pernah meleset perlu ditandai sejak awal: percobaan
          // berikutnya yang gagal langsung meminta kode atasan.
          if (res.data.percobaan_ke > 1) {
            setPesan(resiAktif.noresi,
              'Resi ini sudah pernah tidak sesuai. Ini percobaan ke-' + res.data.percobaan_ke
              + ' - bila masih meleset, verifikasi atasan diminta.',
              'warning');
          } else if (SCAN_GANDA) {
            setPesan(resiAktif.noresi, 'Taruh paket di timbangan, lalu scan resi ini sekali lagi', 'success');
          } else {
            setPesan(resiAktif.noresi, 'Resi siap ditimbang', 'success');
          }

          // Resi yang dimuat lalu ditinggalkan tidak boleh menggantung: paket
          // berikutnya bisa tanpa sengaja tercatat atas namanya.
          if (SCAN_GANDA) {
            mulaiHitungMundur();
          }

          evaluasi();

          // Paket yang sudah lebih dulu diletakkan di timbangan ikut dinilai
          // tanpa menunggu bacaan indikator berikutnya.
          pantauStabil();
        })
        .fail(function(xhr) {
          resetResi();

          setPesan(noresi, bacaGagal(xhr, 'Terjadi kesalahan'), 'danger');
        });
    }

    // ------------------------------------------------------------------
    // Jenis kemasan
    // ------------------------------------------------------------------
    // Pilihan disimpan di browser supaya bertahan saat operator berpindah
    // menu -- satu meja biasanya memakai jenis kemasan yang sama seharian.
    function simpanPilihanKemasan() {
      try {
        localStorage.setItem('timbangan_pakai_kardus', $("#pakai_kardus").is(':checked') ? '1' : '0');
        localStorage.setItem('timbangan_berat_kardus', $("#berat_kardus").val());
        localStorage.setItem('timbangan_jumlah_box', JSON.stringify(jumlahKemasan));
      } catch (e) {}
    }

    function pulihkanPilihanKemasan() {
      try {
        if (localStorage.getItem('timbangan_pakai_kardus') === '1') {
          $("#pakai_kardus").prop('checked', true);
          $("#box_kardus").show();
        }

        var berat = localStorage.getItem('timbangan_berat_kardus');

        if (berat) {
          $("#berat_kardus").val(berat);
        }

        var jumlah = localStorage.getItem('timbangan_jumlah_box');

        if (jumlah) {
          jumlahKemasan = JSON.parse(jumlah) || {};
        }

        tampilkanRingkasKemasan();
      } catch (e) {}
    }

    /**
     * Berat standar dihitung server memakai kemasan yang berlaku, jadi resi
     * yang sudah terlanjur dimuat harus diambil ulang begitu pilihannya
     * berubah -- kalau tidak, rentang ACCEPT di layar tidak sesuai lagi.
     */
    function terapkanPilihanKemasan() {
      simpanPilihanKemasan();

      if (!resiAktif || sibuk || peringatanTerbuka) return;

      // Tahap yang sedang dikerjakan HARUS ikut dikirim. Tanpa itu server
      // menentukan sendiri dari riwayat resi, dan tahap 1 yang meleset belum
      // punya baris ACCEPT -- sehingga layar melompat balik ke tahap 1 hanya
      // gara-gara operator mengubah jenis kemasan di tahap 2.
      muatResi(resiAktif.noresi, tahapAktif);
    }

    /**
     * Tampilkan pertanyaan jumlah box untuk jenis kemasan yang memerlukannya.
     * Urutan pertanyaan mengikuti urutan komponen dari server -- untuk kombo
     * PLCA, dus kecil ditanyakan lebih dulu.
     */
    function tanyaJumlahBox(kode, saatSelesai) {
      var info = infoKemasan(kode);

      if (!info || !info.perlu_jumlah) {
        saatSelesai();
        return;
      }

      var html = '';

      for (var i = 0; i < info.komponen.length; i++) {
        var k = info.komponen[i];

        if (!k.tanya_jumlah) continue;

        var nilai = parseInt(jumlahKemasan[k.id], 10);

        html += '<div class="form-group">'
          + '<label>' + k.nama + ' <small class="text-muted">(' + k.berat + ' g/box)</small></label>'
          + '<input type="number" min="0" max="99" step="1" class="form-control input-lg jb-jumlah" '
          + 'data-id="' + k.id + '" data-berat="' + k.berat + '" value="' + (nilai > 0 ? nilai : 1) + '" />'
          + '</div>';
      }

      $("#jb_nama").text(info.nama);
      $("#jb_isian").html(html);

      hitungTotalBox();

      lanjutJumlahBox = saatSelesai;

      munculkanModal($("#modal_jumlah_box"));
    }

    var lanjutJumlahBox = null;

    function hitungTotalBox() {
      var total = 0;

      $(".jb-jumlah").each(function() {
        total += (parseFloat($(this).data('berat')) || 0) * (parseInt($(this).val(), 10) || 0);
      });

      $("#jb_total").text('Total berat kemasan: ' + total.toLocaleString('id-ID') + ' g');

      return total;
    }

    $("body").on('input change', '.jb-jumlah', hitungTotalBox);

    $("#modal_jumlah_box").on('shown.bs.modal', function() {
      $(".jb-jumlah").first().focus().select();
    });

    $("#form_jumlah_box").on('submit', function(e) {
      e.preventDefault();
      e.stopPropagation();

      $(".jb-jumlah").each(function() {
        jumlahKemasan[$(this).data('id')] = parseInt($(this).val(), 10) || 0;
      });

      $("#modal_jumlah_box").modal('hide');

      var lanjut = lanjutJumlahBox;
      lanjutJumlahBox = null;

      if (lanjut) lanjut();
    });

    // Batal: kembalikan centang ke keadaan semula supaya tidak ada paket
    // tertimbang memakai jumlah box yang belum ditentukan.
    $("#jb_batal").on('click', function() {
      lanjutJumlahBox = null;

      $("#modal_jumlah_box").modal('hide');
      $("#pakai_kardus").prop('checked', false);
      $("#box_kardus").hide();

      terapkanPilihanKemasan();
    });

    function tampilkanRingkasKemasan() {
      var kode = kemasanTerpilih();
      var info = infoKemasan(kode);

      if (!info || !info.perlu_jumlah) {
        $("#ringkas_kemasan").hide();
        return;
      }

      var bagian = [];

      for (var i = 0; i < info.komponen.length; i++) {
        var k = info.komponen[i];

        if (k.tanya_jumlah) {
          bagian.push((parseInt(jumlahKemasan[k.id], 10) || 0) + '× ' + k.nama);
        }
      }

      $("#ringkas_kemasan")
        .html('<strong>' + bagian.join(' + ') + ' = ' + beratKemasanSekarang() + ' g</strong>')
        .show();
    }

    $("#pakai_kardus").on('change', function() {
      var dicentang = $(this).is(':checked');

      $("#box_kardus").toggle(dicentang);

      if (!dicentang) {
        terapkanPilihanKemasan();
        tampilkanRingkasKemasan();
        return;
      }

      tanyaJumlahBox(kemasanTerpilih(), function() {
        terapkanPilihanKemasan();
        tampilkanRingkasKemasan();
      });
    });

    $("#berat_kardus").on('change', function() {
      tanyaJumlahBox(kemasanTerpilih(), function() {
        terapkanPilihanKemasan();
        tampilkanRingkasKemasan();
      });
    });

    pulihkanPilihanKemasan();

    // Pengaman tambahan terhadap nomor resi ganda: kalau masih ada sisa teks
    // di kolom, scan berikutnya menimpanya alih-alih menempel di belakangnya.
    $("#noresi").on('focus', function() {
      this.select();
    });

    /**
     * Satu kolom, dua peran. Nomor yang di-scan menentukan tahap mana yang
     * sedang dijalani:
     *
     *   belum ada resi aktif   -> scan ke-1: muat resi, mulai hitung mundur
     *   sama dengan resi aktif -> scan ke-2: kunci berat, simpan hasil
     *   berbeda                -> resi lama dibatalkan, yang baru dimuat
     */
    $("#noresi").on('keydown', function(e) {
      if (e.which !== 13) return;

      e.preventDefault();

      var noresi = $.trim($(this).val());

      if (!noresi || sibuk || peringatanTerbuka) return;

      if (!SCAN_GANDA) {
        resetResi();
        muatResi(noresi);

        $("#noresi").val('');

        return;
      }

      // Scanner barcode kerap mengirim satu barcode dua kali dalam sekejap.
      // Tanpa jeda ini, tembakan kembar itu langsung menyelesaikan verifikasi
      // sebelum paketnya sempat diletakkan di timbangan.
      var sekarang = Date.now();

      if (sekarang - waktuScanTerakhir < JEDA_SCAN_MS) {
        $("#noresi").val(resiAktif ? '' : noresi);
        return;
      }

      waktuScanTerakhir = sekarang;

      // --- Scan ke-1: belum ada resi aktif ---
      if (!resiAktif) {
        resetResi();
        muatResi(noresi);

        // Kolom dikosongkan supaya scan kedua masuk bersih. Nomor resinya
        // tetap terlihat di panel detail dan di kotak pesan, jadi operator
        // tidak kehilangan jejak resi mana yang sedang aktif.
        $("#noresi").val('');

        return;
      }

      // --- Resi berbeda: yang lama dilepas, tidak tersimpan ---
      if (noresi !== resiAktif.noresi) {
        var lama = resiAktif.noresi;

        resetResi();
        muatResi(noresi);

        $("#noresi").val('');

        setPesan(noresi, 'Resi ' + lama + ' dibatalkan tanpa tersimpan. Scan ulang bila masih perlu ditimbang.', 'warning');

        return;
      }

      // --- Scan ke-2: kunci berat dan simpan ---
      if (beratIndikator === null) {
        setPesan(resiAktif.noresi, 'Taruh paket di timbangan dulu, lalu scan resi sekali lagi', 'danger');
        $("#noresi").val('');
        return;
      }

      if (!beratStabil) {
        setPesan(resiAktif.noresi, 'Angka timbangan masih bergerak. Tunggu diam, lalu scan sekali lagi', 'danger');
        $("#noresi").val('');
        return;
      }

      $("#noresi").val('');

      sudahDiputus = false;

      putuskanHasil(true);
    });

    $("#btn_reset").on('click', function() {
      resetResi();

      $("#noresi").val('').focus();
      setPesan('-', 'Scan nomor resi untuk memuat berat standar paket', 'default');
    });

    // ------------------------------------------------------------------
    // Simpan
    // ------------------------------------------------------------------
    // Tombol Simpan menempuh jalur keputusan yang sama dengan bacaan otomatis,
    // supaya berat di luar rentang tetap mengunci layar dan bukan sekadar
    // ditolak server.
    $("#form_timbangan").on('submit', function(e) {
      e.preventDefault();
      e.stopPropagation();

      if (sibuk) return;

      if (!resiAktif) {
        setPesan('-', 'Scan nomor resi terlebih dahulu', 'danger');
        return;
      }

      if (beratIndikator === null) {
        setPesan(resiAktif.noresi, 'Berat belum terbaca dari indikator', 'danger');
        return;
      }

      sudahDiputus = false;

      putuskanHasil(true);
    });

    // Detail resi baru dibersihkan di sini -- bukan saat peringatan muncul --
    // supaya operator sempat membandingkan bacaan indikator dengan rincian
    // berat per SKU selama jendela peringatan masih terbuka.
    //
    // Kolom resi ikut dikosongkan: kalau nomor lama tertinggal, scan barcode
    // berikutnya menempel di belakangnya dan menghasilkan nomor resi ganda.
    // Menimbang ulang paket yang sama cukup dengan men-scan resinya lagi.
    // Tombol diberi fokus begitu jendela terbuka, supaya operator cukup menekan
    // Enter -- tangannya tidak perlu berpindah dari scanner ke mouse.
    $("#modal_warn").on('shown.bs.modal', function() {
      $("#warn_tombol").focus();
    });

    // Penjaga kedua: sebagian browser memindahkan fokus ke badan jendela, dan
    // di situ Enter tidak menekan tombol apa pun.
    $("#modal_warn").on('keydown', function(e) {
      if (e.which !== 13) return;

      e.preventDefault();

      $("#modal_warn").modal('hide');
    });

    $("#modal_warn").on('hidden.bs.modal', function() {
      peringatanTerbuka = false;

      // Tahap 1 yang meleset tetap diteruskan ke tahap 2: hasilnya sudah
      // tercatat dan menunggu admin, sementara sisa isi resi masih harus
      // ditimbang.
      if (lanjutTahap) {
        lanjutkanTahap();
        return;
      }

      resetResi();

      $("#noresi").val('').focus();
    });

    // ------------------------------------------------------------------
    // Kondisi awal halaman
    // ------------------------------------------------------------------
    $("#noresi").focus();
  })();
</script>
