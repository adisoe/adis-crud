<?php

require_once '../php-activerecord/ActiveRecord.php';
$cfg = ActiveRecord\Config::instance();
$cfg->set_model_directory('.');
$cfg->set_connections(
        array(
            'development' => 'mysql://root:2242374@localhost:3306/sindu',
            'test' => 'mysql://root:2242374@localhost:3306/sindu',
            'production' => 'mysql://root:2242374@localhost:3306/sindu',
        )
);
