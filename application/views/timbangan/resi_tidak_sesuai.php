<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><strong>Berat Resi Tidak Sesuai</strong></h3>
      </div>

      <div class="panel-body">

        <!-- Perbandingan resi terunggah vs sudah ditimbang, per batch unggahan.
             Satu resi dihitung sekali walau ditimbang berkali-kali. -->
        <div class="panel panel-default" style="margin-bottom:18px;">
          <div class="panel-heading">
            <strong>Progres Penimbangan per Batch Unggahan</strong>
            <small class="text-muted" style="margin-left:6px;">unggahan tanggal</small>
            <span class="pull-right" style="margin-top:-4px;">
              <input type="text" id="range_batch" class="form-control input-sm" style="width:auto; display:inline-block;" readonly />
              <button type="button" id="btn_muat_batch" class="btn btn-sm btn-primary"><i class="fa fa-refresh"></i></button>
            </span>
          </div>
          <div class="panel-body" id="isi_batch">
            <p class="text-muted" style="margin-bottom:0;">Memuat…</p>
          </div>
        </div>

        <p class="text-muted">
          Daftar resi yang berat timbangnya di luar rentang standar SKU. Satu baris
          mewakili satu nomor resi (hasil penimbangan terakhirnya). Kolom
          <strong>Percobaan</strong> menunjukkan berapa kali resi tersebut ditimbang
          dengan hasil meleset.
        </p>

        <div class="row" style="margin-bottom: 15px;">
          <div class="col-md-4">
            <label class="control-label">Tampilkan</label>
            <select id="filter_tindak_lanjut" class="form-control">
              <option value="0">Belum ditindaklanjuti</option>
              <option value="1">Sudah ditindaklanjuti</option>
            </select>
          </div>

          <div class="col-md-8">
            <label class="control-label">&nbsp;</label><br />
            <button type="button" id="btn_muat_ulang" class="btn btn-primary"><i class="fa fa-refresh"></i> Muat Ulang</button>

            <!-- Sekali tekan untuk seluruh daftar: admin tidak perlu menekan
                 tombol per baris ketika resi bermasalahnya banyak. -->
            <button type="button" id="btn_tindak_lanjut_semua" class="btn btn-danger" disabled>
              <i class="fa fa-check-square-o"></i> Tindak Lanjut Semua
            </button>

            <!-- class `nojs`: unduhan Excel harus lewat submit biasa. Tanpa
                 penanda itu, handler form global di plugins.js mengubahnya jadi
                 AJAX dan berkasnya tidak pernah terunduh. -->
            <form action="timbangan/export-to-excel-resi-tidak-sesuai" method="post" id="form_export" class="nojs" target="_blank" style="display:inline;">
              <input type="hidden" name="tindak_lanjut" id="export_tindak_lanjut" value="0" />
              <button type="submit" class="btn btn-success"><i class="fa fa-file-excel-o"></i> Export Excel</button>
            </form>
          </div>
        </div>

        <table class="table table-striped datatable-tidak-sesuai">
          <thead>
            <tr>
              <th>#</th>
              <th>No. Resi</th>
              <th>Berat Standar</th>
              <th>Berat Aktual</th>
              <th>Selisih</th>
              <th>Status</th>
              <th>Tahap</th>
              <th>Percobaan</th>
              <th>Petugas</th>
              <th>Device / IP</th>
              <th>Waktu</th>
              <th>Tindak Lanjut</th>
            </tr>
          </thead>
        </table>

      </div>
    </div>
  </div>
</div>

