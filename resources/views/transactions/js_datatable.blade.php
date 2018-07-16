<script src="{{ asset('bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>

<script type="text/javascript">
  (function ($) {
    $(document).ready(function () {

      var initDatatable = function () {
        //initiate dataTables plugin
        var myTable =
          $('#qry_table_transactions')
            .DataTable({
              processing: true,
              serverSide: true,
              responsive: true,
              bLengthChange: false,
              bFilter: false,
              ajax: $.extend({
                url: '{!! route('transactions.data') !!}'
              }, {
                data: function (d) {
                  d.txn_phone = $('#txn_phone').val();
                  d.txn_date = $('#txn_date').val();
                }
              }),
              columns: [
                {data: 'id', name: 'id'},
                {data: 'phone', name: 'phone', orderable: false, searchable: false},
                {data: 'username', name: 'username', orderable: false, searchable: false},
                {data: 'date', name: 'date'},
                {data: 'type', name: 'type', orderable: true, searchable: false},
                {data: 'txn_amount', name: 'txn_amount', orderable: true, searchable: false},
                {data: 'txn_fee', name: 'txn_fee', orderable: false, searchable: false},
                {data: 'note', name: 'note', orderable: false, searchable: false}
              ]
            });

        $('#filter_txn').on('click', function (e) {
          e.preventDefault();

          myTable.draw();
        });
      };

      initDatatable();

    });
  })(jQuery);
</script>