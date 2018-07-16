@extends('layouts.master')

@section('styles')
    <link rel="stylesheet"
          href="{{ asset('bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
@endsection

@section('page_title')
    User List
@endsection

@section('content')
    <div class="error">

    </div>

    @include('users.datatable')

    <!-- Default box -->
    <div class="box box-info hidden" id="qry_game_detail">
        <div class="box-header with-border">
            <h3 class="box-title">Game List</h3>
        </div>
        <div class="box-body">
            <form class="form-horizontal" method="GET" action="{{ route('users.index') }}">
                <div class="box-body">
                    <div class="row">
                        <label for="filter_game_no" class="col-sm-2 control-label">ゲームNO</label>
                        <div class="col-sm-3">
                            <input type="text" class="form-control"
                                   value="{{ isset($game_no) ? $game_no : null }}" name="game_no"
                                   id="qry_game_no"
                                   placeholder="1234">
                        </div>

                        <label for="qry_game_date" class="col-sm-2 control-label">日付</label>
                        <div class="col-sm-3">
                            <input type="date" class="form-control"
                                   value="{{ isset($game_date) ? $game_date : null }}" name="game_date"
                                   id="qry_game_date"
                                   placeholder="YYYY/MM/DD">
                        </div>
                        <div class="col-sm-2">
                            <input type="hidden" name="user_name"
                                   value="{{ isset($user_name) ? $user_name : null }}">
                            <input type="hidden" name="user_phone"
                                   value="{{ isset($user_phone) ? $user_phone : null }}">
                            <input type="hidden" name="user_id"
                                   value="{{ isset($user_id) ? $user_id : null }}">
                            <button type="submit" class="btn btn-info btn-block pull-right">検索</button>
                        </div>
                    </div>
                </div>
                <!-- /.box-body -->
            </form>

            @if(count($game_users) && isset($max_column_questions))

                <table class="table table-bordered table-striped">
                    <tbody>
                    <tr>
                        <th>NO</th>
                        <th>ユーザー名</th>
                        <th>日付</th>
                        <th>結果</th>
                        @for ($i = 1; $i <= $max_column_questions; $i++)
                            <th>{{ $i }}</th>
                        @endfor
                        <th>獲得賞金</th>
                    </tr>

                    @foreach($game_users as $game_user)

                        <tr>
                            <td class="text-center">{{ $game_user['user_id'] }}</td>
                            <td class="text-center">
                                {{ $game_user['user_name'] }}
                            </td>
                            <td class="text-center">{{ $game_user['game_date'] }}</td>
                            <td class="text-center">
                                {{ $game_user['is_win'] ? 'O' : 'X' }}
                            </td>

                            @if( count($game_user['answers']) )
                                @foreach($game_user['answers'] as $answer)

                                    <td class="text-center {{ ! empty($answer['class_answer']) ? $answer['class_answer'] : null }}">{{ $answer['answer'] }}</td>

                                @endforeach
                            @endif

                            <td class="text-center">¥{{ $game_user['game_price'] }}</td>
                        </tr>

                    @endforeach

                    </tbody>
                </table>

            @endif
        </div>
        <!-- /.box-body -->
        <div class="box-footer clearfix">

            @include('pagination.default', ['paginator' => $pagination->appends($_GET)])

        </div>
    </div>
    <!-- /.box -->

    <div class="modal fade" id="modal-add-live-point">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="" id="add-live-points-form">
                    {!! csrf_field() !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Add Live Points For User ID: <span id="form_add_point_user_id"></span>
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-error"></div>
                        <div class="form-group">
                            <div class="row">
                                <label for="game_no" class="col-sm-4 control-label text-right">Live points</label>

                                <div class="col-sm-6">
                                    <input type="text" name="live_points" class="form-control" id="live_points"
                                           placeholder="">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancel</button>
                        <button type="button" id="add_live_point_btn" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>

@endsection

@section('scripts')

    @include('users.js_datatable')

    <script type="text/javascript">
      $('#add_live_point_btn').click(function (e) {
        e.preventDefault();

        var id = $('#form_add_point_user_id').html();
        var live_points = $('input[name=live_points]').val();

        $.ajax({
          method: 'POST',
          url: '{{url('users')}}/' + id + '/live-points',
          data: {live_points: live_points},
          dataType: 'json',
          success: function (resp) {
            if (resp.code == 200) {
              $('.error').html('<div class="callout callout-success qry-alert"><h4>Success !</h4><p>' + resp.msg + '</p></div>')
              $('#modal-add-live-point').modal('hide')
              $('input[name=live_points]').val('')
            } else if (resp.code == 422) {
              $('.form-error').html('<div class="callout callout-danger qry-alert"><h4>Errors !</h4><p>' + resp.msg + '</p></div>')
            }
          },
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        })
      })
    </script>
@endsection
