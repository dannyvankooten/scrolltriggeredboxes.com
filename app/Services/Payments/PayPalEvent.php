<?php

namespace App\Services\Payments;

class PayPalEvent {

    /**
     * @var object
     */
    private $data;

    /**
     * PayPalEvent constructor.
     *
     * @param object $data
     */
    public function __construct( $data ) {
        $this->data = $data;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get( $name ) {
        return $this->data->$name;
    }

    /**
     * @param string $name
     *
     * @param $value
     */
    public function __set( $name, $value ) {
        $this->data->$name = $value;
    }
}