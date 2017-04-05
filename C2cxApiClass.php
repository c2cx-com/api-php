<?php
/*
 * C2CX API PHP Class for Api v1
 *
 * Last update: April 1, 2017
 *
 * Example:
 *
 * $c2cx = new C2cxApi('XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX', 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');
 * $c2cx->getBalance();
 *
 * API returns json, but this class decodes it to an associative array with:
 * $array = json_decode($api_result, true);
 *
 * All API calls in this class return an associative array.
 *
 * See separate C2cxApiTest.php for example how to use.
 *
 */

class C2cxApi
{
    private $apiKey;
    private $secretKey;
    private $apiEndPoint;
    private $headers;

    // Constructor
    public function __construct($apiKey, $secretKey)
    {
        $this->apiKey      = $apiKey;
        $this->secretKey   = $secretKey;
        $this->apiEndPoint = 'https://api.c2cx.com/Rest/';
        $this->headers      = ['User-Agent: C2CX/v1'];
    }


    public function getTicker($symbol)
     /*
     * API returns:
     *
     * {
     *	  "code": 200,
     *	  "message": "success",
     *	  "data": {
     *		 "date": "1476757437",
     *		 "high": 0,
     *		 "last": 88,
     *		 "low": 0,
     *		 "buy": 0,
     *		 "sell": 0
     *	  }
     * }
     *
     */
    {
        $data = $this->getRequest('ticker', array('symbol' => $symbol));

        return $data;
    }


    public function getOrderBook($symbol)
    /*
     * Valid order books as of April 1, 2017: CNY_BTC, CNY_ETH, CNY_ETC, CNY_SKY
     * Symbols are case insensitive.  CNY_BTC or cny_btc both work.
     * Asks and bids are returned in the $price=>$volume format.
     *
     * API returns:
     *
     * {
     *     "code": 200,
     *     "message": "success",
     *     "data": {
     *         "timestamp": "1491365351",
     *         "bids": [
     *             [7864.0, 3.83],
     *             [7863.0, 0.2],
     *             [7861.0, 0.48],
     *             [7860.0, 5.27],
     *             [7859.0, 4.80],
     *         ],
     *         "asks": [
     *             [7920.0, 0.03],
     *             [7919.0, 0.03],
     *             [7918.0, 0.48],
     *             [7917.0, 0.09],
     *             [7916.0, 1.99],
     *             [7915.0, 0.47],
     *         ]
     *     }
     * }
     *
     */
    {
        $data = $this->getRequest('GetOrderbook', array('symbol' => $symbol));

        return $data;
    }


    public function getBalance()
    /*
     * API returns:
     *
     * {
     *   "code": 200,
     *   "message": "success",
     *   "data": {
     *       "funds": {
     *           "asset": {
     *               "net": 8899999118.81216,
     *               "total": 8899999563.81216
     *           },
     *           "borrow": {
     *               "btc": 0,
     *               "cny": 0,
     *               "ltc": 0,
     *               "eth": 0,
     *               "etc": 0
     *           },
     *           "free": {
     *               "btc": 100000000,
     *               "cny": 99781906.08,
     *               "ltc": 100000000,
     *               "eth": 100002473.38332,
     *               "etc": 100000000
     *           },
     *           "freezed": {
     *               "btc": 0,
     *               "cny": 445,
     *               "ltc": 0,
     *               "eth": 0,
     *               "etc": 0
     *           },
     *           "union_fund": {
     *               "btc": 0,
     *               "ltc": 0
     *           }
     *       }
     *    }
     * }
     *
     */
    {
        $parametersArray = array('apiKey' => $this->apiKey);

        $data = $this->postRequest('getuserinfo', $parametersArray);

        return $data;
    }


    public function submitTradeOrder($symbol, $side, $price, $amount, $advanced=false)
    /*
     * Trade (buy/sell)
     *
     * Set $advanced to false if not advanced order, else pass an object.
     *
     * $side is case sensitive on the API side, must be 'Buy' or 'Sell' but
     * here we deal with all other cases so you can call this method using
     * $side in any case.
     *
     * If you get an orderId the order has made it into the Order Book.
     *
     * API returns:
     *
     * {
     *     "code": 200,
     *     "message": "success",
     *     "data": {
     *         "orderId": "298"
     *     }
     * }
     *
     */
    {

        // Deal with $side case sensitivity
        $side = strtolower($side);
        if ($side == 'buy') {
            $side = 'Buy';
        }
        if ($side == 'sell') {
            $side = 'Sell';
        }

        // Advanced Order
        if ($advanced) {
            $parametersArray = array(
                'quantity' => $amount,
                'apiKey' => $this->apiKey,
                'price' => $price,
                'symbol' => $symbol,
                'priceTypeId' => 1,
                'orderType' => $side,
                'isAdvanceOrder' => 1,
                'takeProfit' => $advanced->takeProfit,
                'stopLoss' => $advanced->stopLoss,
                'triggerPrice' => $advanced->triggerPrice
            );

        // Standard Order
        } else {
            $parametersArray = array(
                'quantity' => $amount,
                'apiKey' => $this->apiKey,
                'price' => $price,
                'symbol' => $symbol,
                'priceTypeId' => 1,
                'orderType' => $side,
                'isAdvanceOrder' => 0,
            );
        }

        $data = $this->postRequest('createorder', $parametersArray);

        return $data;
    }


