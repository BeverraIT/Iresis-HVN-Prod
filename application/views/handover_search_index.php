<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><strong>Cari Resi Keluar</strong></h3>
      </div>

      <div class="panel-body">
        <table class="table table-striped datatable-handover">
          <thead>
            <tr>
              <th>#</th>
              <th>No. Resi</th>
              <th>Pegawai</th>
              <th>Tanggal Resi Keluar</th>
              <th>Jam Resi Keluar</th>
              <th>Sudah Dicetak</th>
              <th>Tanggal Cetak</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function() {
    $('.datatable-handover').DataTable({
      'scrollX': true,
      'pageLength': 10,
      'processing': true,
      'serverSide': true,
      'order': [
        [3, 'desc'],
      ],
      'lengthMenu': [
        [10, 50, 100, 150, 200],
        [10, 50, 100, 150, 200]
      ],
      'ajax': {
        url: 'handover_search/get_data',
        type: 'POST',
      },
    });
  });
</script>