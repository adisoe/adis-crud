<?php
// todo:
// [done] search link blm bisa diklik
// [done] search link blm ada tombol add
// [done] check require field
// [done] datepicker
// [done] autonumeric
// [done] dropdown
// test di mysql other
// test di sql server
class CrudPage extends Page {

  function requireDefaultRecords() {
    if (!DataObject::get_one('CrudPage')) {
      $page = new CrudPage();
      $page->Title = 'Crud';
      $page->URLSegment = 'crud';
      $page->Status = 'Published';
      $page->write();
      $page->publish('Stage', 'Live');
      $page->flushCache();
      DB::alteration_message('CrudPage created on page tree', 'created');
    }
    parent::requireDefaultRecords();
  }

}

class CrudPage_Controller extends Page_Controller {

  private static $allowed_actions = array(
      'add',
      'AddForm',
      'AddDo',
      'AddDetailDo',
      'edit',
      'search',
      'searchajax',
      'delete',
      'addmasterdetail',
      'editmasterdetail',
  );
  
  function index(){
    $content = '<li><a href="'.$this->Link().'search">Grid</a></li>';
    $content .= '<li><a href="'.$this->Link().'add">Add</a></li>';
    $content .= '<li><a href="'.$this->Link().'addmasterdetail/1">Add Master Detail</a></li>';
    $content .= '<li><a href="'.$this->Link().'edit/1">Edit</a></li>';
    $content .= '<li><a href="'.$this->Link().'editmasterdetail/1">Edit Master Detail</a></li>';
    $content .= '<li><a href="'.$this->Link().'delete/1">Delete</a></li>';
    return $this->customise(array(
            'Content' => $content
            ));
  }

  // SETTING INI
  function getCustomColumns() {
    //$config = 'Customer';
    $columns = array();

    $columns = array(
        array(
            'Column' => 'ID',
            'Type' => 'Number'
        ),
        array(
            'Column' => 'LastEdited',
            'Type' => 'Date',
            'Required' => false
        ),
        array(
            'Column' => 'Title',
            'Type' => 'Varchar',
            'Required' => false
        ),
        array(
            'Column' => 'Content',
            'Type' => 'Text',
            'Required' => false
        )
    );
    
    return $columns;
  }
  
  // SETTING INI
  function getCustomDetailColumns() {
    //$config = 'Customer';
    $columns = array();

    $columns = array(        
        array(
            'Column' => 'ID',
            'Type' => 'Hidden',
            'Required' => true
        ),
        array(
            'Column' => 'Price',
            'Type' => 'Number',
            'Required' => true
        ),
        array(
            'Column' => 'Qty',
            'Type' => 'Number',
            'Required' => true
        ),
        array(
            'Column' => 'Notes',
            'Type' => 'Text',
            'Required' => false
        ),
        array(
            'Column' => 'Test Date',
            'Type' => 'Date',
            'Required' => false
        ),
        array(
            'Column' => 'Test Select',
            'Type' => 'Select',
            'Required' => true,
            'Source' => CrudData::get()->map() // Source harus array
        )
    );
    // set default value
    foreach($columns as $idx => $row){
      if(!isset($row['Source'])){
        $columns[$idx]['Source'] = '';
      }
    }
    
    return $columns;
  }
  
  function getDetailType($column){
    foreach($this->getCustomDetailColumns() as $row){
      if($row['Column'] == $column){
        return $row['Type'];
      }
    }
    return '';    
  }

  function add() {
    //var_dump(Session::get("FormInfo.BootstrapForm_AddForm.formError"));
    $form = $this->AddForm();
    return $this->customise(array(
                'Title' => 'Add',
                'Form' => $form
            ))->renderWith(array('CrudPage', 'Page'));
  }

  function AddForm() {
    $fields = new FieldList(
            //new TextField('Title', 'Title'), new NumericField('Price', 'Price'), new TextareaField('Content', 'Notes')
    );
    $columns = $this->getCustomColumns();        
    foreach($columns as $idx => $col){
      // create field based on Type
      $fields->push(CrudPage_Controller::generateFieldsByType($col['Type'], $col['Column'], $col['Column']));      
    }
    // custom fields disini
    $fields->removeByName('ID');
    $fields->removeByName('LastEdited');
    $action = new FieldList(
            $button = new FormAction('AddDo', 'Save')
    );
    //echo $button->getName();
    //$button->addExtraClass('btn-lg');
    $validator = new RequiredFields('Title', 'Price');

    $form = new BootstrapForm($this, 'AddForm', $fields, $action, $validator);
    return $form;
  }

