import readline from "node:readline";
import { pathToFileURL } from "node:url";

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
const requiredPeriod = {
  start_date: { type: "string", format: "date", description: "YYYY-MM-DD; required." },
  end_date: { type: "string", format: "date", description: "YYYY-MM-DD; required, inclusive, maximum period is 366 days." },
};
const platforms = ["facebook", "instagram", "tiktok", "threads", "google", "youtube"];
const metrics = ["posts", "reach", "impressions", "engagement", "likes", "comments", "shares", "clicks", "spend", "views", "followers", "followers_gained", "saves", "reactions"];
const topContentMetrics = ["reach", "impressions", "engagement", "likes", "comments", "shares", "clicks", "spend"];
const paginationSchema = {
  oneOf: [
    { type: "null" },
    {
      type: "object",
      required: ["page", "per_page", "total", "last_page"],
      properties: {
        page: { type: "integer" }, per_page: { type: "integer" },
        total: { type: "integer" }, last_page: { type: "integer" },
      },
      additionalProperties: true,
    },
  ],
};
const responseSchema = {
  type: "object",
  required: ["data", "pagination", "meta"],
  properties: {
    data: {},
    pagination: paginationSchema,
    meta: { type: "object", additionalProperties: true },
  },
  additionalProperties: true,
};
const clientResponseSchema = {
  ...responseSchema,
  required: ["client_id", "data", "pagination", "meta"],
  properties: {
    client_id: { type: "integer" },
    ...responseSchema.properties,
  },
};

