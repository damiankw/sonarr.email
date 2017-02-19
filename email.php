<?php
  // A list of all servers and API's
  $SERVERS = Array(
    'sonarr.mydomain' => 'ba94c7cc86e4488ea233871a1234ff',
    '10.0.0.44' => 'ba94c7cc86e4488ea23387111111ff'
  );

  // Email address to send reports to
  $EMAIL = 'damian@mydomain.com';
  
  /********** NO NEED TO EDIT BELOW THIS **********/
  require_once('sonarr.email.php');
  
  // Do it all!
  $SONARR = new Sonarr($SERVERS, $EMAIL);
?>
