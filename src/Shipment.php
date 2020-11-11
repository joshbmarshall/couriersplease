<?php

namespace Cognito\CouriersPlease;

/**
 * A shipment, made up of one or more parcels
 *
 * @package Cognito\CouriersPlease
 * @author Josh Marshall <josh@jmarshall.com.au>
 *
 * @property CouriersPlease $_couriersplease
 * @property string $shipment_reference
 * @property string $customer_reference_1
 * @property string $customer_reference_2
 * @property bool $email_tracking_enabled
 * @property Address $to
 * @property Address $from
 * @property Parcel[] $parcels
 * @property string $product_id The Couriers Please product to use for this shipment
 * @property string $shipment_id The Couriers Please generated id when lodged
 * @property string $reference_number The Couriers Please reference number when lodged
 * @property string $job_number The Couriers Please booking job number when lodged
 * @property \DateTime $shipment_lodged_at The time the shipment was lodged
 */
class Shipment {

    private $_couriersplease;
    public $shipment_reference;
    public $customer_reference_1 = '';
    public $customer_reference_2 = '';
    public $email_tracking_enabled = true;
    public $from;
    public $to;
    public $parcels = [];
    public $delivery_instructions = '';
    public $insurance_type = '';

    public $product_id;
    public $shipment_id;
    public $shipment_lodged_at;

    public function __construct($api) {
        $this->_couriersplease = $api;
    }

    /**
     * Add the To address
     * @param Address $data The address to deliver to
     * @return $this
     */
    public function setTo($data) {
        $this->to = $data;
        return $this;
    }
    /**
     * Add the From address
     * @param Address $data The address to send from
     * @return $this
     */
    public function setFrom($data) {
        $this->from = $data;
        return $this;
    }

    public function addParcel($data) {
        $this->parcels[] = $data;
        return $this;
    }

    public function setInsuranceType($type) {
        $this->insurance_type = $type;
        return $this;
    }

    /**
     * Does this shipment have authority to leave
     * @return bool
     */
    public function isATL() {
        foreach ($this->parcels as $parcel) {
            if ($parcel->authority_to_leave) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @return Quote[]
     * @throws \Exception
     */
    public function getQuotes() {
        $request = [
            'fromSuburb' => $this->from->suburb,
            'fromPostcode' => $this->from->postcode,
            'toSuburb' => $this->to->suburb,
            'toPostcode' => $this->to->postcode,
            'insuranceCategory' => $this->insurance_type,
            'items' => [],
        ];
        foreach ($this->parcels as $parcel) {
            $item = [
                'quantity' => $parcel->qty,
                'length' => intval($parcel->length),
                'height' => intval($parcel->height),
                'width' => intval($parcel->width),
                'physicalWeight' => floatval($parcel->weight),
            ];
            $request['items'][] = $item;
        }
        $response = $this->_couriersplease->sendPostRequest('v2/domestic/quote', $request);
        $quotes = [];
        foreach ($response['data'] as $quote) {
            $quotes[] = new Quote($quote);
        }
        return $quotes;
    }

    public function lodgeShipment() {
        $request = [
            'pickupDeliveryChoiceID'      => null,
            'pickupFirstName'             => $this->from->first_name,
            'pickupLastName'              => $this->from->last_name,
            'pickupCompanyName'           => $this->from->business_name,
            'pickupEmail'                 => $this->from->email,
            'pickupAddress1'              => $this->from->lines[0],
            'pickupAddress2'              => $this->from->lines[1],
            'pickupSuburb'                => $this->from->suburb,
            'pickupState'                 => $this->from->state,
            'pickupPostcode'              => $this->from->postcode,
            'pickupPhone'                 => $this->from->phone,
            'pickupIsBusiness'            => $this->from->business_name ? true : false,
            'destinationDeliveryChoiceID' => null,
            'destinationFirstName'        => $this->to->first_name,
            'destinationLastName'         => $this->to->last_name,
            'destinationCompanyName'      => $this->to->business_name,
            'destinationEmail'            => $this->to->email,
            'destinationAddress1'         => $this->to->lines[0],
            'destinationAddress2'         => $this->to->lines[1],
            'destinationSuburb'           => $this->to->suburb,
            'destinationState'            => $this->to->state,
            'destinationPostcode'         => $this->to->postcode,
            'destinationPhone'            => $this->to->phone,
            'destinationIsBusiness'       => $this->to->business_name ? true : false,
            'contactFirstName'            => $this->to->first_name,
            'contactLastName'             => $this->to->last_name,
            'contactCompanyName'          => $this->to->business_name,
            'contactEmail'                => $this->to->email,
            'contactAddress1'             => $this->to->lines[0],
            'contactAddress2'             => $this->to->lines[1],
            'contactSuburb'               => $this->to->suburb,
            'contactState'                => $this->to->state,
            'contactPostcode'             => $this->to->postcode,
            'contactPhone'                => $this->to->phone,
            'contactIsBusiness'           => $this->to->business_name ? true : false,
            'referenceNumber'             => $this->shipment_reference,
            'termsAccepted'               => true,
            'dangerousGoods'              => false,
            'rateCardId'                  => $this->product_id,
            'specialInstruction'          => $this->delivery_instructions,
            'isATL'                       => $this->isATL(),
            'InsuranceCategory'           => $this->insurance_type,
            'items'                       => [],
        ];
        foreach ($this->parcels as $parcel) {
            $item = [
                'quantity'       => intval($parcel->qty),
                'length'         => intval($parcel->length),
                'height'         => intval($parcel->height),
                'width'          => intval($parcel->width),
                'physicalWeight' => floatval($parcel->weight),
            ];
            $request['items'][] = $item;
        }

        $response = $this->_couriersplease->sendPostRequest('v2/domestic/shipment/create', $request);
        $this->shipment_id = $response['data']['consignmentCode'];
        $this->reference_number = $response['data']['referenceNumber'];
        $this->job_number = $response['data']['jobNumber'];
        $this->shipment_lodged_at = new \DateTime();
        foreach ($this->parcels as $parcel) {
            $parcel->tracking_consignment_id = $this->shipment_id;
        }
    }

    /**
     * Get the labels for this shipment
     * @return binary PDF file contents
     * @throws \Exception
     */
    public function getLabel() {
        $response = $this->_couriersplease->sendGetRequest('v1/domestic/shipment/label', [
            'consignmentNumber' => $this->shipment_id,
        ]);
        return base64_decode($response['data']['label']);
    }

    /**
     * Cancel the shipment
     * @throws \Exception
     */
    public function cancelShipment() {
        $this->_couriersplease->sendPostRequest('v1/domestic/shipment/cancel', [
            'consignmentCode' => $this->shipment_id,
        ]);
    }
}