export const tools = [
  {
    name: "account_get_client_profile",
    description: "Read the authorized client's safe profile, status, division and project summary.",
    inputSchema: { type: "object", required: ["client_id"], properties: { client_id: { type: "integer", minimum: 1 } }, additionalProperties: false },
  },
  {
    name: "account_list_client_contacts",
    description: "List authorized internal PIC contacts using safe identity fields; private email and phone are not returned by default.",
    inputSchema: { type: "object", required: ["client_id"], properties: { client_id: { type: "integer", minimum: 1 } }, additionalProperties: false },
  },
  {
    name: "account_get_client_brief",
    description: "Read the authorized client's stored brief and project brief metadata.",
    inputSchema: { type: "object", required: ["client_id"], properties: { client_id: { type: "integer", minimum: 1 } }, additionalProperties: false },
  },
  {
    name: "account_get_client_needs",
    description: "Read the authorized client's recorded needs, target audience and basic project targets without inventing missing data.",
    inputSchema: { type: "object", required: ["client_id"], properties: { client_id: { type: "integer", minimum: 1 } }, additionalProperties: false },
  },
  {
    name: "account_get_client_project_status",
    description: "Read actual client project activity, timeline, task status, PIC, deadlines, overdue work and calculated progress from stored tasks.",
    inputSchema: { type: "object", required: ["client_id"], properties: { client_id: { type: "integer", minimum: 1 } }, additionalProperties: false },
  },
  {
    name: "account_get_client_milestones",
    description: "Read actual client project milestones and KPI deadlines without estimating missing progress.",
    inputSchema: { type: "object", required: ["client_id"], properties: { client_id: { type: "integer", minimum: 1 } }, additionalProperties: false },
  },
  {
    name: "account_get_client_documents_history",
    description: "Read authorized client document metadata, approval/revision history, and permission-gated contract status without file contents, paths, financial values or personal data.",
    inputSchema: { type: "object", required: ["client_id"], properties: { client_id: { type: "integer", minimum: 1 } }, additionalProperties: false },
  },
  {
    name: "account_get_client_summary",
    description: "Summarize actual client project progress, overdue work, documents, approvals and known communication/contract availability.",
    inputSchema: { type: "object", required: ["client_id"], properties: { client_id: { type: "integer", minimum: 1 } }, additionalProperties: false },
  },
  {
    name: "account_search_client_data",
    description: "Search authorized client briefs, tasks and document metadata without secrets, file contents, financial values or private contacts.",
    inputSchema: { type: "object", required: ["client_id", "q"], properties: { client_id: { type: "integer", minimum: 1 }, q: { type: "string", minLength: 2, maxLength: 100 } }, additionalProperties: false },
  },
  {
    name: "marketing_list_clients",
    description: "List only ERP LMG marketing clients accessible to the authenticated user. Use this for 'clients I can access', not for division-wide totals.",
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
    name: "marketing_get_division_client_summary",
    description: "Return the Digital Marketing division client total for admin or division-wide leaders; ordinary staff receive an explicit restricted scope and only their accessible-client count. Use this for 'Digital Marketing division clients'.",
    inputSchema: { type: "object", properties: {}, additionalProperties: false },
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
  {
    name: "marketing_get_content_plan",
    description: "List canonical, non-mirrored content plans for one authorized client.",
    inputSchema: {
      type: "object",
      required: ["client_id", "start_date", "end_date"],
      properties: {
        client_id: { type: "integer", minimum: 1 }, platform: { type: "string", enum: platforms },
        source: { type: "string", enum: ["organic", "paid"] }, status: { type: "integer", minimum: 1 },
        pic_id: { type: "integer", minimum: 1 }, ...requiredPeriod, ...pagination,
      },
      additionalProperties: false,
    },
  },
  {
    name: "marketing_get_content_mix",
    description: "Aggregate canonical, non-mirrored content-plan composition for one authorized client.",
    inputSchema: {
      type: "object",
      required: ["client_id", "start_date", "end_date"],
      properties: {
        client_id: { type: "integer", minimum: 1 }, platform: { type: "string", enum: platforms },
        source: { type: "string", enum: ["organic", "paid"] }, ...requiredPeriod,
      },
      additionalProperties: false,
    },
  },
  {
    name: "marketing_get_performance_trend",
    description: "Return non-interpolated trend points from stored ERP snapshots for an authorized client.",
    inputSchema: {
      type: "object", required: ["client_id", "start_date", "end_date"],
      properties: {
        client_id: { type: "integer", minimum: 1 }, platform: { type: "string", enum: platforms },
        source: { type: "string", enum: ["organic", "paid"] }, granularity: { type: "string", enum: ["daily", "weekly", "monthly"], default: "daily" },
        metrics: { type: "array", maxItems: 14, uniqueItems: true, items: { type: "string", enum: metrics } }, ...requiredPeriod,
      }, additionalProperties: false,
    },
  },
  {
    name: "marketing_get_top_content",
    description: "Rank safely identified stored snapshot items by one allowlisted metric.",
    inputSchema: {
      type: "object", required: ["client_id", "start_date", "end_date", "metric"],
      properties: {
        client_id: { type: "integer", minimum: 1 }, platform: { type: "string", enum: platforms }, source: { type: "string", enum: ["organic", "paid"] },
        metric: { type: "string", enum: topContentMetrics }, direction: { type: "string", enum: ["best", "worst"], default: "best" },
        limit: { type: "integer", minimum: 1, maximum: 50, default: 10 }, ...requiredPeriod,
      }, additionalProperties: false,
    },
  },
  {
    name: "marketing_get_paid_performance",
    description: "Summarize only stored paid snapshot items without calling advertising platforms or inferring attribution.",
    inputSchema: {
      type: "object", required: ["client_id", "start_date", "end_date"],
      properties: {
        client_id: { type: "integer", minimum: 1 }, platform: { type: "string", enum: platforms }, campaign_id: { type: "string", maxLength: 100 },
        metric: { type: "string", enum: ["spend", "impressions", "reach", "clicks", "engagement", "leads", "conversions"], default: "spend" },
        ...requiredPeriod, ...pagination,
      }, additionalProperties: false,
    },
  },
  {
    name: "marketing_get_market_research",
    description: "Read market research, segmentation, target audience, positioning, and available competitor status for one authorized client.",
    inputSchema: {
      type: "object", required: ["client_id"],
      properties: { client_id: { type: "integer", minimum: 1 }, platform: { type: "string", enum: platforms }, ...pagination },
      additionalProperties: false,
    },
  },
  {
    name: "marketing_get_brand_guidelines",
    description: "Read branding, tone of voice, hashtag, and content-pillar records for one authorized client.",
    inputSchema: {
      type: "object", required: ["client_id"],
      properties: { client_id: { type: "integer", minimum: 1 }, platform: { type: "string", enum: platforms }, ...pagination },
      additionalProperties: false,
    },
  },
  {
    name: "marketing_get_content_calendar",
    description: "Read content calendar metadata, copy notes, hashtags, platform, PIC, and deadlines without exposing credentials.",
    inputSchema: {
      type: "object", required: ["client_id", "start_date", "end_date"],
      properties: { client_id: { type: "integer", minimum: 1 }, platform: { type: "string", enum: platforms }, source: { type: "string", enum: ["organic", "paid"] }, ...requiredPeriod, ...pagination },
      additionalProperties: false,
    },
  },
  {
    name: "marketing_get_tasks",
    description: "Read Digital Marketing task, PIC, status, estimate, and deadline data for one authorized client.",
    inputSchema: {
      type: "object", required: ["client_id", "start_date", "end_date"],
      properties: { client_id: { type: "integer", minimum: 1 }, pic_id: { type: "integer", minimum: 1 }, status: { type: "integer", minimum: 0 }, ...requiredPeriod, ...pagination },
      additionalProperties: false,
    },
  },
  {
    name: "marketing_get_approvals",
    description: "Read the latest available approval and revision metadata for one authorized client.",
    inputSchema: {
      type: "object", required: ["client_id", "start_date", "end_date"],
      properties: { client_id: { type: "integer", minimum: 1 }, ...requiredPeriod, ...pagination },
      additionalProperties: false,
    },
  },
  {
    name: "marketing_get_assets",
    description: "Read safe document and asset metadata; never returns storage location, binary content, or uploader email.",
    inputSchema: {
      type: "object", required: ["client_id"],
      properties: { client_id: { type: "integer", minimum: 1 }, division_id: { type: "integer", minimum: 1 }, ...pagination },
      additionalProperties: false,
    },
  },
  {
    name: "marketing_list_campaigns",
    description: "List organic and paid campaign/content snapshot items, including budget and target only when explicitly stored.",
    inputSchema: {
      type: "object", required: ["client_id", "start_date", "end_date"],
      properties: { client_id: { type: "integer", minimum: 1 }, platform: { type: "string", enum: platforms }, source: { type: "string", enum: ["organic", "paid"] }, campaign_id: { type: "string", maxLength: 100 }, ...requiredPeriod, ...pagination },
      additionalProperties: false,
    },
  },
  {
    name: "marketing_get_performance_metrics",
    description: "Read stored reach, impressions, engagement, clicks, spend, leads, conversions, CTR, CPC, and CPM by platform and source.",
    inputSchema: {
      type: "object", required: ["client_id", "start_date", "end_date"],
      properties: { client_id: { type: "integer", minimum: 1 }, platform: { type: "string", enum: platforms }, source: { type: "string", enum: ["organic", "paid"] }, ...requiredPeriod },
      additionalProperties: false,
    },
  },
  {
    name: "marketing_compare_periods",
    description: "Compare two stored performance periods without treating missing values as zero; defaults to the immediately preceding equal-length period.",
    inputSchema: {
      type: "object", required: ["client_id", "start_date", "end_date"],
      properties: {
        client_id: { type: "integer", minimum: 1 }, platform: { type: "string", enum: platforms }, source: { type: "string", enum: ["organic", "paid"] },
        compare_start_date: { type: "string", format: "date" }, compare_end_date: { type: "string", format: "date" }, ...requiredPeriod,
      }, additionalProperties: false,
    },
  },
  {
    name: "marketing_analyze_channels",
    description: "Analyze ATL, TTL, BTL, or other classifications only when those labels are explicitly stored in snapshot items.",
    inputSchema: {
      type: "object", required: ["client_id", "start_date", "end_date"],
      properties: { client_id: { type: "integer", minimum: 1 }, platform: { type: "string", enum: platforms }, source: { type: "string", enum: ["organic", "paid"] }, ...requiredPeriod },
      additionalProperties: false,
    },
  },
  {
    name: "marketing_search",
    description: "Search authorized market research, branding, content, tasks, approvals, asset metadata, and campaign snapshot data for one client.",
    inputSchema: {
      type: "object", required: ["client_id", "q", "start_date", "end_date"],
      properties: {
        client_id: { type: "integer", minimum: 1 }, q: { type: "string", minLength: 2, maxLength: 100 },
        platform: { type: "string", enum: platforms }, source: { type: "string", enum: ["organic", "paid"] }, ...requiredPeriod, ...pagination,
      }, additionalProperties: false,
    },
  },
];

for (const tool of tools) {
  tool.outputSchema = ["marketing_list_clients", "marketing_get_division_client_summary", "account_get_client_profile", "account_list_client_contacts", "account_get_client_brief", "account_get_client_needs", "account_get_client_project_status", "account_get_client_milestones", "account_get_client_documents_history", "account_get_client_summary", "account_search_client_data"].includes(tool.name) ? responseSchema : clientResponseSchema;
  tool.annotations = { readOnlyHint: true, destructiveHint: false, openWorldHint: false };
}

const toolPaths = {
  account_get_client_profile: "/clients/{client_id}/profile",
  account_list_client_contacts: "/clients/{client_id}/contacts",
  account_get_client_brief: "/clients/{client_id}/brief",
  account_get_client_needs: "/clients/{client_id}/needs",
  account_get_client_project_status: "/clients/{client_id}/project-status",
  account_get_client_milestones: "/clients/{client_id}/milestones",
  account_get_client_documents_history: "/clients/{client_id}/documents-history",
  account_get_client_summary: "/clients/{client_id}/summary",
  account_search_client_data: "/clients/{client_id}/account-search",
  marketing_list_clients: "/clients",
  marketing_get_division_client_summary: "/division-client-summary",
  marketing_get_data_availability: "/clients/{client_id}/data-availability",
  marketing_get_performance_summary: "/clients/{client_id}/performance-summary",
  marketing_get_content_plan: "/clients/{client_id}/content-plan",
  marketing_get_content_mix: "/clients/{client_id}/content-mix",
  marketing_get_performance_trend: "/clients/{client_id}/performance-trend",
  marketing_get_top_content: "/clients/{client_id}/top-content",
  marketing_get_paid_performance: "/clients/{client_id}/paid-performance",
  marketing_get_market_research: "/clients/{client_id}/market-research",
  marketing_get_brand_guidelines: "/clients/{client_id}/brand-guidelines",
  marketing_get_content_calendar: "/clients/{client_id}/content-calendar",
  marketing_get_tasks: "/clients/{client_id}/tasks",
  marketing_get_approvals: "/clients/{client_id}/approvals",
  marketing_get_assets: "/clients/{client_id}/assets",
  marketing_list_campaigns: "/clients/{client_id}/campaigns",
  marketing_get_performance_metrics: "/clients/{client_id}/performance-metrics",
  marketing_compare_periods: "/clients/{client_id}/period-comparison",
  marketing_analyze_channels: "/clients/{client_id}/channel-analysis",
  marketing_search: "/clients/{client_id}/search",
};

export function endpointForTool(name, args = {}, prefix = "/api/marketing-mcp") {
  const template = toolPaths[name];
  if (!template) throw new Error(`Tool tidak dikenal: ${name}`);
  return `${prefix}${template.replace("{client_id}", String(args.client_id ?? ""))}`;
}

export function validateToolArguments(name, args = {}) {
  const tool = tools.find((candidate) => candidate.name === name);
  if (!tool) return [`Tool tidak dikenal: ${name}`];
  if (!args || typeof args !== "object" || Array.isArray(args)) return ["Arguments harus berupa object."];
  const errors = [];
  const schema = tool.inputSchema;
  for (const required of schema.required ?? []) {
    if (args[required] === undefined || args[required] === null || args[required] === "") errors.push(`${required} wajib diisi.`);
  }
  if (schema.additionalProperties === false) {
    for (const key of Object.keys(args)) if (!(key in schema.properties)) errors.push(`Parameter tidak dikenal: ${key}.`);
  }
  for (const [key, value] of Object.entries(args)) {
    const rule = schema.properties[key];
    if (!rule || value === undefined || value === null) continue;
    if (rule.type === "integer" && !Number.isInteger(value)) errors.push(`${key} harus berupa integer.`);
    if (rule.type === "string" && typeof value !== "string") errors.push(`${key} harus berupa string.`);
    if (rule.type === "array" && !Array.isArray(value)) errors.push(`${key} harus berupa array.`);
    if (rule.minimum !== undefined && typeof value === "number" && value < rule.minimum) errors.push(`${key} minimal ${rule.minimum}.`);
    if (rule.maximum !== undefined && typeof value === "number" && value > rule.maximum) errors.push(`${key} maksimal ${rule.maximum}.`);
    if (rule.minLength !== undefined && typeof value === "string" && value.length < rule.minLength) errors.push(`${key} minimal ${rule.minLength} karakter.`);
    if (rule.maxLength !== undefined && typeof value === "string" && value.length > rule.maxLength) errors.push(`${key} maksimal ${rule.maxLength} karakter.`);
    if (rule.enum && !rule.enum.includes(value)) errors.push(`${key} tidak termasuk nilai yang diizinkan.`);
    if (rule.format === "date" && typeof value === "string" && !validDate(value)) errors.push(`${key} harus berformat YYYY-MM-DD yang valid.`);
    if (Array.isArray(value)) {
      if (rule.maxItems !== undefined && value.length > rule.maxItems) errors.push(`${key} maksimal ${rule.maxItems} item.`);
      if (rule.uniqueItems && new Set(value).size !== value.length) errors.push(`${key} tidak boleh berisi duplikasi.`);
      if (rule.items?.enum && value.some((item) => !rule.items.enum.includes(item))) errors.push(`${key} berisi nilai yang tidak diizinkan.`);
      if (rule.items?.type === "string" && value.some((item) => typeof item !== "string")) errors.push(`${key} harus berisi string.`);
    }
  }
  validatePeriod(args.start_date, args.end_date, "periode", errors);
  if ((args.compare_start_date && !args.compare_end_date) || (!args.compare_start_date && args.compare_end_date)) errors.push("compare_start_date dan compare_end_date harus digunakan bersama.");
  validatePeriod(args.compare_start_date, args.compare_end_date, "periode pembanding", errors);
  return errors;
}

function validDate(value) {
  if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) return false;
  const parsed = new Date(`${value}T00:00:00Z`);
  return !Number.isNaN(parsed.valueOf()) && parsed.toISOString().slice(0, 10) === value;
}

