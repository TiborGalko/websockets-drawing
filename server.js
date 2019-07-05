var fs = require('fs');

var cfg = {
    ssl: true,
    port: 5500,
    ssl_key: '/etc/ssl/private/apache-selfsigned.key',
    ssl_cert: '/etc/ssl/certs/apache-selfsigned.crt'
};

//prebrate z http://www.tothenew.com/blog/websockets-in-node-js/
//a http://www.giacomovacca.com/2015/02/websockets-over-nodejs-from-plain-to.html
var WebSocketServer = require('websocket').server;
var http = require('https');
var wsServer;
var server = http.createServer({
    //nastavenie certifikatu a kluca
    key: fs.readFileSync( cfg.ssl_key ),
    cert: fs.readFileSync( cfg.ssl_cert )}
    ,function(request, response) {
    console.log((new Date()) + ' Received request for ' + request.url);
    response.writeHead(404);
    response.end();
});
server.listen(cfg.port, function() {
    console.log((new Date()) + ' Server is listening on port 5500');
});

wsServer = new WebSocketServer({
    httpServer: server
});

var counter = 0;
var clients = [];

//pocuvanie eventov
wsServer.on('request', function(request) {
    var connection = request.accept('echo-protocol', request.origin);
    console.log('Connection created at : ', new Date());

    clients[counter] = connection; //ulozenie spojenia
    connection.id = counter; //ulozenie id
    counter++;

    connection.on('message', function(message) {
        console.log(message.utf8Data);

        //rozposlanie spravy vsetkym pripojenym klientom
        for (index in clients){
            if(clients[index].id != connection.id){
                clients[index].send(JSON.stringify(message));
            }
        }
    });

    connection.on('close', function(connection) {
        delete clients[connection.id];
        console.log('Peer ' + connection.remoteAddress + ' disconnected at : ', new Date());
    });
});