# c2cx-api-v1-php

PHP Library for accessing the C2CX APIs and example code

After cloning, first edit `c2cx-api-creds.sample` and replace your API and Secret keys.

Then rename `c2cx-api-creds.sample` to `c2cx-api-creds.json`.

You can then run `C2cxApiTest.php`.

This is both a test of the API and your keys as well as an example of how to deal with C2CX APIs.  
Look at the code to understand what it does.  It is well documented.  

Sample output of `C2cxApiTest.php`
```
STARTING TESTS @ 2017-04-05 17:24:11

-- Check prices using ticker --------------------------------------
Last price for CNY_BTC is 7762
Last price for CNY_ETC is 18.3
Last price for CNY_ETH is 327.2

-- Check prices using Order Book-----------------------------------
Ask/Bid spread for CNY_BTC from Order Book: 7789/7749 (0.51% spread)
Ask/Bid spread for CNY_ETC from Order Book: 18.52/18.38 (0.76% spread)
Ask/Bid spread for CNY_ETH from Order Book: 325.3/323 (0.71% spread)

-- Get balances -----------------------------------------------
Available btc balance is 0.56
Available cny balance is 18145.3176
Available ltc balance is 0
Available eth balance is 11.72
Available etc balance is 0
Available sky balance is 0

-- Make orders ------------------------------------------------
Making an order: Sell 2 CNY_BTC @ 8150 ... Success.  Order ID: 885792
Making an order: Sell 24 CNY_ETH @ 344 ... Success.  Order ID: 885793
Making an order: Sell 1 CNY_ETC @ 19 ... Success.  Order ID: 885794

-- Check order status -----------------------------------------
Order ID 885792 for CNY_BTC status is 'Suspended'
Order ID 885793 for CNY_ETH status is 'Suspended'
Order ID 885794 for CNY_ETC status is 'Suspended'

-- Cancel orders ----------------------------------------------
Successfully canceled Order ID 885792 for CNY_BTC.
Successfully canceled Order ID 885793 for CNY_ETH.
Successfully canceled Order ID 885794 for CNY_ETC.

-- Check order status again after cancellation ----------------
Order ID 885792 for CNY_BTC status is 'Canceled'
Order ID 885793 for CNY_ETH status is 'Canceled'
Order ID 885794 for CNY_ETC status is 'Canceled'

TEST SUMMARY:
=================================================================
Made 19 API calls in about 18 seconds (0.95 second per call)
0 failed.
Success rate is 100%

Thank you and enjoy trading with C2CX!
```
