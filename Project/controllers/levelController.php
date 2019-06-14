<?php

$level_data = json_decode($level_info['data'], true);

$level_location = $session['location'];

$state = $session['state'];

$message = null;

$level_id = $session['level_id'];



/**
 * Set Location to start location
 */

if ($level_location < 0) { // Location not set yet

    $level_location = (int) $level_data['level']['start']; // Get start from json
}

/**
 * Manage state
 */

 switch ($session['state']) {
     case 0:
        printLevelSelect();
        break;

    case 1:
        manageLevelSelect();
        break;
    
    case 2:
        confirmLevel();
        break;

    case 3:
        manageOptions();
        break;
 }

 if (count($errors) > 0) {
        sendErrors($errors);
    return;
}

/**
 * State Functions
 */


function checkIfInt($word) {
    if(ctype_digit($word)) {
       return true;
    } else {
       return false;
    }
 }

 
 function manageLevelSelect() {

    global $update, $state, $errors, $message, $level_id, $level_info;  

    $input = $update['result']['resolvedQuery']; // Get raw input

    $words = explode(" ", $input); // Split all the words into an array
    
    $num = null;

    foreach($words as $word) 
    {
        if(checkIfInt($word)) {
            // Is integer, take int as ID
            $num = intval($word);

            break;
        }
    }

    if (is_null($num)) {
        $errors['id'] = "Invalid level ID!";
    } else {
        updateLevelData($num);


        $lvl_name = $level_info['name'];

        $message = "Would you like to play level: '$lvl_name'? Whose id is '$level_id'! Say yes or no to confirm!";
        $state = 2;
    }
}

function updateLevelData($lvl_id) {
    global $level_info, $errors, $level_id, $conn;

    $sql = "SELECT levels.name, levels.level_data, levels.safe_for_work
    FROM levels WHERE levels.id = $lvl_id";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);

        $level_info['name'] = $data['name'];
        $level_info['data'] = $data['level_data'];
        $level_info['safe'] = $data['safe_for_work'];

        $level_id = $lvl_id;

    } else {
        $errors['database'] = "Could not collect level info!";
    }
}



function confirmLevel() {
    global $update, $errors, $state, $message, $level_location, $level_data;
    $input = $update['result']['resolvedQuery']; // Get raw input

    $words = explode(" ", $input); // Split all the words into an array

    $confirm = null;

    foreach($words as $word) 
    {
        if (strtolower($word) === "yes") {
            $confirm = true;
            break;
        } else if (strtolower($word) === "no") {
            $confirm = false;
            break;
        }
    }

    if (is_null($confirm)) {
        $errors['id'] = "I did not understand what you said, please say yes or no.";
    } else if ($confirm) {
        // YES

        $state = 3;

        $scenario = $level_data['level']['locations']["$level_location"]['scenario'];
        $message = $scenario;
    } else {
        // NO

        $state = 1; // Maybe 1

        $message = "Cancelled current level selection! Which level would you like to play? Name an ID!";
    }
}

function manageOptions() {
    global $level_data, $update, $errors, $level_location, $message;

    $options = $level_data['level']['locations']["$level_location"]['options'];

    $input = $update['result']['resolvedQuery']; // Get raw input
   
    $words = explode(" ", $input); // Split all the words into an array

   
   $best_matches = 0;

   $next_location = null;
   
   foreach($options as $sentence => $next_loc)  // Loop trough options
   {
       $matches = 0;
   
       foreach($words as $word) 
       {
   
           if (strpos($sentence, $word) !== false) {
               // Found match!
               $matches++;
           }
       }
   
       if ($matches > $best_matches) {
           $best_matches = $matches;
           $next_location = $next_loc;
       }
   }
   
   if (is_null($next_location)) {
       // No good player input, no matching parameters.
   
       $scenario = $level_data['level']['locations']["$level_location"]['scenario'];

       $errors['input'] = "We couldn't match any words of your sentence with one of the options! " . $scenario;
   } else {

    $level_location = $next_location;
       
    $scenario = $level_data['level']['locations']["$level_location"]['scenario'];

    $message = $scenario;
   }

   // TODO if end set state to 0
}

function printLevelSelect() {
    global $message, $state;  
    $message = "Which level would you like to play? Name an ID!";
    $state = 1;
}

/**
 * Update all information
 */

$sql = "UPDATE sessions_info SET sessions_info.location = $level_location, sessions_info.level_id = $level_id, sessions_info.state = $state
WHERE sessions_info.id = $info_id"; // UPDATE location

if (!mysqli_query($conn, $sql)) {
    $errors['database'] = 'Database error: data update failed!';
}

if (count($errors) > 0) { // Checks for errors
    sendErrors($errors);
    return;
}

sendMessage($message);

return;

?>