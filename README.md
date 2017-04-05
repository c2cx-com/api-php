# c2cx-api-v1-php

### PHP Library for accessing the C2CX APIs and example code

C2CX API documentation site is at https://api.c2cx.com/

Sign up for C2CX at http://c2cx.com/ and generate API keys under Settings|API.

After cloning this library with `git clone https://github.com/c2cx-com/c2cx-api-v1-php.git`, first edit `c2cx-api-creds.sample` and replace your API and Secret keys.

Then rename `c2cx-api-creds.sample` to `c2cx-api-creds.json`.

You can then execute `C2cxApiTest.php`.  The script will create impossible orders (for more money than you have, away from the market, this is allowed by C2CX) which will immediatelly be suspended and will not execute, and then the script will cancel them.  If anything goes wrong, it will report.

This is both a test of the API and your keys as well as example of how to deal with C2CX APIs.  Look at the code to understand what it does.  It is well documented.

Any time your code does not work and you suspect that there might be a problem with the API, you can run `C2cxApiTest.php` to see if the basic API functionality is up and running correctly. 

Sample output of `C2cxApiTest.php`
```
STARTING TESTS @ 2017-04-05 19:44:07

-- Check prices using ticker ---------- 2017-04-05 19:44:07
Last price for CNY_BTC is 7825
Last price for CNY_ETC is 18.6
Last price for CNY_ETH is 327.2

-- Check prices using Order Book ------ 2017-04-05 19:44:09
Ask/Bid spread for CNY_BTC from Order Book: 7837/7794 0.55% spread)
Ask/Bid spread for CNY_ETC from Order Book: 18.63/18.46 0.92% spread)
Ask/Bid spread for CNY_ETH from Order Book: 326.2/324.3 0.58% spread)

-- Get balances ----------------------- 2017-04-05 19:44:09
Available btc balance is 0.56
Available cny balance is 18145.3176
Available ltc balance is 0
Available eth balance is 11.72
Available etc balance is 0
Available sky balance is 0

-- Make orders ------------------------ 2017-04-05 19:44:10
Making an order: Sell 2 CNY_BTC @ 8216 ... Success.  Order ID: 885863
Making an order: Sell 24 CNY_ETH @ 344 ... Success.  Order ID: 885864
Making an order: Sell 1 CNY_ETC @ 20 ... Success.  Order ID: 885865

-- Check order status ----------------- 2017-04-05 19:44:11
Order ID 885863 for CNY_BTC status is 'Suspended'
Order ID 885864 for CNY_ETH status is 'Suspended'
Order ID 885865 for CNY_ETC status is 'Suspended'

-- Cancel orders ---------------------- 2017-04-05 19:44:11
Successfully canceled Order ID 885863 for CNY_BTC.
Successfully canceled Order ID 885864 for CNY_ETH.
Successfully canceled Order ID 885865 for CNY_ETC.

-- Check order status again ----------- 2017-04-05 19:44:12
Order ID 885863 for CNY_BTC status is 'Canceled'
Order ID 885864 for CNY_ETH status is 'Canceled'
Order ID 885865 for CNY_ETC status is 'Canceled'

TEST SUMMARY:
======================================= 2017-04-05 19:44:13
Made 19 API calls in about 6 seconds (0.32 second per call)
0 failed.
Success rate is 100%

Thank you and enjoy trading with C2CX!

```
