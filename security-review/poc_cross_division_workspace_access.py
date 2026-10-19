#!/usr/bin/env python3
"""Read-only PoC scaffold for cross-division client access."""
import argparse
import urllib.error
import urllib.request


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--base-url", default="https://erp.example.test")
    parser.add_argument("--division", choices=["mcn", "tech", "ecommerce"], default="tech")
    parser.add_argument("--client-id", type=int, default=1)
    parser.add_argument("--session-cookie")
    parser.add_argument("--execute", action="store_true")
    args = parser.parse_args()
    url = f"{args.base_url.rstrip('/')}/division/{args.division}/client/{args.client_id}"
    print("1. Use a non-admin account from a different division.")
    print("2. Read-only target:", url)
    print("3. Expected secure result: HTTP 403 or 404.")
    if not args.execute:
        print("4. DRY RUN: no request sent.")
        return
    if not args.session_cookie:
        raise SystemExit("--session-cookie is required with --execute")
    request = urllib.request.Request(url, headers={"Cookie": args.session_cookie})
    try:
        with urllib.request.urlopen(request, timeout=10) as response:
            print("4. HTTP status:", response.status)
            print("5. A 200 response confirms cross-division access for the supplied account/client pair.")
    except urllib.error.HTTPError as error:
        print("4. HTTP status:", error.code)


if __name__ == "__main__":
    main()
