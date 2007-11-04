<html>
<title>Xinc Dashboard</title>
<body>
<h1><?php echo $this->getTitle(); ?></h1>
<table cellspacing="0" cellpadding="0" width="100%">
<tr height="50">
<td width="150"><strong>Project</strong></td>
<td width="200"><strong>Last Build Time</strong></td>
<td width="200"><strong>Label</strong></td>
<td><strong>Status</strong></td>
</tr>
<?php
foreach ($this->projects as $project) {
    $bgColor=$project['build.status']==1?'green':
                                         ($project['build.status']==-10 ?'gray':'red');
    $text=$project['build.status']==1   ?'success':
                                         ($project['build.status']==-10 ?'waiting for first build':'failed');
?>
<tr>
<td><a href="/dashboard/detail?project=<?php echo $project['name']; ?>"><?php echo $project['name']; ?></a></td>
<td><?php echo $project['build.time']>0 ? date('Y-m-d H:i:s', $project['build.time']) : ''; ?></td>
<td><?php echo $project['build.label']; ?></td>
<td bgcolor="<?php echo $bgColor; ?>" style="color:white;text-align:center"><?php echo $text; ?></td>
</tr>
<?php 
}
?>
</table>
</body>
</html>