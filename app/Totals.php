<?php

namespace App;

use Illuminate\Support\Facades\DB;

class Totals {

    public $new_users_this_month = 0;
    public $new_users_last_month = 0;
    public $total_revenue_this_month = 0.00;
    public $total_revenue_last_month = 0.00;
    public $new_licenses_this_month = 0;
    public $new_licenses_last_month = 0;


    /**
     * @return Totals
     */
    public static function query() {
        $results = DB::select(
            DB::raw(
<<<SQL
            SELECT 
            ( SELECT COUNT(*) FROM users u WHERE u.created_at > DATE_SUB(CURDATE(), INTERVAL 30 DAY) ) AS new_users_this_month,
            ( SELECT COUNT(*) FROM users u WHERE u.created_at < DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND u.created_at > DATE_SUB(CURDATE(), INTERVAL 60 DAY) ) AS new_users_last_month,
            ( SELECT COUNT(*) FROM licenses l WHERE l.created_at > DATE_SUB(CURDATE(), INTERVAL 30 DAY) ) AS new_licenses_this_month,
            ( SELECT COUNT(*) FROM licenses l WHERE l.created_at < DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND l.created_at > DATE_SUB(CURDATE(), INTERVAL 60 DAY) ) AS new_licenses_last_month,
            ( SELECT SUM(subtotal) FROM payments p WHERE p.created_at > DATE_SUB(CURDATE(), INTERVAL 30 DAY) ) AS total_revenue_this_month,
            ( SELECT SUM(subtotal) FROM payments p WHERE p.created_at < DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND p.created_at > DATE_SUB(CURDATE(), INTERVAL 60 DAY) ) AS total_revenue_last_month
SQL
          )
        );

        $results = array_pop( $results );

        $instance = new Totals();
        foreach( $results as $property => $value  ) {
            $instance->$property = $value;
        }

       return $instance;
    }

    /**
     * @param mixed $new
     * @param mixed $old
     *
     * @return float
     */
    public function calculatePercentageDifference( $new, $old ) {
        return round( $new / $old * 100 - 100 );
    }
}