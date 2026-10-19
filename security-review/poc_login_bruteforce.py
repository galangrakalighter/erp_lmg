#!/usr/bin/env python3
"""Bounded PoC scaffold for missing login throttling."""
import argparse
import http.cookiejar
import re
import urllib.parse
import urllib.request


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--base-url", default="https://erp.example.test")
    parser.add_argument("--email", default="nonexistent-security-test@example.invalid")
    parser.add_argument("--execute", action="store_true")
    args = parser.parse_args()
    print("1. Test is capped at three invalid attempts against a non-existent account.")
    print("2. Expected secure behavior: explicit rate limiting after the configured threshold.")
    if not args.execute:
        print("3. DRY RUN: no login attempts sent.")
        return
    jar = http.cookiejar.CookieJar()
    opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))
    for attempt in range(1, 4):
        page = opener.open(args.base_url.rstrip("/") + "/", timeout=10).read().decode("utf-8", "replace")
        token = re.search(r'name=["\']_token["\'][^>]*value=["\']([^"\']+)', page)
        if not token:
            raise SystemExit("CSRF token not found")
        body = urllib.parse.urlencode({"_token": token.group(1), "email": args.email, "password": "invalid-test-only"}).encode()
        request = urllib.request.Request(args.base_url.rstrip("/") + "/post-login", data=body, method="POST")
        try:
            response = opener.open(request, timeout=10)
            print(f"3.{attempt} final HTTP status:", response.status)
        except urllib.error.HTTPError as error:
            print(f"3.{attempt} HTTP status:", error.code)
    print("4. Three attempts alone do not prove exploitability; inspect configured threshold and verify 429 at its boundary.")


if __name__ == "__main__":
    main()
