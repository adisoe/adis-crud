<?php
class CrudData extends DataObject {
  static $db = array(
      'Title' => 'Varchar(255)',
      'Content' => 'HTMLText',
      'Price' => 'Double',      
      'Qty' => 'Int',
      'Subtotal' => 'Double',
  );
  
  static $summary_fields = array(
      'Title',
      'Price'
  );
  
  static $has_one = array(
      'Photo' => 'Image'
  );
  
  static $has_many = array(
      'Stock' => 'ProductStockData',
      'CrudDetail' => 'CrudDetailData'
  );
  
  function onBeforeWrite() {
    $this->Subtotal = $this->Qty * $this->Price;
    parent::onBeforeWrite();
  }
   
  
  function getCMSFields() {
    //parent::getCMSFields();
    foreach($this->TestMany() as $row){
      Debug::show($row);
      echo $row->Title;
    }
    
    $fields = new FieldList();
    $fields->add(new TabSet("Root"));
    $fields->addFieldToTab("Root.Tab1", new TextField('Title', 'Nama Produk'));                   
    $fields->addFieldToTab("Root.Tab1", new TextareaField('Content', 'Deskripsi Produk'));                   
    $fields->addFieldToTab("Root.Tab1", new NumericField('Price', 'Harga'));                   
    $fields->addFieldToTab("Root.Tab1", new LiteralField('Test', '<p>hallo</p>'));                   
    $fields->addFieldToTab("Root.Tab1", new NumericField('Qty', 'Input Qty'));                   
    $fields->addFieldToTab("Root.Tab1", new ReadonlyField('Subtotal', 'Subtotal'));                   
    $fields->addFieldToTab("Root.Tab1", new ReadonlyField('TotalStock', 'Total Stock', $this->countTotalStock()));
    //$fields->addFieldToTab("Root.Tab1", new DropdownField('TestDropdown', 'TestDropdown', ProductData::get()->map()));
    
    $photo_field = new UploadField('Photo', 'Photo');        
    $img_validator = new Upload_Validator();
    $img_validator->setAllowedMaxFileSize(2048 * 1024);    
    $photo_field->setValidator($img_validator);        
    $fields->addFieldToTab('Root.Tab1', $photo_field);        
    
    $config = GridFieldConfig_RecordEditor::create();            
    $gridField = new GridField("Stock", "Input Stock", $this->Stock(), $config);      
    $fields->addFieldToTab('Root.Tab1', $gridField);
    
    return $fields;
  }
  
  function countTotalStock(){
    $sql = "
      SELECT SUM(Qty)
      FROM ProductStockData
      WHERE ProductID='$this->ID'
    ";
    $result = DB::query($sql);
    return $result->value();
  }
}
?>