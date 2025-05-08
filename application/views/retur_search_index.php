<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><strong>Daftar Resi Retur</strong></h3>
      </div>

      <div class="panel-body">
        <table class="table table-striped datatable-retur">
          <thead>
            <tr>
              <th>#</th>
              <th>No. Resi</th>
              <th>Tanggal Resi Retur</th>
              <th>Jam Resi Retur</th>
              <th>Market Place</th>
              <th>Kurir</th>
              <th>Pegawai</th>
              <th>Aksi</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function() {
    $('.datatable-retur').DataTable({
      'scrollX': true,
      'pageLength': 10,
      'processing': true,
      'serverSide': true,
      'order': [
        [2, 'desc']
      ],
      'lengthMenu': [
        [10, 50, 100, 150, 200],
        [10, 50, 100, 150, 200]
      ],
      'ajax': {
        url: 'retur_search/get_data',
        type: 'POST',
      },
    });
  });
</script>