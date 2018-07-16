@extends('layouts.app')

@section('content')
    <script src="//code.jquery.com/jquery-1.11.2.min.js"></script>
    <script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>

    //Socket.IO Client
    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.1.0/socket.io.js"></script>

    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-lg-offset-2">
                <div id="messages"></div>
                <p>JWT token</p>
                <input type="text" id="jwt_token">
            </div>
        </div>
    </div>
    <script>

      (function ($) {
        $(document).ready(function () {

          $('#jwt_token').on('change', function () {
            var token = $(this).val();

            var socket = io.connect('http://ec2-13-231-12-160.ap-northeast-1.compute.amazonaws.com:8890', {
              query: {
                token: token
              }
            });

            //      var socket = io.connect('http://ec2-13-231-12-160.ap-northeast-1.compute.amazonaws.com:8890');

            socket.on('message_created', function (data) {
              $("#messages").append("<p>" + data + "</p>");
              console.log('data', data);
            });

            socket.on('end_game', function (data) {
              console.log('data', data);
            });

            socket.on('open_question', function (data) {
              console.log('data', data);
            });

            socket.on('open_answer', function (data) {
              console.log('data', data);
            });

            socket.on('show_game_result', function (data) {
              console.log('data', data);
            });

            socket.on('back_game_live', function (data) {
              console.log('data', data);
            });

            socket.on('count_users_online', function (count) {
              console.log('count', count);
            });
          });

        });
      })(jQuery);

    </script>

@endsection