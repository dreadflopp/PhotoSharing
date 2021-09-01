/*******************************************************************************
 * Projekt, Kurs: DT161G
 * File: main.js
 * Desc: main JavaScript file for Projekt
 *
 * Mattias Lindell
 * mali1624
 * mali1624@student.miun.se
 ******************************************************************************/

/* different variables to store XMLHttpRequest objects.
    Using different variables for different type of calls keeps requests
    separated from each other, allowing simultaneous requests while
    making sure the responses reaches the correct event handlers
 */
var xhr_links; // downloads links
var xhr_login; // used for login + logout
var xhr_images; // used for upload + download
var xhr_categories; // used for download, add, delete
var xhr_getRoles;   // download all available roles
var xhr_member; // used for add, delete
var xhr_getMembers // used for downloading member list

/*******************************************************************************
 * Util functions
 ******************************************************************************/

// alias/short for document.getElementById
function byId(id) {
    return document.getElementById(id);
}

// capitalize the first letter in a string, rest of string in lowercase
function firstToUppercase(string) {
    return string.charAt(0).toLocaleUpperCase() + string.substr(1).toLocaleLowerCase();
}

/*******************************************************************************
 * Main function
 ******************************************************************************/
function main() {

    // Add event listeners to login/logout buttons
    byId("loginButton").addEventListener('click', doLogin, false);
    byId("logoutButton").addEventListener('click', doLogout, false);

    // Support for IE7+, Firefox, Chrome, Opera, Safari
    try {
        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xhr_login = new XMLHttpRequest();
            xhr_links = new XMLHttpRequest();
            xhr_images = new XMLHttpRequest();
            xhr_categories = new XMLHttpRequest();
            xhr_getRoles = new XMLHttpRequest();
            xhr_member = new XMLHttpRequest();
            xhr_getMembers = new XMLHttpRequest();
        }
        else {
            throw new Error('Cannot create XMLHttpRequest object');
        }

    } catch (e) {
        alert('"XMLHttpRequest failed!' + e.message);
    }

    // Update links. Since different links are shown depending on the page that is loaded,
    // send the last part of the current pathname as a parameter
    var pathname = window.location.pathname;    // whole pathname
    var lastSlashIndex = pathname.lastIndexOf('/'); // index of last slash in pathname
    var page = pathname.slice(lastSlashIndex + 1); // the page, everything after the last slash
    doGetLinks(page); // get and update links

    // check if the images page is loaded
    if (page === "images.php") {
        // ask for images
        doGetImages();
    }

    // Check if the user page is loaded
    else if (page === 'userpage.php') {
        // add event listeners
        byId("uploadButton").addEventListener('click', doUploadImage, false);
        byId("createCtgButton").addEventListener('click', doAddCategory, false);
        byId("ctgField").addEventListener('input', validateCategoryField, false);
        byId('deleteCtg').addEventListener('click', doRemoveCategory, false);
        byId('imageToUpload').addEventListener('change', showFileName, false);

        // Add categories to selector
        doGetCategories();
    }

    // Check if the admin page is loaded
    else if (page === 'admin.php') {
        // add event listeners
        byId('addMbr').addEventListener('click', doAddMbr, false);
        byId("name").addEventListener('input', validateName, false);
        byId("password").addEventListener('input', validatePassword, false);
        byId('deleteMbr').addEventListener('click', doRemoveMbr, false);

        // Add members to selector
        doGetMembers();

        // Add roles to the checkboxes
        doGetRoles();
    }

}
// Connect the main function to window load event
window.addEventListener("load", main, false);

/*******************************************************************************
 * Function doAddMbr
 * Desc: Sends request to add member. Adds event listener that
 * listens for state changes in the request.
 ******************************************************************************/
function doAddMbr() {
    // if the username and password fields validates...
    if (validateName() && validatePassword()) {

        // get the name and password that are to be added
        var username = byId('name').value;
        var password = byId('password').value;

        // get the roles checkboxes
        var checkboxes = document.getElementsByClassName('checkbox');

        // Get the role for each checked box
        var roles = [];
        for (var i = 0; i < checkboxes.length; i++) {
            if (checkboxes[i].checked === true) {
                roles.push(checkboxes[i].value);
            }
        }

        // Encode roles array as JSON string
        var rolesJSON = JSON.stringify(roles);

        // add event listener and send request
        xhr_member.addEventListener('readystatechange', processAddMbr, false);
        xhr_member.open('GET', 'members.php?task=add&username=' + username + '&password=' + password + '&roles=' + rolesJSON, true);
        xhr_member.send(null);
    }
}

