import http from 'node:http';
import { pathToFileURL } from 'node:url';
import { endpointForTool, tools, validateToolArguments } from '../server/index.mjs';

export const SERVER_NAME = 'erp-lmg-digital-marketing';
export const SERVER_VERSION = '1.5.0';

const defaultConfig = {
  port: Number.parseInt(process.env.MCP_PORT ?? '8787', 10),
  host: process.env.MCP_HOST ?? '127.0.0.1',
  apiBase: (process.env.ERP_LMG_API_URL ?? 'http://127.0.0.1:8000').replace(/\/$/, ''),
  oauthIssuer: (process.env.MCP_OAUTH_ISSUER ?? process.env.ERP_LMG_API_URL ?? 'http://127.0.0.1:8000').replace(/\/$/, ''),
  timeoutMs: 15_000,
};

function json(res, status, body, headers = {}) {
  res.writeHead(status, { 'content-type': 'application/json; charset=utf-8', 'cache-control': 'no-store', ...headers });
  res.end(JSON.stringify(body));
}

function rpcError(id, code, message, data) {
  return { jsonrpc: '2.0', id: id ?? null, error: { code, message, ...(data ? { data } : {}) } };
}

function challengeFor(config) {
  const metadata = `${config.oauthIssuer}/.well-known/oauth-protected-resource`;
  return `Bearer resource_metadata="${metadata}", scope="marketing:read marketing:write offline_access"`;
}

function unauthorized(res, id, config) {
  const challenge = challengeFor(config);
  json(res, 401, rpcError(id, -32001, 'OAuth authorization required', {
    _meta: { 'mcp/www_authenticate': [challenge] },
  }), { 'www-authenticate': challenge });
}

async function readJson(req) {
  let raw = '';
  for await (const chunk of req) {
    raw += chunk;
    if (raw.length > 1_000_000) throw new ClientInputError('Request body terlalu besar.');
  }
  try {
    return JSON.parse(raw || '{}');
  } catch {
    throw new ClientInputError('JSON request tidak valid.');
  }
}

function bearerToken(req) {
  const match = /^Bearer\s+([^\s]+)$/i.exec(req.headers.authorization ?? '');
  return match?.[1] ?? null;
}

function queryFor(args) {
  const query = new URLSearchParams();
  for (const [key, value] of Object.entries(args ?? {})) {
    if (key === 'client_id' || value === undefined || value === null || value === '') continue;
    if (Array.isArray(value)) value.forEach((item) => query.append(`${key}[]`, String(item)));
    else query.set(key, String(value));
  }
  return query;
}

async function callLaravel(fetchImpl, config, token, name, args) {
  const path = endpointForTool(name, args, tools.find((tool) => tool.name === name)?.write ? '/api/oauth-marketing-crud' : '/api/oauth-marketing-mcp');
  const url = new URL(path, config.apiBase);
  const isWrite = tools.find((tool) => tool.name === name)?.write === true;
  url.search = isWrite ? '' : queryFor(args).toString();
  let response;
  try {
    response = await fetchImpl(url, {
      method: isWrite ? (name.includes('create') ? 'POST' : name.includes('update') ? 'PATCH' : 'POST') : 'GET',
      headers: { authorization: `Bearer ${token}`, accept: 'application/json' },
      ...(isWrite ? { headers: { authorization: `Bearer ${token}`, accept: 'application/json', 'content-type': 'application/json' }, body: JSON.stringify(args) } : {}),
      signal: AbortSignal.timeout(config.timeoutMs),
    });
  } catch (error) {
    if (error?.name === 'TimeoutError' || error?.name === 'AbortError') throw new UpstreamError('ERP tidak merespons dalam batas waktu.');
    throw new UpstreamError('ERP tidak dapat dihubungi.');
  }

  const contentType = response.headers?.get?.('content-type') ?? '';
  if (!contentType.toLowerCase().includes('application/json')) {
    throw new UpstreamError('ERP mengembalikan respons yang tidak valid.', response.status);
  }
  let payload;
  try {
    payload = await response.json();
  } catch {
    throw new UpstreamError('ERP mengembalikan respons JSON yang tidak valid.', response.status);
  }
  if (response.status === 401 || response.status === 403) throw new OAuthError();
  if (!response.ok) {
    const safeMessage = response.status === 404
      ? 'Data atau endpoint ERP tidak ditemukan.'
      : response.status === 422
        ? 'ERP menolak parameter yang diberikan.'
        : 'ERP gagal memproses permintaan.';
    throw new UpstreamError(safeMessage, response.status);
  }
  return payload;
}

