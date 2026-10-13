#!/usr/bin/env python3
"""Static, non-secret-printing PoC for database credential exposure."""
import argparse
import pathlib
import re
import socket


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--compose", default="docker-compose.yml")
    parser.add_argument("--host")
    parser.add_argument("--check-port", action="store_true")
    args = parser.parse_args()
    text = pathlib.Path(args.compose).read_text(encoding="utf-8")
    literal = bool(re.search(r"POSTGRES_PASSWORD:\s*(?!\$\{|\$\$|$)\S+", text))
    published = bool(re.search(r"['\"]?5432:5432['\"]?", text))
    print("1. Literal POSTGRES_PASSWORD present:", literal)
    print("2. PostgreSQL host port published:", published)
    print("3. No password value is printed or used by this PoC.")
    if args.check_port:
        if not args.host:
            raise SystemExit("--host is required with --check-port")
        with socket.create_connection((args.host, 5432), timeout=3):
            print("4. TCP/5432 reachable. This does not attempt authentication.")
    else:
        print("4. DRY RUN: network reachability not tested.")


if __name__ == "__main__":
    main()
