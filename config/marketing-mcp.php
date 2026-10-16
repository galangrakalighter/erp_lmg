<?php

$issuer = rtrim((string) env('APP_URL', 'http://localhost'), '/');

return [
    'issuer' => env('MARKETING_MCP_OAUTH_ISSUER', $issuer),
    'resource' => env('MARKETING_MCP_RESOURCE', $issuer.'/mcp'),
    'document_upload_max_kb' => (int) env('MARKETING_MCP_DOCUMENT_MAX_KB', 20480),
    'document_storage_disk' => env('MARKETING_MCP_DOCUMENT_DISK', 'local'),
];
