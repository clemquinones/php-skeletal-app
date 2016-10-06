<?php

//Composer autoloeader can do better than this :d
require 'core/Application.php';
require 'core/Request.php';
require 'core/Router.php';
require 'core/View.php';

// mysql-querybuilder
// Version 0.0.7
// https://github.com/clemquinones/mysql-querybuilder
require 'core/database/Connection.php';
require 'core/database/QueryBuilder.php';

//Load helpers
require 'core/helpers/Url.php';
require 'core/helpers/Debug.php';