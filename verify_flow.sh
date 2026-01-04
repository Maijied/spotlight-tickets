#!/bin/bash

# Base URL
BASE_URL="http://localhost:8082"
COOKIE_FILE="cookies.txt"

# 1. Hit Index to initialize session
echo "1. Visiting Homepage..."
curl -c $COOKIE_FILE -b $COOKIE_FILE -s -o /dev/null "$BASE_URL/public/index.php"

# 2. Submit Booking Form and Follow Redirect
echo "2. Submitting Booking Request..."
# Data: name, pnumber, email, tier, quantity, slot_id
CONTENT=$(curl -c $COOKIE_FILE -b $COOKIE_FILE -s -L \
  -d "full_name=AutoTest&phone_number=01711111111&email=auto@test.com&ticket_type=regular&quantity=1&slot_id=slot_default" \
  -X POST "$BASE_URL/api/create_payment.php")

# 3. Verify Success Page Content
echo "3. Verifying Result..."

if [[ "$CONTENT" == *"QR Code"* ]]; then
    echo "VERIFIED: Success page loaded with 'QR Code'."
    exit 0
elif [[ "$CONTENT" == *"Error"* ]]; then
    echo "FAILED: Application returned error:"
    echo "$CONTENT" | grep "Error"
    exit 1
elif [[ "$CONTENT" == *"<b>Fatal error</b>"* ]]; then
    echo "FAILED: PHP Fatal Error occurred."
    echo "$CONTENT"
    exit 1
else
    echo "WARNING: Check output manually. 'Ticket Confirmed' not found."
    # echo "$CONTENT" # Uncomment to debug
    exit 1
fi