/*******************************************************************************
 * Function processAddMbr
 * Desc: Handles the response from the server regarding adding a member to the database
 ******************************************************************************/
function processAddMbr() {
    if (xhr_member.readyState === XMLHttpRequest.DONE && xhr_member.status === 200) {

        //Remove the registered event
        xhr_member.removeEventListener('readystatechange', processAddCategory, false);

        // Get response
        var myResponse = JSON.parse(this.responseText);

        // Prepare message
        var message = byId('msgAdd');

        // If member successfully added to the database, update the member selector and set message color
        if (myResponse['success']) {
            doGetMembers();
            doGetLinks();
            message.classList.remove('red');
            message.classList.add('green');

            // clear the name and password field
            byId('name').value = '';
            byId('password').value = '';

        }
        // if category wasn't added, set message color
        if (!myResponse['success']) {
            message.classList.remove('green');
            message.classList.add('red');
        }

        // set message
        message.innerHTML = myResponse['message'];
    }
}

/*******************************************************************************
 * Function doRemoveMbr
 * Desc: Sends request to remove member. Adds event listener that
 * listens for state changes in the request.
 ******************************************************************************/
function doRemoveMbr() {
    // get member to remove
    var username = byId('memberSelector').value;

    if (username == '' || username == null) {
        var message = byId('msgRemove');
        message.innerHTML = 'No member is selected';
        message.classList.remove('green');
        message.classList.add('red');
    }
    else {
        // add event listener and send request
        xhr_member.addEventListener('readystatechange', processRemoveMbr, false);
        xhr_member.open('GET', 'members.php?task=remove&username=' + username, true);
        xhr_member.send(null);
    }

}

/*******************************************************************************
 * Function processRemoveMbr
 * Desc: Handles the process of removing member
 ******************************************************************************/
function processRemoveMbr() {
    if (xhr_member.readyState === XMLHttpRequest.DONE && xhr_member.status === 200) {

        //Remove the registered event
        xhr_member.removeEventListener('readystatechange', processRemoveMbr, false);

        // Get response
        var myResponse = JSON.parse(this.responseText);

        // Prepare message
        var messageRm = byId('msgRemove');

        // If member was successfully removed from the database, update the member selector and set message
        if (myResponse['success']) {
            messageRm.classList.remove('red');
            messageRm.classList.add('green');
            doGetMembers();
            doGetLinks();
        }
        // if member wasn't removed
        if (!myResponse['success']) {
            messageRm.classList.remove('green');
            messageRm.classList.add('red');
        }

        // set message
        messageRm.innerHTML = myResponse['message'];
    }
}

/*******************************************************************************
 * Function doUploadImage
 * Desc: Upload image using xhr. Adds event listener that
 * listens for state changes in the request.
 ******************************************************************************/
function doUploadImage() {
    // clear all current messages
    clearMessages();

    // the file
    var file = byId('imageToUpload').files[0];

    // category for the image
    var category = byId('categorySelector').value;

    // message
    var message = byId('msgUpload');

    // if no file is selected, show error message
    if (!file) {
        message.classList.remove('green');
        message.classList.add('red');
        message.innerHTML = 'No file is selected!';
    }
    // if category is empty, show error message
    else if (category === ''){
        message.classList.remove('green');
        message.classList.add('red');
        message.innerHTML = 'No category is selected!';
    }
    else {
        // all checks out
        message.innerHTML = '';

        // create form data with file and category
        var formData = new FormData();
        formData.append('file', file);
        formData.append('category', category);

        // send request
        xhr_images.addEventListener('load', processUploadImage, false);
        xhr_images.open('POST', 'upload.php', true);
        xhr_images.send(formData);

        // set message to show that the file is uploading
        message.classList.remove('red');
        message.classList.remove('green');
        message.innerHTML = 'Processing...';
    }
}

