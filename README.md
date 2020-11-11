# Couriers Please API

Interact with the Couriers Please API

## Installation

Installation is very easy with composer:

    composer require cognito/couriersplease

## Setup

Get a business account at Couriers Please and request API access

## Usage

```
<?php
	$couriersplease = new \Cognito\CouriersPlease\CouriersPlease('Your API Key', 'Your Account Number', $testmode);

	// Create a shipment

	$shipment = $couriersplease->newShipment()
		->setFrom(new \Cognito\CouriersPlease\Address([
			'name' => 'Joe Tester',
			'lines' => [
				'11 MyStreetname Court',
			],
			'suburb' => 'MySuburb',
			'state' => 'QLD',
			'postcode' => '4503',
			'country' => 'AU',
		]))
		->setTo(new \Cognito\CouriersPlease\Address([
			'name' => 'Mary Tester',
			'lines' => [
				'10 ReceiverStreetname St',
			],
			'suburb' => 'HerSuburb',
			'state' => 'NSW',
			'postcode' => '2430',
			'country' => 'AU',
			'phone' => '035555XXXX',
			'email' => 'mary@XXXXX.com.au',
		]))
       	->setInsuranceType('INS2')
		->addParcel(new \Cognito\CouriersPlease\Parcel([
			'item_reference' => 'pkg1',
			'length' => 5,
			'height' => 4,
			'width' => 45,
			'weight' => 0.55,
			'value' => 200,
		]))
		->addParcel(new \Cognito\CouriersPlease\Parcel([
			'item_reference' => 'pkg2',
			'length' => 12,
			'height' => 12,
			'width' => 20,
			'weight' => 1.55,
			'value' => 50,
		]));

	// Get costing for the shipment for the various Couriers Please products available
	$itemQuotes = $shipment->getQuotes();

	foreach ($itemQuotes as $quote) {
		var_dump($quote->product_id);
		var_dump($quote->product_type);
		var_dump($quote->price_inc_gst);
	}

	// Set details about the shipment and lodge it
	$shipment->shipment_reference = 'OurInternalID';
	$shipment->customer_reference_1 = 'INV #12345';
	$shipment->customer_reference_2 = '';
	$shipment->product_id = 'L44'; // The Couriers Please product returned in the Quote
	$shipment->delivery_instructions = 'Leave in a dry place out of the sun';

	$shipment->lodgeShipment();

	var_dump($shipment->shipment_id);

	// Print the labels for all the parcels in a shipment
	if ($shipment->shipment_id) {
		$label = $shipment->getLabel();
		if ($label) {
			while(ob_get_level()) {
				ob_get_clean();
			}
			header('Content-Type: application/pdf');
			die($label);
		}
	}

    // Or to print the label from the api by consignment number
	$label = $couriersplease->printLabels($consignment_number);
	if ($label) {
		while(ob_get_level()) {
			ob_get_clean();
		}
		header('Content-Type: application/pdf');
		die($label);
	}

    // Cancel the shipment
    $shipment->cancelShipment();

    // Or to cancel from the api by consignment number
    $couriersplease->deleteShipment($consignment_number);

    // Book a driver to pick up
    $pickup = $couriersplease->newPickup([
        'contact_name' => 'Sender Contact Name',
        'contact_email' => 'Sender Email Address',
        'pickup_address' => new \Cognito\CouriersPlease\Address([
			'name'          => 'Sender Contact Name',
			'business_name' => 'Sender Business Name',
			'lines' => [
				'Sender Address Line 1',
				'Sender Address Line 2',
			],
			'suburb'   => 'Sender Address Suburb',
			'state'    => 'Sender Address State',
			'postcode' => 'Sender Address Postcode',
			'phone'    => 'Sender Address Phone',
			'country'  => 'Sender Address Country',
			'email'    => 'Sender Email ',
		],
        'deliver_address' => new \Cognito\CouriersPlease\Address([
			'name'          => 'Receiver Contact Name',
			'business_name' => 'Receiver Business Name',
			'lines' => [
				'Receiver Address Line 1',
				'Receiver Address Line 2',
			],
			'suburb'   => 'Receiver Address Suburb',
			'state'    => 'Receiver Address State',
			'postcode' => 'Receiver Address Postcode',
			'phone'    => 'Receiver Address Phone',
			'country'  => 'Receiver Address Country',
			'email'    => 'Receiver Email ',
		]);
    ]);
    $pickup->addParcel(new \Cognito\CouriersPlease\Parcel([
        'item_reference' => 'pkg1',
        'length' => 5,
        'height' => 4,
        'width' => 45,
        'weight' => 0.55,
        'value' => 200,
    ]))
    ->addParcel(new \Cognito\CouriersPlease\Parcel([
        'item_reference' => 'pkg2',
        'length' => 12,
        'height' => 12,
        'width' => 20,
        'weight' => 1.55,
        'value' => 50,
    ]));
    $job_number = $pickup->bookPickup();

```