  function AddDo($data, $form) {
    //echo '<pre>'; var_dump($data);die();
    if (isset($data['ID']) && $data['ID']) {
      // update mode
      $product = CrudData::get()->byID($data['ID']);
      unset($data['ID']);
    } else {
      // new mode
      $product = new CrudData();
    }
    $product->update($data);
    $product->write();

    // good / info / bad
    $form->sessionMessage('success', 'good');
    $this->redirectBack();
  }

  function edit() {
    //var_dump(Session::get("FormInfo.BootstrapForm_AddForm.formError"));
    $id = $this->request->param('ID');
    if (!$id) {
      return 'error';
    }
    $form = $this->AddForm(); // edit juga ttp pakai add form
    $form->Fields()->push(new HiddenField('ID', 'ID', $id));
    $data = CrudData::get()->byID($id); // load data
    if (!$data) {
      return 'error';
    }
    $form->loadDataFrom($data); // inject data to form
    return $this->customise(array(
                'Title' => 'Edit',
                'Form' => $form
            ))->renderWith(array('CrudPage', 'Page'));
  }

  function delete() {
    //var_dump(Session::get("FormInfo.BootstrapForm_AddForm.formError"));
    $id = $this->request->param('ID');
    $data = CrudData::get()->byID($id); // load data
    if (!$data) {
      return 'error';
    }
    $data->delete();
    $form = $this->AddForm();
    $form->sessionMessage('deleted', 'info');
    return $this->redirect($this->Link() . 'search');
  }

  function search() {
    //$result = TestModel::Search(array(), 10, 0);
    return $this->customise(array(
                'Columns' => new ArrayList($this->getCustomColumns())
                //'Result' => $result['Data']
            ))->renderWith(array('CrudSearchPage', 'Page'));
  }

  function searchajax() {
    // ============ filter bawaan datatable
    $start = (isset($_REQUEST['start'])) ? $_REQUEST['start'] : 0;
    $length = (isset($_REQUEST['length'])) ? $_REQUEST['length'] : 10;
    $search = (isset($_REQUEST['search']['value'])) ? $_REQUEST['search']['value'] : '';
    $columnsort = (isset($_REQUEST['order'][0]['column'])) ? $_REQUEST['order'][0]['column'] : 1;
    $typesort = (isset($_REQUEST['order'][0]['dir'])) ? $_REQUEST['order'][0]['dir'] : 'DESC';
    //$status = (isset($_REQUEST['status'])) ? $_REQUEST['status'] : '';
    $fieldsort = (isset($_REQUEST['columns'][$columnsort]['data']) && $_REQUEST['columns'][$columnsort]['data']) ? $_REQUEST['columns'][$columnsort]['data'] : 'Created';
    // ============ end filter

    // SETTING INI
    $result = CrudData::get()
            ->limit($length, $start)
            ->sort($fieldsort . ' ' . $typesort);
    $result_count = CrudData::get();
    if ($search) {
      $result = $result->where("Title LIKE '%$search%'");
      $result_count = $result_count->where("Title LIKE '%$search%'");
    }

    $columns = $this->getCustomColumns();
    $arr = array();
    foreach ($result as $row) {
      // $arr[] = $row;
      //var_dump($row['TglTReqCosting']);die();
      $temp = array();
      foreach($columns as $idx => $col){
        $temp[] = $row->$col['Column'];
      }
      $edit_link = $this->Link().'edit/'.$row->ID;
      if($row->CrudDetail()->count()){
        $edit_link = $this->Link().'editmasterdetail/'.$row->ID;
      }
      $delete_link = $this->Link().'delete/'.$row->ID;
      $temp[] = '<a href="'.$edit_link.'" class="btn btn-primary">Edit</a>
        <a href="'.$delete_link.'" class="btn btn-danger btn_delete">Delete</a>
        <div class="" style="position:relative; display:inline-block;">
          <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
            More
            <span class="caret"></span>
          </button>
          <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
            <li><a href="#">Action</a></li>
            <li><a href="#">Another action</a></li>
            <li><a href="#">Something else here</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="#">Separated link</a></li>
          </ul>
        </div>
      ';
      $arr[] = $temp;      
    }

    $result = array(
        'data' => $arr,
        'recordsTotal' => $result_count->count(),
        'recordsFiltered' => $result_count->count()
    );
    return json_encode($result);
  }