    public function checkOrders($symbol, $orderId = -1)
    /*
     * Check on order status.
     *
     * See also method: getOrderStatusString($statusId) for what various
     * statuses mean.
     *
     * API returns:
     *
     * {
     *	    "code": 200,
     *	    "message": "success",
     *	    "data": [{
     *	    	"amount": 48.21,
     *	    	"avgPrice": 80.8,
     *	    	"createDate": 1467186908787,
     *	    	"orderId": 243506,
     *	    	"price": 100,
     *	    	"status": 5,
     *	    	"type": "buy"
     *	    }]
     * }
     *
     */
    {
        $parametersArray = array(

            'apiKey' => $this->apiKey,
            'symbol' => $symbol,
            'orderId' => $orderId
        );

        $data = $this->postRequest('getorderinfo', $parametersArray);

        return $data;
    }


    public function checkOrdersByStatus($symbol, $statusId, $interval)
    /*
     * Use this method instead of checkOrders if you only want to see
     * orders of certain type.
     *
     * API returns:
     *
     *   {
     *      "code": 200,
     *      "message": "success",
     *      "data": [
     *          {
     *              "Amount": 0.14,
     *              "Avg_price": 82.3,
     *              "Create_date": 1476082005827,
     *              "orderId": 303967,
     *              "price": 82.3,
     *              "status": 4,
     *              "type": "buy"
     *          },
     *          {
     *              "Amount": 1,
     *              "Avg_price": 88,
     *              "Create_date": 1476347610387,
     *              "orderId": 311646,
     *              "price": 88,
     *              "status": 4,
     *              "type": "buy"
     *          }
     *      ]
     *   }
     */
    {
        $parametersArray = array(

            'apiKey' => $this->apiKey,
            'statusId' => $statusId,
            'symbol' => $symbol,
            'interval' => $interval
        );

        $data = $this->postRequest('getorderbystatus', $parametersArray);

        return $data;
    }


    public function cancelOrder($symbol, $orderId)
    /*
     * API returns:
     *
     * {
     *	    "code": 200,
     *	    "message": "success",
     *	    "data": [{
     *	    	"orderId": 243506,
     *	    }]
     * }
     */
    {
        $parametersArray = array(

            'apiKey' => $this->apiKey,
            'orderId' => $orderId,
            'symbol' => $symbol,
        );

        $data = $this->postRequest('cancelorder', $parametersArray);

        return $data;
    }

    ////////////////////////////////////////////////////////////////////////////

    public function getAvailablePairs()
    {
        return ['CNY_BTC', 'CNY_ETC', 'CNY_ETH'];
    }


    public function getOrderStatusString($statusId)
    {
        $orderStatusStrings = [
             "1" => "Pending",
             "2" => "Active",
             "3" => "Partially Filled",
             "4" => "Completed",
             "5" => "Canceled",
             "6" => "Error",
             "7" => "Suspended",
             "8" => "Trigger Pending",
             "9" => "Stop Loss Pending",
            "10" => "Processing",
            "11" => "Expired",
            "12" => "Cancelling",
        ];

        $result = false;
        if (isset($orderStatusStrings["$statusId"])) {
            $result = $orderStatusStrings["$statusId"];
        }

        return $result;

    }


    // Signature
    public function getSignature($parameters)
    {
        ksort($parameters);
        $signature = "";
        while ($key = key($parameters)) {
            $signature .= $key . "=" . $parameters[$key] . "&";
            next($parameters);
        }
        $signature = $signature . "secretKey=" . $this->secretKey;

        $signature = strtoupper(md5($signature));

        return $signature;
    }


    // Get request
    private function getRequest($action, $parameters = null)
    {
        // Url
        $url = $this->apiEndPoint.$action;

        // Curl init
        $ch = curl_init();

        // Full url for 'get' request
        if ($parameters){

            $fullUrl = $url . '?' . http_build_query($parameters);
        } else {
            $fullUrl = $url;
        }

        // Curl options
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        // Execution
        $curlResult = curl_exec($ch);
        list($headers, $response) = explode("\r\n\r\n", $curlResult, 2);

        // Json format
        $json = json_decode($response, true);

        return $json;
    }


    // Post request
    private function postRequest($action, $parameters = null)
    {
        // Url
        $url = $this->apiEndPoint.$action;

        // Signature
        $parameters['sign'] = $this->getSignature($parameters);

        // Header
        $headers = $this->headers;

        // Curl init
        $ch = curl_init();

        // Curl configuration
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        // Execution
        $curlResult = curl_exec($ch);
        list($headers, $response) = explode("\r\n\r\n", $curlResult, 2);

        // Json format
        $json = json_decode($response, true);

        return $json;
    }

}
