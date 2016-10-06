<?php

//Composer autoloeader can do better than this :d
require 'core/Application.php';
require 'core/database/Connection.php';
require 'core/database/QueryBuilder.php';
require 'core/Request.php';
require 'core/Router.php';
require 'core/View.php';

//Load helpers
require 'core/helpers/Url.php';
require 'core/helpers/Debug.php';

$app = new Application;