  function addmasterdetail() {
    //var_dump(Session::get("FormInfo.BootstrapForm_AddForm.formError"));
    $form = $this->AddForm();
    $form->Fields()->push(new LiteralField('DetailForm', $this->AddDetailForm()));
    $form->Actions()->removeByName('action_AddDo');
    $form->Actions()->push(new FormAction('AddDetailDo', 'Save')); // ganti method kalau save
    
    return $this->customise(array(
                'Title' => 'Add Master Detail',
                'Form' => $form,
                'RowDetailForm' => $this->RowDetailForm(),
                'DetailColumns' => new ArrayList($this->getCustomDetailColumns())
            ))->renderWith(array('CrudPage', 'Page'));
  }
  
  static function generateFieldsByType($type, $name, $label, $value='', $source='', $required=false){
    //var_dump($value);
    $field = null;
    if($type == 'Varchar'){
      $field =  new TextField($name, $label);
      $field->setAttribute('placeholder', $label);
      $field->setValue($value);
    }
    elseif($type == 'Text'){
      $field =  new TextareaField($name, $label);
      $field->setAttribute('placeholder', $label);
      $field->setValue($value);
    }
    elseif($type == 'Date'){
      $field =  new TextField($name, $label);
      $field->setAttribute('placeholder', $label);
      $field->setValue($value);
      $field->addExtraClass('datepicker');
    }
    elseif($type == 'Number'){
      $field =  new NumericField($name, $label);
      $field->setAttribute('placeholder', $label);
      $field->setValue($value);
      $field->addExtraClass('autonumeric');
    }
    elseif($type == 'Hidden'){
      $field =  new HiddenField($name, $label);
      $field->setValue($value);
    }
    elseif($type == 'Select'){
      $field =  new DropdownField($name, $label);
      $field->setSource($source);
      $field->setValue($value);
      $field->setEmptyString('(Pilih '.$label.')');
    }
    else{
      $field =  new TextField($name, $label);
      $field->setAttribute('placeholder', $label);
      $field->setValue($value);
    }
    if($required && $field){
      $field->setAttribute('required', 'required');
    }
    //$field->setValue(999);
    return $field;
  }
  
  function RowDetailForm($data=null){
    $is_table = true;
    $fields = new FieldList();
    $columns = $this->getCustomDetailColumns();            
    foreach($columns as $idx => $col){
      // create field based on Type
      if($data){
        // jika ada data, set value
        $fields->push(CrudPage_Controller::generateFieldsByType($col['Type'], 'DataDetail['.$col['Column'].'][]', $col['Column'], $data->$col['Column'], $col['Source'], $col['Required']));
        //var_dump($data->$col['Column']);
      }else{
        $fields->push(CrudPage_Controller::generateFieldsByType($col['Type'], 'DataDetail['.$col['Column'].'][]', $col['Column'], '', $col['Source'], $col['Required']));            
      }      
    } 
    $html_row = '';
    foreach($fields as $field){
      if($is_table){
        $html_row .= '<td>'.$field->Field().'</td>';
      }else{
        $html_row .= '<td>'.$field->Field().'</td>';
      }
    }
    if($is_table){      
      $html_row .= '<td><a href="#" class="btn btn-danger button_delete_detail">delete</a></td>';
      $html_row = '<tr>'.$html_row.'</tr>';
      
      return $html_row;
    }else{
      return $html;
    }    
  }
  
