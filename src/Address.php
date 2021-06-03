<?php

namespace Cognito\CouriersPlease;

/**
 * An address
 *
 * @package Cognito\CouriersPlease
 * @author Josh Marshall <josh@jmarshall.com.au>
 *
 * @property string $type
 * @property string $first_name
 * @property string $last_name
 * @property string $name
 * @property string $business_name
 * @property string[] $lines
 * @property string $suburb
 * @property string $state
 * @property string $postcode
 * @property string $phone
 * @property string $email
 * @property string $country
 */
class Address {

    public $type          = '';
    public $first_name    = '';
    public $last_name     = '';
    public $name          = '';
    public $business_name = '';
    public $lines         = [];
    public $suburb        = '';
    public $state         = '';
    public $postcode      = '';
    public $phone         = '';
    public $email         = '';
    public $country       = 'AU';
    public $raw_details = [];

    public function __construct($details = []) {
        $this->raw_details = $details;
        foreach ($details as $key => $data) {
            if (!property_exists($this, $key)) {
                continue;
            }
            $this->$key = $data;
        }
        if (!$this->name) {
            $this->name = trim($this->first_name . ' ' . $this->last_name);
        }
        if (!$this->first_name) {
            $parts = explode(' ', $this->name, 2);
            if (count($parts) > 1) {
                $this->first_name = $parts[0];
                $this->last_name = $parts[1];
            } else {
                $this->first_name = $this->name;
                $this->last_name = $this->name;
            }
        }
    }

    /**
     * Check field sizes and throw exception if one is too large
     * @throws \Exception
     */
    public function validateData() {
        $fieldSizes = [
            'first_name'    => 50,
            'last_name'     => 50,
            'name'          => 50,
            'business_name' => 50,
            'suburb'        => 50,
            'state'         => 50,
            'postcode'      => 50,
            'phone'         => 20,
            'email'         => 50,
            'country'       => 50,
        ];
        foreach ($fieldSizes as $field => $max_length) {
            if (strlen($this->$field) > $max_length) {
                throw new \Exception('Field ' . $field . ' exceeds max length of ' . $max_length);
            }
        }
        foreach ($this->lines as $address_line) {
            if (strlen($address_line) > 100) {
                throw new \Exception('Address exceeds max length of 100');
            }
        }
    }
}
