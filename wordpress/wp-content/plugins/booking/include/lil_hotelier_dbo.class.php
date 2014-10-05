<?php

/**
 * Database object for little hotelier tables.
 */
class LilHotelierDBO {

    /**
     * Returns all bedsheet data for the given date.
     * $selectedDate : DateTime object
     * Returns raw resultset
     */
    static function fetchBedSheetsFrom($selectedDate, $jobId) {
        global $wpdb;

        // query all our resources (in order)
        $resultset = $wpdb->get_results(
            "SELECT r.room, r.bed_name, c.job_id, c.guest_name, c.checkin_date, c.checkout_date, c.created_date,
                    MAX(c.data_href) as data_href, -- room closures can sometimes have more than one
                    CASE WHEN c.checkout_date = dr.a_date THEN 'CHANGE'
                         WHEN MOD(DATEDIFF(dr.a_date, c.checkin_date), 3) = 0 THEN '3 DAY CHANGE'
                         WHEN c.checkout_date > dr.a_date THEN 'NO CHANGE'
                         ELSE 'EMPTY' END AS bedsheet
               FROM ".$wpdb->prefix."daterange dr 
               JOIN ".$wpdb->prefix."lh_rooms r ON 1 = 1
               LEFT OUTER JOIN ".$wpdb->prefix."lh_calendar c
	             ON r.id = c.room_id 
                AND c.checkout_date >= dr.a_date
                AND c.checkin_date < dr.a_date
              WHERE dr.a_date = '" . $selectedDate->format('Y-m-d') . "'
	            AND (c.job_id = $jobId OR c.job_id IS NULL)
              GROUP BY r.room, r.bed_name, c.job_id, c.guest_name, c.checkin_date, c.checkout_date, 
                    c.created_date, dr.a_date
              ORDER BY r.room, r.bed_name");

error_log( "QUERY: " . $wpdb->last_query );
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        return $resultset;
    }

    /**
     * Returns the latest job with the given type.
     * $jobName name of job, e.g. bedsheets, bedcount
     * Returns matching job recordset or null if no jobs of type found
     */
    static function getLatestJobOfType($jobName) {

        global $wpdb;

        // first find the job id for the most recent job of the given type
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT MAX(job_id) AS job_id
               FROM ".$wpdb->prefix."lh_jobs
              WHERE GREATEST(created_date, last_updated_date) IN (
	                SELECT MAX(GREATEST(created_date, last_updated_date)) 
                      FROM ".$wpdb->prefix."lh_jobs t
	                 WHERE t.name = %s
                       AND t.status = 'completed')", $jobName));
        
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }
        // guaranteed null or int
        $rec = array_shift($resultset);

        // if null, then job hasn't been run yet
        if( $rec->job_id == null) {
            return null;
        }

        // otherwise, retrieve the job details
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT job_id, name, status, created_date, last_updated_date
               FROM ".$wpdb->prefix."lh_jobs
              WHERE job_id = %d", $rec->job_id));
        
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        return array_shift($resultset);
    }
    
}

?>