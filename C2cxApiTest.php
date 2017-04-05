#!/usr/bin/php
<?php
/*
 * C2CX API Test and Example
 * Version April 5, 2017
 *
 * TODO: Test submit advanced order, checkOrdersByStatus
 *
 * This is used both as a test of the C2CX APIs and example.
 *
 * Not all API calls are tested, but we check the prices & balances,
 * make a few orders, check their status and then cancel them.
 *
 * You can safely run this script.  The orders it creates will immediatelly
 * be suspended and will not execute.  They will be canceled a few seconds later.
 *
 * The script will report what it is doing and give a test result.
 *
 * If you encounter errors you think are our problem please copy and paste the
 * results of this script and send to C2CX Customer Service.
 *
 * Make sure you edit c2cx-api-creds.sample and replace your API and Secret keys,
 * then rename the file to c2cx-api-creds.json
 *
 * Here is a sample output of this script:
 *
 *   niks@niks-vostro:~/c2cx$ ./C2cxApiTest.php
 *
 *   STARTING TESTS @ 2017-04-05 19:44:07
 *
 *   -- Check prices using ticker ---------- 2017-04-05 19:44:07
 *   Last price for CNY_BTC is 7825
 *   Last price for CNY_ETC is 18.6
 *   Last price for CNY_ETH is 327.2
 *
 *   -- Check prices using Order Book ------ 2017-04-05 19:44:09
 *   Ask/Bid spread for CNY_BTC from Order Book: 7837/7794 0.55% spread)
 *   Ask/Bid spread for CNY_ETC from Order Book: 18.63/18.46 0.92% spread)
 *   Ask/Bid spread for CNY_ETH from Order Book: 326.2/324.3 0.58% spread)
 *
 *   -- Get balances ----------------------- 2017-04-05 19:44:09
 *   Available btc balance is 0.56
 *   Available cny balance is 18145.3176
 *   Available ltc balance is 0
 *   Available eth balance is 11.72
 *   Available etc balance is 0
 *   Available sky balance is 0
 *
 *   -- Make orders ------------------------ 2017-04-05 19:44:10
 *   Making an order: Sell 2 CNY_BTC @ 8216 ... Success.  Order ID: 885863
 *   Making an order: Sell 24 CNY_ETH @ 344 ... Success.  Order ID: 885864
 *   Making an order: Sell 1 CNY_ETC @ 20 ... Success.  Order ID: 885865
 *
 *   -- Check order status ----------------- 2017-04-05 19:44:11
 *   Order ID 885863 for CNY_BTC status is 'Suspended'
 *   Order ID 885864 for CNY_ETH status is 'Suspended'
 *   Order ID 885865 for CNY_ETC status is 'Suspended'
 *
 *   -- Cancel orders ---------------------- 2017-04-05 19:44:11
 *   Successfully canceled Order ID 885863 for CNY_BTC.
 *   Successfully canceled Order ID 885864 for CNY_ETH.
 *   Successfully canceled Order ID 885865 for CNY_ETC.
 *
 *   -- Check order status again ----------- 2017-04-05 19:44:12
 *   Order ID 885863 for CNY_BTC status is 'Canceled'
 *   Order ID 885864 for CNY_ETH status is 'Canceled'
 *   Order ID 885865 for CNY_ETC status is 'Canceled'
 *
 *   TEST SUMMARY:
 *   ======================================= 2017-04-05 19:44:13
 *   Made 19 API calls in about 6 seconds (0.32 second per call)
 *   0 failed.
 *   Success rate is 100%
 *
 *   Thank you and enjoy trading with C2CX!
 *
 *
 */

require_once("C2cxApiClass.php");

//
// Load credentials
//
$creds_file = 'c2cx-api-creds.json';
$creds_json = false;
if (file_exists($creds_file)) {
    $creds_json = file_get_contents($creds_file);
}

$creds = false;
if ($creds_json) {
    $creds = json_decode($creds_json, true);
}

