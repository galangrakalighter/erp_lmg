import assert from 'node:assert/strict';
import { afterEach, test } from 'node:test';
import { endpointForTool, tools } from '../server/index.mjs';
import { createRemoteMcpServer, SERVER_NAME, SERVER_VERSION } from './server.mjs';

const expectedTools = [
  'account_get_client_profile', 'account_list_client_contacts', 'account_get_client_brief', 'account_get_client_needs',
  'account_get_client_project_status', 'account_get_client_milestones',
  'account_get_client_documents_history',
  'account_get_client_summary', 'account_search_client_data',
  'marketing_list_clients', 'marketing_get_division_client_summary', 'marketing_get_data_availability', 'marketing_get_performance_summary',
  'marketing_get_content_plan', 'marketing_get_content_mix', 'marketing_get_performance_trend',
  'marketing_get_top_content', 'marketing_get_paid_performance', 'marketing_get_market_research',
  'marketing_get_brand_guidelines', 'marketing_get_content_calendar', 'marketing_get_tasks',
  'marketing_get_approvals', 'marketing_get_assets', 'marketing_list_campaigns',
  'marketing_get_performance_metrics', 'marketing_compare_periods', 'marketing_analyze_channels',
  'marketing_search',
];
const expectedEndpoints = {
  account_get_client_profile: '/api/oauth-marketing-mcp/clients/123/profile',
  account_list_client_contacts: '/api/oauth-marketing-mcp/clients/123/contacts',
  account_get_client_brief: '/api/oauth-marketing-mcp/clients/123/brief',
  account_get_client_needs: '/api/oauth-marketing-mcp/clients/123/needs',
  account_get_client_project_status: '/api/oauth-marketing-mcp/clients/123/project-status',
  account_get_client_milestones: '/api/oauth-marketing-mcp/clients/123/milestones',
  account_get_client_documents_history: '/api/oauth-marketing-mcp/clients/123/documents-history',
  account_get_client_summary: '/api/oauth-marketing-mcp/clients/123/summary',
  account_search_client_data: '/api/oauth-marketing-mcp/clients/123/account-search?q=launch',
  marketing_list_clients: '/api/oauth-marketing-mcp/clients',
  marketing_get_division_client_summary: '/api/oauth-marketing-mcp/division-client-summary',
  marketing_get_data_availability: '/api/oauth-marketing-mcp/clients/123/data-availability',
  marketing_get_performance_summary: '/api/oauth-marketing-mcp/clients/123/performance-summary',
  marketing_get_content_plan: '/api/oauth-marketing-mcp/clients/123/content-plan',
  marketing_get_content_mix: '/api/oauth-marketing-mcp/clients/123/content-mix',
  marketing_get_performance_trend: '/api/oauth-marketing-mcp/clients/123/performance-trend',
  marketing_get_top_content: '/api/oauth-marketing-mcp/clients/123/top-content',
  marketing_get_paid_performance: '/api/oauth-marketing-mcp/clients/123/paid-performance',
  marketing_get_market_research: '/api/oauth-marketing-mcp/clients/123/market-research',
  marketing_get_brand_guidelines: '/api/oauth-marketing-mcp/clients/123/brand-guidelines',
  marketing_get_content_calendar: '/api/oauth-marketing-mcp/clients/123/content-calendar',
  marketing_get_tasks: '/api/oauth-marketing-mcp/clients/123/tasks',
  marketing_get_approvals: '/api/oauth-marketing-mcp/clients/123/approvals',
  marketing_get_assets: '/api/oauth-marketing-mcp/clients/123/assets',
  marketing_list_campaigns: '/api/oauth-marketing-mcp/clients/123/campaigns',
  marketing_get_performance_metrics: '/api/oauth-marketing-mcp/clients/123/performance-metrics',
  marketing_compare_periods: '/api/oauth-marketing-mcp/clients/123/period-comparison',
  marketing_analyze_channels: '/api/oauth-marketing-mcp/clients/123/channel-analysis',
  marketing_search: '/api/oauth-marketing-mcp/clients/123/search',
};

const running = [];
afterEach(async () => {
  await Promise.all(running.splice(0).map((server) => new Promise((resolve) => server.close(resolve))));
});

async function harness(fetchImpl = async () => jsonResponse(200, { data: [], pagination: null, meta: {} })) {
  const server = createRemoteMcpServer({
    host: '127.0.0.1', apiBase: 'https://erp.test', oauthIssuer: 'https://erp.test', fetchImpl, timeoutMs: 25,
  });
  await new Promise((resolve) => server.listen(0, '127.0.0.1', resolve));
  running.push(server);
  const address = server.address();
  return `http://127.0.0.1:${address.port}`;
}

function jsonResponse(status, body, contentType = 'application/json') {
  return new Response(typeof body === 'string' ? body : JSON.stringify(body), { status, headers: { 'content-type': contentType } });
}

async function rpc(base, body, token) {
  const response = await fetch(`${base}/mcp`, {
    method: 'POST', headers: { 'content-type': 'application/json', ...(token ? { authorization: `Bearer ${token}` } : {}) },
    body: JSON.stringify(body),
  });
  return { response, body: await response.json() };
}

test('initialize succeeds with production identity and health remains available', async () => {
  const base = await harness();
  const health = await fetch(`${base}/health`).then((response) => response.json());
  assert.deepEqual(health, { status: 'ok', service: SERVER_NAME, version: SERVER_VERSION, tools: 20 });
  const result = await rpc(base, { jsonrpc: '2.0', id: 1, method: 'initialize', params: { protocolVersion: '2025-06-18' } });
  assert.equal(result.response.status, 200);
  assert.equal(result.body.result.serverInfo.name, 'erp-lmg-digital-marketing');
  assert.equal(result.body.result.serverInfo.version, '1.0.0');
});

