<?php

/**
 * A Person represents a record from the UCSF directory at directory.ucsf.edu 
 */
class Person {
    
    private $record;    // See the constructor for a description
    private $keys;      // of $record and $keys.
    
    /**
     * Construct a Person from a UCSF directory record
     * 
     * @param mixed $record An object from a decoded UCSF directory JSON record
     */
    public function __construct($record) {
        $this->record = $record;
        /*
         * The properties within a UCSF directory record are generally a key/value
         * pair wrapped in a single member array.  When multiple records are retrieved
         * from the directory this appears to always be the case. However, when a single
         * record is retrieved, various bits of contact information are repeated
         * within an inner class pointed to by a member named 'primary'.  Since it
         * appears that we already have most of the same relevant data already
         * available within the parent object, ignore these non-array[0] members.
         * Once its keys are excluded, with raw $record information private, the
         * get() and keys() functions will not offer access to these record members.
         */
        foreach (get_object_vars($record) as $key => $value) {
            if (is_array($value)) {
                $this->keys[] = $key;
            }
        }
    }
    
    /**
     * The number and variety of $record fields are unknown quantities. In light
     * of that, this method provides a generic getter that help to prevent
     * runtime errors.
     * 
     * @param string The name of a UCSF directory property.
     * @return string The value of the property, or NULL if the property doesn't exist.
     */
    public function get($key) {
        $value = null;
        if (in_array($key, $this->keys)) {
            $property = $this->record->$key;
            
            $value = is_array($property) ? $property[0] : $property;
        }
        return $value;
    }

    /**
     * Get the names of the UCSF directory record's existing properties.
     * @return array An array of property key names.
     */
    public function keys() {
        return $this->keys;
    }
    
    /**
     * Generate a vCard from a UCSF directory record. Looking at the vCard specification
     * this method does a bit of cheating, as it does not required the N (structured
     * name) field and only relies on the FN field.  However, this appears to import
     * just fine for GMail and Macintosh's built-in contact management.
     * 
     * @return string An importable vCard text document
     */
    public function vCard() {
        $content  = "BEGIN:VCARD\nVERSION:3.0\n";
        $content .= (empty($this->record->displayname)) ? "" : "N:".$this->record->displayname[0]."\n";
        $content .= (empty($this->record->displayname)) ? "" : "FN:".$this->record->displayname[0]."\n";
        $content .= (empty($this->record->departmentname)) ? "" : "ORG:UCSF;".$this->record->departmentname[0]."\n";
        $content .= (empty($this->record->ucsfeduworkingtitle)) ? "" : "TITLE:".$this->record->ucsfeduworkingtitle[0]."\n";
        $content .= (empty($this->record->mail)) ? "" : "EMAIL:".$this->record->mail[0]."\n";
        $content .= (empty($this->record->telephonenumber)) ? "" : "TEL;Type=WORK:".$this->record->telephonenumber[0]."\n";
        $content .= (empty($this->record->postaladdress)) ? "" : "ADR;Type=POSTAL:".preg_replace('/[\r\n]+/', ';', $this->record->postaladdress[0])."\n";
        $content .= (empty($this->record->baseaddress)) ? "" : "ADR;Type=BASE:".preg_replace('/[\r\n]+/', ';', $this->record->baseaddress[0])."\n";
        $content .= "END:VCARD";  
        return $content;
    }
    
}


?>
