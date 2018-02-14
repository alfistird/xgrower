<?php
require_once("facebook.php");

  $config = array(
      'appId' => '293811804131209',
      'secret' => '6a699fcf62446209a483f73e35146cec',
      'fileUpload' => false, // optional
      'allowSignedRequest' => false, // optional, but should be set to false for non-canvas apps
  );

  $facebook = new Facebook($config);
  $user_id = $facebook->getUser();
  if($user_id) {

      // We have a user ID, so probably a logged in user.
      // If not, we'll get an exception, which we handle below.
      try {

        $user_profile = $facebook->api('/me','GET');
        echo "Bienvenido " . $user_profile['id'];
        $estadoURL = "estadoview.php";
        echo '<BR/>Please <a href="' . $estadoURL . '">enter.</a>';
      } catch(FacebookApiException $e) {
        // If the user is logged out, you can have a 
        // user ID even though the access token is invalid.
        // In this case, we'll get an exception, so we'll
        // just ask the user to login again here.
        //$login_url = $facebook->getLoginUrl();
        $login_url = "index.php"; 
        echo 'Please <a href="' . $login_url . '">login.</a>';
        error_log($e->getType());
        error_log($e->getMessage());
      }   
    } else {

      // No user, print a link for the user to login
      $login_url = $facebook->getLoginUrl();
      echo 'Please <a href="' . $login_url . '">login.</a>';

    }
?> 