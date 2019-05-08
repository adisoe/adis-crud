<?php

class ZemmyARPage extends CrudARPage{
  var $page_class = 'ZemmyARPage';
}

class ZemmyARPage_Controller extends CrudARPage_Controller{  
  var $table_class = 'ZemmyARModel';
  var $table_detail_class = 'ZemmyDetailARModel';
  var $table = 'mgtrtsj';
  var $table_detail = 'mgtrtsjdbiaya';
  var $pk = 'IdTSJ';
  var $pk_detail = 'IdTSJDBiaya';
  var $foreign_key = 'IdTSJ';
  var $columns = array(
      array(
          'Column' => 'BuktiTSJ',
          'Label' => 'Bukti',
          'Type' => 'Varchar'
      ),
      array(
          'Column' => 'TglTSJ',
          'Label' => 'Tanggal',
          'Type' => 'Date',
          'Required' => false,
          'HideTable' => false
      ),
      array(
          'Column' => 'WBT',
          'Label' => 'WBT',
          'Type' => 'Varchar',
          'Required' => false,
          'DefaultValue' => '123'
      )
  );
  var $detail_columns = array(
      array(
          'Column' => 'IdMPrk',
          'Label' => 'IdMPrk',
          'Type' => 'Number',
          'Required' => true          
      ),
      array(
          'Column' => 'Biaya',
          'Label' => 'Biaya',
          'Type' => 'Number',
          'Required' => true,
          'DefaultValue' => 123000
      )
  );
  var $search_field = array(
      'BuktiTSJ', 'WBT'
  );
}

?>