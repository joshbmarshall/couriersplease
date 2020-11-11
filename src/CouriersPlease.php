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

    /**
     * Start a new shipment for lodging or quoting
     * @return \Cognito\CouriersPlease\Shipment
     */
    public function newShipment() {
        return new Shipment($this);
    }

    /**
     * Start a new pickup for booking the driver
     * @return \Cognito\CouriersPlease\Pickup
     */
    public function newPickup($data = []) {
        return new Pickup($this, $data);
    }

    private function buildurl($url) {
        if ($this->test_mode) {
            return 'https://api-test.couriersplease.com.au/' . $url;
        }
        return 'https://api.couriersplease.com.au/' . $url;
    }

    public function sendPostRequest($url, $request) {
        return $this->sendRequest($url, $request, true);
    }

    public function sendGetRequest($url, $request) {
        return $this->sendRequest($url, $request, false);
    }

    private function sendRequest($url, $request, $post_request) {
        $encoded = json_encode($request);

        $ch = curl_init();
        if ($post_request) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
        } else {
            if ($request) {
                $url .= '?' . http_build_query($request);
            }
        }
        curl_setopt($ch, CURLOPT_URL, $this->buildurl($url));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_USERPWD, $this->account_number . ':' . $this->api_key);
        $result = curl_exec($ch);
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $return_data = json_decode($result, true);
        if ($status_code == 200) {
            return $return_data;
        }
        if ($return_data) {
            if ($return_data['responseCode'] == 'SUCCESS') {
                return $return_data;
            }
            throw new \Exception($return_data['msg'] . json_encode($return_data['data']));
        }
        throw new \Exception($result);
    }

    /**
     * Print the consignment label(s) by consignment number
     * @param string $consignment_number
     * @return void
     * @throws \Exception
     */
    public function printLabels($consignment_number) {
        $shipment = new Shipment($this);
        $shipment->shipment_id = $consignment_number;
        return $shipment->getLabel();
    }

    /**
     * Cancel the shipment by consignment number
     * @param string $consignment_number
     * @return void
     * @throws \Exception
     */
    public function deleteShipment($consignment_number) {
        $shipment = new Shipment($this);
        $shipment->shipment_id = $consignment_number;
        $shipment->cancelShipment();
    }
}
