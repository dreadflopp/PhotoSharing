<?php
/*******************************************************************************
 * Projekt, Kurs: DT161G
 * File: database.class.php
 * Desc: Class Databas for Projekt. Singleton class. Handles communication
 * with the database.
 *
 * Mattias Lindell
 * mali1624
 * mali1624@student.miun.se
 ******************************************************************************/


class Database
{
    private $dbConn; // the connection
    private static $instance;   // this instance

    /*
     * PRIVATE FUNCTIONS
     *
     * Private functions do not connect to the database by themselves. To use them, make sure
     * the private member dbConn is connected first
     */

    /*******************************************************************************
     * Function constructor
     * Desc: private constructor since this class is singleton. Set the data member
     * value from Config.php
     ******************************************************************************/
    private function __construct() {}

    /*******************************************************************************
     * Function getImages
     * Desc: Private function. Does not open a connection to the database itself.
     * Returns all images belonging to a user.
     * @param string $username - the user to fetch images for
     * @return Image[] - array with images (without image content)
     *******************************************************************************/
    private function getImages($username)
    {

        // get images for user
        $query = "SELECT name, filetype, category, time, checksum FROM dt161g_project.image 
                INNER JOIN dt161g_project.member_image_category ON dt161g_project.image.id = dt161g_project.member_image_category.image_id 
                INNER JOIN dt161g_project.member ON dt161g_project.member_image_category.member_id = dt161g_project.member.id               
                INNER JOIN dt161g_project.category ON dt161g_project.category.id = dt161g_project.member_image_category.category_id WHERE username=$1";
        $result = pg_query_params($this->dbConn, $query, array($username));

        // if images found, get data. Else set an empty array as image data
        if ($result && pg_num_rows($result) > 0)
            $allImageData = pg_fetch_all($result);
        else
            $allImageData = array();

        // Delete resources to free up memory
        pg_free_result($result);

        // Extract data for each image, create an image object and put each object in an array
        $images = array();
        foreach ($allImageData as $imageData) {
            // create image
            $image = new Image();

            // set image data
            $image->setName($imageData['name']);
            $image->setFileType($imageData['filetype']);
            $image->setTime($imageData['time']);
            $image->setChecksum($imageData['checksum']);
            $image->setCategory($imageData['category']);

            // add image to array
            $images[] = $image;
        }

        // return images array (which is empty if no images was found in the db=
        return $images;
    }

    /*******************************************************************************
     * Function getRoles
     * Desc: Private function. Does not open a connection to the database itself.
     * return the roles for a user
     * @param string $username - username of the member to get roles for
     * @return string[] - an array with all roles
     ******************************************************************************/
    private function getRoles($username)
    {
        // search for roles
        $query = "SELECT role FROM dt161g_project.member 
                INNER JOIN dt161g_project.member_role ON dt161g_project.member.id = dt161g_project.member_role.member_id
                INNER JOIN dt161g_project.role ON dt161g_project.role.id = dt161g_project.member_role.role_id
                WHERE username=$1";
        $result = pg_query_params($this->dbConn, $query, array($username));

        // Get roles in an array.
        $roles = pg_fetch_all_columns($result, 0);

        // Delete resources to free up memory
        pg_free_result($result);

        return $roles;
    }

    /*******************************************************************************
     * Function getCategories
     * Desc: Private function. Does not open a connection to the database itself.
     * return the categories for a user
     * @param string $username - user to get categories for
     * @return string[] - all categories that belongs to the user
     ******************************************************************************/
    private function getCategories($username)
    {
        // get categories for user
        $query = "SELECT category FROM dt161g_project.category 
                INNER JOIN dt161g_project.member_category ON dt161g_project.category.id = dt161g_project.member_category.category_id 
                INNER JOIN dt161g_project.member ON dt161g_project.member_category.member_id = dt161g_project.member.id  
                WHERE username=$1";
        $result = pg_query_params($this->dbConn, $query, array($username));

        // get array with all categories as strings
        $categories = pg_fetch_all_columns($result, 0);

        // Delete resources to free up memory
        pg_free_result($result);

        return $categories;
    }

