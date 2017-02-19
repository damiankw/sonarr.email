<?php
/* Sonarr.Email
 * by Damian West <damian@damian.id.au>
 * -
 * Email a list of episodes for a specific day.
 */

class Sonarr {
  function Sonarr($SERVERS, $EMAIL) {
    $EPISODES = Array();
    
    // loop through all servers
    foreach ($SERVERS as $SERVER => $API) {
      // gather detail from the server
      $EPISODES = array_merge($EPISODES, $this->get_shows($SERVER, $API));
    }
    
    $this->email($EPISODES, $EMAIL);
  }
  
  function get_shows($SERVER, $API) {
    // set up the HTTP Options
    $HTTP_OPTS = Array(
    'http' => Array(
      'method' => "GET",
      'header' => "Accept: text/html\r\n".
                  "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36\r\n"
      )
    );
    $HTTP_CONTEXT = stream_context_create($HTTP_OPTS);

    // set up the URL (do it over a few days, that way we can pick out the Utc time for current location)
    $URL = "http://". $SERVER ."/api/calendar?apikey=". $API ."&start=" . date('Y-m-d', strtotime('-1 days')) ."&end=". date('Y-m-d', strtotime('+1 days'));

    // grab the HTML
    $HTML = file_get_contents($URL, false, $HTTP_CONTEXT);

    // convert to Array
    $JSON = json_decode($HTML, true);
    
    // return the array
    return $JSON;
  }

  function email($EPISODES, $EMAIL) {
    // just a hash for the filename
    $HASH = time();
    
    // create the email
    $ROW = 0;
    $TOTAL = 0;
    $FIRST = "";
    $SECOND = "";
    $THIRD = "";

    foreach ($EPISODES as $EPISODE) {
      // Check if the date is actually for today (UTC) - otherwise it comes through as US date ..
      if (date('d-m-o') == date('d-m-o', strtotime($EPISODE['airDateUtc']))) {
        $TOTAL++;
        $THIRD .= "  <tr style='background-color: ". ($ROW == 0 ? '#EEEEEE' : '#FFFFFF') .";'>" . PHP_EOL;
        $THIRD .= "    <td>". date('h:ia', strtotime($EPISODE['airDateUtc'])) ."</td>" . PHP_EOL;
        $THIRD .= "    <td>". $EPISODE['series']['title'] ."</td>" . PHP_EOL;
        $THIRD .= "    <td>". (strlen($EPISODE['seasonNumber']) == 1 ? '0' : '') . $EPISODE['seasonNumber'] .'E'. (strlen($EPISODE['episodeNumber']) == 1 ? '0' : '') . $EPISODE['episodeNumber'] ."</td>" . PHP_EOL;
        $THIRD .= "    <td>". $EPISODE['title'] . "</td>" . PHP_EOL;
        $THIRD .= "    <td>". (isset($EPISODE['episodeFile']['quality']['quality']['name']) ? $EPISODE['episodeFile']['quality']['quality']['name'] : 'n/a') . "</td>" . PHP_EOL;
        $THIRD .= "    <td>". (isset($EPISODE['episodeFile']['sceneName']) ? $EPISODE['episodeFile']['sceneName'] : 'n/a') ."</td>" . PHP_EOL;
        $THIRD .= "    <td>". (isset($EPISODE['episodeFile']['quality']['quality']['name']) ? 'Complete' : 'Waiting..') ."</td>" . PHP_EOL;
        $THIRD .= "  </tr>" . PHP_EOL;

        if ($ROW == 0) { $ROW = 1; } else { $ROW = 0; }
      }
    }

    $FIRST .= "To: $EMAIL" . PHP_EOL;
    $FIRST .= "From: TV Monitor <download@nictitate.net>" . PHP_EOL;
    $FIRST .= "Subject: [". date('d/m/o') ."] TV Update" . PHP_EOL;
    $FIRST .= "MIME-Version: 1.0" . PHP_EOL;
    $FIRST .= "Content-Type: text/html; charset=ISO-8859-1" . PHP_EOL;
    $FIRST .= "" . PHP_EOL;
    $FIRST .= "<html>" . PHP_EOL;
    $FIRST .= "<body style='font-family: Calibri; font-size: 11pt;'>" . PHP_EOL;
    $FIRST .= "<strong style='font-size: 14pt;'>TV Monitor</strong> - There are $TOTAL episodes released today.<br />" . PHP_EOL;
    $FIRST .= "<br />" . PHP_EOL;

    if ($TOTAL > 0) {
      $SECOND .= "<table cellspacing='0' style='width: 100%; border: 1px solid #137CDD; font-family: Calibri; font-size: 8pt;'>" . PHP_EOL;
      $SECOND .= "  <tr style='font-weight: bold; background-color: #137CDD; color: #FFFFFF;'>" . PHP_EOL;
      $SECOND .= "    <td>TIME</td>" . PHP_EOL;
      $SECOND .= "    <td>SERIES</td>" . PHP_EOL;
      $SECOND .= "    <td>S00E00</td>" . PHP_EOL;
      $SECOND .= "    <td>EPISODE</td>" . PHP_EOL;
      $SECOND .= "    <td>QUALITY</td>" . PHP_EOL;
      $SECOND .= "    <td>FILENAME</td>" . PHP_EOL;
      $SECOND .= "    <td>STATUS</td>" . PHP_EOL;
      $SECOND .= "  </tr>" . PHP_EOL;

    }

    $THIRD .= "</table>" . PHP_EOL;
    $THIRD .= "" . PHP_EOL;
    $THIRD .= "." . PHP_EOL;
     
    system('echo "'. $FIRST . $SECOND . $THIRD .'" >> /tmp/tmp.email.'. $HASH .'.eml');

    system('/usr/sbin/sendmail ' . $EMAIL . ' < /tmp/tmp.email.'. $HASH .'.eml');

    system('rm /tmp/tmp.email.'. $HASH .'.eml');

    echo date('r') ."]: & Sent email to " .$EMAIL . " (". $TOTAL . " total)". PHP_EOL;
  }
}


?>