function validatePeriod(start, end, label, errors) {
  if (!start || !end || !validDate(start) || !validDate(end)) return;
  const startMs = Date.parse(`${start}T00:00:00Z`);
  const endMs = Date.parse(`${end}T00:00:00Z`);
  if (endMs < startMs) errors.push(`Tanggal akhir ${label} tidak boleh sebelum tanggal awal.`);
  if (Math.floor((endMs - startMs) / 86_400_000) + 1 > 366) errors.push(`${label} maksimal 366 hari.`);
}

async function callApi(path, args) {
  if (!token) throw new Error("ERP_LMG_SANCTUM_TOKEN belum dikonfigurasi.");
  const query = new URLSearchParams();
  for (const [key, value] of Object.entries(args)) {
    if (key === "client_id" || value === undefined || value === null || value === "") continue;
    if (Array.isArray(value)) value.forEach((item) => query.append(`${key}[]`, String(item)));
    else query.set(key, String(value));
  }
  const response = await fetch(`${apiUrl}${path}${query.size ? `?${query}` : ""}`, {
    headers: { Authorization: `Bearer ${token}`, Accept: "application/json" },
    signal: AbortSignal.timeout(15000),
  });
  const body = await response.json().catch(() => ({}));
  if (!response.ok) {
    const error = new Error(body?.error?.message || body.message || `ERP LMG API merespons HTTP ${response.status}.`);
    error.status = response.status;
    throw error;
  }
  return body;
}

async function invoke(name, args = {}) {
  const errors = validateToolArguments(name, args);
  if (errors.length) throw new Error(errors.join(" "));
  return callApi(endpointForTool(name, args), args);
}

async function handle(message) {
  const { id, method, params = {} } = message;
  if (method === "notifications/initialized") return null;
  if (method === "initialize") {
    return { jsonrpc: "2.0", id, result: { protocolVersion: params.protocolVersion || "2025-06-18", capabilities: { tools: { listChanged: false } }, serverInfo: { name: "erp-lmg-marketing", version: "0.2.0" } } };
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

if (process.argv[1] && import.meta.url === pathToFileURL(process.argv[1]).href) {
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
}
