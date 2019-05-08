<?php

class AdisCobaARPage extends CrudARPage{
  var $page_class = 'AdisCobaARPage';
}

class AdisCobaARPage_Controller extends CrudARPage_Controller{  
  var $table_class = 'AdisCobaARModel';
  var $table_detail_class = 'AdisCobaDetailARModel';
  var $table = 'mgartjual';
  var $table_detail = 'mgartjuald';
  var $pk = 'IdTJual';
  var $pk_detail = 'IdTJualD';
  var $foreign_key = 'IdTJual';
  var $columns = array(
      array(
          'Column' => 'IdTJual',
          'Label' => 'ID Jual',
          'Type' => 'Number'
      ),
      array(
          'Column' => 'TglTJual',
          'Label' => 'Tgl Jual',
          'Type' => 'Date',
          'Required' => false,
          'HideTable' => false
      ),
      array(
          'Column' => 'BuktiTJual',
          'Label' => 'Bukti Jual',
          'Type' => 'File',
          'Required' => false,
          'DefaultValue' => '123'
      ),
      array(
          'Column' => 'Bruto',
          'Label' => 'Bruto',
          'Type' => 'Select',
          'Required' => false,
          'DefaultValue' => 100,
          'Source' => array(
              '1' => 'val 1',
              '2' => 'val 2'
          )
      ),
      array(
          'Column' => 'IdMCust',
          'Label' => 'IdMCust',
          'Type' => 'Browse',
          'BrowseModule' => 'Customer',
          'BrowseReturnKey' => 'ID',
          'Required' => true,
          'DefaultValue' => 100
      )
  );
  var $detail_columns = array(
      array(
          'Column' => 'IdTJual',
          'Label' => 'ID Jual',
          'Type' => 'Hidden',
          'Required' => true          
      ),
      array(
          'Column' => 'Qty1',
          'Label' => 'Qty 1',
          'Type' => 'Number',
          'Required' => true,
          'DefaultValue' => 111
      ),
      array(
          'Column' => 'Qty2',
          'Label' => 'Qty 2',
          'Type' => 'Number',
          'Required' => true
      )
  );
  var $search_field = array(
      'BuktiTJual', 'TglTJual', 'IdTJual'
  );
}

?>