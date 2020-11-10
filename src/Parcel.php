<?php

namespace Cognito\CouriersPlease;

/**
 * Description of a parcel or multiple parcels of the same size and weight
 *
 * @package Cognito\CouriersPlease
 * @author Josh Marshall <josh@jmarshall.com.au>
 *
 * @property integer $length Length of the parcel in centimeters
 * @property integer $width Length of the parcel in centimeters
 * @property integer $height Length of the parcel in centimeters
 * @property double $weight Physical weight of the parcel in kilograms
 * @property bool $authority_to_leave Whether the parcel can be left without signature
 * @property string $tracking_consignment_id The consignment number for this parcel
 * @property integer $qty The number of parcels of this size in the shipment (optional, defaults to 1)
 */
class Parcel {
    public $length;
    public $width;
    public $height;
    public $weight;
    public $authority_to_leave = false;
    public $tracking_consignment_id;
    public $qty = 1;
    public $raw_details = [];

    public function __construct($values = []) {
        $this->raw_details = $values;
        foreach ($values as $key => $data) {
            if (!property_exists($this, $key)) {
                continue;
            }
            $this->$key = $data;
        }
    }
}
