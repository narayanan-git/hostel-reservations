<?php

/**
 * Class for resetting random booking/allocation data.
 */
class GenerateTestDataContent extends GenerateTestData {

    /** 
     * Default constructor.
     */
    function GenerateTestDataContent() {
        parent::GenerateTestData();
    }

    /**
     * Creates sample test data in the database. Assumes an empty database.
     */
    function reloadTestData() {
        $loadsql = strncasecmp(PHP_OS, 'WIN', 3) == 0 ? "loadsql.cmd" : "loadsql.sh";
        $this->lastCommand[] = exec("sql".DIRECTORY_SEPARATOR."$loadsql sql".DIRECTORY_SEPARATOR."wp_bookingresources_cr.sql");
        $this->lastCommand[] = exec("sql".DIRECTORY_SEPARATOR."$loadsql sql".DIRECTORY_SEPARATOR."wp_booking.sql");
        $this->lastCommand[] = exec("sql".DIRECTORY_SEPARATOR."$loadsql sql".DIRECTORY_SEPARATOR."wp_allocation.sql");
        $this->lastCommand[] = exec("sql".DIRECTORY_SEPARATOR."$loadsql sql".DIRECTORY_SEPARATOR."wp_bookingdates.sql");
        $this->lastCommand[] = exec("sql".DIRECTORY_SEPARATOR."$loadsql sql".DIRECTORY_SEPARATOR."wp_bookingcomment.sql");
        $this->lastCommand[] = exec("sql".DIRECTORY_SEPARATOR."$loadsql sql".DIRECTORY_SEPARATOR."wp_resource_properties_map.sql");
        $this->lastCommand[] = 'Done generating sample data... ';
    }

    /**
     * Runs all unit tests. $this->lastCommand contains all pass/failure messages.
     */
    function runUnitTests() {
        $this->lastCommand[] = self::testNewEmptyDateArray();
    }

    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return WPDEV_BK_PLUGIN_DIR. '/include/generate_test_data_content.xsl';
    }
}

?>
