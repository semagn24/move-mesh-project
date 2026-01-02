const http = require('http');
const httpProxy = require('http-proxy');

// Create a proxy server
const proxy = httpProxy.createProxyServer({});

// List of backend servers
const targets = [
    'http://localhost:5001',
    'http://localhost:5002',
    'http://localhost:5003'
];

let i = 0;

const server = http.createServer((req, res) => {
    // Round-robin load balancing
    const target = targets[i];
    i = (i + 1) % targets.length;

    console.log(`[Load Balancer] Proxying request to ${target} | Path: ${req.url}`);

    proxy.web(req, res, { target: target }, (err) => {
        console.error(`[Load Balancer] Error forwarding to ${target}:`, err.message);
        res.writeHead(502, { 'Content-Type': 'text/plain' });
        res.end('Bad Gateway: Unable to connect to backend server.');
    });
});

console.log('Load Balancer running on http://localhost:5000');
server.listen(5000);
