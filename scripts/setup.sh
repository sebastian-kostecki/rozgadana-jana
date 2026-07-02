#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

if [[ ! -f .env ]]; then
  cp .env.example .env
  echo "Created .env from .env.example"
fi

if [[ ! -f wp-config.php ]]; then
  cp wp-config-docker.php wp-config.php
  echo "Created wp-config.php from wp-config-docker.php"
fi

echo "Setup complete. Run: make up"
