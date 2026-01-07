const WebSocket = require('ws');
const { Client } = require('ssh2');
const mysql = require('mysql2/promise');
const url = require('url');

const wss = new WebSocket.Server({ port: 3000 });

const dbConfig = {
  host: 'mysql',
  user: 'monitor',
  password: 'monitor123',
  database: 'monitoring'
};

console.log("🚀 Terminal Gateway listening on port 3000");

wss.on('connection', async (ws, req) => {
  const params = url.parse(req.url, true).query;
  const serverId = params.server_id;

  if (!serverId) {
    ws.send("❌ server_id missing");
    ws.close();
    return;
  }

  let conn, ssh;

  try {
    // Fetch server details
    conn = await mysql.createConnection(dbConfig);
    const [rows] = await conn.execute(
      "SELECT hostname, ip_address, ssh_user, ssh_password, ssh_port FROM servers WHERE id=? AND enabled=1",
      [serverId]
    );

    if (rows.length === 0) {
      ws.send("❌ Server not found or disabled");
      ws.close();
      return;
    }

    const s = rows[0];
    ssh = new Client();

    ssh.on('ready', () => {
      ws.send(`✅ Connected to ${s.hostname}\r\n`);

      ssh.shell((err, stream) => {
        if (err) {
          ws.send("❌ Shell error");
          ws.close();
          return;
        }

        // SSH → Browser
        stream.on('data', data => ws.send(data.toString()));
        stream.on('close', () => ws.close());

        // Browser → SSH
        ws.on('message', msg => stream.write(msg));
      });
    });

    ssh.on('error', err => {
      ws.send("❌ SSH connection failed");
      ws.close();
    });

    ssh.connect({
      host: s.ip_address,
      port: s.ssh_port || 22,
      username: s.ssh_user,
      password: s.ssh_password,
      readyTimeout: 5000
    });

  } catch (e) {
    ws.send("❌ Internal error");
    ws.close();
  }

  ws.on('close', () => {
    if (ssh) ssh.end();
    if (conn) conn.end();
  });
});