/*******************************************************************************
 * Function processUploadImage
 * Desc: Handles the process of uploading images
 * the page using DOM
 ******************************************************************************/
function processUploadImage() {
    if (xhr_images.status === 200) {

        //Remove the registered event
        xhr_images.removeEventListener('readystatechange', processUploadImage, false);

        // Get response
        var myResponse = JSON.parse(this.responseText);

        // prepare message
        var message = byId('msgUpload');

        // If successful
        if (myResponse['success'] === true) {
            message.innerHTML = "Image uploaded!";
            message.classList.remove('red');
            message.classList.add('green');
        }

        if ('imageData' in myResponse) {
            var dataUrl = myResponse['imageData'];

            // create image element, set attributes, id and class
            var image = document.createElement('img');
            image.setAttribute('src', dataUrl);
            image.setAttribute('alt', 'Image: tmp');
            image.classList.add('thumbnail');

            // Set message
            message.innerHTML = 'This image exists in the database. Your copy has been uploaded.';
            message.classList.remove('green');
            message.classList.add('red');

            // Add image to page
            byId('msgUpload').appendChild(image);
        }

        else {
            message.innerHTML = myResponse['message'];
            message.classList.remove('green');
            message.classList.add('red');
        }
    }
}

/*******************************************************************************
 * Function doGetImages
 * Desc: Requests images using xhr. Adds event listener that
 * listens state changes in the request.
 ******************************************************************************/
function doGetImages() {
    // get file
    xhr_images.addEventListener('readystatechange', processGetImages, false);
    xhr_images.open('GET', 'sendimages.php', true);
    xhr_images.send(null);
}

/*******************************************************************************
 * Function processGetImages
 * Desc: Handles the process of getting the paths to the correct images and puts them on
 * the page using DOM
 ******************************************************************************/
function processGetImages() {
    if (xhr_images.readyState === XMLHttpRequest.DONE && xhr_images.status === 200) {
        //Remove the registered event
        xhr_images.removeEventListener('readystatechange', processGetImages, false);

        // Get response
        var myResponse = JSON.parse(this.responseText);

        // add thumbnails
        // get thumbnail element from DOM
        var thumbnails = byId('thumbnails');

        // add thumbnail for each image from response, add event listener to show full size image. Add category.
        for (var i = 0; i < myResponse.length; i++) {
            // get image data
            var imageName = myResponse[i]['name'];
            var imageTime = myResponse[i]['time'];
            var dataUrl = myResponse[i]['dataUrl'];

            // create image element, set attributes, id and class
            var image = document.createElement('img');
            image.setAttribute('src', dataUrl);
            image.setAttribute('alt', 'Image: ' + imageName);
            image.setAttribute('title', imageTime);
            image.classList.add('thumbnail');

            // add event listener that opens a larger image when a thumbnail is clicked
            image.addEventListener('click', openImage);

            // append image to the DOM element 'thumbnails'
            thumbnails.appendChild(image);
        }
    }
}

/*******************************************************************************
 * Function doLogin
 * Desc: Send username and password from web page form using xhr. Adds event listener that
 * listens state changes in the request.
 ******************************************************************************/
function doLogin() {
    var uname = byId('uname').value;
    var psw = byId('psw').value;
    if (byId('uname').value !== "" && byId('psw').value !== "") {
        xhr_login.addEventListener('readystatechange', processLogin, false);
        xhr_login.open('GET', 'login.php?uname=' + uname + '&psw=' +psw, true);
        xhr_login.send(null);
    }
}

/*******************************************************************************
 * Function doLogout.
 * Desc: Requests a logout. Adds event listener that
 * listens state changes in the request.
 ******************************************************************************/
function doLogout() {
    xhr_login.addEventListener('readystatechange', processLogout, false);
    xhr_login.open('GET', 'logout.php', true);
    xhr_login.send(null);
}

/*******************************************************************************
 * Function processLogin
 * Desc: Handles the login process.
 ******************************************************************************/
