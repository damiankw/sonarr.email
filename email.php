<?php
  // A list of all servers and API's
  $SERVERS = Array(
    'sonarr.damian.id.au' => 'ba94c7cc86e4488ea233871a8ab5eb11'
  );

  // Email address to send reports to
  $EMAIL = 'damian@damian.id.au';
  
  /********** NO NEED TO EDIT BELOW THIS **********/
  require_once('sonarr.email.php');
  
  // Do it all!
  $SONARR = new Sonarr($SERVERS, $EMAIL);
?>