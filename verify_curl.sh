#!/bin/bash
# 1. Post and get location
echo "Step 1: Initiating Payment..."
LOC=$(curl -s -i -c cookies.txt -b cookies.txt -d "full_name=AutoVerify&email=verify@check.com&phone_number=01799999999&ticket_type=vip&quantity=2&slot_id=slot_default" http://127.0.0.1:8081/api/create_payment.php | grep -i "Location:" | awk '{print $2}' | tr -d '\r')

echo "Redirect Location: $LOC"

if [ -z "$LOC" ]; then
    echo "Error: No redirect location found."
    exit 1
fi

# 2. Follow redirect
echo "Step 2: Following Redirect to Callback..."
# Note: The Location URL from create_payment.php (in dummy mode) should be the callback.php with query params.
# We need to make sure we use the same cookie jar.
curl -s -i -c cookies.txt -b cookies.txt "$LOC" > step2_output.txt

echo "Step 2 Complete. Checking for success..."
if grep -q "Location: public/success.php" step2_output.txt; then
    echo "SUCCESS: Redirected to success page."
elif grep -q "Location: .*success.php" step2_output.txt; then
    echo "SUCCESS: Redirected to success page (variant)."
else
    echo "FAILURE: Did not redirect to success page. Output headers:"
    head -n 20 step2_output.txt
fi
