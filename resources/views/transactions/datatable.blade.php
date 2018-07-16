<!-- Default box -->
<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">Transaction List</h3>
    </div>
    <div class="box-body">
        <form class="form-horizontal">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3"></div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="txn_phone" class="col-sm-4 control-label">Phone</label>

                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="txn_phone" placeholder="0123456789">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="txn_date" class="col-sm-4 control-label">日付</label>

                            <div class="col-sm-8">
                                <input type="date" class="form-control" id="txn_date"
                                       placeholder="MM/DD/YYYY">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-info btn-block pull-right" id="filter_txn">検索
                        </button>
                    </div>
                    <div class="col-md-3"></div>
                </div>
            </div>
            <!-- /.box-body -->

        </form>

        <table class="table table-bordered table-hover" id="qry_table_transactions">
            <thead>
            <tr>
                <th>#</th>
                <th>Phone</th>
                <th>ユーザ名</th>
                <th>日付</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Transaction Fee</th>
                <th>Note</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

</div>
