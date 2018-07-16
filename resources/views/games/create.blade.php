@extends('layouts.master')

@section('styles')
@endsection

@section('page_title')
    Game Create
@endsection

@section('content')

    <!-- Default box -->
    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">Add Game</h3>
        </div>
        <div class="box-body">
            <form class="form-horizontal" method="POST" action="{{ route('games.store') }}">

                {!! csrf_field() !!}
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-3"></div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date" class="col-sm-4 control-label">Live streaming start time</label>

                                <div class="col-sm-8">
                                    <input id="date" type="datetime-local" class="form-control" name="date"
                                           value="{{ old('date') }}">
                                    <p class="help-block">Time to when live stream will open for admin livestream</p>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="time_start_game" class="col-sm-4 control-label">When to start game</label>

                                <div class="col-sm-8">
                                    <input id="time_start_game" type="time" class="form-control" name="time_start_game"
                                           value="{{ old('time_start_game') }}">
                                    <p class="help-block">Time for announcement on home</p>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="name" class="col-sm-4 control-label">Name</label>

                                <div class="col-sm-8">
                                    <input id="name" type="text" class="form-control" name="name"
                                           value="{{ old('name') }}" autofocus>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="price" class="col-sm-4 control-label">Price</label>

                                <div class="col-sm-8">
                                    <input id="price" type="text" class="form-control" name="price"
                                           value="{{ old('price') }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3"></div>
                    </div>


                    <div class="row">
                        <div class="form-group">
                            <div class="col-md-2"></div>
                            <div class="col-md-6">
                                <label for="content_notification" class=" ontrol-label">Content
                                    Notification</label>
                                <input id="content_notification" class="form-control"
                                       name="content_notification" value="{{ old('content_notification') }}">
                                <p class="help-block">Content to send notification to device</p>
                            </div>

                            <div class="col-md-2">
                                <label for="time_notification" class="control-label">Push Time</label>
                                <input id="time_notification" type="time" class="form-control"
                                       name="time_notification"
                                       value="{{ old('time_notification') }}">
                                <p class="help-block">Time to send notification to device</p>
                            </div>
                            <div class="col-md-2"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <div class="col-md-2"></div>
                            <div class="col-md-6">
                                <label for="content_notification_2" class=" ontrol-label">Content
                                    Notification 2</label>
                                <input id="content_notification_2" class="form-control"
                                       name="content_notification_2" value="{{ old('content_notification_2') }}">
                                <p class="help-block">Content to send notification to device</p>
                            </div>

                            <div class="col-md-2">
                                <label for="time_notification_2" class="control-label">Push Time 2</label>
                                <input id="time_notification_2" type="time" class="form-control"
                                       name="time_notification_2"
                                       value="{{ old('time_notification_2') }}">
                                <p class="help-block">Time to send notification to device</p>
                            </div>
                            <div class="col-md-2"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <div class="col-md-2"></div>
                            <div class="col-md-6">
                                <label for="content_notification_3" class=" ontrol-label">Content
                                    Notification 3</label>
                                <input id="content_notification_3" class="form-control"
                                       name="content_notification_3" value="{{ old('content_notification_3') }}">
                                <p class="help-block">Content to send notification to device</p>
                            </div>

                            <div class="col-md-2">
                                <label for="time_notification_3" class="control-label">Push Time 3</label>
                                <input id="time_notification_3" type="time" class="form-control"
                                       name="time_notification_3"
                                       value="{{ old('time_notification_3') }}">
                                <p class="help-block">Time to send notification to device</p>
                            </div>
                            <div class="col-md-2"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <div class="col-md-2"></div>
                            <div class="col-md-6">
                                <label for="content_notification_4" class=" ontrol-label">Content
                                    Notification 4</label>
                                <input id="content_notification_4" class="form-control"
                                       name="content_notification_4" value="{{ old('content_notification_4') }}">
                                <p class="help-block">Content to send notification to device</p>
                            </div>

                            <div class="col-md-2">
                                <label for="time_notification_4" class="control-label">Push Time 4</label>
                                <input id="time_notification_4" type="time" class="form-control"
                                       name="time_notification_4"
                                       value="{{ old('time_notification_4') }}">
                                <p class="help-block">Time to send notification to device</p>
                            </div>
                            <div class="col-md-2"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <div class="col-md-2"></div>
                            <div class="col-md-6">
                                <label for="content_notification_5" class=" ontrol-label">Content
                                    Notification 5</label>
                                <input id="content_notification_5" class="form-control"
                                       name="content_notification_5" value="{{ old('content_notification_5') }}">
                                <p class="help-block">Content to send notification to device</p>
                            </div>

                            <div class="col-md-2">
                                <label for="time_notification_5" class="control-label">Push Time 5</label>
                                <input id="time_notification_5" type="time" class="form-control"
                                       name="time_notification_5"
                                       value="{{ old('time_notification_5') }}">
                                <p class="help-block">Time to send notification to device</p>
                            </div>
                            <div class="col-md-2"></div>
                        </div>
                    </div>
                </div>
                <!-- /.box-body -->

                <div class="box-footer clearfix">

                    <button type="submit" class="btn btn-flat btn-primary"><i class="fa fa-plus"></i>
                        Add Game
                    </button>

                </div>
            </form>
        </div>

    </div>
    <!-- /.box -->

@endsection

@section('scripts')
@endsection
