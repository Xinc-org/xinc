<?php
$logFile = '/var/log/xinc.xml';
$statusDir = '/var/log/xinc';
date_default_timezone_set('America/New_York');
?>
<h1>Xinc Web Report</h1>
<small>Timezone is currently set to <?php echo date_default_timezone_get() ?> (see index.php).</small>
<h2>Projects Summary</h2>
<table>
<?php 
$handle = opendir($statusDir);
while (false !== ($statusFile = readdir($handle))) {
    if (strpos($statusFile, '.xml') !== false) {
        $statusXml = new SimpleXMLElement(file_get_contents($statusDir . '/' . $statusFile)); 
        ?>
        <tr>
            <td><em>Project name:</em> </td>
            <td><strong><?php echo $statusXml->name ?></strong></td>
        </tr>
        <?php if ((string) $statusXml->lastbuildtime): ?>
            <tr>
                <td><em>Last build status:</em> </td>
                <td><font color="#<?php echo ((string) $statusXml->buildsuccessful) ? '00ff00' : 'ff0000'; ?>"><?php echo ((string) $statusXml->buildsuccessful) ? 'Pass' : 'Fail'; ?></span></td>
            </tr>
        <?php endif ?>
        <tr>
            <td><em>Last build time:</em> </td>
            <td><?php echo ((string) $statusXml->lastbuildtime) ? strftime('%a, %d %b %Y %H:%M:%S %z', (string) $statusXml->lastbuildtime) : 'Never' ?></td>
        </tr>
        <tr>
            <td><em>Next build time:</em> </td>
            <td><?php echo strftime('%a, %d %b %Y %H:%M:%S %z', (string) $statusXml->schedule) ?></td>
        </tr>
        <tr>
            <td></td>
        </tr>
        <?php 
    }
}
?>
</table>
<h2>Recent Log Messages</h2>
<table border="1">
<tr><th>Level</th><th>Time</th><th>Message</th></tr>
<?php
$logXml = new SimpleXMLElement(file_get_contents($logFile));
foreach ($logXml->children() as $logEntry) {
    $attributes = $logEntry->attributes();
    $priority = $attributes['priority'];
    $time = strftime('%a, %d %b %Y %H:%M:%S %z', substr((string) $logEntry, 0, strpos((string) $logEntry, '::')));
    $message = substr((string) $logEntry, strrpos((string) $logEntry, '::') + 2);
    ?>
    <tr>
        <td><?php echo $priority ?> </td>
        <td><?php echo $time ?></td>
        <td><?php echo $message ?></td>
    </tr>
    <?php
}
?>
</table>
<?php
/*
<build>
  <message priority="info">1178356800::sleeping for 2 seconds</message>
  <message priority="info">1178356799::PUBLISHING status to email 
To: gfoster@fosterconsulting.com
Subject: Email publisher is working
Message: The xinc test build failed.</message>
  <message priority="debug">1178356799::EXECUTING PUBLISHERS</message>

  <message priority="info">1178356799::BUILD PASSED</message>
  <message priority="debug">1178356799::return = 0</message>
  <message priority="info">1178356799::changing directory to .</message>
  <message priority="info">1178356799::code not up to date, building new project</message>
  <message priority="info">1178356799::timer expired checking modification sets</message>
  <message priority="info">1178356797::sleeping for 2 seconds</message>

  <message priority="info">1178356795::sleeping for 2 seconds</message>
  <message priority="info">1178356795::PUBLISHING status to email 
To: gfoster@fosterconsulting.com
Subject: Email publisher is working
Message: The xinc test build failed.</message>
  <message priority="debug">1178356795::EXECUTING PUBLISHERS</message>
  <message priority="info">1178356795::BUILD PASSED</message>
  <message priority="debug">1178356795::return = 0</message>
  <message priority="info">1178356795::changing directory to .</message>

  <message priority="info">1178356795::code not up to date, building new project</message>
  <message priority="info">1178356795::timer expired checking modification sets</message>
  <message priority="info">1178356793::sleeping for 2 seconds</message>
</build>
*/
?>