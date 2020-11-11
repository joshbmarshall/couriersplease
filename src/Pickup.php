<?php

namespace Cognito\CouriersPlease;

/**
 * Description of a number of parcels, pickup address and optionally delivery address for booking a driver
 *
 * @package Cognito\CouriersPlease
 * @author Josh Marshall <josh@jmarshall.com.au>
 *
 * @property \DateTime $pickup_time The date and time the parcel is ready for delivery. Leave blank for "NOW"
 * @property string $contact_name Pickup contact name
 * @property string $contact_email Pickup contact email
 * @property Address $pickup_address
 * @property Address $deliver_address Optional delivery address - required if only one parcel in the pickup
 * @property Parcel[] $parcels Require the weight and consignment code elements
 * @property string $job_number The job number for pickup, once driver is booked
 */
class Pickup {
    private $_couriersplease;
    public $pickup_time;
    public $contact_name;
    public $contact_email;
    public $pickup_address;
    public $deliver_address;
    public $parcels = [];
    public $job_number = '';
    public $raw_details = [];

    public function __construct($api, $values = []) {
        $this->_couriersplease = $api;
        $this->raw_details = $values;
        foreach ($values as $key => $data) {
            if (!property_exists($this, $key)) {
                continue;
            }
            $this->$key = $data;
        }
        if (!$this->pickup_time) {
            $this->pickup_time = new \DateTime('now');
        }
    }

    /**
     * Add a parcel to the list
     * @param Parcel $parcel
     * @return $this
     */
    public function addParcel($parcel) {
        $this->parcels[] = $parcel;
        return $this;
    }

    /**
     * Book pickup and return the job number
     * @return string
     */
    public function bookPickup() {
        $consignments = [];
        $itemcount = count($this->parcels);
        $total_weight = 0;
        $consignmentCode = '';
        foreach ($this->parcels as $parcel) {
            if (!array_key_exists($parcel->tracking_consignment_id, $consignments)) {
                $consignments[$parcel->tracking_consignment_id] = $parcel->tracking_consignment_id;
                $consignmentCode = $parcel->tracking_consignment_id;
            }
            $total_weight += $parcel->weight;
        }
        $consignmentCount = count($consignments);
        $pickup_address_1 = array_shift($this->pickup_address->lines) ?: '';
        $pickup_address_2 = array_shift($this->pickup_address->lines) ?: '';
        $pickup_address_3 = array_shift($this->pickup_address->lines) ?: '';
        $request = [
            'readyDateTime' => $this->pickup_time->format('Y-m-d h:i a'),
            'contactName' => $this->contact_name,
            'contactEmail' => $this->contact_email,
            'consignmentCount' => $consignmentCount,
            'totalItemCount' => $itemcount,
            'totalWeight' => $total_weight,
            'pickup' => [
                'phoneNumber' => $this->pickup_address->phone,
                'companyName' => $this->pickup_address->business_name,
                'address1' => $pickup_address_1,
                'address2' => $pickup_address_2,
                'address3' => $pickup_address_3,
                'suburb' => $this->pickup_address->suburb,
                'postcode' => $this->pickup_address->postcode,
            ],

        ];
        if ($consignmentCount == 1) {
            $request['consignmentCode'] = $consignmentCode;
            $deliver_address_1 = array_shift($this->deliver_address->lines) ?: '';
            $deliver_address_2 = array_shift($this->deliver_address->lines) ?: '';
            $request['delivery'] = [
                'companyName' => $this->deliver_address->business_name,
                'address1' => substr($deliver_address_1, 0, 19),
                'address2' => substr($deliver_address_2, 0, 19),
                'suburb' => $this->deliver_address->suburb,
                'postcode' => $this->deliver_address->postcode,
            ];
        }

        $response = $this->_couriersplease->sendPostRequest('v2/domestic/bookPickup', $request);
        $this->job_number = $response['data']['jobNumber'];
        return $this->job_number;
    }
}