class ClientInputError extends Error {}
class OAuthError extends Error {}
class UpstreamError extends Error {
  constructor(message, status = 502) {
    super(message);
    this.status = status;
  }
}

export function createRemoteMcpServer(options = {}) {
  const config = { ...defaultConfig, ...options };
  const fetchImpl = options.fetchImpl ?? fetch;

  return http.createServer(async (req, res) => {
    const requestUrl = new URL(req.url ?? '/', 'http://localhost');
    if (req.method === 'GET' && requestUrl.pathname === '/health') {
      return json(res, 200, { status: 'ok', service: SERVER_NAME, version: SERVER_VERSION, tools: tools.length });
    }
    if (req.method !== 'POST' || requestUrl.pathname !== '/mcp') {
      return json(res, 404, { message: 'Not found' });
    }

    let message;
    try {
      message = await readJson(req);
    } catch (error) {
      return json(res, 400, rpcError(null, -32700, error instanceof ClientInputError ? error.message : 'Request tidak valid.'));
    }

    if (message.method === 'initialize') {
      return json(res, 200, {
        jsonrpc: '2.0', id: message.id, result: {
          protocolVersion: message.params?.protocolVersion ?? '2025-06-18',
          capabilities: { tools: { listChanged: false } },
          serverInfo: { name: SERVER_NAME, version: SERVER_VERSION },
          instructions: "ERP LMG Digital Marketing. Read tools never mutate data. Write tools require explicit confirmation and a short-lived confirmation token. Use marketing_list_clients for 'clients I can access' and marketing_get_division_client_summary for division-wide client totals. Never present a restricted personal count as the division total.",
        },
      });
    }
    if (message.method === 'notifications/initialized') {
      res.writeHead(202, { 'cache-control': 'no-store' }).end();
      return;
    }

    const token = bearerToken(req);
    if (!token) return unauthorized(res, message.id, config);

    if (message.method === 'tools/list') {
      return json(res, 200, { jsonrpc: '2.0', id: message.id, result: { tools } });
    }
    if (message.method === 'tools/call') {
      const name = message.params?.name;
      const args = message.params?.arguments ?? {};
      if (!tools.some((tool) => tool.name === name)) {
        return json(res, 200, rpcError(message.id, -32602, 'Tool tidak dikenal.'));
      }
      const validationErrors = validateToolArguments(name, args);
      if (validationErrors.length) {
        return json(res, 200, rpcError(message.id, -32602, 'Input tool tidak valid.', { errors: validationErrors }));
      }
      try {
        const data = await callLaravel(fetchImpl, config, token, name, args);
        return json(res, 200, {
          jsonrpc: '2.0', id: message.id,
          result: { content: [{ type: 'text', text: JSON.stringify(data) }], structuredContent: data },
        });
      } catch (error) {
        if (error instanceof OAuthError) return unauthorized(res, message.id, config);
        const messageText = error instanceof UpstreamError ? error.message : 'Remote MCP gagal memproses permintaan.';
        return json(res, 200, {
          jsonrpc: '2.0', id: message.id,
          result: { isError: true, content: [{ type: 'text', text: messageText }] },
        });
      }
    }
    return json(res, 200, rpcError(message.id, -32601, 'Method not found'));
  });
}

export function startRemoteMcpServer(options = {}) {
  const config = { ...defaultConfig, ...options };
  const server = createRemoteMcpServer(config);
  server.listen(config.port, config.host, () => {
    console.log(`${SERVER_NAME} ${SERVER_VERSION} listening on ${config.host}:${config.port}/mcp`);
  });
  return server;
}

if (process.argv[1] && import.meta.url === pathToFileURL(process.argv[1]).href) {
  startRemoteMcpServer();
}
