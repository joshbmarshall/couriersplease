<?php

namespace Cognito\CouriersPlease;

/**
 * A Quote, returned from a pricing request
 *
 * @package Cognito\CouriersPlease
 * @author Josh Marshall <josh@jmarshall.com.au>
 *
 * @property string $product_id The Couriers Please product / shipment type to send this parcel
 * @property string $product_type The Couriers Please name of the product
 * @property string $eta The estimated time to delivery
 * @property string $pickup_cutoff_time The latest pickup can occur for delivery eta
 * @property float $price_inc_gst
 * @property float $price_exc_gst
 * @property float $weight The charged weight (greater of physical or volumetric)
 */
class Quote {

    public $product_id;
    public $product_type;
    public $eta;
    public $pickup_cutoff_time;
    public $price_inc_gst;
    public $price_exc_gst;
    public $weight;
    public $raw_details = [];

    public function __construct($details, $fuel_levy) {
        $this->raw_details = $details;
        $this->product_id = $details['RateCardCode'];
        $this->product_type = $details['RateCardDescription'];
        $this->eta = $details['ETA'];
        $this->pickup_cutoff_time = $details['PickupCutOffTime'];
        if (!floatval($details['CalculatedFuelCharge'])) {
            $fuel_levy = 0;
        }
        $this->price_exc_gst = round((floatval($details['CalculatedFreightCharge']) * (100 + $fuel_levy) / 100) + floatval($details['InsuranceAmt']), 2);
        $this->price_inc_gst = round($this->price_exc_gst * 1.1, 2);
        $this->weight = $details['Weight'];
    }
}