    /*******************************************************************************
     * Function connect
     * Desc: Connect to the db
     * @return bool - true if connection is successfully initiated
     ******************************************************************************/
    private function connect()
    {
        // connect to db
        $this->dbConn = pg_connect(Config::getInstance()->getDbDsn());

        if ($this->dbConn)
            return true;
        else
            return false;
    }

    /*******************************************************************************
     * Function disconnect
     * Desc: Disconnect from the db
     * @return bool - true if connection successfully terminated
     ******************************************************************************/
    private function disconnect()
    {
        // Close connection
        return pg_close($this->dbConn);
    }

    /*******************************************************************************
     * Function findUser
     * Desc: Private function. Does not open a connection to the database itself.
     * Check if user exists in database
     * @param string $username - User to search for
     * @return int|bool - id if user is found, else false
     ******************************************************************************/
    private function findUser($username)
    {
        // search for user
        $query = "SELECT id FROM dt161g_project.member WHERE username=$1";
        $result = pg_query_params($this->dbConn, $query, array($username));

        // if the result has more than 0 rows, the member was found
        if ($result && pg_num_rows($result) > 0)
            $id = pg_fetch_row($result, 0)[0];
        else
            $id = false;

        // Delete resources to free up memory
        if ($result)
            pg_free_result($result);

        return $id;
    }

    /*******************************************************************************
     * Function cleanCategories
     * Desc: Clean up. Delete all categories not associated with any user
     * @return bool - true if successful
     */
    private function cleanCategories() {

        $query = "DELETE FROM dt161g_project.category WHERE id NOT IN (SELECT category_id FROM dt161g_project.member_category)";
        $result = pg_query($this->dbConn, $query);

        if ($result)
            return true;
        else
            return false;
    }

    /*******************************************************************************
     * Function cleanImages
     * Desc: Clean up. Delete all images not associated with any user
     * @return bool - true if successful
     */
    private function cleanImages() {

        $query = "DELETE FROM dt161g_project.image WHERE id NOT IN (SELECT image_id FROM dt161g_project.member_image_category)";
        $result = pg_query($this->dbConn, $query);

        if ($result)
            return true;
        else
            return false;
    }

    /*******************************************************************************
     * Function getImageId
     * Desc: Returns image id
     * @param string $checksum - the image's checksum
     * @return int|bool - image id if it was found, else false
     */
    private function getImageId($checksum) {

        $query = "SELECT id FROM dt161g_project.image WHERE checksum =$1";
        $result = pg_query_params($this->dbConn, $query, array($checksum));

        if ($result && pg_num_rows($result) > 0) {
            return pg_fetch_row($result, 0)[0];
        }
        else
            return false;
    }

    /*******************************************************************************
     * Function getCategoryId
     * Desc: Returns category id
     * @param string $category - the categorys name
     * @return int|bool - category id if it was found, else false
     */
    private function getCategoryId($category) {

        $query = "SELECT id FROM dt161g_project.category WHERE category =$1";
        $result = pg_query_params($this->dbConn, $query, array($category));

        if ($result && pg_num_rows($result) > 0) {
            return pg_fetch_row($result, 0)[0];
        }
        else
            return false;
    }

    /*
    * PUBLIC FUNCTIONS
    */


    /*******************************************************************************
     * Function getInstance
     * Desc: Get the one instance of this singleton class     *
     * @return Database
     ******************************************************************************/
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /*******************************************************************************
     * Function hasImage
     * Desc: Checks if user has a certain image
     * @param string $username - member to search for
     * @param string $checksum - image to search for
     * @return bool - true if image is found
     ******************************************************************************/
    public function hasImage($username, $checksum) {

        // connect
        $this->connect();

        $query = "SELECT * FROM dt161g_project.image
                        INNER JOIN dt161g_project.member_image_category ON dt161g_project.image.id = dt161g_project.member_image_category.image_id
                        INNER JOIN dt161g_project.member ON dt161g_project.member.id = dt161g_project.member_image_category.member_id
                        AND checksum=$1 AND username=$2";
        $result = pg_query_params($this->dbConn, $query, array($checksum, $username));

        if ($result && pg_num_rows($result) > 0)
            $success = true;
        else
            $success = false;

        // Delete resources to free up memory
        pg_free_result($result);

        // disconnect
        $this->disconnect();

        return $success;
    }

