<?php

define('DBHOST','localhost');
define('DBUSER','diggit');
define('DBPASS','polygnome');
define('DBNAME','diggit');

$db = mysqli_connect(DBHOST,DBUSER,DBPASS,DBNAME);

date_default_timezone_set("CET");
