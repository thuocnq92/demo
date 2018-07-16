@extends('layouts.master')

@section('styles')
    <link rel="stylesheet"
          href="{{ asset('bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <style>
        .pagination {
            margin: 0;
        }
        ul.bd-head-box {
            padding-left: 10px;
        }
        ul.bd-head-box > li {
            list-style: none;
            line-height: 1.5em;
            padding: 8px 0;
        }
        ul.bd-head-box > li > strong > span {
            display: inline-block;
        }
    </style>
@endsection

@section('page_title')
    Game Result
@endsection

@section('content')

    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">ゲーム結果</h3>

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
            <ul class=bd-head-box">
                <li><strong><span>参加人数:</span> {{ number_format($count_users) }}人</strong></li>
                <li><strong><span>全問正解者数:</span> {{ number_format($right_users) }}人</strong></li>
            </ul>
            @if($count_users)
                <table class="table table-bordered table-striped">
                        <tbody>
                        <tr>
                            <th>ユーザーID</th>
                            <th>ユーザ名</th>
                            <th>招待コード</th>
                            <th>獲得賞金</th>
                            <th>全問正解回数</th>
                        </tr>

                        @foreach($users as $user)

                            <tr>
                                <td class="text-right">{{ $user->id }}</td>
                                <td class="text-left">{{ $user->name }}</td>
                                <td class="text-right">
                                    {{ $user->affiliate_id }}
                                </td>
                                <td class="text-right">
                                    {{ App\Http\Helper\Helper::currencyFormat($bonus) }}
                                </td>
                                <td class="text-right">
                                    {{ @number_format($user->transactions_count) }}
                                </td>
                            </tr>

                        @endforeach

                        </tbody>
                    </table>
            @endif
        </div>
        <!-- /.box-body -->
        <div class="box-footer">
            <div class="pull-left">
                <a href="{{ route('games.show', ['id' => $game->id]) }}"
                   class="btn btn-flat btn-default">
                    Back
                </a>
            </div>

            <div class="pull-right">
                {{ $users->render() }}
            </div>
        </div>
    </div>
@endsection