    /*******************************************************************************
     * Function userExist
     * Desc: Checks if user exists in the database.
     * @param string $username - User to search for
     * @return bool - true if user is found
     ******************************************************************************/
    public function userExist($username)
    {
        // connect
        $this->connect();

        // use private function to search for user
        $result = $this->findUser($username);

        // disconnect
        $this->disconnect();

        if ($result == false)
            return false;
        else
            return true;
    }

    /*******************************************************************************
     * Function getMemberData
     * Desc: Returns all necessary data to instantiate a member
     * @param string $username - username of the member for whom data is requested
     * @return array - response with data
     ******************************************************************************/
    public function getMemberData($username)
    {
        // connect
        $this->connect();

        $roles = $this->getRoles($username);
        $categories = $this->getCategories($username);
        $images = $this->getImages($username);

        // disconnect
        $this->disconnect();

        $memberData = array('roles' => $roles, 'categories' => $categories, 'images' => $images);

        return $memberData;
    }

    /*******************************************************************************
     * Function getAllUsernames
     * Desc: Returns the usernames from all members and admins
     * @return string[] with strings
     ******************************************************************************/
    public function getAllUsernames()
    {
        // connect to db
        $this->connect();

        // Get all users
        $query = "SELECT username FROM dt161g_project.member";
        $result = pg_query($this->dbConn, $query);

        // Get array with all userNames as strings
        $usernames = pg_fetch_all_columns($result, 0);

        // Delete resources to free up memory
        pg_free_result($result);

        // Close connection
        $this->disconnect();

        return $usernames;
    }

    /*******************************************************************************
     * Function getAllMemberUsernames
     * Desc: Returns the usernames from all members (admins excluded)
     * @return string[] with strings
     ******************************************************************************/
    public function getAllMemberUsernames()
    {
        // connect to db
        $this->connect();

        // Get all users
        $query = "SELECT username FROM dt161g_project.member 
                INNER JOIN dt161g_project.member_role ON dt161g_project.member.id = dt161g_project.member_role.member_id
                INNER JOIN dt161g_project.role ON dt161g_project.role.id = dt161g_project.member_role.role_id
                AND role = $1";
        $result = pg_query_params($this->dbConn, $query, array('member'));

        // Get array with all userNames as strings
        $usernames = pg_fetch_all_columns($result, 0);

        // Delete resources to free up memory
        pg_free_result($result);

        // Close connection
        $this->disconnect();

        return $usernames;
    }

    /*******************************************************************************
     * Function getAllRoles
     * Desc: Returns all possible roles
     * @return string[] with strings
     ******************************************************************************/
    public function getAllRoles()
    {
        // connect to db
        $this->connect();

        // Get all roles
        $query = "SELECT role FROM dt161g_project.role";
        $result = pg_query($this->dbConn, $query);

        // Get array with all userNames as strings
        $roles = pg_fetch_all_columns($result, 0);

        // Delete resources to free up memory
        pg_free_result($result);

        // Close connection
        $this->disconnect();

        return $roles;
    }

    /*******************************************************************************
     * Function validate password
     * Desc: Checks if the given password is correct for the given username
     * @param string $username - username of member
     * @param string $password - password of member
     * @return bool - true if the password is correct
     ******************************************************************************/
    public function validPassword(string $username, string $password)
    {
        // connect to db
        $this->connect();

        // search for password
        $query = "SELECT username FROM dt161g_project.member WHERE username=$1 AND password = md5($2)";
        $result = pg_query_params($this->dbConn, $query, array($username, $password . $username));

        // if password was found
        if ($result && pg_num_rows($result) > 0)
            $success = true;
        else
            $success =  false;

        // Delete resources to free up memory
        pg_free_result($result);

        // Close connection
        $this->disconnect();

        return $success;
    }

