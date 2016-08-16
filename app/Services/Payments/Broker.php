<?php

namespace App\Services\Payments;

use App\User;
use DateTime;
use PayPal\Api\Agreement;
use PayPal\Api\Payer;
use PayPal\Api\Plan;
use PayPal\Rest\ApiContext;

class Broker {

    /**
     * @var ApiContext
     */
    protected $paypalContext;

    /**
     * Broker constructor.
     *
     * @param ApiContext $paypalContext
     */
    public function __construct( ApiContext $paypalContext ) {
        $this->paypalContext = $paypalContext;
    }

    /**
     * @param string $plan
     * @param string $frequency
     *
     * @return string
     */
    public function setupSubscription( $plan, $frequency ) {

        $planIds = array(
            'personal-month' => '',
            'personal-year' => 'P-96H91767A9138750XSLK22OI',
            'developer-month' => 'P-2240095086982242JSLKR3JY',
            'developer-year' => 'P-5YT84357S6659264YSLKWEPQ',
            'agency-month' => 'P-2BP759689K9230608SLLAAHI',
            'agency-year' => 'P-4EY75214JP612711JSLLEO6Y',
        );

        if(! isset($planIds["{$plan}-$frequency"])) {
            throw new \InvalidArgumentException("Unknown plan & frequency argument combination.");
        }

        $planId = $planIds["{$plan}-$frequency"];

        $agreement = new Agreement();
        $agreement->setName('Boxzilla Premium Agreement')
            ->setDescription('Billing agreement for Boxzilla Premium')
            ->setStartDate((new Datetime('+1 minute'))->format(DateTime::ISO8601));

        $plan = new Plan();
        $plan->setId($planId);
        $agreement->setPlan($plan);

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        $agreement->setPayer($payer);

        $agreement = $agreement->create($this->paypalContext);
        $approvalUrl = $agreement->getApprovalLink();
        return $approvalUrl;
    }


}