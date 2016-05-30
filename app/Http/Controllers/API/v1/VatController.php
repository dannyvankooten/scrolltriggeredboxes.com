<?php

namespace App\Http\Controllers\API\v1;

use Illuminate\Routing\Controller as BaseController;
use Doctrine\DBAL\Query\QueryBuilder;
use DvK\Laravel\Vat\Validator;
use GuzzleHttp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VatController extends BaseController {

    /**
     * @var Validator
     */
    protected $vatValidator;

    /**
     * VatController constructor.
     *
     * @param Validator $vatValidator
     */
    public function __construct( Validator $vatValidator ) {
        $this->vatValidator = $vatValidator;
    }

    /**
     * @param Request $number
     *
     * @return JsonResponse
     */
    public function validate( $number ) {
        $valid = $this->vatValidator->validate( $number );

        return new JsonResponse([
            'valid' => $valid,
            'number' => $number
        ]);
    }

}