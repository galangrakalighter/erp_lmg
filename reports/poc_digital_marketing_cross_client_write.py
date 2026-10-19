#!/usr/bin/env python3
"""Non-executing PoC scaffold for cross-client content/CMS mutations."""

import argparse


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--base-url", default="http://127.0.0.1:8000")
    parser.add_argument("--client-id", type=int, required=True)
    parser.add_argument("--content-source", choices=["organic", "paid"], default="organic")
    args = parser.parse_args()

    print("1. Prerequisite: use a specialist account allowed for the selected source but not assigned to the target client.")
    print("2. Obtain a CSRF token in an isolated test environment.")
    print(f"3. Candidate trigger: POST {args.base_url.rstrip('/')}/manajemen/{args.client_id}/create")
    print(f"   Include content_source={args.content_source} and otherwise valid content-plan fields.")
    print("4. Secure result should be 403 before validation/persistence.")
    print("5. Verify no data_jasa/platform_mirror_copies/task rows were created; remove test markers if a vulnerable write occurred.")
    print("This scaffold intentionally never sends a state-changing request.")


if __name__ == "__main__":
    main()
