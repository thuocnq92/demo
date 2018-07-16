<!-- Default box -->
<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">Question List</h3>
    </div>
    <div class="box-body">
        <a type="button" href="{{ route('games.create') }}" class="btn btn-flat btn-primary pull-left btn-add-game"><i
                    class="fa fa-plus"></i>
            Add Game
        </a>
        <form class="form-horizontal">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3"></div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="game_no" class="col-sm-4 control-label">ゲームNO</label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="game_no" placeholder="123456">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="game_date" class="col-sm-4 control-label">日付</label>

                            <div class="col-sm-8">
                                <input type="date" class="form-control" id="game_date"
                                       placeholder="MM/DD/YYYY">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-info btn-block pull-right" id="filter_game">検索
                        </button>
                    </div>
                    <div class="col-md-3"></div>
                </div>
            </div>
            <!-- /.box-body -->

        </form>

        <table class="table table-bordered table-hover" id="qry_table_games">
            <thead>
            <tr>
                <th>ゲームNO</th>
                <th>日付</th>
                <th width="60">Live Streaming start time</th>
                <th width="60">When to start game</th>
                <th width="60">Is Notified</th>
                <th>問題数</th>
                <th>賞金金額</th>
                <th>Stream Link</th>
                <th>Live Code</th>
                <th>Status</th>
                <th style="max-width: 180px;">Action</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

</div>

<div class="modal fade" id="modal-delete-game">
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
                    <p>Do you want delete this game ?</p>
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
<!-- /.box -->