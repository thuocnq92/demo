#!/bin/bash

# Run pm2 to start socket.io
pm2 start node -- nodejs/server.js
echo "Run pm2 to start socket.io"

# Start rtmp server for live stream
sudo /usr/sbin/nginx
echo "Start rtmp server for live stream"