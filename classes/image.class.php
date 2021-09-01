<?php
/*******************************************************************************
 * Projekt, Kurs: DT161G
 * File: image.class.php
 * Desc: Class Image for Projekt
 *
 * Mattias Lindell
 * mali1624
 * mali1624@student.miun.se
 ******************************************************************************/


class Image {
    private $name; // name of the image
    private $fileType;
    private $category; // category the image belongs to
    private $time;  // time of the image exif data
    private $checksum;  // checksum makes sure each image is unique and identifiable
    private $content;   // the image data

    public function __construct() {
        $this->setTime('2000-01-01 00:00');
        $this->content = false;
    }

    /*******************************************************************************
     * Getters
     ******************************************************************************/
    /** @return string */
    public function getName() { return $this->name; }
    /** @return string */
    public function getFileType() { return $this->fileType; }
    /** @return string */
    public function getCategory() { return $this->category; }
    /** @return string */
    public function getTime() { return $this->time; }
    /** @return string */
    public function getChecksum() { return $this->checksum; }
    /** @return string|bool - false if content couldn't be retrieved*/
    public function getContent() { return $this->content; }

    /*******************************************************************************
     * Setters
     ******************************************************************************/
    /** @param string $name */
    public function setName($name) { $this->name = $name; }
    /** @param string $fileType */
    public function setFileType($fileType) { $this->fileType = $fileType; }
    /** @param string $category */
    public function setCategory($category) { $this->category = $category; }
    /** @param string $time */
    public function setTime($time) { $this->time = date('Y-m-d H:i', strtotime($time)); }
    /** @param $content */
    public function setContent($content) { $this->content = $content; }
    /** @param $checksum */
    public function setChecksum($checksum) { $this->checksum = $checksum; }


    /*******************************************************************************
     * Function getDataUrl
     * Desc: Returns dataUrl
     * @return string|bool - dataUrl or false if image content couldn't be retrieved
     ******************************************************************************/
    public function getDataUrl() {
        // if image has no content, get it from the database
        if ($this->content == false)
            $this->content = Database::getInstance()->getImageContent($this->checksum);

        // if content was successfully set or already set, return data url. Else return false.
        if (!$this->content == false)
            return 'data:' . $this->fileType . ';base64,' . $this->content;
        else
            return false;
    }

    /*******************************************************************************
     * Function calculateChecksum
     * Desc: Calculates and sets a unique checksum
     * @param string $username of the  member the image belongs to
     * @return bool - true if checksum was calculated and set
     ******************************************************************************/
    public function calculateChecksum(string $username) {
        // if content is set, calculate checksum
        if (!$this->content == false) {
            $this->checksum = md5($this->content);

            // checksum calculated and set.
            return true;
        } else {
            // failed to calculate checksum
            return false;
        }
    }
}