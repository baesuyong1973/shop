#!/bin/bash
set -uo pipefail
cd "$(dirname "$0")"

items=(
  "OsSDyFse6EGrsL7N1aiM5OEuo68GFVOqKu4mVqIm.jpg|corn maize|0"
  "Lg75ztVfJF3mfif8utRxYchREAnoMm8KIIqGRcLD.jpg|apple fruit|1"
  "EZHePqEjmHtYKxjSBhv9PjGXaObvyxbHsfI7iSIa.jpg|ginger root|0"
  "NxA8S73OoAyE4SlTIgECnZWUCZqzUUgFcVYeyde2.jpg|muscat grapes|0"
  "8fEzA2N6XohZEuotmbRtq9tOaQAeU9mEQ5J42caz.jpg|amaou strawberry|0"
  "h4P8hnNFY2fHAHKEIMbDqFgwDcTWzuFC9KEIoB9e.jpg|onion vegetable|0"
  "lns9v7sZNKltRBwzwa8CNfMREbOozuKOpraKh38H.jpg|strawberry|1"
  "aCk633nJMlj48SgHGELpwJB91AVFwDueqhLUaoWO.jpg|lotus root|0"
  "Zf9QSlL6nSVTBc1SXNlvIPhxSYC51fl84fIJ9bDo.jpg|white rice|1"
  "OmVljzWnxcx5Fmho6pLDuyb3nMQoKIi1w0pz2RFg.jpg|grapes|0"
  "JrEUFPNsLytRYbILeig1kWKnOhXGt9pcDVZ2ohq0.jpg|white rice|2"
  "Uyczbw6ZDdqjBOD0lOgCn9uPCXq3rNO0IY5EU7KC.jpg|persimmon fruit|0"
)

for item in "${items[@]}"; do
  IFS='|' read -r fname term idx <<< "$item"
  echo "=== $fname ($term, idx=$idx) ==="
  attempt=1
  while [[ $attempt -le 3 ]]; do
    fetch_out=$(node fetch_image.js "$term" "$fname" "$idx" 2>&1)
    if [[ "$fetch_out" == OK* ]]; then
      echo "$fetch_out"
      conv_out=$(docker compose exec -T app php -d memory_limit=1024M replace_image.php "$fname" 2>&1)
      echo "$conv_out"
      break
    else
      echo "attempt $attempt failed: $(echo "$fetch_out" | head -1)"
      attempt=$((attempt+1))
      sleep 15
    fi
  done
  sleep 8
done
