<?php
/* Sonarr.Email
 * by Damian West <damian@damian.id.au>
 * -
 * Email a list of episodes for a specific day.
 */

  $SONARR_URL = "sonarr.ip";
  $SONARR_API = "api-code";
  $EMAIL = "email@1.com,email@2.com";
  $HASH = time();
 
  $HTTP_OPTS = Array(
  'http' => Array(
    'method' => "GET",
    'header' => "Accept: text/html\r\n".
                "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36\r\n"
    )
  );
  
  $HTTP_CONTEXT = stream_context_create($HTTP_OPTS);
  
  // set up the URL (by default, this searches images of a 'Large' type)
  $URL = "http://". $SONARR_URL ."/api/calendar?apikey=". $SONARR_API ."&start=" . date('Y/m/d');
  
  // grab the HTML
  $HTML = file_get_contents($URL, false, $HTTP_CONTEXT);

  // convert to Json
  $JSON = json_decode($HTML, true);
  
  // create the email
  system('echo "To: ' . $EMAIL . '" > /tmp/nadteu.'. $HASH .'.eml');
  system('echo "From: TV Monitor <download@nictitate.net>" >> /tmp/nadteu.'. $HASH .'.eml');
  system('echo "Subject: [' . date('d/m/o') . '] TV Update" >> /tmp/nadteu.'. $HASH .'.eml');
  system('echo "MIME-Version: 1.0" >> /tmp/nadteu.'. $HASH .'.eml');
  system('echo "Content-Type: text/html; charset=ISO-8859-1" >> /tmp/nadteu.'. $HASH .'.eml');
  system('echo "" >> /tmp/nadteu.'. $HASH .'.eml');
  system('echo "<html>" >> /tmp/nadteu.'. $HASH .'.eml');
  system('echo "<body style=\'font-family: Calibri; font-size: 11pt;\'>" >> /tmp/nadteu.'. $HASH .'.eml');
  system('echo "<strong style=\'font-size: 14pt;\'>TV Monitor</strong> - There are ' . count($JSON) . ' episodes released today.<br />" >> /tmp/nadteu.'. $HASH .'.eml');
  system('echo "<br />" >> /tmp/nadteu.'. $HASH .'.eml');

  if (count($JSON) > 0) {
    system('echo "<table cellspacing=\'0\' style=\'width: 100%; border: 1px solid #137CDD; font-family: Calibri; font-size: 8pt;\'>" >> /tmp/nadteu.'. $HASH .'.eml');
    system('echo "  <tr style=\'font-weight: bold; background-color: #137CDD; color: #FFFFFF;\'>" >> /tmp/nadteu.'. $HASH .'.eml');
    system('echo "    <td>STATUS</td>" >> /tmp/nadteu.'. $HASH .'.eml');
    system('echo "    <td>SERIES</td>" >> /tmp/nadteu.'. $HASH .'.eml');
    system('echo "    <td>S00E00</td>" >> /tmp/nadteu.'. $HASH .'.eml');
    system('echo "    <td>EPISODE</td>" >> /tmp/nadteu.'. $HASH .'.eml');
    system('echo "    <td>QUALITY</td>" >> /tmp/nadteu.'. $HASH .'.eml');
    system('echo "    <td>FILENAME</td>" >> /tmp/nadteu.'. $HASH .'.eml');
    system('echo "  </tr>" >> /tmp/nadteu.'. $HASH .'.eml');

    $ROW = 0;

    foreach ($JSON as $EPISODE) {
      system('echo "  <tr style=\'background-color: ' . ($ROW == 0 ? '#EEEEEE' : '#FFFFFF') . ';\'>" >> /tmp/nadteu.'. $HASH .'.eml');
      system('echo "    <td>' . (isset($EPISODE['episodeFile']['quality']['quality']['name']) ? 'Complete' : 'Waiting..') . '</td>" >> /tmp/nadteu.'. $HASH .'.eml');
      system('echo "    <td>' . $EPISODE['series']['title'] . '</td>" >> /tmp/nadteu.'. $HASH .'.eml');
      system('echo "    <td>S' . (strlen($EPISODE['seasonNumber']) == 1 ? '0' : '') . $EPISODE['seasonNumber'] .'E'. (strlen($EPISODE['episodeNumber']) == 1 ? '0' : '') . $EPISODE['episodeNumber'] .'</td>" >> /tmp/nadteu.'. $HASH .'.eml');
      system('echo "    <td>' . $EPISODE['title'] . '</td>" >> /tmp/nadteu.'. $HASH .'.eml');
      system('echo "    <td>' . (isset($EPISODE['episodeFile']['quality']['quality']['name']) ? $EPISODE['episodeFile']['quality']['quality']['name'] : 'n/a') . '</td>" >> /tmp/nadteu.'. $HASH .'.eml');
      system('echo "    <td>' . (isset($EPISODE['episodeFile']['sceneName']) ? $EPISODE['episodeFile']['sceneName'] : 'n/a') . '</td>" >> /tmp/nadteu.'. $HASH .'.eml');
      system('echo "  </tr>" >> /tmp/nadteu.'. $HASH .'.eml');

      if ($ROW == 0) { $ROW = 1; } else { $ROW = 0; }
    }
  }

  system('echo "</table>" >> /tmp/nadteu.'. $HASH .'.eml');
  system('echo "" >> /tmp/nadteu.'. $HASH .'.eml');
  system('echo "." >> /tmp/nadteu.'. $HASH .'.eml');

  system('/usr/sbin/sendmail -ftvdl@nictitate.net ' . $EMAIL . ' < /tmp/nadteu.'. $HASH .'.eml');

  system('rm /tmp/nadteu.'. $HASH .'.eml');

  echo date('r') ."]: & Sent email to " . $EMAIL . " (". count($JSON) . " total)". PHP_EOL;

?>