test('tools/list without token returns OAuth challenge in header and MCP metadata', async () => {
  const base = await harness();
  const result = await rpc(base, { jsonrpc: '2.0', id: 2, method: 'tools/list' });
  assert.equal(result.response.status, 401);
  const challenge = result.response.headers.get('www-authenticate');
  assert.match(challenge, /resource_metadata="https:\/\/erp\.test\/\.well-known\/oauth-protected-resource"/);
  assert.deepEqual(result.body.error.data._meta['mcp/www_authenticate'], [challenge]);
});

test('tools/list exposes exactly the expected 20 read-only tools', async () => {
  const base = await harness();
  const result = await rpc(base, { jsonrpc: '2.0', id: 3, method: 'tools/list' }, 'safe-test-token');
  assert.deepEqual(result.body.result.tools.map((tool) => tool.name), expectedTools);
  for (const tool of result.body.result.tools) {
    assert.deepEqual(tool.annotations, { readOnlyHint: true, destructiveHint: false, openWorldHint: false });
    assert.ok(tool.inputSchema);
    assert.ok(tool.outputSchema);
  }
});

test('every tool maps to an OAuth Laravel endpoint', () => {
  for (const tool of tools) {
    const path = endpointForTool(tool.name, { client_id: 123 }, '/api/oauth-marketing-mcp');
    assert.equal(path, expectedEndpoints[tool.name]);
    assert.ok(!path.startsWith('/api/marketing-mcp'));
  }
});

test('legacy marketing_list_clients forwards bearer token without returning it', async () => {
  const calls = [];
  const token = 'oauth-super-secret-token';
  const base = await harness(async (url, options) => {
    calls.push({ url: String(url), authorization: options.headers.authorization, method: options.method });
    return jsonResponse(200, { data: [{ id: 7, nama: 'Client' }], pagination: { page: 1, per_page: 25, total: 1, last_page: 1 }, meta: {} });
  });
  const result = await rpc(base, { jsonrpc: '2.0', id: 4, method: 'tools/call', params: { name: 'marketing_list_clients', arguments: { page: 1 } } }, token);
  assert.deepEqual(calls, [{ url: 'https://erp.test/api/oauth-marketing-mcp/clients?page=1', authorization: `Bearer ${token}`, method: 'GET' }]);
  assert.equal(result.body.result.structuredContent.data[0].id, 7);
  assert.ok(!JSON.stringify(result.body).includes(token));
});

test('unknown tool and invalid input are rejected before Laravel', async () => {
  let calls = 0;
  const base = await harness(async () => { calls++; return jsonResponse(200, {}); });
  const unknown = await rpc(base, { jsonrpc: '2.0', id: 5, method: 'tools/call', params: { name: 'unknown', arguments: {} } }, 'token');
  assert.equal(unknown.body.error.code, -32602);
  const invalid = await rpc(base, { jsonrpc: '2.0', id: 6, method: 'tools/call', params: { name: 'marketing_search', arguments: { client_id: 1, q: 'x', start_date: '2026-12-31', end_date: '2026-01-01', per_page: 101 } } }, 'token');
  assert.equal(invalid.body.error.code, -32602);
  assert.ok(invalid.body.error.data.errors.length >= 3);
  assert.equal(calls, 0);
});

for (const status of [401, 403]) {
  test(`Laravel ${status} triggers OAuth challenge without leaking its body`, async () => {
    const base = await harness(async () => jsonResponse(status, { message: 'secret upstream authorization detail' }));
    const result = await rpc(base, { jsonrpc: '2.0', id: status, method: 'tools/call', params: { name: 'marketing_list_clients', arguments: {} } }, 'expired-token');
    assert.equal(result.response.status, 401);
    assert.ok(result.response.headers.get('www-authenticate'));
    assert.ok(!JSON.stringify(result.body).includes('secret upstream'));
    assert.ok(!JSON.stringify(result.body).includes('expired-token'));
  });
}

for (const [status, expected] of [[404, 'tidak ditemukan'], [422, 'menolak parameter'], [500, 'gagal memproses']]) {
  test(`Laravel ${status} is returned as a safe MCP tool error`, async () => {
    const base = await harness(async () => jsonResponse(status, { message: 'stack trace and database secret' }));
    const result = await rpc(base, { jsonrpc: '2.0', id: status, method: 'tools/call', params: { name: 'marketing_list_clients', arguments: {} } }, 'token');
    assert.equal(result.response.status, 200);
    assert.equal(result.body.result.isError, true);
    assert.match(result.body.result.content[0].text, new RegExp(expected));
    assert.ok(!JSON.stringify(result.body).includes('stack trace'));
  });
}

test('non-JSON and timeout responses are handled safely', async () => {
  let mode = 'html';
  const base = await harness(async () => {
    if (mode === 'html') return jsonResponse(502, '<html>proxy detail</html>', 'text/html');
    throw new DOMException('contains infrastructure secret', 'TimeoutError');
  });
  const request = { jsonrpc: '2.0', id: 10, method: 'tools/call', params: { name: 'marketing_list_clients', arguments: {} } };
  const nonJson = await rpc(base, request, 'token');
  assert.match(nonJson.body.result.content[0].text, /tidak valid/);
  assert.ok(!JSON.stringify(nonJson.body).includes('proxy detail'));
  mode = 'timeout';
  const timeout = await rpc(base, request, 'token');
  assert.match(timeout.body.result.content[0].text, /batas waktu/);
  assert.ok(!JSON.stringify(timeout.body).includes('infrastructure secret'));
});
