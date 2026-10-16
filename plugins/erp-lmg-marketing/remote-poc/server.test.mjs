import assert from 'node:assert/strict';
import { afterEach, test } from 'node:test';
import { endpointForTool, tools } from '../server/index.mjs';
import { createRemoteMcpServer, SERVER_NAME, SERVER_VERSION } from './server.mjs';

const expectedTools = [
  'marketing_request_approval_confirmation', 'marketing_submit_for_approval', 'marketing_approve', 'marketing_reject', 'marketing_request_revision',
  'marketing_request_task_confirmation', 'marketing_create_task', 'marketing_update_task', 'marketing_archive_task', 'marketing_change_task_pic', 'marketing_change_task_deadline', 'marketing_change_task_status', 'marketing_complete_task',
  'marketing_request_asset_metadata_confirmation', 'marketing_create_asset_metadata', 'marketing_update_asset_metadata', 'marketing_archive_asset_metadata',
  'marketing_request_campaign_confirmation', 'marketing_create_campaign', 'marketing_update_campaign', 'marketing_archive_campaign',
  'marketing_request_brand_guideline_confirmation', 'marketing_create_brand_guideline', 'marketing_update_brand_guideline', 'marketing_archive_brand_guideline',
  'marketing_request_content_plan_confirmation', 'marketing_create_content_plan', 'marketing_update_content_plan', 'marketing_archive_content_plan',
  'account_get_client_profile', 'account_list_client_contacts', 'account_get_client_brief', 'account_get_client_needs',
  'account_get_client_project_status', 'account_get_client_milestones',
  'account_get_client_documents_history',
  'account_get_client_summary', 'account_search_client_data',
  'marketing_list_clients', 'marketing_get_division_client_summary', 'marketing_get_data_availability', 'marketing_get_performance_summary',
  'marketing_get_content_plan',
  'marketing_list_product_types', 'marketing_list_strategies', 'marketing_list_hooks', 'marketing_list_bodies', 'marketing_list_ctas',
  'marketing_list_content_plan_statuses',
  'marketing_get_content_mix', 'marketing_get_performance_trend',
  'marketing_get_top_content', 'marketing_get_paid_performance', 'marketing_get_market_research',
  'marketing_get_brand_guidelines', 'marketing_get_content_calendar', 'marketing_get_tasks',
  'marketing_get_approvals', 'marketing_get_assets', 'marketing_list_campaigns',
  'marketing_get_performance_metrics', 'marketing_compare_periods', 'marketing_analyze_channels',
  'marketing_search',
];
for (const entity of ['four_p', 'swot', 'segmentation', 'target_audience', 'positioning']) {
  expectedTools.push(`marketing_request_${entity}_confirmation`, `marketing_create_${entity}`, `marketing_update_${entity}`, `marketing_archive_${entity}`);
}
const expectedEndpoints = {
  marketing_request_approval_confirmation: '/api/oauth-marketing-crud/approvals/confirmation',
  marketing_submit_for_approval: '/api/oauth-marketing-crud/approvals/submit',
  marketing_approve: '/api/oauth-marketing-crud/approvals/approve',
  marketing_reject: '/api/oauth-marketing-crud/approvals/reject',
  marketing_request_revision: '/api/oauth-marketing-crud/approvals/request-revision',
  marketing_request_task_confirmation: '/api/oauth-marketing-crud/tasks/confirmation',
  marketing_create_task: '/api/oauth-marketing-crud/tasks',
  marketing_update_task: '/api/oauth-marketing-crud/tasks/123',
  marketing_archive_task: '/api/oauth-marketing-crud/tasks/123/archive',
  marketing_change_task_pic: '/api/oauth-marketing-crud/tasks/123/change-pic',
  marketing_change_task_deadline: '/api/oauth-marketing-crud/tasks/123/change-deadline',
  marketing_change_task_status: '/api/oauth-marketing-crud/tasks/123/change-status',
  marketing_complete_task: '/api/oauth-marketing-crud/tasks/123/complete',
  marketing_request_asset_metadata_confirmation: '/api/oauth-marketing-crud/assets/confirmation',
  marketing_create_asset_metadata: '/api/oauth-marketing-crud/assets',
  marketing_update_asset_metadata: '/api/oauth-marketing-crud/assets/123',
  marketing_archive_asset_metadata: '/api/oauth-marketing-crud/assets/123/archive',
  marketing_request_campaign_confirmation: '/api/oauth-marketing-crud/campaigns/confirmation',
  marketing_create_campaign: '/api/oauth-marketing-crud/campaigns',
  marketing_update_campaign: '/api/oauth-marketing-crud/campaigns/123',
  marketing_archive_campaign: '/api/oauth-marketing-crud/campaigns/123/archive',
  marketing_request_brand_guideline_confirmation: '/api/oauth-marketing-crud/brand-guidelines/confirmation',
  marketing_create_brand_guideline: '/api/oauth-marketing-crud/brand-guidelines',
  marketing_update_brand_guideline: '/api/oauth-marketing-crud/brand-guidelines/123',
  marketing_archive_brand_guideline: '/api/oauth-marketing-crud/brand-guidelines/123/archive',
  marketing_request_content_plan_confirmation: '/api/oauth-marketing-crud/content-plans/confirmation',
  marketing_create_content_plan: '/api/oauth-marketing-crud/content-plans',
  marketing_update_content_plan: '/api/oauth-marketing-crud/content-plans/123',
  marketing_archive_content_plan: '/api/oauth-marketing-crud/content-plans/123/archive',
  account_get_client_profile: '/api/oauth-marketing-mcp/clients/123/profile',
  account_list_client_contacts: '/api/oauth-marketing-mcp/clients/123/contacts',
  account_get_client_brief: '/api/oauth-marketing-mcp/clients/123/brief',
  account_get_client_needs: '/api/oauth-marketing-mcp/clients/123/needs',
  account_get_client_project_status: '/api/oauth-marketing-mcp/clients/123/project-status',
  account_get_client_milestones: '/api/oauth-marketing-mcp/clients/123/milestones',
  account_get_client_documents_history: '/api/oauth-marketing-mcp/clients/123/documents-history',
  account_get_client_summary: '/api/oauth-marketing-mcp/clients/123/summary',
  account_search_client_data: '/api/oauth-marketing-mcp/clients/123/account-search',
  marketing_list_clients: '/api/oauth-marketing-mcp/clients',
  marketing_get_division_client_summary: '/api/oauth-marketing-mcp/division-client-summary',
  marketing_get_data_availability: '/api/oauth-marketing-mcp/clients/123/data-availability',
  marketing_get_performance_summary: '/api/oauth-marketing-mcp/clients/123/performance-summary',
  marketing_get_content_plan: '/api/oauth-marketing-mcp/clients/123/content-plan',
  marketing_list_product_types: '/api/oauth-marketing-mcp/clients/123/content-plan-masters/product-types',
  marketing_list_strategies: '/api/oauth-marketing-mcp/clients/123/content-plan-masters/strategies',
  marketing_list_hooks: '/api/oauth-marketing-mcp/clients/123/content-plan-masters/hooks',
  marketing_list_bodies: '/api/oauth-marketing-mcp/clients/123/content-plan-masters/bodies',
  marketing_list_ctas: '/api/oauth-marketing-mcp/clients/123/content-plan-masters/ctas',
  marketing_list_content_plan_statuses: '/api/oauth-marketing-mcp/clients/123/content-plan-statuses',
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
for (const entity of ['four_p', 'swot', 'segmentation', 'target_audience', 'positioning']) {
  expectedEndpoints[`marketing_request_${entity}_confirmation`] = `/api/oauth-marketing-crud/market-research/${entity}/confirmation`;
  expectedEndpoints[`marketing_create_${entity}`] = `/api/oauth-marketing-crud/market-research/${entity}`;
  expectedEndpoints[`marketing_update_${entity}`] = `/api/oauth-marketing-crud/market-research/${entity}/123`;
  expectedEndpoints[`marketing_archive_${entity}`] = `/api/oauth-marketing-crud/market-research/${entity}/123/archive`;
}

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
  assert.deepEqual(health, { status: 'ok', service: SERVER_NAME, version: SERVER_VERSION, tools: 84 });
  const result = await rpc(base, { jsonrpc: '2.0', id: 1, method: 'initialize', params: { protocolVersion: '2025-06-18' } });
  assert.equal(result.response.status, 200);
  assert.equal(result.body.result.serverInfo.name, 'erp-lmg-digital-marketing');
  assert.equal(result.body.result.serverInfo.version, '1.6.1');
});

