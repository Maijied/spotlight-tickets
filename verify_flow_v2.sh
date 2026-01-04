#!/bin/bash
TXN="FLOW_TEST_$(date +%s)"
echo "Using TXN: $TXN"

echo "1. Creating Booking..."
curl -s -X POST http://localhost:8082/api/submit_manual_booking.php \
     -d "full_name=FlowTest&email=flow@test.com&phone_number=123456789&ticket_type=regular&quantity=1&payment_method=bkash&bkash_txn_id=$TXN&slot_id=slot_default"

echo -e "\n2. Login Admin..."
rm -f cookies.txt
curl -s -c cookies.txt -d "username=admin&password=admin123&login=Login" http://localhost:8082/public/admin.php -o /dev/null

echo "3. Approve Booking..."
curl -s -b cookies.txt -X POST http://localhost:8082/api/admin_approve.php -d "txn_id=$TXN" -L -o /dev/null -w "%{http_code}\n"
# Check if approval worked - wait a second
sleep 1

echo "4. Verify Success Page Content..."
CONTENT=$(curl -s http://localhost:8082/public/success.php?txnid=$TXN)
if echo "$CONTENT" | grep -q "কনফার্মড (PAID)"; then
    echo "SUCCESS: Ticket is Confirmed!"
else
    echo "FAILURE: Ticket not confirmed."
    echo "$CONTENT" | grep "Status"
fi

echo "5. Verify Check-in API..."
SCAN=$(curl -s -b cookies.txt -X POST http://localhost:8082/api/checkin.php -d "qrcode=$TXN")
echo "Scan Result: $SCAN"

echo "6. Verify Double Scan..."
SCAN2=$(curl -s -b cookies.txt -X POST http://localhost:8082/api/checkin.php -d "qrcode=$TXN")
echo "Scan 2 Result: $SCAN2"