function processLogin() {
    if (xhr_login.readyState === XMLHttpRequest.DONE && xhr_login.status === 200) {

        // Remove the registered event
        xhr_login.removeEventListener('readystatechange', processLogin, false);

        // Get response
        var myResponse = JSON.parse(this.responseText);

        // Prepare a message
        var message = byId('msgLogin');
        message.classList.add('red');

        // If user wasn't found, set appropriate error message
        if (myResponse["userCorrect"] === false) {
            message.innerHTML = 'Username is unknown.';
        }
        // If user was found but password was incorrect, set appropriate error message
        else if (myResponse['passwordCorrect'] === false) {
            message.innerHTML = 'The password is wrong.';
        }
        // If login was successful
        else {
            // Remove error-message
            message.innerHTML = '';

            // Switch login/logout forms
            byId('logout').style.display = "block";
            byId('login').style.display = "none";

            // redirect to user page
            window.location.replace("userpage.php");
        }
    }
}

/*******************************************************************************
 * Function processLogout
 * Desc: Handles the logout process
 ******************************************************************************/
function processLogout() {
    if (xhr_login.readyState === XMLHttpRequest.DONE && xhr_login.status === 200) {
        //Remove the registered event
        xhr_login.removeEventListener('readystatechange', processLogout, false);

        // Switch login/logout buttons
        byId('login').style.display = "block";
        byId('logoutButton').style.display = "none";

        // refresh page to make certain the user isn't on a page that is forbidden for
        // users that are not logged in
        location.reload();
    }
}

/*******************************************************************************
 * Function doGetCategories
 * Desc: Requests categories using xhr. Adds event listener that
 * listens state changes in the request.
 ******************************************************************************/
function doGetCategories() {
    // get categories
    xhr_categories.addEventListener('readystatechange', processGetCategories, false);
    xhr_categories.open('GET', 'categories.php?task=fetch', true);
    xhr_categories.send(null);
}

/*******************************************************************************
 * Function processGetCategories
 * Desc: Handles the process of retrieving categories for the user page
 ******************************************************************************/
function processGetCategories() {
    if (xhr_categories.readyState === XMLHttpRequest.DONE && xhr_categories.status === 200) {
        //Remove the registered event
        xhr_categories.removeEventListener('readystatechange', processGetCategories, false);

        // Get response
        var myResponse = JSON.parse(this.responseText);

        // if response is empty, hide the upload form
        if (myResponse.length === 0) {
            byId('uploadImages').classList.add('hidden');
        } else {

            // array which will be filled with all options. Adding the options here
            // allows them to be sorted before adding to the selector.
            var options = [];

            // Add each category to the options array
            for (var i = 0; i < myResponse.length; i++)
                options.push(myResponse[i]);

            // sort all options
            options.sort();

            // Selector
            var selector = byId('categorySelector');

            // clear the selector of all current options
            while (selector.firstChild)
                selector.removeChild(selector.firstChild);

            // Add all options to the selector.
            for (var j = 0; j < options.length; j++) {
                // create option element
                var opt = document.createElement('option');
                opt.id = options[j];
                opt.value = options[j];
                opt.text = options[j];

                // add to selector
                selector.add(opt);
            }
        }
    }
}

/*******************************************************************************
 * Function doAddCategory
 * Desc: Requests category to be added. Adds event listener that
 * listens state changes in the request.
 ******************************************************************************/
function doAddCategory() {
    // clear all current messages
    clearMessages();

    // if the category field validates, add the category
    if (validateCategoryField()) {
        // get the category that is to be added
        var category = byId('ctgField').value;

        xhr_categories.addEventListener('readystatechange', processAddCategory, false);
        xhr_categories.open('GET', 'categories.php?task=add&category=' + category, true);
        xhr_categories.send(null);
    }
}

/*******************************************************************************
 * Function processAddCategory
 * Desc: Handles the process of retrieving categories for the logged in user
 ******************************************************************************/
