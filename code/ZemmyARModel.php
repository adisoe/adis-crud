<?php

class ZemmyARModel extends CrudARModel{
  static $table_name = 'mgtrtsj';
  var $table_name_custom = 'ZemmyARModel';
  var $child_class = 'ZemmyDetailARModel';
}

class ZemmyDetailARModel extends ActiveRecord\Model {
  static $table_name = 'mgtrtsjdbiaya';
}

?>