    /*******************************************************************************
     * Function getImageContent
     * Desc: return the content for an image as a string encoded with base64
     * @param string $checksum - checksum of the image to search for
     * @return string|bool - image content if query succeed, else false
     ******************************************************************************/
    public function getImageContent($checksum)
    {
        // connect to db
        $this->connect();

        // search for roles
        $query = "SELECT content FROM dt161g_project.image WHERE checksum=$1";
        $result = pg_query_params($this->dbConn, $query, array($checksum));

        // Get content if query succeed. Else set content to false.
        if ($result && pg_num_rows($result) > 0)
            $content = pg_fetch_all_columns($result, 0)[0];
        else
            $content = false;

        // Delete resources to free up memory
        pg_free_result($result);

        // Close connection
        $this->disconnect();

        return $content;
    }

    /*******************************************************************************
     * Function addCategory
     * Desc: Add a category
     * @param string $username - user this category should be linked to
     * @param string $category - category to be added
     * @return array - array with boolean key 'success' set to true if query was successful and
     * string key 'message' containing a message
     ******************************************************************************/
    public function addCategory($username, $category)
    {
        // connect
        $this->connect();

        // check if category exists for the given user
        $query = "SELECT category FROM dt161g_project.category 
                        INNER JOIN dt161g_project.member_category ON dt161g_project.category.id = dt161g_project.member_category.category_id
                        INNER JOIN dt161g_project.member ON dt161g_project.member.id = dt161g_project.member_category.member_id
                        AND category=$1 AND username=$2";
        $result = pg_query_params($this->dbConn, $query, array($category, $username));

        // if answer has any rows, the category already exists for the given user
        // if that is the case, set error message and quit.
        if ($result && pg_num_rows($result) > 0) {
            pg_free_result($result);
            $this->disconnect();
            return array('success' => false, 'message' => '[db error: category already exist]');
        }


        //  get the user id.
        if ($result) {
            $userId = $this->findUser($username);

            // if user not found, set response and quit
            if ($userId == false) {
                $this->disconnect();
                return array('success' => false, 'message' => '[db error: user not found]');
            }
        }


        // create category if it doesn't exist for any user
        if ($result) {
            // Delete resources to free up memory
            pg_free_result($result);

            // Create category if it doesn't exist
            $query = "INSERT INTO dt161g_project.category (category) VALUES ($1) ON CONFLICT DO NOTHING";
            $result = pg_query_params($this->dbConn, $query, array($category));

            // if category was added, remember that in case of a roll back
            if ($result && pg_affected_rows($result) != 0)
                $categoryAdded = true;
            else
                $categoryAdded = false;
        }




        // Get id of category
        if ($result) {

            $categoryId = $this->getCategoryId($category);

            if ($categoryId == false) {
                $this->disconnect();
                return array('success' => false, 'message' => '[db error: category not found]');
            }
        }



        // Link category and user
        if ($result) {
            $result = pg_insert($this->dbConn, 'dt161g_project.member_category', array('member_id' => $userId, 'category_id' => $categoryId));
        }




        // If all queries has been successful, set response and quit
        if ($result) {
            // Delete resources to free up memory
            pg_free_result($result);

            // Close connection
            $this->disconnect();

            // return true
            return array('success' => true, 'message' => '[db notice: category "' . $category . '" added]');
        }




        // if any query failed, perform a rollback if necessary, set response and quit
        if (!$result) {
            // if roll back needed
            if (isset($categoryAdded) && $categoryAdded == true) {
                $result = pg_delete($this->dbConn, 'dt161g_project.category', array('category' => $category));

                // if roll back failed
                if (!$result)
                    return array('success' => false, 'message' => '[db fatal error: category has been partially added and a rollback failed.]');
                else
                    pg_free_result($result);
            }

            // Close connection
            $this->disconnect();
            return array('success' => false, 'message' => '[db error: a query has failed, category not added.]');
        }
    }