function processAddCategory() {
    if (xhr_categories.readyState === XMLHttpRequest.DONE && xhr_categories.status === 200) {

        //Remove the registered event
        xhr_categories.removeEventListener('readystatechange', processAddCategory, false);

        // Get response
        var myResponse = JSON.parse(this.responseText);

        // Prepare message
        var message = byId('msgAdd');

        // If category successfully added to the database, update the category selector and set message color
        if (myResponse['success']) {
            doGetCategories();
            message.classList.remove('red');
            message.classList.add('green');

            // clear the categoryField
            byId('ctgField').value = '';

            // make sure the upload form is shown
            byId('uploadImages').classList.remove('hidden');
        }
        // if category wasn't added, set message color
        if (!myResponse['success']) {
            message.classList.remove('green');
            message.classList.add('red');
        }

        // set message
        message.innerHTML = myResponse['message'];
    }
}

/*******************************************************************************
 * Function doRemoveCategory
 * Desc: Requests category to be deleted. Adds event listener that
 * listens state changes in the request.
 ******************************************************************************/
function doRemoveCategory() {
    // clear all current messages
    clearMessages();

    // get the category that is to be deleted
    var category = byId('categorySelector').value;

    xhr_categories.addEventListener('readystatechange', processRemoveCategory, false);
    xhr_categories.open('GET', 'categories.php?task=remove&category=' + category, true);
    xhr_categories.send(null);
}

/*******************************************************************************
 * Function processRemoveCategory
 * Desc: Handles the process of removing category for the logged in user
 ******************************************************************************/
function processRemoveCategory() {
    if (xhr_categories.readyState === XMLHttpRequest.DONE && xhr_categories.status === 200) {

        //Remove the registered event
        xhr_categories.removeEventListener('readystatechange', processRemoveCategory, false);

        // Get response
        var myResponse = JSON.parse(this.responseText);

        // Prepare message
        var message = byId('msgRemove');

        // If category successfully removed from the database, update the category selector and set message
        if (myResponse['success']) {
            doGetCategories();
            message.classList.remove('red');
            message.classList.add('green');

            // If user has no categories, hide upload form
            if (byId('categorySelector').length === 0)
                byId('uploadImages').classList.add('hidden');
        }
        // if category wasn't removed
        if (!myResponse['success']) {
            // set error message
            message.classList.remove('green');
            message.classList.add('red');
        }

        // set message
        message.innerHTML = myResponse['message'];
    }
}

/*******************************************************************************
 * Function doGetMembers
 * Desc: Requests members using xhr. Adds event listener that
 * listens state changes in the request.
 ******************************************************************************/
function doGetMembers() {
    // get members
    xhr_getMembers.addEventListener('readystatechange', processGetMembers, false);
    xhr_getMembers.open('GET', 'members.php?task=fetch', true);
    xhr_getMembers.send(null);
}

/*******************************************************************************
 * Function processGetMembers
 * Desc: Handles the process of retrieving members for the selector on the admin page
 ******************************************************************************/
function processGetMembers() {
    if (xhr_getMembers.readyState === XMLHttpRequest.DONE && xhr_getMembers.status === 200) {
        //Remove the registered event
        xhr_getMembers.removeEventListener('readystatechange', processGetMembers, false);

        // Get response
        var myResponse = JSON.parse(this.responseText);

        // Get all usernames from response
        var usernames = Object.getOwnPropertyNames(myResponse);

        // sort usernames
        usernames.sort();

        // array/object which will be filled with all members username + roles
        var members = [];

        // Add each member to members, set username as key. Create a value of all roles.
        for (var i = 0; i < usernames.length; i++) {
            // get all roles
            var roles = myResponse[usernames[i]];

            // sort roles to be sure they are always displayed the same in the selector
            roles.sort();

            // create a string that will contain all roles
            var roleString = '';

            // fill roles string
            for (var j=0; j < roles.length; j++) {
                // add '(' if this is the first iteration
                if (j === 0)
                    roleString += '(';
                // add a slash if this isn't the first iteration
                if (j !== 0)
                    roleString += '/';
                // add role
                roleString += roles[j];
            }
            // add ')' to string
            roleString += ')';

            // Add username and roles string to members
            members[usernames[i]] = roleString;
        }

        // Selector
        var selector = byId('memberSelector');

        // clear the selector of all current members
        while (selector.firstChild)
            selector.removeChild(selector.firstChild);

        // Add all members to the selector.
        for (var username in members)
            if (members.hasOwnProperty(username)) {
                // create option element
                var opt = document.createElement('option');
                opt.id = username;
                opt.value = username;
                opt.text = username + ' ' + members[username];

                // add to selector
                selector.add(opt);
            }

    }
}

