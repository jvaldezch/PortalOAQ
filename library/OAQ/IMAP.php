<?php

/**
 * http://www.sitepoint.com/exploring-phps-imap-library-1/
 */
class OAQ_IMAP {

    protected $_mbox;
    protected $_domain;
    protected $_user;
    protected $_pass;

    function __construct($domain, $user, $pass, $folder) {
        $this->_domain = $domain;
        $this->_user = $user;
        $this->_pass = $pass;
        $this->_mbox = imap_open('{' . $this->_domain . ':993/novalidate-cert/ssl}' . $folder, $this->_user, $this->_pass) or die("Unable to connect to INBOX");
        imap_sort($this->_mbox, SORTDATE, 1);
        imap_sort($this->_mbox, SORTARRIVAL, 1);
    }

    public function getNumMessages() {
        return imap_num_msg($this->_mbox);
    }

    public function getHeader($i) {
        return imap_header($this->_mbox, $i);
    }

    public function getUid($i) {
        return imap_uid($this->_mbox, $i);
    }

    public function getStructure($i) {
        return imap_fetchstructure($this->_mbox, $i);
    }

    public function getFolders() {
        return imap_list($this->_mbox, '{' . $this->_domain . ':993/novalidate-cert/ssl}', "*");
    }

    public function getBody($uid) {
        $body = $this->get_part($this->_mbox, $uid, "TEXT/HTML");
        if ($body == "") {
            $body = $this->get_part($this->_mbox, $uid, "TEXT/PLAIN");
        }
        return $body;
    }

    public function getImapBody($uid) {
        return quoted_printable_decode(imap_body($this->_mbox, $uid));
    }

    public function get_part($uid, $mimetype, $structure = false, $partNumber = false) {
        if (!$structure) {
            $structure = imap_fetchstructure($this->_mbox, $uid, FT_UID);
        }
        if ($structure) {
            if ($mimetype == $this->get_mime_type($structure)) {
                if (!$partNumber) {
                    $partNumber = 1;
                }
                $text = imap_fetchbody($this->_mbox, $uid, $partNumber, FT_UID);
                switch ($structure->encoding) {
                    case 3: return imap_base64($text);
                    case 4: return imap_qprint($text);
                    default: return $text;
                }
            }
            // multipart 
            if ($structure->type == 1) {
                foreach ($structure->parts as $index => $subStruct) {
                    $prefix = "";
                    if ($partNumber) {
                        $prefix = $partNumber . ".";
                    }
                    $data = $this->get_part($this->_mbox, $uid, $mimetype, $subStruct, $prefix . ($index + 1));
                    if ($data) {
                        return $data;
                    }
                }
            }
        }
        return false;
    }

    public function get_mime_type($structure) {
        $primaryMimetype = array("TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER");
        if ($structure->subtype) {
            return $primaryMimetype[(int) $structure->type] . "/" . $structure->subtype;
        }
        return "TEXT/PLAIN";
    }

    public function getAttachments($mailNum, $part, $partNum) {
        $attachments = array();
        if (isset($part->parts)) {
            foreach ($part->parts as $key => $subpart) {
                if ($partNum != "") {
                    $newPartNum = $partNum . "." . ($key + 1);
                } else {
                    $newPartNum = ($key + 1);
                }
                $result = $this->getAttachments($mailNum, $subpart, $newPartNum);
                if (count($result) != 0) {
                    array_push($attachments, $result);
                }
            }
        } else if (isset($part->disposition)) {
            if (preg_match('/ATTACHMENT/i', $part->disposition)) {
                $partStruct = imap_bodystruct($this->_mbox, $mailNum, $partNum);
                $attachmentDetails = array(
                    "name" => isset($part->dparameters[0]->value) ? $part->dparameters[0]->value : $part->parameters[0]->value,
                    "partNum" => $partNum,
                    "enc" => (isset($partStruct->encoding)) ? $partStruct->encoding : null
                );
                return $attachmentDetails;
            }
        }
        return $attachments;
    }

    public function downloadAttachmentToNav($uid, $partNum, $encoding) {
        $partStruct = imap_bodystruct($this->_mbox, imap_msgno($this->_mbox, $uid), $partNum);

        $filename = $partStruct->dparameters[0]->value;
        $message = imap_fetchbody($this->_mbox, $uid, $partNum, FT_UID);

        switch ($encoding) {
            case 0:
            case 1:
                $message = imap_8bit($message);
                break;
            case 2:
                $message = imap_binary($message);
                break;
            case 3:
                $message = imap_base64($message);
                break;
            case 4:
                $message = quoted_printable_decode($message);
                break;
        }

        header("Content-Description: File Transfer");
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=" . $filename);
        header("Content-Transfer-Encoding: binary");
        header("Expires: 0");
        header("Cache-Control: must-revalidate");
        header("Pragma: public");
        echo $message;
    }

