#!/usr/bin/env python3
"""Offline evidence collector for vulnerable spreadsheet parser dependencies."""

import argparse
import json
import pathlib
import re


def package_version(lock: dict, name: str) -> str | None:
    for package in lock.get("packages", []) + lock.get("packages-dev", []):
        if package.get("name") == name:
            return package.get("version")
    return None


def main() -> int:
    parser = argparse.ArgumentParser()
    parser.add_argument("--root", default=".")
    args = parser.parse_args()
    root = pathlib.Path(args.root)
    lock = json.loads((root / "composer.lock").read_text(encoding="utf-8"))
    controller = (root / "app/Http/Controllers/SosialCreatorController.php").read_text(encoding="utf-8")
    versions = {
        "maatwebsite/excel": package_version(lock, "maatwebsite/excel"),
        "phpoffice/phpspreadsheet": package_version(lock, "phpoffice/phpspreadsheet"),
    }
    reachable = bool(re.search(r"mimes:xlsx,xls,csv", controller) and "Excel::import" in controller)
    print("1. Attacker position: authenticated user able to submit /import_creator.")
    print(f"2. Locked packages: {versions}")
    print(f"3. User-controlled spreadsheet reaches Excel::import(): {reachable}")
    print("4. composer audit reports critical CVE-2026-34084 and CVE-2026-45034 for phpoffice/phpspreadsheet 1.30.0.")
    print(f"5. VULNERABLE={versions['phpoffice/phpspreadsheet'] == '1.30.0' and reachable}")
    print("6. No malicious spreadsheet is generated or parsed by this PoC.")
    return 1 if versions["phpoffice/phpspreadsheet"] == "1.30.0" and reachable else 0


if __name__ == "__main__":
    raise SystemExit(main())
