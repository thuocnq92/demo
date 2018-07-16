@extends('layouts.master')

@section('styles')
    <link rel="stylesheet" href="{{ asset('bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <style>
    </style>
@endsection

@section('page_title')
    Game List
@endsection

@section('content')

    @include('games.datatable')

    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">Game Notifications</h3>

            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip"
                        title="" data-original-title="Collapse">
                    <i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip"
                        title="" data-original-title="Remove">
                    <i class="fa fa-times"></i></button>
            </div>
        </div>
        <div class="box-body">

            <table class="table table-bordered table-striped" id="qry_table_notifications">
                <thead>
                <tr>
                    <th>NO</th>
                    <th>Content</th>
                    <th width="150">Push Time</th>
                    <th>Is Sented</th>
                    <th style="max-width: 150px;">Action</th>
                </tr>
                </thead>
                <tbody>

                @if (count($game->notifications))

                    @foreach( $game->notifications as $i => $notification )

                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                {{ $notification->content }}
                            </td>
                            <td>{{ $notification->time->format('Y/m/d H:i A') }}</td>
                            <td>{{ $notification->is_sent }}</td>
                            <td>
                                <button type="button" class="btn btn-flat btn-danger delete-game-notification"
                                        {{ $notification->is_sent == \App\Models\Notification::IS_SENT ? 'disabled' : null }}
                                        data-toggle="modal"
                                        data-target="#modal-delete-notification"
                                        data-notification_id="{{ $notification->id }}"><i
                                            class="fa fa-trash"></i>
                                    Delete
                                </button>
                            </td>
                        </tr>

                    @endforeach

                @endif

                </tbody>
            </table>

        </div>

        <div class="modal fade" id="modal-delete-notification">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post">
                        <input type="hidden" name="_method" value="delete"/>
                        {!! csrf_field() !!}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">Alert</h4>
                        </div>
                        <div class="modal-body">
                            <p>Do you want delete this notification ?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </div>
                    </form>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->

    </div>

    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">Question Detail</h3>

            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip"
                        title="" data-original-title="Collapse">
                    <i class="fa fa-minus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip"
                        title="" data-original-title="Remove">
                    <i class="fa fa-times"></i></button>
            </div>
        </div>
        <div class="box-body">

            @if($game)
                <div class="box-body">
                    <table class="table table-bordered table-striped">
                        <tbody>
                        <tr>
                            <th>NO</th>
                            <th>問題</th>
                            <th colspan="3">回答</th>
                            <th>正解番号</th>
                            <th style="max-width: 250px;">Action</th>
                        </tr>

                        @foreach($game->questions as $question)

                            <tr id="qry_game_question_{{ $question->id }}">
                                <td class="text-center">{{ $question->no }}</td>
                                <td class="text-left">{{ $question->question }}</td>
                                <td class="text-center {{ $question->correct_answer == 1 ? 'cell-yellow' : null }}">
                                    {{ $question->answer1 }}
                                </td>
                                <td class="text-center {{ $question->correct_answer == 2 ? 'cell-yellow' : null }}">
                                    {{ $question->answer2 }}
                                </td>
                                <td class="text-center {{ $question->correct_answer == 3 ? 'cell-yellow' : null }}">
                                    {{ $question->answer3 }}
                                </td>
                                {{--<td class="text-center {{ $question->correct_answer == 4 ? 'cell-yellow' : null }}">--}}
                                {{--{{ $question->answer4 }}--}}
                                {{--</td>--}}
                                <td class="text-center">
                                    <span class="badge bg-red">{{ $question->correct_answer }}</span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-flat btn-info edit-game-question"
                                            data-question_id="{{ $question->id }}"
                                            {{ $game->status != \App\Models\Game::STATUS_DEFAULT ? 'disabled' : null }}>
                                        <i class="fa fa-edit"></i>
                                        登録
                                    </button>
                                    <button type="button" class="btn btn-flat btn-danger delete-game-question"
                                            {{ $game->status != \App\Models\Game::STATUS_DEFAULT ? 'disabled' : null }}
                                            data-toggle="modal"
                                            data-target="#modal-delete" data-question_id="{{ $question->id }}"><i
                                                class="fa fa-trash"></i>
                                        設問削除
                                    </button>
                                    <a href="{{ ($game->status == \App\Models\Game::STATUS_OPENED && $question->status == \App\Models\Question::STATUS_INIT && $question->id == $question_opening->id) ? url('open-question?question_id=' . $question->id) : null }}"
                                       class="btn btn-flat btn-success"
                                            {{ ($game->status == \App\Models\Game::STATUS_OPENED && $question->status == \App\Models\Question::STATUS_INIT && $question->id == $question_opening->id) ? null : 'disabled' }}>
                                        <i class="fa fa-play"></i>
                                        Open Question
                                    </a>
                                    <a href="{{ ($game->status == \App\Models\Game::STATUS_OPENED && $question->status == \App\Models\Question::STATUS_OPENED && $question->id == $question_opening->id) ? url('open-answer?question_id=' . $question->id) : null }}"
                                       class="btn btn-flat btn-danger"
                                            {{ ($game->status == \App\Models\Game::STATUS_OPENED && $question->status == \App\Models\Question::STATUS_OPENED && $question->id == $question_opening->id) ? null : 'disabled' }}>
                                        <i class="fa fa-stop"></i>
                                        Open Answer
                                    </a>
                                </td>
                            </tr>

                        @endforeach

                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        <!-- /.box-body -->
        <div class="box-footer" id="qry_end_game_result">
            <div class="pull-left">
                <button type="button" id="add_question" class="btn btn-flat btn-primary"
                        {{ $game->status != \App\Models\Game::STATUS_DEFAULT ? 'disabled' : null }}>
                    <i class="fa fa-plus"></i>
                    設問追加
                </button>
                @if($game->status == \App\Models\Game::STATUS_ENDED)
                    <a href="{{ route('games.result', ['id' => $game->id]) }}" type="button" id="show_game_result"
                       class="btn btn-flat btn-primary">
                        ゲーム結果
                    </a>
                @endif
            </div>

            <div class="pull-right">
                <a href="{{ $game->status != \App\Models\Game::STATUS_OPENED ? null : url('show-game-result?game_id=' . $game->id) }}"
                   class="btn btn-flat btn-info"
                        {{ $game->status != \App\Models\Game::STATUS_OPENED ? 'disabled' : null }}>
                    <i class="fa fa-play"></i>
                    Show Game Result
                </a>

                <a href="{{ $game->status != \App\Models\Game::STATUS_SHOW ? null : url('back-game-live?game_id=' . $game->id) }}"
                   class="btn btn-flat btn-warning"
                        {{ $game->status != \App\Models\Game::STATUS_SHOW ? 'disabled' : null }}>
                    <i class="fa fa-video-camera"></i>
                    Close Game Result
                </a>

                <a href="{{ $game->status != \App\Models\Game::STATUS_SHOW ? null : url('end-game?game_id=' . $game->id) }}"
                   class="btn btn-flat btn-danger"
                        {{ $game->status != \App\Models\Game::STATUS_SHOW ? 'disabled' : null }}>
                    <i class="fa fa-stop"></i>
                    End Game
                </a>
            </div>
        </div>

        <div class="modal fade" id="modal-delete">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post">
                        <input type="hidden" name="_method" value="delete"/>
                        {!! csrf_field() !!}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">Alert</h4>
                        </div>
                        <div class="modal-body">
                            <p>Do you want delete this question ?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </div>
                    </form>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->
    </div>

    <div class="box box-info hidden" id="form_add_question">
        <div class="box-header with-border">
            <h3 class="box-title">Add Question</h3>
        </div>
        <!-- /.box-header -->
        <!-- form start -->
        <form class="form-horizontal" method="POST" action="{{ route('games.add_question', ['id' => $game->id]) }}">
            {!! csrf_field() !!}
            <div class="box-body">
                <div class="form-group">
                    <label for="question_name" class="col-sm-2 control-label">問題</label>

                    <div class="col-sm-10">
                        <input type="text" name="question" class="form-control" id="question_name"
                               placeholder="Question">
                    </div>
                </div>
                <div class="form-group">
                    <label for="form_question_1" class="col-sm-3 control-label">回答 1</label>

                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="answer1" id="form_question_1"
                               placeholder="Answer">
                    </div>
                </div>
                <div class="form-group">
                    <label for="form_question_2" class="col-sm-3 control-label">回答 2</label>

                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="answer2" id="form_question_2"
                               placeholder="Answer">
                    </div>
                </div>
                <div class="form-group">
                    <label for="form_question_3" class="col-sm-3 control-label">回答 3</label>

                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="answer3" id="form_question_3"
                               placeholder="Answer">
                    </div>
                </div>
                {{--<div class="form-group">--}}
                {{--<label for="form_question_4" class="col-sm-3 control-label">回答 4</label>--}}

                {{--<div class="col-sm-9">--}}
                {{--<input type="text" class="form-control" name="answer4" id="form_question_4"--}}
                {{--placeholder="Answer">--}}
                {{--</div>--}}
                {{--</div>--}}
                <div class="form-group">
                    <label for="form_correct_answer" class="col-sm-2 control-label">正解</label>

                    <div class="col-sm-10">
                        <input type="text" name="correct_answer" class="form-control" id="form_correct_answer"
                               placeholder="Answer">
                    </div>
                </div>
            </div>
            <!-- /.box-body -->
            <div class="box-footer">
                <button type="submit" class="btn btn-flat btn-default"><i class="fa fa-plus"></i> 設問追加登録</button>
            </div>
            <!-- /.box-footer -->
        </form>
    </div>

    <div class="box box-info hidden" id="form_update_question">
        <div class="box-header with-border">
            <h3 class="box-title">Edit Question</h3>
        </div>
        <!-- /.box-header -->
        <!-- form start -->
        <form class="form-horizontal" method="post">
            <input type="hidden" name="_method" value="put"/>
            {!! csrf_field() !!}
            <div class="box-body">
                <div class="form-group">
                    <label for="update_question_name" class="col-sm-2 control-label">問題</label>

                    <div class="col-sm-10">
                        <input type="text" name="question" class="form-control" id="update_question_name"
                               placeholder="Question">
                    </div>
                </div>
                <div class="form-group">
                    <label for="update_question_1" class="col-sm-3 control-label">回答 1</label>

                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="answer1" id="update_question_1"
                               placeholder="Answer">
                    </div>
                </div>
                <div class="form-group">
                    <label for="update_question_2" class="col-sm-3 control-label">回答 2</label>

                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="answer2" id="update_question_2"
                               placeholder="Answer">
                    </div>
                </div>
                <div class="form-group">
                    <label for="update_question_3" class="col-sm-3 control-label">回答 3</label>

                    <div class="col-sm-9">
                        <input type="text" class="form-control" name="answer3" id="update_question_3"
                               placeholder="Answer">
                    </div>
                </div>
                {{--<div class="form-group">--}}
                {{--<label for="update_question_4" class="col-sm-3 control-label">回答 4</label>--}}

                {{--<div class="col-sm-9">--}}
                {{--<input type="text" class="form-control" name="answer4" id="update_question_4"--}}
                {{--placeholder="Answer">--}}
                {{--</div>--}}
                {{--</div>--}}
                <div class="form-group">
                    <label for="update_correct_answer" class="col-sm-2 control-label">正解</label>

                    <div class="col-sm-10">
                        <input type="text" name="correct_answer" class="form-control" id="update_correct_answer"
                               placeholder="Answer">
                    </div>
                </div>
            </div>
            <!-- /.box-body -->
            <div class="box-footer">
                <button type="submit" class="btn btn-flat btn-default"><i class="fa fa-plus"></i> 設問追加登録</button>
            </div>
            <!-- /.box-footer -->
        </form>
    </div>
