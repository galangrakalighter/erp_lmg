<?php

$issuer = rtrim((string) env('APP_URL', 'http://localhost'), '/');

return [
    'issuer' => env('MARKETING_MCP_OAUTH_ISSUER', $issuer),
    'resource' => env('MARKETING_MCP_RESOURCE', $issuer.'/mcp'),
];