    /*******************************************************************************
     * Function addImage
     * Desc: Add an image
     * @param Image $image - the image that will be added
     * @param string $username - the user that will be the owner of the image
     * @return array - array with boolean key 'success' set to true if query was successful and
     * string key 'message' containing a message
     ******************************************************************************/

    public function addImage(Image $image, $username)
    {
        // Get image data
        $name = $image->getName();
        $fileType = $image->getFileType();
        $category = $image->getCategory();
        $time = $image->getTime();
        $content = base64_encode($image->getContent()); // encoded as string
        $checksum = $image->getChecksum();

        // if data is set...
        if (isset($name) && isset($fileType) && isset($category) && isset($time) && isset($content) && isset($checksum)) {
            // connect
            $this->connect();

            // get id:s
            $userId = $this->findUser($username);
            $categoryId = $this->getCategoryId($category);
            $imageId = $this->getImageId($checksum);        // if image doesn't exit in the db, this will be false

            // bool that will remember if an image has been added
            $imageAdded = false;
            $imageLinkedToMember = false;

            // if user and category exist
            if ($userId != false && $categoryId != false) {

                // add the image if it doesn't exist
                if ($imageId == false) {
                    // Create image if it doesn't exist
                    $query = "INSERT INTO dt161g_project.image (name, filetype, time, content, checksum) VALUES ($1, $2, $3, $4, $5) RETURNING  id";
                    $result = pg_query_params($this->dbConn, $query, array($name, $fileType, $time, $content, $checksum));

                    // if image was added, get the id
                    if ($result && pg_affected_rows($result) > 0) {
                        $imageId = pg_fetch_row($result, 0)[0];
                        $imageAdded = true;  // remember that the image was added in case of a roll back
                    } else if (!$result) {
                        // if query failed, quit
                        $this->disconnect();
                        return array('success' => false, 'message' => '[db error: image not added]');
                    }
                }

                // if the image did exit previously in the db, check if it exists for the given username
                if (!$imageAdded) {
                    $query = "SELECT checksum FROM dt161g_project.image
                        INNER JOIN dt161g_project.member_image_category ON dt161g_project.image.id = dt161g_project.member_image_category.image_id
                        INNER JOIN dt161g_project.member ON dt161g_project.member.id = dt161g_project.member_image_category.member_id
                        AND checksum=$1 AND username=$2";
                    $result = pg_query_params($this->dbConn, $query, array($checksum, $username));

                    // if answer has any rows, the image already exists for the given user
                    // if that is the case, set error message and quit.
                    if ($result && pg_num_rows($result) > 0) {
                        pg_free_result($result);
                        $this->disconnect();
                        return array('success' => false, 'message' => '[db error: image already exist]');
                    } else if (!$result) {
                        // if query failed, quit
                        $this->disconnect();
                        return array('success' => false, 'message' => '[db error: a query failed, nothing has been added]');
                    }
                }


                // link image to member and category
                $result = pg_insert($this->dbConn, 'dt161g_project.member_image_category', array('member_id' => $userId, 'image_id' => $imageId, 'category_id' => $categoryId));
                if ($result) {

                    // Delete resources to free up memory
                    pg_free_result($result);

                    // and we are done adding the image
                    // Close connection and return response
                    $this->disconnect();

                    return array('success' => true, 'message' => "[db notice: image added]");
                }  else {

                    // an error occurred in a query. We need to roll back.
                    if ($imageAdded) {
                        $result = pg_delete($this->dbConn, 'dt161g_project.image', array('checksum' => $checksum));
                        if (!$result) {
                            $this->disconnect();
                            return array('success' => false, 'message' => "[db fatal error: image was partially added and a rollback failed]");
                        }  else {
                            $this->disconnect();
                            return array('success' => false, 'message' => "[db error: the db recovered after a rollback. Image not added]");
                        }
                    }
                }

            } else {
                // user or category not found. Return response
                $this->disconnect();
                return array('success' => false, 'message' => "[db error: Check that the given username and category exist]");
            }


        } else {
            // image data was not correctly set. Return response
            $this->disconnect();
            return array('success' => false, 'message' => "[db error: The uploaded image's data is not set]");
        }
    }

