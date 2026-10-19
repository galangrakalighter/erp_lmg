#!/usr/bin/env python3
"""Bounded verifier for missing login throttling; dry-run by default."""

import argparse
import http.cookiejar
import re
import urllib.parse
import urllib.request


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("base_url")
    parser.add_argument("--email", default="nonexistent-security-test@example.invalid")
    parser.add_argument("--attempts", type=int, default=6)
    parser.add_argument("--execute", action="store_true")
    args = parser.parse_args()
    attempts = max(1, min(args.attempts, 10))
    print("1. Attacker position: unauthenticated network client.")
    print(f"2. Planned invalid login attempts: {attempts} (hard cap: 10).")
    if not args.execute:
        print("3. DRY RUN: no login requests sent.")
        print("4. Secure result should become HTTP 429 before repeated guesses continue.")
        return 0

    jar = http.cookiejar.CookieJar()
    opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))
    base = args.base_url.rstrip("/")
    html = opener.open(base + "/", timeout=10).read().decode("utf-8", errors="ignore")
    match = re.search(r'name=["\']_token["\']\s+value=["\']([^"\']+)', html)
    if not match:
        raise RuntimeError("CSRF token not found on login page")
    token = match.group(1)
    statuses = []
    for number in range(1, attempts + 1):
        body = urllib.parse.urlencode({"_token": token, "email": args.email, "password": f"invalid-{number}"}).encode()
        request = urllib.request.Request(base + "/post-login", data=body, method="POST")
        response = opener.open(request, timeout=10)
        statuses.append(response.status)
    print(f"3. Observed statuses: {statuses}")
    print(f"4. VULNERABLE={429 not in statuses}")
    print("5. Cleanup: none; only a reserved .invalid address was used.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
