<?php 

define('SOURCE', $update['result']["source"]);

function sendMessage($message) {

    $json = array(
                "source" => SOURCE,
                "speech" => $message,
                "displayText" => $message,
                "contextOut" => array()
    );
    echo json_encode($json);
}

function sendErrors($errors) {
    $message = '';

    foreach ($errors as $key => $value) {

        $message = (empty($message) ? '' : $message . ', ') . $value;
    }

    $json = array(
                "source" => SOURCE,
                "speech" => $message,
                "displayText" => $message,
                "contextOut" => array()
    );
    echo json_encode($json);
}
?>
