const WebSocket = require('ws');
const { Client } = require('ssh2');
const fs = require('fs');

const wss = new WebSocket.Server({ port: 8080 });

wss.on('connection', ws => {
 const ssh = new Client();
 ssh.on('ready', () => {
  ssh.shell((err, stream) => {
   ws.on('message', msg => stream.write(msg));
   stream.on('data', d => ws.send(d.toString()));
  });
 }).connect({
  host: process.env.SSH_HOST,
  username: process.env.SSH_USER,
  privateKey: fs.readFileSync('/keys/id_rsa')
 });
});

