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
     * @param int $days
     *
     * @return Totals
     */
    public static function query( $days = 30 ) {

        // TODO: updated_at column for license churn is not entirely true. could be separate column.

        $sql =
<<<SQL
            SELECT 
            ( SELECT DATE_SUB(CURDATE(), INTERVAL %d DAY ) ) AS date_1,
            ( SELECT DATE_SUB(date_1, INTERVAL %d DAY ) ) AS date_2,
            ( SELECT COUNT(*) FROM users u WHERE u.created_at > date_1 ) AS new_users_this_month,
            ( SELECT COUNT(*) FROM users u WHERE u.created_at < date_1 AND u.created_at > date_2 ) AS new_users_last_month,
            ( SELECT COUNT(*) FROM licenses l WHERE l.created_at > date_1 ) AS new_licenses_this_month,
            ( SELECT COUNT(*) FROM licenses l WHERE l.created_at < date_1 AND l.created_at > date_2 ) AS new_licenses_last_month,
            ( SELECT SUM(subtotal) FROM payments p WHERE p.created_at > date_1 ) AS total_revenue_this_month,
            ( SELECT SUM(subtotal) FROM payments p WHERE p.created_at < date_1 AND p.created_at > date_2 ) AS total_revenue_last_month,
            ( SELECT COUNT(*) FROM licenses l WHERE l.deactivated_at IS NOT NULL AND l.status != 'active' AND l.deactivated_at > date_1 ) AS churn_this_month,
            ( SELECT COUNT(*) FROM licenses l WHERE l.deactivated_at IS NOT NULL AND l.status != 'active' AND l.deactivated_at < date_1 AND l.deactivated_at > date_2 ) AS churn_last_month
SQL;

        $query = sprintf( $sql, $days, $days );

        $results = DB::select(DB::raw($query));
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
        if( $old == 0 ) {
            return 0;
        }

        return round( $new / $old * 100 - 100 );
    }
}