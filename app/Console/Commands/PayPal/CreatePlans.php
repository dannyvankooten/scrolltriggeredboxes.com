<?php

namespace App\Console\Commands\PayPal;

use Illuminate\Console\Command;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Common\PayPalModel;
use PayPal\Rest\ApiContext;
use PayPal\Api\Currency;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\Plan;

class CreatePlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paypal:create-plans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new billing plan in PayPal';

    /**
     * @var ApiContext
     */
    protected $paypalContext;

    /**
     * Create a new command instance.
     *
     * @param ApiContext $paypalContext
     */
    public function __construct(ApiContext $paypalContext)
    {
        parent::__construct();

        $this->paypalContext = $paypalContext;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $plans = [];

        // the core plans (current)
        $plans[] = [
            'id' => 'boxzilla-personal-monthly',
            'name' => 'Boxzilla Personal (monthly)',
            'amount' => 6,
            'interval' => 'month',
        ];
        $plans[] = [
            'id' => 'boxzilla-personal-yearly',
            'name' => 'Boxzilla Personal (yearly)',
            'amount' => 60,
            'interval' => 'year',
        ];
        $plans[] = [
            'id' => 'boxzilla-developer-monthly',
            'name' => 'Boxzilla Developer (monthly)',
            'amount' => 20,
            'interval' => 'month',
        ];
        $plans[] = [
            'id' => 'boxzilla-developer-yearly',
            'name' => 'Boxzilla Developer (yearly)',
            'amount' => 200,
            'interval' => 'year',
        ];

        foreach( $plans as $planConfig ) {

            // Create a new instance of Plan object
            $paypalPlan = new Plan();
            $paypalPlan->setId($planConfig['id']);
            $paypalPlan->setName($planConfig['name'])
                ->setDescription($planConfig['name'])
                ->setType('INFINITE');

            $paymentDefinition = new PaymentDefinition();
            $paymentDefinition->setName('Regular Payments')
                ->setType('REGULAR')
                ->setFrequency(strtoupper($planConfig['interval']))
                ->setFrequencyInterval("1")
                ->setCycles("0")
                ->setAmount(new Currency(array('value' => $planConfig['amount'], 'currency' => 'USD')));

            $merchantPreferences = new MerchantPreferences();
            $merchantPreferences->setReturnUrl(domain_url('/licenses/new/paypal', 'account'))
                ->setCancelUrl(domain_url('/license/new', 'account'))
                ->setAutoBillAmount("yes")
                ->setInitialFailAmountAction("CONTINUE")
                ->setMaxFailAttempts("3");
            $paypalPlan->setPaymentDefinitions(array($paymentDefinition));
            $paypalPlan->setMerchantPreferences($merchantPreferences);

            $paypalPlan = $paypalPlan->create($this->paypalContext);
            $this->info(sprintf("Plan %s created: %s", $planConfig['id'], $paypalPlan->id));

            // set plan state to "ACTIVE"
            $patch = new Patch();
            $value = new PayPalModel('{
               "state":"ACTIVE"
             }');
            $patch->setOp('replace')
                ->setPath('/')
                ->setValue($value);
            $patchRequest = new PatchRequest();
            $patchRequest->addPatch($patch);
            $paypalPlan->update($patchRequest, $this->paypalContext);
            $this->info("Plan state changed to ACTIVE");
        }
    }
}