test('tools/list without token returns OAuth challenge in header and MCP metadata', async () => {
  const base = await harness();
  const result = await rpc(base, { jsonrpc: '2.0', id: 2, method: 'tools/list' });
  assert.equal(result.response.status, 401);
  const challenge = result.response.headers.get('www-authenticate');
  assert.match(challenge, /resource_metadata="https:\/\/erp\.test\/\.well-known\/oauth-protected-resource"/);
  assert.deepEqual(result.body.error.data._meta['mcp/www_authenticate'], [challenge]);
});

test('tools/list exposes exactly the expected tools and annotations', async () => {
  const base = await harness();
  const result = await rpc(base, { jsonrpc: '2.0', id: 3, method: 'tools/list' }, 'safe-test-token');
  assert.deepEqual(result.body.result.tools.map((tool) => tool.name), expectedTools);
  for (const tool of result.body.result.tools) {
    assert.equal(typeof tool.annotations.readOnlyHint, 'boolean');
    assert.equal(tool.annotations.openWorldHint, false);
    if (tool.write) assert.equal(tool.annotations.readOnlyHint, false);
    else assert.deepEqual(tool.annotations, { readOnlyHint: true, destructiveHint: false, openWorldHint: false });
    assert.ok(tool.inputSchema);
    assert.ok(tool.outputSchema);
  }
});

