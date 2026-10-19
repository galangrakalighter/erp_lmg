#!/usr/bin/env python3
"""Checks whether a known client-document storage path is public without authentication."""

import argparse
import urllib.error
import urllib.parse
import urllib.request


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("base_url")
    parser.add_argument("known_path", help="DB location value, e.g. pdf/12/3/file.pdf")
    parser.add_argument("--execute", action="store_true", help="Issue one unauthenticated GET")
    args = parser.parse_args()
    path = "/".join(urllib.parse.quote(part, safe="") for part in args.known_path.strip("/").split("/"))
    url = args.base_url.rstrip("/") + "/storage/" + path
    print("1. Attacker position: unauthenticated client who already knows a stored document path.")
    print(f"2. Target: GET {url} without cookies or tokens.")
    if not args.execute:
        print("3. DRY RUN: request not sent.")
        print("4. Secure result should be 401/403/404, not PDF bytes.")
        return 0
    try:
        with urllib.request.urlopen(urllib.request.Request(url), timeout=10) as response:
            signature = response.read(5)
            print(f"3. HTTP {response.status}; Content-Type={response.headers.get('Content-Type', '')}; PDF={signature == b'%PDF-'}")
            print(f"4. VULNERABLE={response.status == 200 and signature == b'%PDF-'}")
    except urllib.error.HTTPError as error:
        print(f"3. HTTP {error.code}; protected={error.code in (401, 403, 404)}")
    print("5. Cleanup: none; at most five response bytes were read.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
