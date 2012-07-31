<?php

/**
 * A single comment for a booking.
 */
class BookingComment {
    var $id;
    var $bookingId;
    var $comment;
    var $commentType;
    var $createdBy;
    var $createdDate;
    
    /* user created comment */
    const COMMENT_TYPE_USER = 'user';
    /* system created comment */
    const COMMENT_TYPE_AUDIT = 'audit';
  
    /**
     * Default constructor.
     * $bookingId : booking id (0 if not yet saved)
     * $comment : text comment
     * $commentType : type of comment
     * $createdBy : user creating comment (defaults to current user if not specified)
     * $createdDate : date comment was created (defaults to now if not specified)
     */
    function BookingComment($bookingId, $comment, $commentType, $createdBy = null, $createdDate = null) {
        if ($createdBy == null) {
            $current_user = wp_get_current_user();
            $createdBy = $current_user->user_login;
        }
        if ($createdDate == null) {
            $createdDate = new DateTime();
        }
        $this->bookingId = $bookingId;
        $this->comment = $comment;
        $this->commentType = $commentType;
        $this->createdBy = $createdBy;
        $this->createdDate = $createdDate;
    }
    
    /**
     * Adds this comment to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this row will be added
     */
    function addSelfToDocument($domtree, $parentElement) {
        // create the root element for this object
        $xmlRoot = $parentElement->appendChild($domtree->createElement('comment'));
    
        $xmlRoot->appendChild($domtree->createElement('id', $this->id));
        $xmlRoot->appendChild($domtree->createElement('bookingid', $this->bookingId));
        $commentNode = $xmlRoot->appendChild($domtree->createElement('value'));
        $commentNode->appendChild($domtree->createTextNode($this->comment));
        $xmlRoot->appendChild($domtree->createElement('commentType', $this->commentType));
        $xmlRoot->appendChild($domtree->createElement('createdBy', $this->createdBy));
        $xmlRoot->appendChild($domtree->createElement('createdDate', $this->createdDate->format('D, d M Y g:i a')));
    }

    /** 
      Generates the following xml:
        <comment>
            <id>3</id>
            <bookingid>21</bookingid>
            <value>This is a comment</value>
            <commentType>user</commentType>
            <createdBy>admin</createdBy>
            <createdDate>Tue, 12 Jun 2012 04:29 am</createdDate>
        </comment>
     */
    function toXml() {
        /* create a dom document with encoding utf8 */
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $this->addSelfToDocument($domtree, $domtree);
        return $domtree->saveXML();
    }
}

?>