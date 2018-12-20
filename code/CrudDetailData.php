<?php
class CrudDetailData extends DataObject {
  static $db = array(      
      'Notes' => 'Text',
      'Price' => 'Double',      
      'Qty' => 'Int',
  );
  
  static $summary_fields = array(
//      'Title',
//      'Price'
  );
  
  static $has_one = array(
      'Crud' => 'CrudData'
  );
  
  static $has_many = array(
      //'Stock' => 'ProductStockData'
  );
}
?>