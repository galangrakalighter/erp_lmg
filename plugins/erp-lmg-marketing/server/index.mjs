import readline from "node:readline";

const apiUrl = (process.env.ERP_LMG_API_URL || "http://127.0.0.1:8000").replace(/\/$/, "");
const token = process.env.ERP_LMG_SANCTUM_TOKEN || "";

const pagination = {
  page: { type: "integer", minimum: 1, default: 1 },
  per_page: { type: "integer", minimum: 1, maximum: 100, default: 25 },
};
const period = {
  start_date: { type: "string", format: "date", description: "YYYY-MM-DD; defaults to 30 days before end_date." },
  end_date: { type: "string", format: "date", description: "YYYY-MM-DD; inclusive. Maximum period is 366 days." },
};
const platforms = ["facebook", "instagram", "tiktok", "threads", "google", "youtube"];

const tools = [
  {
    name: "marketing_list_clients",
    description: "List only ERP LMG marketing clients authorized for the authenticated Sanctum user.",
    inputSchema: {
      type: "object",
      properties: {
        search: { type: "string", maxLength: 100 },
        status: { type: "integer", enum: [0, 1] },
        ...pagination,
      },
      additionalProperties: false,
    },
  },
  {
    name: "marketing_get_data_availability",
    description: "Show available content-plan and stored performance datasets for one authorized client.",
    inputSchema: {
      type: "object",
      required: ["client_id"],
      properties: {
        client_id: { type: "integer", minimum: 1 },
        platform: { type: "string", enum: platforms },
        ...period,
        ...pagination,
      },
      additionalProperties: false,
    },
  },
  {
    name: "marketing_get_performance_summary",
    description: "Return the latest stored metrics and change from the first snapshot in the selected period; never fetches social APIs or writes snapshots.",
    inputSchema: {
      type: "object",
      required: ["client_id"],
      properties: {
        client_id: { type: "integer", minimum: 1 },
        platform: { type: "string", enum: platforms },
        source: { type: "string", enum: ["organic", "paid"] },
        ...period,
        ...pagination,
      },
      additionalProperties: false,
    },
  },
];

async function callApi(path, args) {
  if (!token) throw new Error("ERP_LMG_SANCTUM_TOKEN belum dikonfigurasi.");
  const query = new URLSearchParams();
  for (const [key, value] of Object.entries(args)) {
    if (key !== "client_id" && value !== undefined && value !== null && value !== "") query.set(key, String(value));
  }
  const response = await fetch(`${apiUrl}${path}${query.size ? `?${query}` : ""}`, {
    headers: { Authorization: `Bearer ${token}`, Accept: "application/json" },
    signal: AbortSignal.timeout(15000),
  });
  const body = await response.json().catch(() => ({}));
  if (!response.ok) {
    const error = new Error(body.message || `ERP LMG API merespons HTTP ${response.status}.`);
    error.status = response.status;
    throw error;
  }
  return body;
}

async function invoke(name, args = {}) {
  if (name === "marketing_list_clients") return callApi("/api/marketing-mcp/clients", args);
  if (name === "marketing_get_data_availability") return callApi(`/api/marketing-mcp/clients/${Number(args.client_id)}/data-availability`, args);
  if (name === "marketing_get_performance_summary") return callApi(`/api/marketing-mcp/clients/${Number(args.client_id)}/performance-summary`, args);
  throw new Error(`Tool tidak dikenal: ${name}`);
}

async function handle(message) {
  const { id, method, params = {} } = message;
  if (method === "notifications/initialized") return null;
  if (method === "initialize") {
    return { jsonrpc: "2.0", id, result: { protocolVersion: params.protocolVersion || "2025-06-18", capabilities: { tools: { listChanged: false } }, serverInfo: { name: "erp-lmg-marketing", version: "0.1.0" } } };
  }
  if (method === "tools/list") return { jsonrpc: "2.0", id, result: { tools } };
  if (method === "tools/call") {
    try {
      const result = await invoke(params.name, params.arguments || {});
      return { jsonrpc: "2.0", id, result: { content: [{ type: "text", text: JSON.stringify(result) }], structuredContent: result } };
    } catch (error) {
      return { jsonrpc: "2.0", id, result: { isError: true, content: [{ type: "text", text: error.message }] } };
    }
  }
  return { jsonrpc: "2.0", id, error: { code: -32601, message: `Method tidak didukung: ${method}` } };
}

const input = readline.createInterface({ input: process.stdin, crlfDelay: Infinity });
input.on("line", async (line) => {
  if (!line.trim()) return;
  try {
    const response = await handle(JSON.parse(line));
    if (response) process.stdout.write(`${JSON.stringify(response)}\n`);
  } catch (error) {
    process.stdout.write(`${JSON.stringify({ jsonrpc: "2.0", id: null, error: { code: -32700, message: error.message } })}\n`);
  }
});