if ($creds) {

    $apiKey = $creds['apiKey'];
    $secretKey = $creds['secretKey'];

    // For our testing
    $proceed = true;
    $countSuccess = 0;
    $countFail = 0;

    // Create object
    $c2cx = new C2cxApi($apiKey, $secretKey);

    $pairs = $c2cx->getAvailablePairs(); // returns an array

    // Ready to start testing
    $start_test = time();
    $now = date('Y-m-d H:i:s');
    print "\nSTARTING TESTS @ $now\n";

    print "\n-- Check prices using ticker ---------- $now\n";
    //
    // Get last price for each pair from ticker
    //
    $prices = [];
    foreach ($pairs as $symbol) {

        // Call API
        $call = $c2cx->getTicker($symbol);

        if ($call['code'] == 200) { // success

            if ($call['data']['last'] > 0) {
                $countSuccess++;
                $price["$symbol"] = $call['data']['last'];
                print "Last price for $symbol is " . $call['data']['last'] . "\n";
            } else {
                $countFail++;
                print "API ticker call for $symbol succeeded but last price is
                not greater than 0.\nIgnoring $symbol for further tests.\n";
            }

        } else { // fail

            $countFail++;
            $proceed = false;
            print "Not able to get last price for $symbol. ";
            print "Error " . $call['code'] . ": " . $call['message'] . "\n";

        }

    }

    $now = date('Y-m-d H:i:s');
    print "\n-- Check prices using Order Book ------ $now\n";
    //
    // Get last price for each pair from Order Book
    //
    foreach ($pairs as $symbol) {

        // Call API
        $call = $c2cx->getOrderBook($symbol);

        if ($call['code'] == 200) { // success

            $now = time();
            if ($now - $call['data']['timestamp'] < 10) {
                $countSuccess++;
                $asks = $call['data']['asks'];
                $bids = $call['data']['bids'];
                $last = count($asks)-1;
                $ask = $asks[$last][0];
                $bid = $bids[0][0];
                $spread = round(($ask - $bid) / (($ask + $bid) / 2) * 100, 2);
                print "Ask/Bid spread for $symbol from Order Book: $ask/$bid " .
                 $spread . "% spread)\n";
            } else {
                $countFail++;
                print "API ticker call for $symbol succeeded but Order Book is
                more than 10 seconds old!\nIgnoring $symbol for further tests.\n";
                if (isset($price["$symbol"])) {
                    unset($price["$symbol"]);
                }
            }

        } else { // fail

            $countFail++;
            $proceed = false;
            print "Not able to get Order Book for $symbol. ";
            print "Error " . $call['code'] . ": " . $call['message'] . "\n";

        }

    }

    if ($proceed) {
        $now = date('Y-m-d H:i:s');
        print "\n-- Get balances ----------------------- $now\n";
        //
        // Get our balances
        //
        $call = $c2cx->getBalance();
        if ($call['code'] == 200) {
            $countSuccess++;
            $balances = $call['data']['funds'];
        } else {
            $countFail++;
            $proceed = false;
            print 'Not able to get balances. ';
            print "Error " . $call['code'] . ": " . $call['message'] . "\n";
        }
    }

    if ($proceed) {
        // Print available ('free') balances and remember currencies for later
        $currencies = [];
        foreach ($balances['free'] as $currency=>$amount) {
            print "Available $currency balance is $amount\n";
            $currencies[] = $currency;
        }
    }

    if ($proceed) {
        $now = date('Y-m-d H:i:s');
        print "\n-- Make orders ------------------------ $now\n";
        //
        // Create an order with each currency that we know will NOT execute
        // right away. We can do this by making an order that requires more
        // balance than we have. Also 5% away from the market.  C2CX allows this.
        //
        $side = 'Sell';
        $orders = [];
        foreach ($currencies as $currency) {
            $symbol = 'CNY_' . strtoupper($currency);
            if (in_array($symbol, $pairs) && isset($price["$symbol"])) {
                $qty = round($balances['free']["$currency"] * 2, 0) + 1; // this won't execute!
                $ask = round($price["$symbol"] + ($price["$symbol"] * 0.05), 0); // this won't execute!
                print "Making an order: $side $qty $symbol @ $ask ... ";
                $call = $c2cx->submitTradeOrder($symbol, $side, $ask, $qty);
                if ($call['code'] == 200) {
                    $countSuccess++;
                    $orderId = $call['data']['orderId'];
                    $orders["$symbol"] = $orderId;
                    print "Success.  Order ID: $orderId\n";
                } else {
                    $countFail++;
                    print "Order failed. ";
                    print "Error " . $call['code'] . ": " . $call['message'] . "\n";
                }
            }
        }

        $proceed = count($orders) > 0;

    }


    if ($proceed) {
        $now = date('Y-m-d H:i:s');
        print "\n-- Check order status ----------------- $now\n";
        //
        // Next, we check on the status of each successful order
        //
        foreach ($orders as $symbol=>$orderId) {
            $call = $c2cx->checkOrders($symbol, $orderId);
            if ($call['code'] == 200) {
                $countSuccess++;
                $status = $call['data'][0]['status']; // because there is only one order
                print "Order ID $orderId for $symbol status is '" .
                $c2cx->getOrderStatusString($status) . "'\n";
            } else {
                $countFail++;
                print "Was not able to get order status for Order ID $orderId. ";
                print "Error " . $call['code'] . ": " . $call['message'] . "\n";
            }
        }

        print "\n-- Cancel orders ---------------------- $now\n";
        //
        // Now we will cancel the orders we made
        //
        foreach ($orders as $symbol=>$orderId) {
            $call = $c2cx->cancelOrder($symbol, $orderId);
            if ($call['code'] == 200) {
                $countSuccess++;
                print "Successfully canceled Order ID $orderId for $symbol.\n";
            } else {
                $countFail++;
                print "Was not able to cancel order ID $orderId! ";
                print "Error " . $call['code'] . ": " . $call['message'] . "\n";
            }
        }

        $now = date('Y-m-d H:i:s');
        print "\n-- Check order status again ----------- $now\n";
        //
        // Next, we check on the status of each successful order
        //
        foreach ($orders as $symbol=>$orderId) {
            $call = $c2cx->checkOrders($symbol, $orderId);
            if ($call['code'] == 200) {
                $countSuccess++;
                $status = $call['data'][0]['status']; // because there is only one order
                print "Order ID $orderId for $symbol status is '" .
                $c2cx->getOrderStatusString($status) . "'\n";
            } else {
                $countFail++;
                print "Was not able to get order status for Order ID $orderId. ";
                print "Error " . $call['code'] . ": " . $call['message'] . "\n";
            }
        }

    }

    print "\nTEST SUMMARY:\n";
    $now = date('Y-m-d H:i:s');
    print "======================================= $now\n";
    //
    // Final report
    //
    $stop_test = time();
    $elapsed = $stop_test - $start_test;
    $totalTests = $countSuccess + $countFail;
    $per = round($elapsed / $totalTests, 2);
    $rate = round(($countSuccess / $totalTests) * 100, 0);
    print "Made $totalTests API calls in about $elapsed seconds ($per second per call)\n";
    print "$countFail failed.\n";
    print "Success rate is $rate%\n";
    if ($countFail > 0) {
        print "\n\nPlease let C2CX Customer Service know if you believe any failure is \n";
        print "a C2CX server or API problem.\n";
        print "Double check that your API Key and Secret Key are correct.\n";
        print "Copy and paste results of this test and send to C2CX Customer Service.\n";
        print "Remember never send your API key or Secret Key over e-mail or WeChat!\n";
    }
    print "\nThank you and enjoy trading with C2CX!\n\n";

} else {
    print "Unable to load credentials, please check that the credentials file exists ";
    print "and is in valid json format.\n";
}
