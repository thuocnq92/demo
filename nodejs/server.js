var app = require('express')();
var server = require('http').Server(app);
var io = require('socket.io')(server);
var redis = require('redis');
var socketioJwt = require('socketio-jwt');
var myEnv = require('dotenv').config({path: '.env'});

io.use(socketioJwt.authorize({
  secret: myEnv.parsed.JWT_SECRET,
  timeout: 15000,
  handshake: true
}));

var online_users = new Array();

io.on('connection', function (socket) {

  console.log("new client connected");
  console.log(socket.decoded_token);

  // Add user array user online
  if (socket.decoded_token) {
    var user_id = socket.decoded_token.sub;
    if (online_users.indexOf(user_id) === -1) {
      online_users.push(user_id);
    }
  }

  // Emit event count_users_online
  io.sockets.emit('count_users_online', { count: online_users.length });

  var redisClient = redis.createClient();
  redisClient.subscribe('message');
  redisClient.subscribe('end_game');
  redisClient.subscribe('open_question');
  redisClient.subscribe('open_answer');
  redisClient.subscribe('show_game_result');
  redisClient.subscribe('back_game_live');
  redisClient.subscribe('message_created');

  redisClient.on("message", function (channel, message) {
    console.log("mew message in queue " + message + " channel");
    socket.emit(channel, message);
  });

  redisClient.on("end_game", function (channel, data) {
    console.log("end game with game_id: " + data + " channel");
    socket.emit(channel, data);
  });

  redisClient.on("open_question", function (channel, data) {
    console.log("open question with question_id: " + data + " channel");
    socket.emit(channel, data);
  });

  redisClient.on("open_answer", function (channel, data) {
    console.log("open answer with question_id " + data + " channel");
    socket.emit(channel, data);
  });

  redisClient.on("show_game_result", function (channel, data) {
    console.log("show all winners of game" + data + " channel");
    socket.emit(channel, data);
  });

  redisClient.on("back_game_live", function (channel, data) {
    console.log("back to game live " + data + " channel");
    socket.emit(channel, data);
  });

  redisClient.on("message_created", function (channel, data) {
    console.log("send message " + data + " for all users");
    socket.emit(channel, data);
  });

  socket.on('disconnect', function () {
    console.log('disconnect');
    redisClient.quit();

    // Splice user array user online
    if (socket.decoded_token) {
      var user_id = socket.decoded_token.sub;
      var index = online_users.indexOf(user_id);

      if (index !== -1) {
        online_users.splice(index, 1);

        // Emit event count_users_online
        io.sockets.emit('count_users_online', { count: online_users.length });
      }
    }
  });

});

server.listen(8890);
