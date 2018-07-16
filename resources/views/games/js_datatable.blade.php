<script src="{{ asset('bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>

<script type="text/javascript">
  (function ($) {
    $(document).ready(function () {

      var initDatatable = function () {
        //initiate dataTables plugin
        var myTable =
          $('#qry_table_games')
            .DataTable({
              processing: true,
              serverSide: true,
              responsive: true,
              bLengthChange: false,
              bFilter: false,
              pageLength: 5,
              ajax: $.extend({
                url: '{!! route('games.data') !!}'
              }, {
                data: function (d) {
                  d.game_no = $('#game_no').val();
                  d.game_date = $('#game_date').val();
                }
              }),
              columns: [
                {data: 'id', name: 'id'},
                {data: 'date', name: 'date'},
                {data: 'time_game', name: 'time_game', searchable: false},
                {data: 'time_notification', name: 'time_notification', orderable: false, searchable: false},
                {data: 'is_notified', name: 'is_notified', orderable: false, searchable: false},
                {data: 'total_question', name: 'total_question', orderable: false, searchable: false},
                {data: 'price', name: 'price'},
                {data: 'stream_link', name: 'stream_link', orderable: false, searchable: false},
                {data: 'live_code', name: 'live_code', orderable: false, searchable: false},
                {data: 'status', name: 'status', orderable: false, searchable: false},
                {data: 'actions', name: 'actions', orderable: false, searchable: false}
              ],
              drawCallback: function (settings) {
                // Handle delete question
                $('.delete-game').on('click', function (e) {
                  var game_id = $(this).data('game_id');
                  if (game_id) {
                    var url_remove = '{{ url('games/') }}' + '/' + game_id;

                    // Change action form update
                    $('#modal-delete-game form').attr('action', url_remove);
                  }
                });
              }
            });

        $('#filter_game').on('click', function (e) {
          e.preventDefault();

          myTable.draw();
        });
      };

      initDatatable();

    });
  })(jQuery);
</script>