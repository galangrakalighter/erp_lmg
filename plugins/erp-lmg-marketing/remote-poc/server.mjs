import http from 'node:http';

const port = Number.parseInt(process.env.MCP_PORT ?? '8787', 10);
const host = process.env.MCP_HOST ?? '127.0.0.1';
const apiBase = (process.env.ERP_LMG_API_URL ?? 'http://127.0.0.1:8000').replace(/\/$/, '');
const publicUrl = (process.env.MCP_PUBLIC_URL ?? `http://${host}:${port}`).replace(/\/$/, '');
const oauthIssuer = (process.env.MCP_OAUTH_ISSUER ?? apiBase).replace(/\/$/, '');
const resourceMetadataUrl = `${oauthIssuer}/.well-known/oauth-protected-resource`;

const tool = {
  name: 'marketing_list_clients',
  description: 'Daftar client ERP LMG yang boleh dibaca oleh pengguna yang sedang terhubung.',
  inputSchema: {
    type: 'object',
    properties: {
      search: { type: 'string', maxLength: 100 },
      status: { type: 'integer', enum: [0, 1] },
      page: { type: 'integer', minimum: 1 },
      per_page: { type: 'integer', minimum: 1, maximum: 100 },
    },
    additionalProperties: false,
  },
  annotations: { readOnlyHint: true, destructiveHint: false, openWorldHint: false },
};

function json(res, status, body, headers = {}) {
  res.writeHead(status, { 'content-type': 'application/json; charset=utf-8', ...headers });
  res.end(JSON.stringify(body));
}

function rpcError(id, code, message, data) {
  return { jsonrpc: '2.0', id: id ?? null, error: { code, message, ...(data ? { data } : {}) } };
}

function unauthorized(res, id) {
  const challenge = `Bearer resource_metadata="${resourceMetadataUrl}", scope="marketing:read offline_access"`;
  json(res, 401, rpcError(id, -32001, 'OAuth authorization required', {
    _meta: { 'mcp/www_authenticate': [challenge] },
  }), { 'www-authenticate': challenge });
}

async function readJson(req) {
  let raw = '';
  for await (const chunk of req) {
    raw += chunk;
    if (raw.length > 1_000_000) throw new Error('Request body too large');
  }
  return JSON.parse(raw || '{}');
}

async function callListClients(token, args) {
  const url = new URL('/api/oauth-marketing-mcp/clients', apiBase);
  for (const [key, value] of Object.entries(args ?? {})) {
    if (value !== undefined && value !== null && value !== '') url.searchParams.set(key, String(value));
  }
  const response = await fetch(url, {
    headers: { authorization: `Bearer ${token}`, accept: 'application/json' },
    signal: AbortSignal.timeout(15_000),
  });
  const payload = await response.json().catch(() => ({ message: 'ERP returned an invalid response.' }));
  if (!response.ok) {
    const error = new Error(payload.message ?? `ERP request failed (${response.status}).`);
    error.status = response.status;
    throw error;
  }
  return payload;
}

const server = http.createServer(async (req, res) => {
  if (req.method === 'GET' && req.url === '/health') {
    return json(res, 200, { status: 'ok', service: 'erp-lmg-marketing-remote-poc' });
  }
  if (req.method !== 'POST' || req.url !== '/mcp') {
    return json(res, 404, { message: 'Not found' });
  }

  let message;
  try {
    message = await readJson(req);
  } catch (error) {
    return json(res, 400, rpcError(null, -32700, error.message));
  }

  if (message.method === 'initialize') {
    return json(res, 200, {
      jsonrpc: '2.0', id: message.id, result: {
        protocolVersion: message.params?.protocolVersion ?? '2025-06-18',
        capabilities: { tools: {} },
        serverInfo: { name: 'erp-lmg-marketing-remote-poc', version: '0.1.0' },
        instructions: 'Read-only POC. Use only returned ERP data and never invent unavailable figures.',
      },
    });
  }
  if (message.method === 'notifications/initialized') {
    res.writeHead(202).end();
    return;
  }

  const match = /^Bearer\s+(.+)$/i.exec(req.headers.authorization ?? '');
  if (!match) return unauthorized(res, message.id);

  if (message.method === 'tools/list') {
    return json(res, 200, { jsonrpc: '2.0', id: message.id, result: { tools: [tool] } });
  }
  if (message.method === 'tools/call' && message.params?.name === tool.name) {
    try {
      const data = await callListClients(match[1], message.params.arguments);
      return json(res, 200, {
        jsonrpc: '2.0', id: message.id,
        result: { content: [{ type: 'text', text: JSON.stringify(data) }], structuredContent: data },
      });
    } catch (error) {
      if (error.status === 401 || error.status === 403) return unauthorized(res, message.id);
      return json(res, 200, {
        jsonrpc: '2.0', id: message.id,
        result: { isError: true, content: [{ type: 'text', text: error.message }] },
      });
    }
  }
  return json(res, 200, rpcError(message.id, -32601, 'Method not found'));
});

server.listen(port, host, () => {
  console.log(`ERP LMG remote MCP POC listening on ${publicUrl}/mcp`);
});
