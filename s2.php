<?php

require_once 'vendor/autoload.php';

$ods = new \Ken880guchi\Jra\Ods();
$showRacePageParams = $ods->getShowRacePageStrings();

var_dump($showRacePageParams);