/*******************************************************************************
 * Function doGetRoles
 * Desc: Requests roles using xhr. Adds event listener that
 * listens state changes in the request.
 ******************************************************************************/
function doGetRoles() {
    // get roles
    xhr_getRoles.addEventListener('readystatechange', processGetRoles, false);
    xhr_getRoles.open('GET', 'members.php?task=roles', true);
    xhr_getRoles.send(null);
}

/*******************************************************************************
 * Function processGetRoles
 * Desc: Handles the process of retrieving roles for the checkboxes on the admin page
 ******************************************************************************/
function processGetRoles() {
    if (xhr_getRoles.readyState === XMLHttpRequest.DONE && xhr_getRoles.status === 200) {
        //Remove the registered event
        xhr_getRoles.removeEventListener('readystatechange', processGetMembers, false);

        // Get response
        var myResponse = JSON.parse(this.responseText);

        // sort roles
        myResponse.sort();

        // Add all roles
        for (var i = 0; i < myResponse.length; i++) {
            // Create input element for checkboxes
            var input = document.createElement('input');
            input.setAttribute('type', 'checkbox');
            input.classList.add('checkbox');
            input.setAttribute('name', myResponse[i]);
            input.setAttribute('id', myResponse[i]);
            input.setAttribute('value', myResponse[i] )

            // create label element
            var label = document.createElement('label');
            label.setAttribute('for', myResponse[i]);
            label.innerHTML = myResponse[i];

            // add to page
            byId('roles').appendChild(input);
            byId('roles').appendChild(label);
        }
    }
}

/*******************************************************************************
 * Function doGetLinks
 * Desc: Requests links. Adds event listener that
 * listens state changes in the request.
 ******************************************************************************/
function doGetLinks(document) {
    xhr_links.addEventListener('readystatechange', processGetLinks, false);
    xhr_links.open('GET', 'links.php?links=' + document, true);
    xhr_links.send(null);
}

/*******************************************************************************
 * Function processGetLinks
 * Desc: Handles the process of getting the links for the page
 ******************************************************************************/
function processGetLinks() {
    if (xhr_links.readyState === XMLHttpRequest.DONE && xhr_links.status === 200) {
        //Remove the registered event
        xhr_links.removeEventListener('readystatechange', processGetLinks, false);

        // Get response
        var myResponse = JSON.parse(this.responseText);

        // Delete current links on the page, if any
        if (document.getElementsByTagName('nav')[0])
            document.getElementsByTagName('nav')[0].parentNode.removeChild(document.getElementsByTagName('nav')[0]);

        // create new nav element
        var nav = document.createElement('nav');

        // if links array isn't empty, add the links to the page
        // myResponse may contain several arrays with links
        // for each array
        for (var linksArray in myResponse) {
            if (myResponse.hasOwnProperty(linksArray)) {

                // first create a links section
                var linksSection = document.createElement('ul');

                // next we add links to this section
                for (var link in  myResponse[linksArray]) {
                    if (myResponse[linksArray].hasOwnProperty(link)) {

                        // create a new link element <a>
                        var linkElement = document.createElement('a');
                        linkElement.setAttribute('href', myResponse[linksArray][link]);
                        linkElement.innerHTML = firstToUppercase(link);

                        // create a new list element <li> and append link element
                        var listElement = document.createElement('li');
                        listElement.appendChild(linkElement);

                        // append list element to list of links
                        linksSection.appendChild(listElement);
                    }
                }
            }
            // Add links to nav element
            nav.appendChild(linksSection);
        }
        // append links to menu
        var menu = document.getElementsByTagName('aside')[0];
        menu.appendChild(nav);
    }
}

/*******************************************************************************
 * Function openImage
 * Desc: Opens tha larger image when a thumbnail is clicked
 ******************************************************************************/
