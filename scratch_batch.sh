#!/bin/bash
set -uo pipefail
cd "$(dirname "$0")"

# filename|search term|result index (0-based, use >0 to avoid picking the same
# photo already used for another product with an overlapping term)
items=(
  "PTylq7Qd3smFy2wMv0hqhouPW2WsN0KJMD9RD0sL.jpg|potato vegetable|0"
  "yVsI0HikZ43EjO1pzDZTTegGyS7rT3X3fusYqX8K.jpg|tomato|0"
  "JIHJ6K4yYy1udkfPnJKhT1kMU32whoWWE3eHBaOu.jpg|mango fruit|0"
  "4DeuAGkFFqwI7Sj2trpcdw8Cddc9zIjTPnn8iraP.jpg|cherries fruit|0"
  "ed3bglUavZwULhWrb3tO9tVakXdqJgn8X9ktkTgC.jpg|mandarin orange|0"
  "1eiUBcOY45bEhE9CgbuFHTI3AZk7n0sv9E6hgXvS.jpg|peanuts|0"
  "V75DsiQ4u91IfUEXzBlewB3zxUSopTBpF6YvKLWc.jpg|white rice|0"
  "LYhCXgfAntBuLLePy8gF7BDi0xDKNeMbuZ7fdzs6.jpg|green tea leaves|0"
  "vPgqT5NOf7eyjLxS9myVzQF7wRIpTt27UNIaYDqH.jpg|iyokan citrus|0"
  "GzlG8jN1lWpN2mnXypVLxAQqffW3lvg3dZrfO10B.jpg|sweet potato|0"
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
  "ZkT39BBkHXKuetaQn1ahmzICwyhQ0tUaJ8pSBMzP.jpg|lemon fruit|0"
  "ZjfMERAbXnpt2Bp9UgZEAHEep6hwj51MvLGwagXk.jpg|sudachi citrus|0"
  "XuCzdreHUwgoa5iB1vOgRoqynnmvjuFQvGQZeEY8.jpg|peach fruit|0"
  "VNVhvyLkSN3aYLnyXc5SmDoRM6zNZjkolxFwQcT3.jpg|green tea leaves|1"
  "QF4sk7GgQjewq3kB9dQhUk68okmCf6iAR9mbFvoM.jpg|pineapple fruit|0"
  "icACHwUuIlwQSmodLkDi8wMypviwcy6JW0pCBpiY.jpg|lotus root|1"
  "74kN4s7Xsgh0KXLXPWuqiE9SYpU5rABqh1EPxXgy.jpg|shrimp seafood|0"
  "JrEUFPNsLytRYbILeig1kWKnOhXGt9pcDVZ2ohq0.jpg|white rice|2"
  "Uyczbw6ZDdqjBOD0lOgCn9uPCXq3rNO0IY5EU7KC.jpg|persimmon fruit|0"
)

for item in "${items[@]}"; do
  IFS='|' read -r fname term idx <<< "$item"
  echo "=== $fname ($term, idx=$idx) ==="
  fetch_out=$(node fetch_image.js "$term" "$fname" "$idx" 2>&1)
  echo "$fetch_out"
  if [[ "$fetch_out" == OK* ]]; then
    conv_out=$(docker compose exec -T app php replace_image.php "$fname" 2>&1)
    echo "$conv_out"
  else
    echo "SKIP conversion (download failed)"
  fi
  sleep 1
done
