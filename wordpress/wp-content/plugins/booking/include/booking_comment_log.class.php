<?php

/**
 * Container and transformer for a set of comments.
 */
class BookingCommentLog extends XslTransform {

    var $comments;  // array() of BookingComment

    /**
     * Default constructor.
     * $comments : array() of BookingComment  (default empty)
     */
    function BookingCommentLog($comments = array()) {
        $this->comments = $comments;
    }

    /**
     * Saves all *new* comments to the db.
     * $mysqli : manual db connection (for transaction handling)
     * $bookingId : booking id for this allocation
     */
    function save($mysqli, $bookingId) {

        foreach ($this->comments as $comment) {
            if ($comment->id == 0) {
                // ignore $commentId for now, it needs to remain set to 0 in case we rollback on exception
                $comment->bookingId = $bookingId;
                $commentId = BookingDBO::insertBookingComment($mysqli, $comment);
            }
        }
    }
    
    /**
     * Loads existing comments from the db.
     * $bookingId : booking id for this allocation
     */
    function load($bookingId) {
        $this->comments = BookingDBO::fetchBookingComments($bookingId);
    }
    
    /**
     * Adds all comments to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this row will be added
     */
    function addSelfToDocument($domtree, $parentElement) {
        $xmlRoot = $parentElement->appendChild($domtree->createElement('comments'));
        foreach ($this->comments as $bookingComment) {
            $bookingComment->addSelfToDocument($domtree, $xmlRoot);
        }
    }

    /** 
      Generates the following xml:
        <comments>
            <comment>...</comment>
            <comment>...</comment>
            <comment>...</comment>
            ...
        </comments>
     */
    function toXml() {
        // create a dom document with encoding utf8
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $this->addSelfToDocument($domtree, $domtree);
        return $domtree->saveXML();
    }
    
    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return WPDEV_BK_PLUGIN_DIR. '/include/booking_comment_log.xsl';
    }
}

?>