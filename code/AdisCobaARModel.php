<?php

class AdisCobaARModel extends CrudARModel{
  static $table_name = 'mgartjual';
  var $table_name_custom = 'AdisCobaARModel';
  var $child_class = 'AdisCobaDetailARModel';
}

class AdisCobaDetailARModel extends ActiveRecord\Model {
  static $table_name = 'mgartjuald';
}

?>