test('every tool maps to an OAuth Laravel endpoint', () => {
  for (const tool of tools) {
    const prefix = tool.write ? '/api/oauth-marketing-crud' : '/api/oauth-marketing-mcp';
    const path = endpointForTool(tool.name, { client_id: 123, asset_metadata_id: 123, task_id: 123, campaign_id: 123, content_plan_id: 123, brand_guideline_id: 123, record_id: 123 }, prefix);
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

test('content plan MCP mutations use OAuth CRUD endpoints and forward bearer token', async () => {
  const calls = [];
  const token = 'content-plan-oauth-token';
  const base = await harness(async (url, options) => {
    calls.push({ url: String(url), method: options.method, authorization: options.headers.authorization, body: options.body });
    return jsonResponse(200, { data: { id: 8 }, meta: { operation: options.method } });
  });
  const args = { client_id: 1, confirmation_token: 'x'.repeat(64), judul: 'Plan', type_produk_id: 1, strategy_id: 1, pilar_id: 1, hooks_id: 1, body_id: 1, cta_id: 1, status_id: 1, priority: 1, menit: 30 };
  const result = await rpc(base, { jsonrpc: '2.0', id: 11, method: 'tools/call', params: { name: 'marketing_create_content_plan', arguments: args } }, token);
  assert.equal(result.body.result.structuredContent.data.id, 8);
  assert.equal(calls[0].url, 'https://erp.test/api/oauth-marketing-crud/content-plans');
  assert.equal(calls[0].method, 'POST');
  assert.equal(calls[0].authorization, `Bearer ${token}`);
  assert.ok(!JSON.stringify(result.body).includes(token));
});

test('campaign MCP mutations use canonical OAuth CRUD endpoints and never expose bearer token', async () => {
  const calls = [];
  const token = 'campaign-oauth-secret';
  const base = await harness(async (url, options) => {
    calls.push({ url: String(url), method: options.method, authorization: options.headers.authorization, body: JSON.parse(options.body) });
    return jsonResponse(201, { data: { id: 21, name: 'Canonical Launch' }, meta: { operation: 'create' } });
  });
  const args = { client_id: 7, confirmation_token: 'c'.repeat(64), name: 'Canonical Launch', status: 'planned', source: 'paid', start_date: '2026-04-01', end_date: '2026-06-30' };
  const result = await rpc(base, { jsonrpc: '2.0', id: 12, method: 'tools/call', params: { name: 'marketing_create_campaign', arguments: args } }, token);
  assert.equal(result.body.result.structuredContent.data.id, 21);
  assert.equal(calls[0].url, 'https://erp.test/api/oauth-marketing-crud/campaigns');
  assert.equal(calls[0].method, 'POST');
  assert.equal(calls[0].authorization, `Bearer ${token}`);
  assert.deepEqual(calls[0].body, args);
  assert.ok(!JSON.stringify(result.body).includes(token));
});

test('approval MCP events use append-only OAuth endpoints and forward no token in the response', async () => {
  const calls = [];
  const token = 'approval-oauth-secret';
  const base = await harness(async (url, options) => {
    calls.push({ url: String(url), method: options.method, authorization: options.headers.authorization, body: JSON.parse(options.body) });
    return jsonResponse(201, { data: { event_id: 31, action: 'submit_for_approval', status: 'pending' }, meta: { operation: 'submit_for_approval' } });
  });
  const args = { client_id: 7, record_type: 'campaign', record_id: 21, note: 'Ready', confirmation_token: 'a'.repeat(64) };
  const result = await rpc(base, { jsonrpc: '2.0', id: 13, method: 'tools/call', params: { name: 'marketing_submit_for_approval', arguments: args } }, token);
  assert.equal(result.body.result.structuredContent.data.event_id, 31);
  assert.equal(calls[0].url, 'https://erp.test/api/oauth-marketing-crud/approvals/submit');
  assert.equal(calls[0].method, 'POST');
  assert.equal(calls[0].authorization, `Bearer ${token}`);
  assert.deepEqual(calls[0].body, args);
  assert.ok(!JSON.stringify(result.body).includes(token));
});

test('task MCP mutations use OAuth CRUD endpoints and keep bearer token out of the response', async () => {
  const calls = [];
  const token = 'task-oauth-secret';
  const base = await harness(async (url, options) => {
    calls.push({ url: String(url), method: options.method, authorization: options.headers.authorization, body: JSON.parse(options.body) });
    return jsonResponse(200, { data: { id: 44, status: 1 }, meta: { operation: 'change_status' } });
  });
  const args = { task_id: 44, client_id: 7, status: 1, confirmation_token: 't'.repeat(64) };
  const result = await rpc(base, { jsonrpc: '2.0', id: 14, method: 'tools/call', params: { name: 'marketing_change_task_status', arguments: args } }, token);
  assert.equal(result.body.result.structuredContent.data.id, 44);
  assert.equal(calls[0].url, 'https://erp.test/api/oauth-marketing-crud/tasks/44/change-status');
  assert.equal(calls[0].method, 'POST');
  assert.equal(calls[0].authorization, `Bearer ${token}`);
  assert.deepEqual(calls[0].body, args);
  assert.ok(!JSON.stringify(result.body).includes(token));
});

test('asset metadata MCP mutation never sends binary fields or exposes bearer token', async () => {
  const calls = [];
  const token = 'asset-oauth-secret';
  const base = await harness(async (url, options) => {
    calls.push({ url: String(url), method: options.method, authorization: options.headers.authorization, body: JSON.parse(options.body) });
    return jsonResponse(201, { data: { id: 52, name: 'Key Visual', current_version: 1 }, meta: { operation: 'create' } });
  });
  const args = { client_id: 7, name: 'Key Visual', asset_type: 'asset', mime_type: 'image/png', confirmation_token: 'm'.repeat(64) };
  const result = await rpc(base, { jsonrpc: '2.0', id: 15, method: 'tools/call', params: { name: 'marketing_create_asset_metadata', arguments: args } }, token);
  assert.equal(result.body.result.structuredContent.data.id, 52);
  assert.equal(calls[0].url, 'https://erp.test/api/oauth-marketing-crud/assets');
  assert.equal(calls[0].method, 'POST');
  assert.deepEqual(calls[0].body, args);
  assert.ok(!('location' in calls[0].body));
  assert.ok(!('binary' in calls[0].body));
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