  function AddDetailForm($data=null) {
    $is_table = true;
    if($is_table){      
      $columns = $this->getCustomDetailColumns();            
      
      $html_head = '';
      foreach($columns as $idx => $col){
        $html_head .= '<th>'.$col['Column'].'</th>';
      }
      $html_head .= '<th>Action</th>';
      
      // body
      $html_body = '';
      if($data){
        // jika ada data, loop semua data dlm bentuk table
        foreach($data->CrudDetail() as $detail){
          $html_body.= $this->RowDetailForm($detail);    
        }
      }else{
        $html_body = $this->RowDetailForm();
      }
      
      return '<table class="table" id="table_detail">
        <thead>'.$html_head.'</thead>
        <tbody>'.$html_body.'</tbody>
      </table>
      <a href="#" class="btn btn-primary" id="button_add_detail">Add Detail</a>';
    }else{
      return $this->RowDetailForm();
    }    
  }
  
  function AddDetailDo($data, $form) {
    //echo '<pre>'; var_dump($data);die();
    // SAVE MASTER
    if (isset($data['ID']) && $data['ID']) {
      // update mode
      $product = CrudData::get()->byID($data['ID']);
      unset($data['ID']);      
    } else {
      // new mode
      $product = new CrudData();
    }
    $product->update($data);
    $product->write();
    
    // SAVE DETAIL
    
    if(isset($data['DataDetail']) && count($data['DataDetail'])){
      // hapus all detail
      $sql = "delete from CrudDetailData where CrudID='$product->ID'";
      DB::query($sql);
      
      $arr_detail = array();
      // get first key
      $first_key = key($data['DataDetail']);
      $total_data = count($data['DataDetail'][$first_key]);
      //echo $first_key.' '.$total_data;die();     
      for($i=0; $i<$total_data; $i++){
        // ubah format array supaya mudah dibaca
        $arr_temp = array();
        foreach($data['DataDetail'] as $idx => $detail){                    
          $arr_temp[$idx] = $data['DataDetail'][$idx][$i];
          // kalau number harus di-convert
          //echo $this->getDetailType($idx).' '.$idx.' ';
          if($this->getDetailType($idx) == 'Number'){            
            $arr_temp[$idx] = CT::convertCurrencyToFloat($arr_temp[$idx]);
          }
        }
        $arr_detail[] = $arr_temp;
        
        // save detail
        $detail_obj = new CrudDetailData();
        unset($arr_temp['ID']);
        $detail_obj->update($arr_temp);        
        $detail_obj->CrudID = $product->ID; // link to master
        $detail_obj->write();
      }
      //echo '<pre>'; var_dump($arr_detail);die();
    }

    // good / info / bad
    $form->sessionMessage('success', 'good');
    $this->redirectBack();
  }
  
  function editmasterdetail() {
    $id = $this->request->param('ID');
    if (!$id) {
      return 'error';
    }        
    $data = CrudData::get()->byID($id); // load data    
    if (!$data) {
      return 'error';
    }
    $form = $this->AddForm();    
    $form->Fields()->push(new HiddenField('ID', 'ID', $id));
    $form->Fields()->push(new LiteralField('DetailForm', $this->AddDetailForm($data)));
    $form->Actions()->removeByName('action_AddDo');
    $form->Actions()->push(new FormAction('AddDetailDo', 'Save')); // ganti method kalau save
    $form->loadDataFrom($data); // inject data to form
    
    // convert detail data to json
    //$html_detail = '';
    //$arr_detail = array();
    //foreach($data->CrudDetail() as $detail){
      //$html_detail .= $this->RowDetailForm($detail);
//      $columns = $this->getCustomDetailColumns();            
//      $arr_temp = array();
//      foreach($columns as $idx => $col){        
//        $arr_temp[$col['Column']] = $detail->$col['Column'];
//      }
//      $arr_detail[] = $arr_temp;
    //}
    //echo json_encode($arr_detail);die();
    //var_dump($html_detail);die();
    
    return $this->customise(array(
                'Title' => 'Edit Master Detail',
                'Form' => $form,
                'IsEdit' => true,
                'RowDetailForm' => $this->RowDetailForm(),
                'DetailColumns' => new ArrayList($this->getCustomDetailColumns())
                //'DetailData' => json_encode($arr_detail)
            ))->renderWith(array('CrudPage', 'Page'));
  }
}

?>