<!-- Default box -->
<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">User Search</h3>
    </div>
    <div class="box-body">
        <form class="form-horizontal" method="GET" action="{{ route('users.index') }}">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3"></div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="user_name" class="col-sm-4 control-label">ユーザー名</label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control"
                                       id="user_name"
                                       name="user_name"
                                       placeholder="Username"
                                       value="{{ isset($user_name) ? $user_name : null }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="user_phone" class="col-sm-4 control-label">電話番号</label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control"
                                       id="user_phone"
                                       name="user_phone"
                                       value="{{ isset($user_phone) ? $user_phone : null }}"
                                       placeholder="0123456789">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-info btn-block pull-right" id="filter_user">検索
                        </button>
                    </div>
                    <div class="col-md-3"></div>
                </div>
            </div>
            <!-- /.box-body -->

        </form>

        <table class="table table-bordered table-hover" id="qry_table_users">
            <thead>
            <tr>
                <th>User ID</th>
                <th>電話番号</th>
                <th width="60">ユーザ名</th>
                <th width="60">招待コード</th>
                <th width="60">銀行名</th>
                <th width="60">支店名</th>
                <th width="60">口座番号</th>
                <th>賞金(総合)</th>
                <th>賞金残高</th>
                <th>復活P</th>
                <th>参加回数</th>
                <th style="max-width: 200px;">Actions</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

</div>
