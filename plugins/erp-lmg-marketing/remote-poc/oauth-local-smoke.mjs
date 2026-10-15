import http from 'node:http';
import crypto from 'node:crypto';

const issuer = (process.env.OAUTH_ISSUER ?? 'http://192.168.14.99:8000').replace(/\/$/, '');
const mcpUrl = process.env.MCP_URL ?? 'http://192.168.14.99:8787/mcp';
const clientId = process.env.OAUTH_CLIENT_ID;
const redirectUri = process.env.OAUTH_REDIRECT_URI ?? 'http://127.0.0.1:8400/callback';

if (!clientId) {
  throw new Error('OAUTH_CLIENT_ID wajib diisi.');
}

const base64url = (value) => Buffer.from(value).toString('base64url');
const verifier = base64url(crypto.randomBytes(48));
const challenge = base64url(crypto.createHash('sha256').update(verifier).digest());
const state = base64url(crypto.randomBytes(24));

const authorizeUrl = new URL('/oauth/authorize', issuer);
authorizeUrl.search = new URLSearchParams({
  client_id: clientId,
  redirect_uri: redirectUri,
  response_type: 'code',
  scope: 'marketing:read offline_access',
  state,
  code_challenge: challenge,
  code_challenge_method: 'S256',
}).toString();

async function rpc(token, body) {
  const response = await fetch(mcpUrl, {
    method: 'POST',
    headers: {
      authorization: `Bearer ${token}`,
      'content-type': 'application/json',
      accept: 'application/json',
    },
    body: JSON.stringify(body),
    signal: AbortSignal.timeout(15_000),
  });
  const payload = await response.json();
  if (!response.ok || payload.error) throw new Error(payload.error?.message ?? `MCP gagal (${response.status}).`);
  return payload.result;
}

const callback = new URL(redirectUri);
const server = http.createServer(async (request, response) => {
  const url = new URL(request.url, redirectUri);
  if (url.pathname !== callback.pathname) {
    response.writeHead(404).end('Not found');
    return;
  }

  try {
    if (url.searchParams.get('state') !== state) throw new Error('State OAuth tidak cocok.');
    const code = url.searchParams.get('code');
    if (!code) throw new Error(url.searchParams.get('error_description') ?? 'Authorization code tidak diterima.');

    const tokenResponse = await fetch(new URL('/oauth/token', issuer), {
      method: 'POST',
      headers: { 'content-type': 'application/x-www-form-urlencoded', accept: 'application/json' },
      body: new URLSearchParams({
        grant_type: 'authorization_code',
        client_id: clientId,
        redirect_uri: redirectUri,
        code,
        code_verifier: verifier,
      }),
      signal: AbortSignal.timeout(15_000),
    });
    const tokens = await tokenResponse.json();
    if (!tokenResponse.ok || !tokens.access_token) {
      throw new Error(tokens.message ?? tokens.error_description ?? `Token exchange gagal (${tokenResponse.status}).`);
    }

    await rpc(tokens.access_token, {
      jsonrpc: '2.0', id: 1, method: 'initialize',
      params: { protocolVersion: '2025-06-18', capabilities: {}, clientInfo: { name: 'local-smoke', version: '1' } },
    });
    const tools = await rpc(tokens.access_token, { jsonrpc: '2.0', id: 2, method: 'tools/list' });
    const result = await rpc(tokens.access_token, {
      jsonrpc: '2.0', id: 3, method: 'tools/call',
      params: { name: 'marketing_list_clients', arguments: { page: 1, per_page: 5 } },
    });

    const clientCount = result.structuredContent?.data?.length ?? 0;
    console.log(JSON.stringify({
      oauth: 'ok',
      refresh_token_issued: Boolean(tokens.refresh_token),
      tools: tools.tools?.map((tool) => tool.name) ?? [],
      accessible_client_sample_count: clientCount,
    }, null, 2));
    response.writeHead(200, { 'content-type': 'text/html; charset=utf-8' });
    response.end('<h1>Koneksi OAuth berhasil</h1><p>Anda dapat menutup tab ini dan kembali ke Codex.</p>');
  } catch (error) {
    console.error(`OAuth smoke test gagal: ${error.message}`);
    response.writeHead(500, { 'content-type': 'text/plain; charset=utf-8' });
    response.end(`Pengujian gagal: ${error.message}`);
  } finally {
    server.close();
  }
});

server.listen(Number(callback.port || 80), callback.hostname, () => {
  console.log('Buka URL berikut di browser untuk login dan memberikan izin:');
  console.log(authorizeUrl.toString());
});
