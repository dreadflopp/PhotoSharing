<?php
/*******************************************************************************
 * Projekt, Kurs: DT161G
 * File: member.class.php
 * Desc: Class Member for Projekt. Initiates from database, syncs to database
 *
 * Mattias Lindell
 * mali1624
 * mali1624@student.miun.se
 ******************************************************************************/


class Member {
    private $username;
    private $roles; // array with the members different roles
    private $categories;    // array with the members categories
    private $images;    // array with the members images

    /*******************************************************************************
     * Getters
     ******************************************************************************/
    /** @return string */
    public function getUsername() { return $this->username; }

    /** @return string[] */
    public function getRoles() { return $this->roles; }

    /** @return string[] */
    public function getCategories() { return $this->categories; }

    /** @return Image[] */
    public function getImages() { return $this->images; }

    /*******************************************************************************
     * Function initialize
     * Desc: Initializes member from database
     * @param string $username - $the username of the member that will be initialized
     * @return bool - true if member initialized
     ******************************************************************************/

    public function initialize($username) {

        // Check if user exist.
        if (Database::getInstance()->userExist($username)) {

            // Get member data
            $memberData = Database::getInstance()->getMemberData($username);

            // set member data
            $this->username = $username;
            $this->roles = $memberData['roles'];
            $this->categories = $memberData['categories'];
            $this->images = $memberData['images'];;

            return true; // member initialised
        } else
            return false;   // member not found
    }

    /*******************************************************************************
     * Function validatePassword
     * Desc: Checks if password is correct
     * @param string $password - password that will be checked
     * @return bool - true if password is correct
     ******************************************************************************/
    public function validatePassword($password) {

        if (Database::getInstance()->validPassword($this->username, $password))
            return true;
        else
            return false;
    }

    /*******************************************************************************
     * Function addCategory
     * Desc: add a category
     * @param string $category - the category that will be added
     * @return array with key 'success' as a bool set to true if category was added
     * and key 'message' as a message from the db
     ******************************************************************************/
    public function addCategory($category) {
        // if category is added to db
        $result = Database::getInstance()->addCategory($this->username, $category);
        if ($result['success']) {

            // add category to member
            $this->categories[] = $category;
        }

        // return result
        return $result;
    }

    /*******************************************************************************
     * Function removeCategory
     * Desc: remove a category
     * @param string $category - the category that will be removed
     * @return array with key 'success' as a bool set to true if category was removed
     * and key 'message' as a message from the db
     ******************************************************************************/
    public function removeCategory($category) {
        // if category is removed from database
        $result = Database::getInstance()->deleteCategory($this->username, $category);
        if ($result['success']) {

            // search through all categories, if a match is found, delete the category and exit the loop
            for($i = 0; $i < count($this->categories); $i++ )
                if ($this->categories[$i] == $category) {
                    array_splice($this->categories, $i, 1);
                    break;
                }
        }
        // return result
        return $result;
    }

    /*******************************************************************************
     * Function hasCategory
     * Desc: Checks if user has a category
     * @param string $category - the category to check for
     * @return bool - true is user has category, else false
     ******************************************************************************/
    public function hasCategory($category) {

        if (in_array($category, $this->categories))
            return true;
        else
            return false;
    }

    /*******************************************************************************
     * Function hasImage
     * Desc: Checks if member has given image
     * @param string $checksum - checksum of image to search for
     * @return bool - true if image was found
     ******************************************************************************/
    public function hasImage($checksum) {
        // for each image
        foreach ($this->images as $image)
            // if image checksum matches given checksum
            if ($image->getChecksum() == $checksum)
                return true;

        return false;
    }

    /*******************************************************************************
     * Function hasRole
     * Desc: Checks if user has a role
     * @param string $role - the role to check for
     * @return bool - true is user has role, else false
     ******************************************************************************/
    public function hasRole($role) {

        if (in_array($role, $this->roles))
            return true;
        else
            return false;
    }



    /*******************************************************************************
     * Function addImage
     * Desc: Adds image to member
     * @param Image $image - the image that will be added
     * @return bool - true if image was added
     ******************************************************************************/
    public function addImage($image) {
        // if image doesn't already exist
        if (!$this->hasImage($image->getChecksum()))
            // if image was added to db
            if (Database::getInstance()->addImage($image, $this->username)) {
                // add image to member and return true
                $this->images[] = $image;
                return true;
            }
        // image not added. Either it exists or database reported an error
        return false;
    }
}