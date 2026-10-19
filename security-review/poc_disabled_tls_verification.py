#!/usr/bin/env python3
"""Static PoC for disabled certificate verification in outbound HTTP calls."""
import pathlib
import re


FILES = [
    "app/Services/InstagramService.php",
    "app/Services/FacebookService.php",
    "app/Services/MetaAdsService.php",
]


def main():
    print("1. Attacker prerequisite: control of DNS/proxy/network path to the remote API.")
    total = 0
    for name in FILES:
        path = pathlib.Path(name)
        text = path.read_text(encoding="utf-8")
        hits = len(re.findall(r"['\"]verify['\"]\s*=>\s*false", text))
        total += hits
        print(f"2. {name}: {hits} disabled-verification call(s)")
    print("3. Total unsafe calls:", total)
    print("4. Safe static check only; no interception or network request was performed.")


if __name__ == "__main__":
    main()
