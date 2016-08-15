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

class CreatePlan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paypal:create-plan {--name=} {--price=} {--frequency=}';

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
        $description = sprintf('%sly plan for %s', ucfirst($this->option('frequency')), $this->option('name'));

        // Create a new instance of Plan object
        $plan = new Plan();
        $plan->setName($this->option('name'))
            ->setDescription($description)
            ->setType('infinite');

        $paymentDefinition = new PaymentDefinition();
        $paymentDefinition->setName('Regular Payments')
            ->setType('REGULAR')
            ->setFrequency(ucfirst($this->option('frequency')))
            ->setFrequencyInterval("1")
            ->setCycles("0")
            ->setAmount(new Currency(array('value' => $this->option('price'), 'currency' => 'USD')));

        $merchantPreferences = new MerchantPreferences();
        $merchantPreferences->setReturnUrl(url('/paypal/success'))
            ->setCancelUrl(url('/paypal/error'))
            ->setAutoBillAmount("yes")
            ->setInitialFailAmountAction("CONTINUE")
            ->setMaxFailAttempts("0");
        $plan->setPaymentDefinitions(array($paymentDefinition));
        $plan->setMerchantPreferences($merchantPreferences);

        $plan = $plan->create($this->paypalContext);
        $this->info(sprintf("Plan created: %s", $plan->id));

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
        $plan->update($patchRequest, $this->paypalContext);
        $this->info("Plan state changed to ACTIVE");
    }
}
