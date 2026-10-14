# ERP LMG Marketing MCP

Private, read-only MCP adapter for the ERP LMG marketing analytics API.

## Runtime configuration

- `ERP_LMG_API_URL`: ERP LMG base URL, for example `http://127.0.0.1:8000`.
- `ERP_LMG_SANCTUM_TOKEN`: a personal Sanctum token with the `marketing:read` ability.

The MCP process never connects directly to PostgreSQL. The Laravel API authenticates the token owner and applies the existing Digital Marketing client-access rules on every client-scoped call.

## Tools

- `marketing_list_clients`
- `marketing_get_data_availability`
- `marketing_get_performance_summary`

The performance tool reads stored snapshots only. It does not fetch social-platform APIs or create snapshots.
