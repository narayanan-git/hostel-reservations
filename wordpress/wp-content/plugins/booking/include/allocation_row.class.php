<?php

/**
 * Encapsulates one row in the allocation table (one bed for one person)
 * and renders it.
 */
class AllocationRow {
    var $name;
    var $gender;
    var $resource;
    var $showMinDate;   // minimum date to show on the table
    var $showMaxDate;   // maximum date to show on the table
    private $bookingDatePayment = array();  // key = booking date, value = payment amount

    function AllocationRow($name, $gender, $resource) {
        $this->name = $name;
        $this->gender = $gender;
        $this->resource = $resource;
    }
    
    /**
     * Adds a date/payment entry for this allocation row
     * $dt  date string in format dd.MM.yyyy
     * $payment  value of payment for specified date
     */
    function addPaymentForDate($dt, $payment) {
        $this->bookingDatePayment[$dt] = $payment;
    }
    
    /**
     * Returns the minimum date (DateTime) for this allocation.
     */
    function getMinDate() {
        $result = null;
        foreach ($this->bookingDatePayment as $bookingDate => $payment) {
            $bd = DateTime::createFromFormat('!d.m.Y', $bookingDate, new DateTimeZone('UTC'));
            if($result == null || $bd < $result) {
                $result = $bd;
            }
        }
        return $result;
    }
    
    /**
     * Calculates total payment by summing bookingDatePayment
     * Returns: numeric value
     */
    function getTotalPayment() {
        $result = 0;
        foreach ($this->bookingDatePayment as $bookingDate => $payment) {
            $result += $payment;
        }
        return $result;
    }

    /**
     * Adds this allocation row to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this row will be added
     */
    function addSelfToDocument($domtree, $parentElement) {
        // create the root element for this allocation row
        $xmlRoot = $domtree->createElement('allocation');
        $xmlRoot = $parentElement->appendChild($xmlRoot);
    
        $xmlRoot->appendChild($domtree->createElement('name', $this->name));
        $xmlRoot->appendChild($domtree->createElement('gender', $this->gender));
        $xmlRoot->appendChild($domtree->createElement('resource', $this->resource));

        $dateRow = $domtree->createElement('dates');
        $attrTotal = $domtree->createAttribute('total');
        $attrTotal->value = $this->getTotalPayment();
        $dateRow->appendChild($attrTotal);
        
        if($this->showMinDate != null && $this->showMaxDate != null) {
        
            // loop from showMinDate to showMaxDate creating a date element for every day in between
            // set the appropriate state if a booking exists for that date
            $dt = clone $this->showMinDate;
            while ($dt < $this->showMaxDate) {
                $dateElem = $dateRow->appendChild($domtree->createElement('date', $dt->format('d.m.Y')));
                $payment = $this->bookingDatePayment[$dt->format('d.m.Y')];
                if($payment != null) {   // also means that booking exists on this date
                    $attrPayment = $domtree->createAttribute('payment');
                    $attrPayment->value = $payment;
                    $dateElem->appendChild($attrPayment);
    
                    $attrState = $domtree->createAttribute('state');
                    $attrState->value = 'pending';
                    $dateElem->appendChild($attrState);
                
                // TODO: different state when date is in the past?

                } else { // booking doesn't exist
                    $attrPayment = $domtree->createAttribute('payment');
                    $attrPayment->value = 15;  // TODO: add payment rules
                    $dateElem->appendChild($attrPayment);
                    
                    $attrState = $domtree->createAttribute('state');
                    $attrState->value = 'inactive';
                    $dateElem->appendChild($attrState);
                }
                $dt->add(new DateInterval('P1D'));  // increment by day
            }
        }
        $xmlRoot->appendChild($dateRow);
    }
    
    /** 
      Generates the following xml:
        <allocation>
            <name>Megan-1</name>
            <gender>Female</gender>
            <resource>Bed A</resource>
            <dates total="24.90">
                <date payment="12.95" state="checkedin">15.08.2012</date>
                <date payment="12.95" state="pending">16.08.2012</date>
            </dates>
        </allocation>
     */
    function toXml() {
        /* create a dom document with encoding utf8 */
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $this->addSelfToDocument($domtree, $domtree);
        return $domtree->saveXML();
    }
    
}

?>