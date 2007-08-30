<?php

ini_set("include_path", "classes/Xinc".PATH_SEPARATOR.ini_get('include_path'));

require_once("Xinc_Logger.php");
require_once("Xinc_Xinc.php");


require_once("Xinc_Parser.php");
require_once("Xinc_ModificationSet.php");
require_once("Xinc_PhingBuilder.php");


require_once("ModificationSets/Xinc_ModificationSets_FakeModificationSet.php");
require_once("ModificationSets/Xinc_ModificationSets_SVNModificationSet.php");
require_once("Publishers/Xinc_Publishers_PhingPublisher.php");

$logger = new Xinc_Logger();

$parser = new Xinc_Parser();
$project = $parser->parse("config.xml");
var_dump($project);

//exit;

$continuousBuild = new Xinc_Xinc();
// populate with projects..

$continuousBuild->setProjects(array($project));

// start loop..
$continuousBuild->start();



?>