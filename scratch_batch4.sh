#!/bin/bash
set -uo pipefail
cd "$(dirname "$0")"

items=(
  "OsSDyFse6EGrsL7N1aiM5OEuo68GFVOqKu4mVqIm.jpg|corn cob vegetable food|0"
  "Lg75ztVfJF3mfif8utRxYchREAnoMm8KIIqGRcLD.jpg|red apple fruit|0"
  "EZHePqEjmHtYKxjSBhv9PjGXaObvyxbHsfI7iSIa.jpg|ginger root spice|0"
  "NxA8S73OoAyE4SlTIgECnZWUCZqzUUgFcVYeyde2.jpg|green grapes bunch|0"
  "8fEzA2N6XohZEuotmbRtq9tOaQAeU9mEQ5J42caz.jpg|strawberry fruit red|0"
  "h4P8hnNFY2fHAHKEIMbDqFgwDcTWzuFC9KEIoB9e.jpg|onion vegetable|0"
  "lns9v7sZNKltRBwzwa8CNfMREbOozuKOpraKh38H.jpg|strawberry fruit red|1"
  "aCk633nJMlj48SgHGELpwJB91AVFwDueqhLUaoWO.jpg|lotus root vegetable|0"
  "Zf9QSlL6nSVTBc1SXNlvIPhxSYC51fl84fIJ9bDo.jpg|cooked white rice bowl|0"
  "OmVljzWnxcx5Fmho6pLDuyb3nMQoKIi1w0pz2RFg.jpg|purple grapes fruit|0"
  "JrEUFPNsLytRYbILeig1kWKnOhXGt9pcDVZ2ohq0.jpg|white rice bag|0"
  "Uyczbw6ZDdqjBOD0lOgCn9uPCXq3rNO0IY5EU7KC.jpg|persimmon fruit orange|0"
)

for item in "${items[@]}"; do
  IFS='|' read -r fname term idx <<< "$item"
  echo "=== $fname ($term, idx=$idx) ==="
  fetch_out=$(node fetch_image2.js "$term" "$fname" "$idx" 2>&1)
  echo "$fetch_out"
  if [[ "$fetch_out" == OK* ]]; then
    conv_out=$(docker compose exec -T app php -d memory_limit=1024M replace_image.php "$fname" 2>&1)
    echo "$conv_out"
  else
    echo "SKIP conversion (download failed)"
  fi
  sleep 2
done