    public function downloadAttachment($uid, $partNum, $encoding) {
        $partStruct = imap_bodystruct($this->_mbox, imap_msgno($this->_mbox, $uid), $partNum);
        $filename = $partStruct->dparameters[0]->value;
        $message = imap_fetchbody($this->_mbox, $uid, $partNum, FT_UID);
        switch ($encoding) {
            case 0:
            case 1:
                $message = imap_8bit($message);
                break;
            case 2:
                $message = imap_binary($message);
                break;
            case 3:
                $message = imap_base64($message);
                break;
            case 4:
                $message = quoted_printable_decode($message);
                break;
        }
        return $message;
    }

    public function copyMessage($mailNum, $mailBox) {
        return imap_mail_copy($this->_mbox, $mailNum, $mailBox);
    }

    public function moveMessage($mailNum, $mailBox) {
        imap_mail_move($this->_mbox, $mailNum, $mailBox);
    }

    public function deleteMessage($uid) {
        return imap_delete($this->_mbox, $uid, FT_UID);
    }

    public function imapPing() {
        if (!imap_ping($this->_mbox)) {
            
        }
    }

    public function setFlagSeenFlag($uid) {
        imap_setflag_full($this->_mbox, $uid, "\\Seen \\Flagged");
    }

    public function expunge() {
        return imap_expunge($this->_mbox);
    }

    function __destruct() {
        imap_close($this->_mbox);
    }

    public function flattenParts($messageParts, $flattenedParts = array(), $prefix = '', $index = 1, $fullPrefix = true) {
        if(isset($messageParts) && !empty($messageParts)) {
            foreach ($messageParts as $part) {
                $flattenedParts[$prefix . $index] = $part;
                if (isset($part->parts)) {
                    if ($part->type == 2) {
                        $flattenedParts = $this->flattenParts($part->parts, $flattenedParts, $prefix . $index . '.', 0, false);
                    } elseif ($fullPrefix) {
                        $flattenedParts = $this->flattenParts($part->parts, $flattenedParts, $prefix . $index . '.');
                    } else {
                        $flattenedParts = $this->flattenParts($part->parts, $flattenedParts, $prefix);
                    }
                    unset($flattenedParts[$prefix . $index]->parts);
                }
                $index++;
            }
            return $flattenedParts;
        } else {
            return;
        }
    }

    public function getPart($messageNumber, $partNumber, $encoding) {
        $data = imap_fetchbody($this->_mbox, $messageNumber, $partNumber);
        switch ($encoding) {
            case 0: return $data; // 7BIT
            case 1: return $data; // 8BIT
            case 2: return $data; // BINARY
            case 3: return base64_decode($data); // BASE64
            case 4: return quoted_printable_decode($data); // QUOTED_PRINTABLE
            case 5: return $data; // OTHER
        }
    }

    public function getFilenameFromPart($part) {
        $filename = '';
        if ($part->ifdparameters) {
            foreach ($part->dparameters as $object) {
                if (strtolower($object->attribute) == 'filename') {
                    $filename = $object->value;
                }
            }
        }
        if (!$filename && $part->ifparameters) {
            foreach ($part->parameters as $object) {
                if (strtolower($object->attribute) == 'name') {
                    $filename = $object->value;
                }
            }
        }
        return $filename;
    }
    
    public function close() {
        imap_close($this->_mbox);
    }
    
    public function getDetails($header) {
        $fromInfo = $header->from[0];
        $replyInfo = $header->reply_to[0];
        $details = array(
            "fromAddr" => (isset($fromInfo->mailbox) && isset($fromInfo->host)) ? $fromInfo->mailbox . "@" . $fromInfo->host : "",
            "fromName" => (isset($fromInfo->personal)) ? $fromInfo->personal : "",
            "replyAddr" => (isset($replyInfo->mailbox) && isset($replyInfo->host)) ? $replyInfo->mailbox . "@" . $replyInfo->host : "",
            "replyName" => (isset($replyInfo->personal)) ? $replyInfo->personal : "",
            "subject" => (isset($header->subject)) ? $header->subject : "",
            "udate" => (isset($header->udate)) ? $header->udate : ""
        );
        return $details;
    }

}