function openImage() {
    // Remove any open full size image
    if (byId('largeImage')) {
        byId('largeImage').parentNode.removeChild(byId('largeImage'));
    }

    // Create large img element
    var image = document.createElement('img');
    image.setAttribute('alt', 'large photo');
    image.id ='largeImage';

    // set source for large photo. Since full sized photos are used as thumbnails,
    // the source is the same as for the thumbnail
    image.src = this.src;

    // add event listener to close large photo
    image.addEventListener('click', closeImage);

    // append large photo
    this.parentNode.insertBefore(image, this.nextSibling);
}

/*******************************************************************************
 * Function closeImage
 * Desc: Close tha larger image when it is clicked
 ******************************************************************************/
function closeImage() {
    this.parentNode.removeChild(this);
}

/*******************************************************************************
 * Function validateCategoryField
 * Desc: Validates input when user creates a new category
 ******************************************************************************/
function validateCategoryField() {
    // clear all current messages
    clearMessages();

    // get value of category field
    var value = byId('ctgField').value;

    // message
    var message = byId('msgAdd');

    // if value contains whitespace
    if (value.indexOf(' ') !== -1) {
        message.classList.remove('green');
        message.classList.add('red');
        message.innerHTML = 'Only one word is allowed for categories';
        return false;
    }

    // restrict to alphabetic english characters
    else if (value.match("[^a-zA-Z]")) {
        message.classList.remove('green');
        message.classList.add('red');
        message.innerHTML = 'Use english alphabetic characters only (a-z, A-Z)';
        return false;
    }

    // if value is empty
    else if (value === '') {
        message.classList.remove('green');
        message.classList.add('red');
        message.innerHTML = 'Category can not be empty';
        return false;
    }

    // restrict to 20 characters
    else if (value.length > 20) {
        message.classList.remove('green');
        message.classList.add('red');
        message.innerHTML = 'Category can not be longer than 20 characters';
        return false;
    }

    // all checks out
    message.innerHTML = '';

    return true;
}

/*******************************************************************************
 * Function validateName
 * Desc: Validates input when admin creates a new member name
 ******************************************************************************/
function validateName() {
    // clear all current messages
    clearMessages();

    // get value of name field
    var value = byId('name').value;

    // message
    var message = byId('msgAdd');

    // restrict to alphanumeric english characters
    if (value.match("[^a-zA-Z0-9]")) {
        message.classList.remove('green');
        message.classList.add('red');
        message.innerHTML = 'Use english alphanumeric characters only (a-z, A-Z, 0-9)';
        return false;
    }

    // if value is empty
    else if (value === '') {
        message.classList.remove('green');
        message.classList.add('red');
        message.innerHTML = 'Username can not be empty';
        return false;
    }

    // all checks out
    message.innerHTML = '';

    return true;
}

/*******************************************************************************
 * Function validatePassword
 * Desc: Validates password when admin creates a new password
 * password is shown and validated as the administrator type.
 ******************************************************************************/
function validatePassword() {
    // clear all current messages
    clearMessages();

    // get value of name field
    var value = byId('password').value;

    // message
    var message = byId('msgAdd');

    // disallow whitespace
    if (/\s/.test(value)) {
        message.classList.remove('green');
        message.classList.add('red');
        message.innerHTML = 'Spaces are not allowed in passwords';
        return false;
    }

    // if value is empty
    else if (value === '') {
        message.classList.remove('green');
        message.classList.add('red');
        message.innerHTML = 'Password can not be empty';
        return false;
    }

    // all checks out
    message.innerHTML = '';

    return true;
}

/*******************************************************************************
 * Function showFileName
 * Desc: Shows file name when a file is selected
 ******************************************************************************/
function showFileName() {
    // get message
    var message = byId('fileName');

    // Get filename without full path
    var filename = this.value.substring(this.value.lastIndexOf('/')+1);
    filename = filename.substring(filename.lastIndexOf('\\')+1);

    // set message
    message.innerHTML = filename;
}

/*******************************************************************************
 * Function clearMessages
 * Desc: Resets all messages belonging to the class message
 ******************************************************************************/
function clearMessages() {

    // get all elements with class name message
    var messages = document.getElementsByClassName('message');

    // delete inner html for all messages
    for (var i = 0; i < messages.length; i++) {
        messages[i].innerHTML = '';
    }
}