@endsection

@section('scripts')
    @include('games.js_datatable')

    <script type="text/javascript">
      (function ($) {
        $(document).ready(function () {
          // Scroll to question if question hash in url
          var hash_element_id = window.location.hash;
          if (hash_element_id && typeof hash_element_id !== undefined) {
            $('html, body').animate({
              scrollTop: $(hash_element_id).offset().top
            }, 0);
          }

          // Show form add question
          $('#add_question').on('click', function (e) {
            e.preventDefault();

            $("#form_add_question").removeClass("hidden");
          });

          var data_questions = '{!! json_encode($game->questions) !!}';
          if (data_questions) {
            var questions = JSON.parse(data_questions);

            // Handle update question
            $('.edit-game-question').on('click', function (e) {
              e.preventDefault();

              var question_id = $(this).data('question_id');
              if (question_id && questions && questions.length) {
                var url_update = '{{ url('question/') }}' + '/' + question_id;

                // Change action form update
                $('#form_update_question form').attr('action', url_update);

                questions.forEach(function (question) {
                  if (question.id === question_id) {
                    $('#update_question_name').val(question.question);
                    $('#update_question_1').val(question.answer1);
                    $('#update_question_2').val(question.answer2);
                    $('#update_question_3').val(question.answer3);
                    $('#update_question_4').val(question.answer4);
                    $('#update_correct_answer').val(question.correct_answer);
                  }
                });

                // Show form update
                $('#form_update_question').removeClass('hidden');
              }
            });

            // Handle delete question
            $('.delete-game-question').on('click', function (e) {
              var question_id = $(this).data('question_id');
              if (question_id) {
                var url_remove = '{{ url('question/') }}' + '/' + question_id;

                // Change action form update
                $('#modal-delete form').attr('action', url_remove);
              }
            });

            // Handle delete Searching for Usages in All Places...
            $('.delete-game-notification').on('click', function (e) {
              var notification_id = $(this).data('notification_id');
              if (notification_id) {
                var url_remove = '{{ url('games/' . $game->id) }}' + '/delete-notification/' + notification_id;

                // Change action form update
                $('#modal-delete-notification form').attr('action', url_remove);
              }
            });
          }

        });
      })(jQuery)
    </script>
@endsection
