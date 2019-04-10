<?php

class CrudARModel extends ActiveRecord\Model {

  static $table_name = 'mgaptbeli';
  var $table_name_custom = 'CrudARModel';
  var $child_class = 'CrudDetailARModel';

  static function getByID($pk, $id) {
    $result = self::all(array(
                'conditions' => $pk . " = '" . $id . "' ",
                'limit' => 1
    ));
    if ($result) {
      return $result[0];
    }
    return null;
  }

  function getChild($field, $id) {
//    $result = CrudDetailARModel::find('all', array(
//                'conditions' => "IdTBeli = '$id'"
//    ));
    //echo "$this->child_class $field = '$id'";die();
    $result = call_user_func(array($this->child_class, 'find'), 'all', array(
                'conditions' => "$field = '$id'"
    ));
    return $result;
  }

  function deleteChild($pk, $id) {
    $class = new ReflectionClass($this->child_class);
    $table = $class->getStaticPropertyValue('table_name');
    $sql = "DELETE FROM " . $table . "      
      WHERE " . $pk . "='" . $id . "'";
    //echo $sql;die();
    $connection = ActiveRecord\ConnectionManager::get_connection();
    $result = $connection->query($sql);
  }

  function countChild($pk, $id) {    
    $class = new ReflectionClass($this->child_class);
    $table = $class->getStaticPropertyValue('table_name');
    $sql = "select count($pk) as total
      from " . $table . "
      where $pk = '$id'";
    //echo $sql;
    //$result = self::find_by_sql($sql);
    $result = call_user_func(array($this->table_name_custom, 'find_by_sql'), $sql);
    if ($result) {
      return $result[0]->total;
    }
    return 0;
  }

  static function getByUsername($username) {
    $result = UserAR::find('last', array(
                'conditions' => "username = '$username'"
    ));
    return $result;
  }

  static function getByNama($nama) {
    $result = UserAR::find('last', array(
                'conditions' => "nama = '$nama'"
    ));
    return $result;
  }


}

class CrudDetailARModel extends ActiveRecord\Model {

  static $table_name = 'mgaptbelid';

}

?>