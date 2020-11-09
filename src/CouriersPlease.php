<?php

namespace Cognito\CouriersPlease;

/**
 * Interact with the Couriers Please API
 *
 * @package Cognito
 * @author Josh Marshall <josh@jmarshall.com.au>
 */
class CouriersPlease {

    private $api_key        = null;
    private $account_number = null;
    private $test_mode      = null;

    /**
     *
     * @param string $api_key The Couriers Please API Key
     * @param string $account_number The Couriers Please Account number
     * @param bool $test_mode Whether to use test mode or not
     */
    public function __construct($api_key, $account_number, $test_mode = false) {
        $this->api_key = $api_key;
        $this->account_number = $account_number;
        $this->test_mode = $test_mode;
    }
}