    /*******************************************************************************
     * Function deleteCategory
     * Desc: Deletes a category association for a member. All images belonging to that member and category are deleted
     * as well. If no other member uses the category, the category will be deleted.
     * @param string $username - the member whose category will be deleted
     * @param string $category - category to be deleted
     * @return array - array with boolean key 'success' set to true if query was successful and
     * string key 'message' containing a message
     *******************************************************************************/
    public function deleteCategory($username, $category)
    {
        // connect
        $this->connect();

        // delete image links associated with the user and category.
        $query = "DELETE FROM dt161g_project.member_image_category
                USING dt161g_project.member, dt161g_project.image, dt161g_project.category, dt161g_project.member_category
                WHERE dt161g_project.member.id = dt161g_project.member_image_category.member_id
                AND dt161g_project.image.id = dt161g_project.member_image_category.image_id
                AND dt161g_project.category.id = dt161g_project.member_image_category.category_id
                AND category=$1 AND username=$2";
        $result = pg_query_params($this->dbConn, $query, array($category, $username));

        // if query failed
        if (!$result) {
            // close connection and exit
            $this->disconnect();
            return array('success' => false, 'message' => "[db error: query failed, nothing has been deleted]");
        }

        // delete the association between user and category (if previous query was successful)
        else {
            $query = "DELETE FROM dt161g_project.member_category
                USING dt161g_project.member, dt161g_project.category
                WHERE dt161g_project.member_category.member_id = dt161g_project.member.id
                AND dt161g_project.member_category.category_id = dt161g_project.category.id
                AND category=$1 AND username=$2";
            $result = pg_query_params($this->dbConn, $query, array($category, $username));
        }

        // if query failed
        if (!$result) {
            // close connection and exit
            $this->disconnect();
            return array('success' => false, 'message' => "[db fatal error: images deleted. Failed to delete category]");
        }

        // Clean up. Delete all images categories not associated with any user (if previous query was successful)
        else {
            if ($this->cleanImages()) {
                if ($this->cleanCategories()) {
                    // Close connection and exit
                    $this->disconnect();
                    return array('success' => true, 'message' => "[db notice: category has been deleted]");
                }
            }
            else {
                // Close connection and exit
                $this->disconnect();
                return array('success' => false, 'message' => "[db fatal error: category partially deleted.");
            }
        }
    }

