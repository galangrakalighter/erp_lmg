import assert from "node:assert/strict";
import { tools, endpointForTool, validateToolArguments } from "./index.mjs";

const readOnly = tools.filter((tool) => tool.annotations?.readOnlyHint === true);
const mutations = ["marketing_create_content_plan", "marketing_update_content_plan", "marketing_archive_content_plan"];

assert.equal(readOnly.length, 29, "harus ada tepat 29 tool read-only");
for (const tool of readOnly) {
  assert.equal(tool.annotations.destructiveHint, false);
  assert.equal(tool.annotations.openWorldHint, false);
  assert.ok(tool.inputSchema && tool.outputSchema);
}
for (const name of mutations) {
  const tool = tools.find((candidate) => candidate.name === name);
  assert.ok(tool, `${name} harus terdaftar`);
  assert.equal(tool.annotations.readOnlyHint, false);
  assert.equal(tool.annotations.openWorldHint, false);
  assert.ok(tool.inputSchema && tool.outputSchema);
  assert.doesNotThrow(() => endpointForTool(name, { client_id: 1, id: 1 }));
}
assert.ok(tools.some((tool) => tool.name === "marketing_request_content_plan_confirmation"));
assert.deepEqual(validateToolArguments("marketing_create_content_plan", {}), [
  "client_id wajib diisi.",
  "confirmation_token wajib diisi.",
  "judul wajib diisi.",
  "type_produk_id wajib diisi.",
  "strategy_id wajib diisi.",
  "pilar_id wajib diisi.",
  "hooks_id wajib diisi.",
  "body_id wajib diisi.",
  "cta_id wajib diisi.",
  "priority wajib diisi.",
  "menit wajib diisi.",
]);
console.log(`MCP tool inventory OK: ${tools.length} total, ${readOnly.length} read-only, ${mutations.length} content-plan mutations.`);
