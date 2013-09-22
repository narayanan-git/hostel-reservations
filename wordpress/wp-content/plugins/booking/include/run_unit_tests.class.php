<?php

/**
 * Executes all unit tests.
 */
class RunUnitTests extends XslTransform {

    // results of executing unit tests
    var $results;   // array() of String

    /** 
     * Default constructor.
     */
    function RunUnitTests() {
        $this->results = array();
    }

    /**
     * Fetches this page in the following format:
     * <view>
     *   <unitTest>...</unitTest>
     *   ...
     * </view>
     */
    function toXml() {
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $xmlRoot = $domtree->appendChild($domtree->createElement('view'));
        foreach ($this->results as $ut) {
            $xmlRoot->appendChild($domtree->createElement('unitTest', $ut));
        }
        return $domtree->saveXML();
    }

    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return WPDEV_BK_PLUGIN_DIR. '/include/run_unit_tests.xsl';
    }

    /**
     * Runs the given test with the specified parameters.
     * $testName : name of test in current class to execute
     */
    function runTest($testName) {
        try {
            call_user_func(array(&$this, $testName));
            $this->results[] = "$testName passed.";
        } catch (Exception $e) {
            $this->results[] = "$testName failed with error ".$e->getMessage();
        }
error_log("test $testName finished");
    }

    /**
     * Asserts the given condition and throws an exception with $message if not.
     * $bool : do nothing if true; if false, throw exception
     * $message : exception message when $bool is false
     */
    function assert($bool, $message) {
        if (! $bool) {
            throw new Exception($message);
        }
    }

    /**
     * Asserts that $expected == $observed and throws an exception if not.
     * $expected : expected value
     * $observed : observed value
     * $message : exception message when $expected != $observed
     */
    function assertEquals($expected, $observed, $message = "") {
        if ($expected != $observed) {
            throw new Exception($message . " Expected: [".$expected."] Observed: [".$observed."]");
        }
    }

   /**
    * Fail-fast with the given message.
    * $message: failure message
    */
    function assertFail( $message ) {
        throw new Exception( "Assertion failed: " . $message);
    }
}

?>
