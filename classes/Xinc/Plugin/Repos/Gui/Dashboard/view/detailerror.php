<html>
<title>Xinc Dashboard</title>
<body>
<h1>No Details for project 
<?php 
    echo $this->projectName;
    if ($this->buildTimeStamp!=null) {
        echo ' and timestamp '.$this->buildTimeStamp;
    } 
?></h1>

</body>

</html>