    /*******************************************************************************
     * Function addMember
     * Desc: Adds a member
     * @param string $username - members username
     * @param string $password - members password
     * @param array $roles - roles to be added to user. If this array is empty, no roles are added
     * @return array - array with boolean key 'success' set to true if query was successful and
     * string key 'message' containing a message
     ******************************************************************************/
    public function addMember($username, $password, $roles)
    {
        // If roles array is not empty, continue
        if (count($roles) > 0) {

            // connect
            $this->connect();

            // Get id:s of all roles given in the roles array and store in an array
            $roleIds = [];
            foreach ($roles as $role) {
                $query = "SELECT id FROM dt161g_project.role WHERE role =$1";
                $result = pg_query_params($this->dbConn, $query, array($role));

                // if success
                if ($result && pg_num_rows($result) > 0) {
                    // add id to array
                    $roleIds[] = pg_fetch_all_columns($result, 0)[0];

                    // Delete resources to free up memory
                    pg_free_result($result);

                } else {
                    // error, role id couldn't be retrieved. Error in db or non existing role given as parameter. Quit.

                    // Close connection and quit
                    $this->disconnect();
                    return array('success' => false, 'message' => "[db error: the role sent as parameter could not be retrieved from the database]");
                }
            }

            // if user exist, quit. If not, continue
            if (!$this->findUser($username)) {

                // add user. Password is encrypted with md5. Since crypto is not installed in the database,
                // simpler means on encryption is used. To prevent different users with the same password having identical
                // encrypted passwords, the username is added to the password before it is encrypted.
                $query = "INSERT INTO dt161g_project.member (username, password) VALUES ($1, md5($2)) RETURNING id";
                $result = pg_query_params($this->dbConn, $query, array($username, $password . $username));

                // if user added
                if ($result) {

                    // Save member id and delete resources to free up memory
                    $memberId = pg_fetch_row($result)[0];
                    pg_free_result($result);

                    // link member to each role
                    foreach ($roleIds as $roleId) {
                        $result = pg_insert($this->dbConn, 'dt161g_project.member_role', array('member_id' => $memberId, 'role_id' => $roleId));
                        // if query failed, break the loop
                        if (!$result)
                            break;
                    }

                    // if result is true, the user has been successfully added
                    if ($result) {
                        // Delete resources to free up memory
                        pg_free_result($result);

                        // Close connection and return true
                        $this->disconnect();
                        return array('success' => true, 'message' => "[db notice: member added]");
                    }

                    // if result is false, we need to roll back, delete the user
                    if (!$result) {
                        $result = pg_delete($this->dbConn, 'dt161g_project.member', array('username' => $username));

                        // if rollback failed
                        if (!result) {
                            // Close connection and return
                            $this->disconnect();
                            return array('success' => false, 'message' => "[db fatal error: roll back failed, member partially added]");
                        }
                        // Delete resources to free up memory
                        pg_free_result($result);
                    }
                }
                // Close connection and return
                $this->disconnect();
                return array('success' => false, 'message' => "[db error: a query failed, member not added]");
            }
            // Close connection and return
            $this->disconnect();
            return array('success' => false, 'message' => "[db error: user already exist]");
        }
        return array('success' => false, 'message' => "[db error: no roles sent as parameter, unable to add member]");
    }

    /*******************************************************************************
     * Function deleteMember
     * Desc: Deletes a member
     * @param string $username - members username
     * @return array - array with boolean key 'success' set to true if query was successful and
     * string key 'message' containing a message
     ******************************************************************************/
    public function deleteMember($username)
    {
        // connect
        $this->connect();

        // if user doesn't exist, quit. Else continue
        if ($this->findUser($username)) {

            // delete all images belonging to the user by deleting the links to the image, not the actual image
            $query = "DELETE FROM dt161g_project.member_image_category
                USING dt161g_project.member, dt161g_project.image
                WHERE dt161g_project.member.id = dt161g_project.member_image_category.member_id
                AND dt161g_project.image.id = dt161g_project.member_image_category.image_id
                AND username=$1";
            $result = pg_query_params($this->dbConn, $query, array($username));

            // if successful, delete user
            if ($result) {
                // Delete resources to free up memory
                pg_free_result($result);

                // delete user
                $query = "DELETE FROM dt161g_project.member WHERE username=$1";
                $result = pg_query_params($this->dbConn, $query, array($username));

                // if successful, clean up leftover categories and images
                if ($result) {
                    // Delete resources to free up memory
                    pg_free_result($result);

                    if ($this->cleanImages()) {
                        if ($this->cleanCategories()) {
                            // success
                            $this->disconnect();
                            return array('success' => true, 'message' => "[db notice: member deleted]");
                        }
                    }
                    else {
                        $this->disconnect();
                        return array('success' => false, 'message' => "[db fatal error: member partially deleted]");

                    }
                }
                else {
                    // query failed
                    $this->disconnect();
                    return array('success' => false, 'message' => "[db fatal error: a query failed, images belonging to the user has been deleted]");
                }
            }
            else {
                // query failed
                $this->disconnect();
                return array('success' => false, 'message' => "[db error: a query failed, nothing has been deleted]");
            }
        }
        // disconnect and quit
        $this->disconnect();
        return array('success' => false, 'message' => "[db error: member doesn't exist]");
    }
}
