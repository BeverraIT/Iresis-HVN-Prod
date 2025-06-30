<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><strong>Cari Resi Picker</strong></h3>
      </div>

      <div class="panel-body">
        <table class="table table-striped datatable-picking">
          <thead>
            <tr>
              <th>#</th>
              <th>No. Resi</th>
              <th>Picker</th>
              <th>Tanggal Resi Ambil Barang</th>
              <th>Jam Resi Ambil Barang</th>
              <th>Diinput Oleh</th>
              <th>Nama Komputer</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function() {
    $('.datatable-picking').DataTable({
      'scrollX': true,
      'pageLength': 10,
      'processing': true,
      'serverSide': true,
      'order': [
        [3, 'desc']
      ],
      'lengthMenu': [
        [10, 50, 100, 150, 200],
        [10, 50, 100, 150, 200]
      ],
      'ajax': {
        url: 'picker/get-search-picker-data',
        type: 'POST',
      },
    });
  });
</script>