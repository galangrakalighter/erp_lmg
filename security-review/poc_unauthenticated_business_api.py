#!/usr/bin/env python3
"""Safe verifier for business APIs that currently lack authentication."""

import argparse
import json
import urllib.error
import urllib.request


READ_ONLY_PATHS = [
    "/api/users/json",
    "/api/crm/human",
    "/api/get-data-payroll",
    "/api/audit-trails",
]


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("base_url", help="Example: http://127.0.0.1:8000")
    parser.add_argument("--execute", action="store_true", help="Perform harmless unauthenticated GET requests")
    args = parser.parse_args()
    base = args.base_url.rstrip("/")

    print("1. Attacker position: unauthenticated network client.")
    print("2. No cookies, bearer token, or CSRF token will be supplied.")
    if not args.execute:
        print("3. DRY RUN: would request:")
        for path in READ_ONLY_PATHS:
            print(f"   GET {base}{path}")
        print("4. Secure result should be HTTP 401/403 for every endpoint.")
        return 0

    print("3. Executing read-only checks (response bodies are not printed).")
    for path in READ_ONLY_PATHS:
        request = urllib.request.Request(base + path, headers={"Accept": "application/json"})
        try:
            with urllib.request.urlopen(request, timeout=10) as response:
                sample = response.read(2048)
                content_type = response.headers.get("Content-Type", "")
                looks_like_data = "json" in content_type.lower()
                if looks_like_data:
                    try:
                        json.loads(sample.decode("utf-8", errors="ignore"))
                    except json.JSONDecodeError:
                        looks_like_data = False
                print(f"   {path}: HTTP {response.status}; JSON={looks_like_data}; VULNERABLE={response.status == 200}")
        except urllib.error.HTTPError as error:
            print(f"   {path}: HTTP {error.code}; protected={error.code in (401, 403)}")
        except Exception as error:
            print(f"   {path}: check failed: {type(error).__name__}")
    print("4. Cleanup: none; only GET requests were used.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
