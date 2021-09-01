<?php
/*******************************************************************************
 * Projekt, Kurs: DT161G
 * File: config.class.php
 * Desc: Class Config for Projekt. Singleton class. Used to access data stored in
 * the config file. Can create links using data from the config file and data given
 * as parameters.
 *
 * Mattias Lindell
 * mali1624
 * mali1624@student.miun.se
 ******************************************************************************/


class Config {
    private $db_settings;
    private $default_link_array;
    private $member_link_array;
    private $admin_link_array;
    private $imagePage;
    private static $instance;

    /*******************************************************************************
     * Function constructor
     * Desc: private constructor since this class is singleton. Set the data members
     * values from Config.php
     ******************************************************************************/
    private function  __construct()
    {
        include('config.php');
        $this->db_settings = $db_settings;
        $this->default_link_array = $default_link_array;
        $this->member_link_array = $member_link_array;
        $this->admin_link_array = $admin_link_array;
        $this->imagePage = $image_page;

    }

    /*******************************************************************************
     * Function getInstance
     * Desc: Get the one instance of this singleton class
     ******************************************************************************/
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Config();
        }
        return self::$instance;
    }

    /*******************************************************************************
     * Getters
     ******************************************************************************/
    public function getHost() { return $this->db_settings['host']; }
    public function getPort() { return $this->db_settings['port']; }
    public function getDbName() { return $this->db_settings['dbname']; }
    public function getUser() { return $this->db_settings['user']; }
    public function getPassword() { return $this->db_settings['password']; }
    public function getSslMode() { return $this->db_settings['sslmode']; }
    public function getDefaultLinkArray() { return $this->default_link_array; }
    public function getMemberLinkArray() { return $this->member_link_array; }
    public function getAdminLinkArray() { return $this->admin_link_array; }

    /*******************************************************************************
     * Function getDbDsn
     * Desc: Get string used by database class to connect
     ******************************************************************************/
    public function getDbDsn(){
        $dsn = "host=" . $this->getHost() . " port=" . $this->getPort() . " dbname=" .
            $this->getDbName() . " user=" . $this->getUser() . " password=" .
            $this->getPassword() . " sslmode=" . $this->getSslMode();
        return $dsn;
    }

    /*******************************************************************************
     * Function getImagePageLinks
     * Desc: Create the links to each users image page
     * @param string - all users usernames
     * @return array - an associative array with all links.
     *****************************************************************************/
    public function getImagePagesLinks($usernames) {
    // object that will contain all links;
    $links = [];
    foreach ($usernames as $username)
        $links[$username] = $this->imagePage . '?user=' . $username;

    return $links;
    }

    /*******************************************************************************
     * Function getCategoryLinks
     * Desc: Create links to given users category sub-pages
     * @param string $username - the user to create links for
     * @param string $categories - the categories belonging to the user
     * @return array - an associative  array with all links
     ******************************************************************************/
    public function getCategoryLinks($username, $categories) {
        // array that will contain all links;
        $links = [];

        // add 'all categories' category
        $links['All categories'] = $this->imagePage . '?user=' . $username;

        // add all categories
        foreach ($categories as $category)
            $links[$category] = $this->imagePage . '?user=' . $username . '&category=' . $category;

        return $links;
    }

}