<!-- ============ MODAL TINDAK LANJUT ============ -->
<div class="modal" id="modal_tindak_lanjut" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <!-- class `nojs`: pengiriman ditangani skrip di halaman ini, bukan
         handler form global di plugins.js. -->
    <form id="form_tindak_lanjut" class="nojs" autocomplete="off">
      <div class="modal-content">
        <div class="modal-header" style="background-color:#d9534f; color:#fff;">
          <h4 class="modal-title"><i class="fa fa-exclamation-triangle"></i> Tindak Lanjut Resi</h4>
        </div>

        <div class="modal-body">
          <p>Resi <strong id="tl_noresi">-</strong></p>
          <p id="tl_detail" class="text-muted"></p>

          <div class="alert alert-warning" id="tl_peringatan_semua" style="display:none;">
            Seluruh resi yang sedang menunggu akan ditandai sekaligus. Tindakan ini
            tidak bisa dibatalkan.
          </div>

          <div class="form-group">
            <label>Kode Atasan</label>
            <input type="password" id="tl_kode" class="form-control input-lg" placeholder="Diisi oleh admin" />
          </div>

          <div class="form-group">
            <label>Catatan <small class="text-muted">(opsional)</small></label>
            <input type="text" id="tl_catatan" class="form-control" maxlength="255" placeholder="Mis. paket dibuka ulang, isi kurang 1 pcs" />
          </div>

          <div id="tl_pesan" class="alert alert-danger" style="display:none; margin-bottom:0;"></div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-danger"><i class="fa fa-check"></i> Tandai Ditindaklanjuti</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script type="text/javascript">
  (function() {
    var tabel = $('.datatable-tidak-sesuai').DataTable({
      'scrollX': true,
      'pageLength': 25,
      'processing': true,
      'serverSide': true,
      'order': [[10, 'desc']],
      'lengthMenu': [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
      'columnDefs': [{ 'orderable': false, 'targets': [0, 11] }],
      'ajax': {
        url: 'timbangan/get-data-resi-tidak-sesuai',
        type: 'POST',
        data: function(d) {
          d.tindak_lanjut = $("#filter_tindak_lanjut").val();
        },
      },
    });

    // ------------------------------------------------------------------
    // Progres per batch unggahan
    // ------------------------------------------------------------------
    // Satu hari sekali tampil. Rentang tujuh hari membuat daftarnya belasan
    // baris sekaligus dan justru sulit dibaca.
    $("#range_batch").daterangepicker({
      singleDatePicker: true,
      showDropdowns: true,
      startDate: moment(),
      maxDate: moment(),
      locale: { format: 'YYYY-MM-DD' },
    });

    function warnaProgres(persen) {
      if (persen >= 100) return 'progress-bar-success';
      if (persen >= 60) return 'progress-bar-info';
      if (persen >= 30) return 'progress-bar-warning';
      return 'progress-bar-danger';
    }

    function muatProgresBatch() {
      $("#isi_batch").html('<p class="text-muted" style="margin-bottom:0;">Memuat…</p>');

      var hari = $("#range_batch").val();

      $.post('timbangan/get-progres-batch', {
        reportrange: hari + ' 00:00:00 - ' + hari + ' 23:59:59'
      })
        .done(function(res) {
          var d = res.data;

          if (!d.batch.length) {
            $("#isi_batch").html('<p class="text-muted" style="margin-bottom:0;">Tidak ada batch yang diunggah pada tanggal ini.</p>');
            return;
          }

          var persenTotal = d.total_resi ? Math.round(d.total_ditimbang / d.total_resi * 100) : 0;

          var html = '<div style="margin-bottom:14px;">'
            + '<strong style="font-size:16px;">Total: ' + d.total_ditimbang.toLocaleString('id-ID')
            + ' dari ' + d.total_resi.toLocaleString('id-ID') + ' resi sudah ditimbang ('
            + persenTotal + '%)</strong>'
            + '<div class="progress" style="height:22px; margin-top:6px; margin-bottom:0;">'
            + '<div class="progress-bar ' + warnaProgres(persenTotal) + '" style="width:' + persenTotal + '%; line-height:22px;">'
            + persenTotal + '%</div></div>'
            + '<small class="text-muted">Sisa ' + (d.total_resi - d.total_ditimbang).toLocaleString('id-ID') + ' resi belum ditimbang.</small>'
            + '</div><hr style="margin:10px 0;" />';

          html += '<table class="table table-condensed" style="margin-bottom:0;">'
            + '<thead><tr><th>Batch (Picklist)</th><th>Diunggah</th>'
            + '<th class="text-right">Resi</th><th class="text-right">Ditimbang</th>'
            + '<th class="text-right">Sisa</th><th style="width:35%;">Progres</th></tr></thead><tbody>';

          $.each(d.batch, function(i, b) {
            var total = parseInt(b.jumlah_resi, 10) || 0;
            var sudah = parseInt(b.jumlah_ditimbang, 10) || 0;
            var persen = total ? Math.round(sudah / total * 100) : 0;

            html += '<tr>'
              + '<td><strong>' + b.nomorpicklist + '</strong></td>'
              + '<td>' + (b.waktu_unggah || '-').substring(0, 16) + '</td>'
              + '<td class="text-right">' + total + '</td>'
              + '<td class="text-right">' + sudah + '</td>'
              + '<td class="text-right">' + (total - sudah) + '</td>'
              + '<td><div class="progress" style="height:18px; margin-bottom:0;">'
              + '<div class="progress-bar ' + warnaProgres(persen) + '" style="width:' + persen + '%; line-height:18px;">'
              + persen + '%</div></div></td>'
              + '</tr>';
          });

          $("#isi_batch").html(html + '</tbody></table>');
        })
        .fail(function() {
          $("#isi_batch").html('<p class="text-danger" style="margin-bottom:0;">Gagal memuat progres batch.</p>');
        });
    }

    $("#btn_muat_batch").on('click', muatProgresBatch);
    $("#range_batch").on('apply.daterangepicker', muatProgresBatch);

    muatProgresBatch();

    // Menandai satu resi atau seluruh daftar memakai jendela yang sama.
    var modeSemua = false;

    function bukaModal(judul, detail, semua) {
      modeSemua = semua;

      $("#tl_noresi").text(judul);
      $("#tl_detail").text(detail);
      $("#tl_peringatan_semua").toggle(semua);

      $("#tl_kode").val('');
      $("#tl_catatan").val('');
      $("#tl_pesan").hide().text('');

      $("#modal_tindak_lanjut").modal('show');
    }

    $("#btn_muat_ulang, #filter_tindak_lanjut").on('click change', function() {
      tabel.ajax.reload();
    });

    // Export mengikuti kelompok yang sedang dilihat, supaya isi berkas selalu
    // sama dengan yang tampil di layar.
    $("#filter_tindak_lanjut").on('change', function() {
      $("#export_tindak_lanjut").val($(this).val());
    });

    // Tombol "semua" hanya masuk akal pada daftar yang belum ditindaklanjuti,
    // dan hanya bila memang ada isinya.
    tabel.on('draw', function() {
      var jumlah = tabel.page.info().recordsTotal;
      var bisa = jumlah > 0 && $("#filter_tindak_lanjut").val() === '0';

      $("#btn_tindak_lanjut_semua")
        .prop('disabled', !bisa)
        .html('<i class="fa fa-check-square-o"></i> Tindak Lanjut Semua'
          + (bisa ? ' (' + jumlah + ')' : ''));
    });

    $("#btn_tindak_lanjut_semua").on('click', function() {
      var jumlah = tabel.page.info().recordsTotal;

      bukaModal('SEMUA (' + jumlah + ' resi)',
        'Menandai seluruh resi yang berat timbangnya tidak sesuai dan belum ditindaklanjuti.',
        true);
    });

    // Tombol baris dibuat ulang tiap kali tabel digambar, jadi penanganannya
    // didelegasikan dari body.
    $("body").on('click', '.btn-tindak-lanjut', function() {
      bukaModal($(this).data('noresi'),
        'Status ' + $(this).data('status') + ' - ' + $(this).data('detail'),
        false);
    });

    $("#modal_tindak_lanjut").on('shown.bs.modal', function() {
      $("#tl_kode").focus();
    });

    $("#form_tindak_lanjut").on('submit', function(e) {
      e.preventDefault();
      e.stopPropagation();

      var kode = $.trim($("#tl_kode").val());

      if (!kode) {
        $("#tl_pesan").text('Kode atasan masih kosong').show();
        return;
      }

      var tombol = $(this).find('button[type=submit]');
      tombol.prop('disabled', true);

      $.post('timbangan/tindak-lanjut-timbangan', {
        noresi: modeSemua ? '' : $("#tl_noresi").text(),
        semua: modeSemua ? '1' : '0',
        kode_supervisor: kode,
        catatan: $.trim($("#tl_catatan").val())
      })
        .done(function(res) {
          $("#modal_tindak_lanjut").modal('hide');

          noty({ text: res.message, timeout: 3000, layout: 'topRight', type: 'success' });

          tabel.ajax.reload(null, false);
        })
        .fail(function(xhr) {
          var pesan = 'Gagal menandai tindak lanjut';
          try { pesan = JSON.parse(xhr.responseText).message; } catch (e) {}

          $("#tl_pesan").text(pesan).show();
          $("#tl_kode").val('').focus();
        })
        .always(function() {
          tombol.prop('disabled', false);
        });
    });
  })();
</script>
