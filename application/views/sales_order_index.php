<?php $bg_colors = ['#ffb3ba', '#ffffba', '#bae1ff'];  ?>

<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title">Daftar pesanan</h3>
      </div>
      <div class="panel-body">
        <table class="table table-hover datatable-order">
          <thead>
            <tr>
              <th>#</th>
              <th>Salesorder No</th>
              <th>Transaction Date</th>
              <th>Tn Created Date</th>
              <th>Mp Timestamp</th>
              <th>Courier</th>
              <th>Status</th>
              <th>Picklist No</th>
              <th>Store</th>
              <th>Source</th>
              <th>Tracking No</th>
              <th>Total Amount Mp</th>
              <th>Total Disc</th>
              <th>Add Fee</th>
              <th>Escrow Amount</th>
              <th>Sub Total</th>
              <th>Grand Total</th>
              <th>Action</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function() {
    var table = $('.datatable-order').DataTable({
      'scrollX': true,
      'pageLength': 20,
      'processing': true,
      'serverSide': true,
      'order': [
        [1, 'asc']
      ],
      'lengthMenu': [
        [20, 50, 100, 150, 200],
        [20, 50, 100, 150, 200]
      ],
      'ajax': {
        url: 'sales_order/get_data',
        type: 'POST',
      },
    });
  });
</script>