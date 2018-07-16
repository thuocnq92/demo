<script src="{{ asset('bower_components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>

<script type="text/javascript">
  (function ($) {
    $(document).ready(function () {

      // Get value from query string
      $.QueryString = (function (paramsArray) {
        var params = {};

        for (var i = 0; i < paramsArray.length; ++i) {
          var param = paramsArray[i]
            .split('=', 2);

          if (param.length !== 2)
            continue;

          params[param[0]] = decodeURIComponent(param[1].replace(/\+/g, " "));
        }

        return params;
      })(window.location.search.substr(1).split('&'))

      var initDatatable = function () {
        //initiate dataTables plugin
        var myTable =
          $('#qry_table_users')
            .DataTable({
              processing: true,
              serverSide: true,
              responsive: true,
              bLengthChange: false,
              deferLoading: 0,
              bFilter: false,
              pageLength: 5,
              ajax: $.extend({
                url: '{!! route('users.data') !!}'
              }, {
                data: function (d) {
                  d.user_name = $('#user_name').val();
                  d.user_phone = $('#user_phone').val();
                }
              }),
              columns: [
                {data: 'id', name: 'id'},
                {data: 'phone', name: 'phone', orderable: false, searchable: false},
                {data: 'user_name', name: 'user_name', orderable: false, searchable: false},
                {data: 'affiliate_id', name: 'affiliate_id', orderable: false, searchable: false},
                {data: 'bank_name', name: 'bank_name', orderable: false, searchable: false},
                {data: 'bank_branch', name: 'bank_branch', orderable: false, searchable: false},
                {data: 'bank_id', name: 'bank_id', orderable: false, searchable: false},
                {data: 'total_amount', name: 'total_amount', orderable: false, searchable: false},
                {data: 'current_amount', name: 'current_amount', orderable: false, searchable: false},
                {data: 'fukkatu', name: 'fukkatu', orderable: false, searchable: false},
                {data: 'no_game_played', name: 'no_game_played', orderable: false, searchable: false},
                {data: 'actions', name: 'actions', orderable: false, searchable: false}
              ],
              drawCallback: function (settings) {
                // Handle drawback
                $('.add-point-user').on('click', function (e) {
                  var user_id = $(this).data('user_id');
                  if (user_id) {
                    $('#form_add_point_user_id').html(user_id);
                  }
                });
              }
            });

        var searchUsersWithParams = function () {
          var user_name = $('#user_name').val();
          var user_phone = $('#user_phone').val();

          if ((user_name !== null && user_name !== '') || (user_phone !== null && user_phone !== '')) {
            myTable.draw();
          }
        };

        var showGameDetailWithQueryString = function () {
          var game_no = $.QueryString.game_no;
          var game_date = $.QueryString.game_date;
          var user_id = $.QueryString.user_id;

          if (game_no || game_date || user_id) {
            $("#qry_game_detail").removeClass("hidden");
          }
        };

        searchUsersWithParams();
        showGameDetailWithQueryString();
      };

      initDatatable();

    });
  })(jQuery);
</script>