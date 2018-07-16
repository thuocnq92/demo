@extends('layouts.app')

@section('styles')
    <style>
        table tr.selected td {
            background-color: #f7e1b5;
        }

        table.table-questions tr td.answer-1-1,
        table.table-questions tr td.answer-2-2,
        table.table-questions tr td.answer-3-3,
        table.table-questions tr td.answer-4-4 {
            background: yellow;
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading">Questions List</div>

                    <div class="panel-body">
                        <div class="filter filter-game">
                            <div class="row">
                                <form class="form-horizontal" action="">
                                    <input type="hidden" value="{{ csrf_token() }}">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label col-sm-4" for="game_id">Game No</label>
                                            <div class="col-sm-8">
                                                <input type="number" class="form-control" id="game_id" name="game_id"
                                                       placeholder="Eg: 123456" value="{{ request('game_id') ? request('game_id') : null }}">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label col-sm-4" for="date">Date</label>
                                            <div class="col-sm-8">
                                                <input type="date" dataformatas="Y-m-d" value="{{ request('date') ? \Carbon\Carbon::parse(request('date'))->format('Y-m-d') : null }}" name="date" class="form-control" >
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <div class="col-sm-offset-2 col-sm-10">
                                                <button type="submit" class="btn btn-default">Filter</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-games">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Game</th>
                                    <th>Date - Time</th>
                                    <th>No questions</th>
                                    <th>Price</th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach($games as $game)
                                    @if(request('game_id') == $game->id)
                                        <tr class="clickable selected" data-game-id="{{ $game->id }}">
                                    @else
                                        <tr class="clickable" data-game-id="{{ $game->id }}">
                                            @endif
                                            <td>{{ $game->id }}</td>
                                            <td>{{ $game->name }}</td>
                                            <td>{{ $game->date->format('Y-m-d H:i:s') }}</td>
                                            <td>{{ $game->questions_count }}</td>
                                            <td>{{ App\Http\Helper\Helper::currencyFormat($game->price) }}</td>
                                        </tr>
                                        @endforeach
                                </tbody>
                            </table>
                            {{ $games->links() }}
                        </div>

                        <div class="game-questions-detail">
                            {{-- Include questions table--}}
                            {{--@include('questions.questions_table', ['questions' => $questions])--}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            $('tr.clickable').click(function () {
                var $this = $(this)
                var game_id = $(this).attr('data-game-id')

                $.ajax({
                    url: "{{ route('question.by_game') }}",
                    method: 'POST',
                    dataType: 'html',
                    data: {game_id: game_id},
                    success: function (res) {
                        $('.game-questions-detail').html(res)
                        $('.table-games tr.clickable').removeClass('selected')
                        $this.addClass('selected');
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                })
            })
        })
    </script>
@endsection
