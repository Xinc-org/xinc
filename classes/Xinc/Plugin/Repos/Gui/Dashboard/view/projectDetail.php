<?php 
$bgColor=$this->project['build.status']==1?'green':'red';
?>
<html>
<title>Xinc Dashboard</title>
<head>
<style>
.debug {display:;}
.info {display:;}
</style>
<script type="text/javascript">

function hide(type){
	var idx=0;
	if(type=="debug") idx=0;
	if(type=="info") idx=1;
	document.styleSheets[0].deleteRule(idx); //delete the second rule
	document.styleSheets[0].insertRule('.'+type+' { display:none; }',idx);
}
function show(type){
	var idx=0;
	if(type=="debug") idx=0;
	if(type=="info") idx=1;
	document.styleSheets[0].deleteRule(idx); //delete the second rule
	document.styleSheets[0].insertRule('.'+type+' { display:; }',idx);
}
</script>
</head>
<body>
<h1><a href="/dashboard">
<?php echo $this->getTitle(); ?>
</a> - Project Details for <?php echo $this->projectName; ?></h1>
<table cellspacing="0" cellpadding="5" width="100%">
<tr height="50">
<td width="200"><strong>Status</strong></td>
<td width="600" bgcolor="<?php echo $bgColor; ?>"></td>
<td rowspan="5" valign="top"><strong>All Builds</strong>
<table>

<?php foreach ($this->historyBuilds as $history) {
$bgColorH = $history['build.status'] == 1 ? 'green':'red';
    ?>
    <tr>
<td bgcolor="<?php echo $bgColorH;?>" width="50">
<a href="/dashboard/detail/?project=<?php echo $this->projectName; ?>&timestamp=<?php echo $history['build.time']; ?>">
Details</a></td>
<td bgcolor="<?php echo $bgColorH;?>"><?php echo $history['build.time'] ?></td>
<td bgcolor="<?php echo $bgColorH;?>"><?php echo $history['build.label'] ?></td>
</tr>
<?php 
}
?>

</table>
</td>
</tr>
<tr height="50">
<td width="200"><strong>Build Time</strong></td>
<td width="600"><?php echo date('Y-m-d H:i:s', $this->project['build.time']); ?></td>
</tr>
<tr height="50">
<td width="200"><strong>Build Label</strong></td>
<td width="600">
<?php echo isset($this->project['build.label'])?$this->project['build.label']:' '; ?>
</td>

</tr>
<tr>
<td colspan="2"><h2>Log Messages</h2> <small>debug(<a href="javascript:hide('debug');">hide</a>,
<a href="javascript:show('debug');">show</a>) info(<a href="javascript:hide('info');">hide</a>,
<a href="javascript:show('info');">show</a>)</td>
</tr>
<tr>
<td colspan="2">
<table>
<tr>
<td width="200"><strong>Date</strong></td>
<td width="60"><strong>Priority</strong></td>
<td width="440"><strong>Message</strong></td>
</tr>
<?php foreach ($this->logXml->children() as $logEntry) { 
$style="normal";
switch($logEntry['priority']){
    
    case 'error':
        $bgColorLine='red';
        break;
    case 'debug':
        $bgColorLine='gray';
        $style="debug";
        break;
    case "info":
        $style="info";
        
    default:
        $bgColorLine='white';
        break;
}
    ?>
<tr bgcolor="<?php echo $bgColorLine; ?>" class="<?php echo $style;?>">
<td><?php echo $logEntry['time']; ?></td>
<td><?php echo $logEntry['priority']; ?></td>
<td><small><?php echo $logEntry; ?></small></td>
</tr>
<?php 
}
?>
</table>
</td>
</tr>
</table>
</body>
</html>