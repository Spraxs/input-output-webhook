<?php 
$errors = array();

require 'config/db.php';
require 'controllers/requestController.php';
require 'controllers/responseController.php';

$user_id = $update['originalRequest']['data']['user']['userId'];

$id = null;

if ($user_id === null) 
	$errors['userId'] = 'Could not retieve userId!';

$sql = "SELECT sessions.id FROM sessions WHERE id_user=? LIMIT 1"; // Select session id via userId
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $user_id);

$userCount = 0;

if ($stmt->execute()) { // Check if execute is finished successfully
	$result = $stmt->get_result();
    $userCount = $result->num_rows;
} else {
	$errors['database'] = 'Database error: Could not check if userId is already defined';
}

$stmt->close(); // Close statement

if ($userCount === 0) { // Check if userId is defined in database
	$sql = "INSERT INTO `sessions` (`id_user`) VALUES (?)"; // Register user

	$stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $user_id);

    if ($stmt->execute()) {
        $id = $conn->insert_id;
	} else {
		$errors['database'] = 'Database error: failed to register user';
	}
} else {
	$sql = "SELECT id FROM sessions WHERE session.id_user = '$user_id' LIMIT 1";
	if (mysqli_num_rows($result) > 0) {
		$data = mysqli_fetch_assoc($result);
		$id = $data['id'];
	} else {
		$errors['database'] = 'Database error: failed to select session id';
	}
}


if (count($errors) > 0) {
	sendErrors($errors);
	return;
}

/**
 * Collect all data needed from database
 */

$info_id = null;

$level_info = array(); // create empty level array
$session = array(); // create empty session array

$sql = "SELECT sessions_info.level_id, sessions_info.location, sessions_info.l_key, sessions_info.state,
		levels.name, levels.level_data, levels.safe_for_work,
		sessions_info.id 
		FROM sessions_info 
		JOIN sessions ON sessions_info.id = sessions.id_session 
		JOIN levels ON levels.id = sessions_info.level_id 
		WHERE sessions.id = '$id'"; //TODO test if LIMIT 1 works
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
	$data = mysqli_fetch_assoc($result);

	$session['level_id'] = $data['level_id']; // Good
	$session['location'] = $data['location']; // Good
	$session['keys'] = $data['l_key']; // Good
	$session['state'] = $data['state'];

	$level_info['name'] = $data['name'];
	$level_info['data'] = $data['level_data']; // Good
	$level_info['safe'] = $data['safe_for_work'];

	$info_id = $data['id']; // good

} else {
	$errors['database'] = "Could not collect information!";
}

if (count($errors) > 0) {
	sendErrors($errors);
	return;
}

/**
 * Manage level
 */

 require 'controllers/levelController.php';

?>