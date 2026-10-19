#!/usr/bin/env python3
"""Offline detector for the hardcoded WAHA credential; never prints the secret."""

import argparse
import pathlib
import re


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--root", default=".", help="Repository root")
    args = parser.parse_args()
    controller = pathlib.Path(args.root) / "app/Http/Controllers/CRMController.php"
    text = controller.read_text(encoding="utf-8")
    declaration = re.search(r"private\s+const\s+WAHA_API_KEY\s*=\s*['\"]([^'\"]+)['\"]", text)
    usage = "self::WAHA_API_KEY" in text and "X-Api-Key" in text

    print("1. Attacker position: source, backup, build artifact, or deployed-code reader.")
    print(f"2. Constant declaration found: {bool(declaration)}")
    print(f"3. Credential is used in an outbound X-Api-Key header: {usage}")
    if declaration:
        print(f"4. Secret length: {len(declaration.group(1))}; value intentionally suppressed.")
    print(f"5. VULNERABLE={bool(declaration and usage)}")
    print("6. Cleanup/remediation prerequisite: rotate the exposed WAHA key after moving it to server-side environment configuration.")
    return 1 if declaration and usage else 0


if __name__ == "__main__":
    raise SystemExit(main())
