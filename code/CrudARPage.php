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
class CrudARPage extends Page {

  var $page_class = 'CrudARPage';

  function requireDefaultRecords() {
    $class = $this->page_class;
    if (!DataObject::get_one($class)) {
      $page = new $class();
      $page->Title = $class;
      $page->URLSegment = strtolower($class);
      $page->Status = 'Published';
      $page->write();
      $page->publish('Stage', 'Live');
      $page->flushCache();
      DB::alteration_message($class . ' created on page tree', 'created');
    }
    parent::requireDefaultRecords();
  }

}

class CrudARPage_Controller extends Page_Controller {

  var $table_class = 'CrudARModel';
  var $table_detail_class = 'CrudDetailARModel';
  var $table = 'mgaptbeli';
  var $table_detail = 'mgaptbelid';
  var $pk = 'IdTBeli';
  var $pk_detail = 'IdTBeliD';
  var $foreign_key = 'IdTBeli';
  var $columns = array(
      array(
          'Column' => 'IdTBeli',
          'Label' => 'ID Beli',
          'Type' => 'Number'
      ),
      array(
          'Column' => 'TglTBeli',
          'Label' => 'Tgl Beli',
          'Type' => 'Date',
          'Required' => false,
          'HideTable' => false
      ),
      array(
          'Column' => 'BuktiTBeli',
          'Label' => 'Bukti Beli',
          'Type' => 'Varchar',
          'Required' => false,
          'DefaultValue' => '123'
      ),
      array(
          'Column' => 'Bruto',
          'Label' => 'Bruto',
          'Type' => 'Number',
          'Required' => false,
          'DefaultValue' => 100
      )
  );
  var $detail_columns = array(
      array(
          'Column' => 'IdTBeli',
          'Label' => 'ID Beli',
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
      ),
//        array(
//            'Column' => 'Notes',
//            'Type' => 'Text',
//            'Required' => false
//        ),
//        array(
//            'Column' => 'Test Date',
//            'Type' => 'Date',
//            'Required' => false
//        ),
//        array(
//            'Column' => 'Test Select',
//            'Type' => 'Select',
//            'Required' => true,
//            //'Source' => CrudData::get()->map() // Source harus array
//        )
  );
  var $search_field = array(
      'BuktiTBeli', 'BuktiAsli', 'IdTBeli'
  );
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

  function index() {
    $content = '<li><a href="' . $this->Link() . 'search">Grid</a></li>';
    $content .= '<li><a href="' . $this->Link() . 'add">Add</a></li>';
    $content .= '<li><a href="' . $this->Link() . 'addmasterdetail/1">Add Master Detail</a></li>';
    $content .= '<li><a href="' . $this->Link() . 'edit/1">Edit</a></li>';
    $content .= '<li><a href="' . $this->Link() . 'editmasterdetail/1">Edit Master Detail</a></li>';
    $content .= '<li><a href="' . $this->Link() . 'delete/1">Delete</a></li>';
    return $this->customise(array(
                'Content' => $content
    ));
  }

  function getCustomColumns() {
    //$config = 'Customer';
    $columns = $this->columns;
    return $columns;
  }

  function getCustomDetailColumns() {
    //$config = 'Customer';
    $columns = $this->detail_columns;
    // set default value
    foreach ($columns as $idx => $row) {
      if (!isset($row['Source'])) {
        $columns[$idx]['Source'] = '';
      }
    }
    return $columns;
  }

  function getDetailType($column) {
    foreach ($this->getCustomDetailColumns() as $row) {
      if ($row['Column'] == $column) {
        return $row['Type'];
      }
    }
    return '';
  }

  function getColumnType($column) {
    foreach ($this->getCustomColumns() as $row) {
      if ($row['Column'] == $column) {
        return $row['Type'];
      }
    }
    return '';
  }
  
  function getColumnLabel($column) {
    foreach ($this->getCustomColumns() as $row) {
      if ($row['Column'] == $column) {
        return $row['Label'];
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
    foreach ($columns as $idx => $col) {
      // create field based on Type
      $val = '';
      if (isset($col['DefaultValue'])) {
        $val = $col['DefaultValue'];
      }
      if ($col['Type'] == 'Browse') {
        $browse_field = self::generateFieldsByType($col['Type'], $col['Column'], $col['Label'], $val, $col['BrowseModule']);
        $browse_field->setAttribute('browse-return-key', $col['BrowseReturnKey']);
        $fields->push($browse_field);
      }else{
        $fields->push(self::generateFieldsByType($col['Type'], $col['Column'], $col['Label'], $val));
      }
      // jika file, tambahkan field preview
      if ($col['Type'] == 'File') {
        $fields->push(self::generateFieldsByType('File_Preview', $col['Column'] . '_Preview', $val, $val));
      }
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
    unset($data['SecurityID']);
    unset($data['url']);
    unset($data['action_AddDo']);
    unset($data['MAX_FILE_SIZE']);
    //echo '<pre>'; var_dump($data);die();
    $product = null;
    if (isset($data[$this->pk]) && $data[$this->pk]) {
      // update mode
      //echo 'xxx';
//      $table_class = $this->table_class;
//      $table = new $table_class(); 
      $product = call_user_func(array($this->table_class, 'getByID'), $this->pk, $data[$this->pk]);

      //$product = CrudData::get()->byID($data[$this->pk]);
      //if($product)
      //unset($data[$this->pk]);
    }
    //var_dump($product);die();
    // convert data
    foreach ($data as $idx => $row) {
      if ($this->getColumnType($idx) == 'Number') {
        $data[$idx] = CT::convertCurrencyToFloat($data[$idx]);
      } elseif ($this->getColumnType($idx) == 'File') {
        if($data[$idx]['size']){
          $file = new File();
          $upload = new Upload();
          $upload->loadIntoFile($data[$idx], $file);
          if ($upload->isError()) {
            echo $upload->getErrors();die();
          }
          $data[$idx] = $file->Filename;
          //echo $idx.' '.$file->Filename;die();
        }else{
          // kalau file tidak diganti
          unset($data[$idx]);
        }
      }
    }
    if (!$product) {
      // new mode
      $class = $this->table_class;
      $product = new $class();
    }
    $product->set_attributes($data);
    $product->save();

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
    $data = call_user_func(array($this->table_class, 'getByID'), $this->pk, $id);
    if (!$data) {
      return 'error';
    }

    $form = $this->AddForm(); // edit juga ttp pakai add form
    $form->Fields()->push(new HiddenField($this->pk, $this->pk, $id));
    $arr_data = $data->attributes();
    $arr_data = $this->convertLowerArray($arr_data);
    $form->loadDataFrom($arr_data); // inject data to form
    return $this->customise(array(
                'Title' => 'Edit',
                'Form' => $form
            ))->renderWith(array('CrudPage', 'Page'));
  }

  function delete() {
    //var_dump(Session::get("FormInfo.BootstrapForm_AddForm.formError"));
    $id = $this->request->param('ID');
    $data = call_user_func(array($this->table_class, 'getByID'), $this->pk, $id);
    if (!$data) {
      return 'error';
    }
    $data->delete();
    $form = $this->AddForm();
    $form->sessionMessage('deleted', 'info');
    return $this->redirect($this->Link() . 'search');
  }
  
  function SearchForm() {
    $columns = $this->getCustomColumns();
    $fields = new FieldList();
    foreach ($this->search_field as $idx => $col) {
      $fields->push(self::generateFieldsByType('Varchar', $col, $this->getColumnLabel($col)));      
    }
    // custom fields disini
    //$fields->removeByName('ID');
    //$fields->removeByName('LastEdited');
    $action = new FieldList(
            $button = new FormAction('search', 'Search')
    );
    //echo $button->getName();
    //$button->addExtraClass('btn-lg');
    //$validator = new RequiredFields('Title', 'Price');

    $form = new BootstrapForm($this, 'search', $fields, $action);
    $form->setFormMethod('get');
    $form->disableSecurityToken();
    $form->addExtraClass('form_search');
    return $form;
  }
  
  function searchDataTableAdditional(){
    $html = '';
    foreach ($this->search_field as $idx => $col) {
      $html .= 'd.'.$col.' = $(\'input[name="'.$col.'"]\').val();';      
    }
    return $html;
  }

  function search() {
    //$result = TestModel::Search(array(), 10, 0);
    return $this->customise(array(
                'Columns' => new ArrayList($this->getCustomColumns()),
                'SearchDataTableAdditional' => $this->searchDataTableAdditional()
                    //'Result' => $result['Data']
            ))->renderWith(array('CrudSearchPage', 'Page'));
  }

  function generateSearchWhere($query) {
    $sql = '';
    foreach ($this->search_field as $row) {
      if ($sql) {
        $sql.= " OR ";
      }
      $sql .= " $row LIKE '%" . $query . "%' ";
    }
    return $sql;
  }
  
  /**
   * query search di form search yang lebih detail
   * @return string
   */
  function searchWhereQuery(){
    $where = '';
    foreach ($this->search_field as $idx => $col) {
      if(isset($_REQUEST[$col]) && $_REQUEST[$col]){
        $where .= " AND $col LIKE '%" . $_REQUEST[$col] . "%' ";
      }
    }
    return $where;
  }

  function searchajax() {
    // ============ filter bawaan datatable
    $start = (isset($_REQUEST['start'])) ? $_REQUEST['start'] : 0;
    $length = (isset($_REQUEST['length'])) ? $_REQUEST['length'] : 10;
    $search = (isset($_REQUEST['search']['value'])) ? $_REQUEST['search']['value'] : '';
    $columnsort = (isset($_REQUEST['order'][0]['column'])) ? $_REQUEST['order'][0]['column'] : 1;
    $typesort = (isset($_REQUEST['order'][0]['dir'])) ? $_REQUEST['order'][0]['dir'] : 'DESC';
    //$status = (isset($_REQUEST['status'])) ? $_REQUEST['status'] : '';
    //$fieldsort = (isset($_REQUEST['columns'][$columnsort]['data']) && $_REQUEST['columns'][$columnsort]['data']) ? $_REQUEST['columns'][$columnsort]['data'] : 'TglTBeli';
    $fieldsort = $this->columns[$columnsort]['Column'];
    // ============ end filter    
    $where = " " . $this->pk . " != '' ";
    if ($search) {
      $where .= " AND (" . $this->generateSearchWhere($search) . ") ";
    }
    $where .= $this->searchWhereQuery();  // search dari form detail
    //echo $where;die();
//    $result = CrudARModel::find('all', array(
//                'conditions' => $where,
//                'order' => $fieldsort.' '.$typesort,
//                'limit' => $length,
//                'offset' => $start
//    ));
    //echo $fieldsort . ' ' . $typesort;
    $result = call_user_func(array($this->table_class, 'find'), 'all', array(
        'conditions' => $where,
        'order' => $fieldsort . ' ' . $typesort,
        'limit' => $length,
        'offset' => $start
    ));
    $sql = "select count(" . $this->pk . ") as total
      from " . $this->table . "
      where $where";
    //$result_count = CrudARModel::find_by_sql($sql);
    $result_count = call_user_func(array($this->table_class, 'find_by_sql'), $sql);
    //var_dump($result_count[0]->total);
    $total = 0;
    if ($result_count) {
      $total = $result_count[0]->total;
    }

    $columns = $this->getCustomColumns();
    $arr = array();
    foreach ($result as $row) {
      // $arr[] = $row;
      //var_dump($row['TglTReqCosting']);die();
      $temp = array();
      foreach ($columns as $idx => $col) {
        if (isset($col['HideTable']) && $col['HideTable']) {
          continue;
        }
        $temp_field = strtolower($col['Column']);
        if (isset($col['Type']) && $col['Type'] == 'Date') {
          //echo $row->$temp_field;
          if ($row->$temp_field) {
            $temp[] = $row->$temp_field->format('Y-m-d H:i:s');
          } else {
            $temp[] = '';
          }
        } elseif (isset($col['Type']) && $col['Type'] == 'Number') {
          //echo $row->$temp_field;
          $temp[] = CT::convertNumber($row->$temp_field);
        } else {
          $temp[] = $row->$temp_field;
        }
      }
      $temp_field_pk = strtolower($this->pk);
      $edit_link = $this->Link() . 'edit/' . $row->$temp_field_pk;
      //if (CrudARModel::countChild($row->$temp_field_pk)) {
      //var_dump(call_user_func(array($this->table_class, 'countChild'), $row->$temp_field_pk));
      $table_class = $this->table_class;
      $table = new $table_class();
      //if (call_user_func(array($this->table_class, 'countChild'), $this->table_detail_class, $temp_field_pk, $row->$temp_field_pk)) {
      if ($table->countChild($temp_field_pk, $row->$temp_field_pk)) {
        $edit_link = $this->Link() . 'editmasterdetail/' . $row->$temp_field_pk;
      }
      $delete_link = $this->Link() . 'delete/' . $row->$temp_field_pk;
      $temp[] = '<a href="' . $edit_link . '" class="btn btn-primary">Edit</a>
        <a href="' . $delete_link . '" class="btn btn-danger btn_delete">Delete</a>
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
        'recordsTotal' => $total,
        'recordsFiltered' => $total
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

  static function generateFieldsByType($type, $name, $label, $value = '', $source = '', $required = false) {
    //var_dump($name, $value);
    $field = null;
    if ($type == 'Varchar') {
      $field = new TextField($name, $label);
      $field->setAttribute('placeholder', $label);
      $field->setValue($value);
    } elseif ($type == 'Text') {
      $field = new TextareaField($name, $label);
      $field->setAttribute('placeholder', $label);
      $field->setValue($value);
    } elseif ($type == 'Date') {
      //var_dump($value);
      $field = new TextField($name, $label);
      $field->setAttribute('placeholder', $label);
      $field->setValue($value);
      $field->addExtraClass('datepicker');
    } elseif ($type == 'Number') {
      $field = new TextField($name, $label);
      $field->setAttribute('placeholder', $label);
      $field->setValue($value);
      $field->addExtraClass('autonumeric');
    } elseif ($type == 'Hidden') {
      $field = new HiddenField($name, $label);
      $field->setValue($value);
    } elseif ($type == 'Select') {
      $field = new DropdownField($name, $label);
      $field->setSource($source);
      $field->setValue($value);
      $field->setEmptyString('(Pilih ' . $label . ')');
    } elseif ($type == 'File') {
//      if($value){
//        $img = '<br/><img src="'.$value.'"/>';
//      }
      $field = new FileField($name, $label);
      //$field->setValue($value);
      //$field->setEmptyString('(Pilih ' . $label . ')');  
    } elseif ($type == 'File_Preview') {
      $field = new LiteralField($name, $value);
      $field->setValue($value);
    } elseif ($type == 'Browse') {
      $field = new TextField($name, $label);
      $field->addExtraClass('text-browse');
      $field->setAttribute('browse-module', $source);
      $field->setValue($value);
    } else {
      $field = new TextField($name, $label);
      $field->setAttribute('placeholder', $label);
      $field->setValue($value);
    }
    if ($required && $field) {
      $field->setAttribute('required', 'required');
    }
    //$field->setValue(999);
    return $field;
  }

  function RowDetailForm($data = null) {
    $is_table = true;
    $fields = new FieldList();
    $columns = $this->getCustomDetailColumns();
    foreach ($columns as $idx => $col) {
      // create field based on Type
      if ($data) {
        // jika ada data, set value
        $temp_field = strtolower($col['Column']);
        //echo $col['Type'].' ';
        $fields->push(self::generateFieldsByType($col['Type'], 'DataDetail[' . $col['Column'] . '][]', $col['Label'], $data->$temp_field, $col['Source'], $col['Required']));
        //var_dump($data->$col['Column']);
      } else {
        $val = '';
        if (isset($col['DefaultValue'])) {
          $val = $col['DefaultValue'];
        }
        $fields->push(self::generateFieldsByType($col['Type'], 'DataDetail[' . $col['Column'] . '][]', $col['Label'], $val, $col['Source'], $col['Required']));
      }
    }
    $html_row = '';
    foreach ($fields as $field) {
      if ($is_table) {
        $html_row .= '<td>' . $field->Field() . '</td>';
      } else {
        $html_row .= '<td>' . $field->Field() . '</td>';
      }
    }
    if ($is_table) {
      $html_row .= '<td><a href="#" class="btn btn-danger button_delete_detail">delete</a></td>';
      $html_row = '<tr>' . $html_row . '</tr>';

      return $html_row;
    } else {
      return $html;
    }
  }

  function AddDetailForm($data = null) {
    $is_table = true;
    if ($is_table) {
      $columns = $this->getCustomDetailColumns();

      $html_head = '';
      foreach ($columns as $idx => $col) {
        $html_head .= '<th>' . $col['Label'] . '</th>';
      }
      $html_head .= '<th>Action</th>';

      // body
      $html_body = '';
      if ($data) {
        //echo '<pre>';var_dump($data);
        // jika ada data, loop semua data dlm bentuk table
        $temp_field_pk = strtolower($this->pk);
        //if (call_user_func(array($this->table_class, 'countChild'), $this->table_detail_class, $temp_field_pk, $data->$temp_field_pk)) {
        $table_class = $this->table_class;
        $table = new $table_class();
        if ($table->countChild($temp_field_pk, $data->$temp_field_pk)) {
          //$childs = call_user_func(array($this->table_class, 'getChild'), $temp_field_pk, $data->$temp_field_pk);
          $childs = $table->getChild($temp_field_pk, $data->$temp_field_pk);
          foreach ($childs as $detail) {
            $html_body.= $this->RowDetailForm($detail);
          }
        }
      } else {
        $html_body = $this->RowDetailForm();
      }

      return '<table class="table" id="table_detail">
        <thead>' . $html_head . '</thead>
        <tbody>' . $html_body . '</tbody>
      </table>
      <a href="#" class="btn btn-primary" id="button_add_detail">Add Detail</a>';
    } else {
      return $this->RowDetailForm();
    }
  }

  function AddDetailDo($data, $form) {
    //echo '<pre>'; var_dump($data);die();
    // SAVE MASTER
    unset($data['SecurityID']);
    unset($data['url']);
    unset($data['action_AddDo']);
    unset($data['action_AddDetailDo']);
    unset($data['MAX_FILE_SIZE']);
    $product = null;
    if (isset($data[$this->pk]) && $data[$this->pk]) {
      // update mode
      //echo 'xxx';
      $product = call_user_func(array($this->table_class, 'getByID'), $this->pk, $data[$this->pk]);
      //$product = CrudData::get()->byID($data[$this->pk]);
      //if($product)
      //unset($data[$this->pk]);
    }
    // convert data
    foreach ($data as $idx => $row) {
      if ($this->getColumnType($idx) == 'Number') {
        $data[$idx] = CT::convertCurrencyToFloat($data[$idx]);
      } elseif ($this->getColumnType($idx) == 'File') {
        //var_dump($data[$idx]);die();
        if($data[$idx]['size']){
          $file = new File();
          $upload = new Upload();
          $upload->loadIntoFile($data[$idx], $file);
          if ($upload->isError()) {
            echo $upload->getErrors();die();
          }
          $data[$idx] = $file->Filename;
          //echo $idx.' '.$file->Filename;die();
        }else{
          // kalau file tidak diganti
          unset($data[$idx]);
        }
      }
    }
    //var_dump($product);die();
    if (!$product) {
      // new mode
      $class = $this->table_class;
      $product = new $class();
    }
    $data_save = $data;
    unset($data_save['DataDetail']);
    $product->set_attributes($data_save);
    $product->save();

    // SAVE DETAIL

    if (isset($data['DataDetail']) && count($data['DataDetail'])) {
      // hapus all detail
      $table_class = $this->table_class;
      $table = new $table_class();
      //call_user_func(array($this->table_class, 'deleteChild'), $this->pk, $data[$this->pk]);
      $table->deleteChild($this->pk, $data[$this->pk]);

      $arr_detail = array();
      // get first key
      $first_key = key($data['DataDetail']);
      $total_data = count($data['DataDetail'][$first_key]);
      //echo $first_key.' '.$total_data;die();     
      for ($i = 0; $i < $total_data; $i++) {
        // ubah format array supaya mudah dibaca
        $arr_temp = array();
        foreach ($data['DataDetail'] as $idx => $detail) {
          $arr_temp[$idx] = $data['DataDetail'][$idx][$i];
          // kalau number harus di-convert
          //echo $this->getDetailType($idx).' '.$idx.' ';
          if ($this->getDetailType($idx) == 'Number') {
            $arr_temp[$idx] = CT::convertCurrencyToFloat($arr_temp[$idx]);
          }
        }
        $arr_detail[] = $arr_temp;

        // save detail
        $class = $this->table_detail_class;
        $foreign_key = strtolower($this->foreign_key);
        $pk_detail = strtolower($this->pk_detail);
        $detail_obj = new $class();
        //unset($arr_temp['ID']);
        //var_dump($arr_temp);die();
        $detail_obj->set_attributes($arr_temp);
        //$detail_obj->$pk_detail = rand(); // TODO: generate id detail
        $detail_obj->$pk_detail = $this->generateDetailID();
        $detail_obj->$foreign_key = $data[$this->pk];
        $detail_obj->save();
      }
      //echo '<pre>'; var_dump($arr_detail);die();
    }

    // good / info / bad
    $form->sessionMessage('success', 'good');
    $this->redirectBack();
  }

  function generateDetailID() {
    $sql = "SELECT MAX(" . $this->pk_detail . ") as idrow FROM " . $this->table_detail . "";
    $find = call_user_func(array($this->table_class, 'find_by_sql'), $sql);
    //$find = self::find_by_sql($sql); 
    return $find[0]->idrow + 1;
  }

  /**
   * set value field IdTBeli from field idtbeli
   * @param type $arr
   * @return type
   */
  function convertLowerArray($arr) {
    foreach ($arr as $idx => $row) {
      foreach ($this->getCustomColumns() as $idx_col => $col) {
        if (strtolower($col['Column']) == $idx) {
          $arr[$col['Column']] = $row;
          // date time          
          if ($row instanceof \DateTime) {
            //echo 'yeah';die();
            $arr[$col['Column']] = $row->format('Y-m-d H:i:s');
          }
          if ($col['Type'] == 'File') {
            $arr[$col['Column'] . '_Preview'] = $row;
          }
          continue;
        }
      }
    }
    return $arr;
  }

  function endsWith($haystack, $needle) {
    $length = strlen($needle);
    if ($length == 0) {
      return true;
    }

    return (substr($haystack, -$length) === $needle);
  }

  function formFilePreview($form, $arr) {
    foreach ($arr as $idx => $row) {
      if ($this->endsWith($idx, '_Preview')){
        $img = '<img src="'.$row.'" style="height:100px;"/>';
        $form->Fields()->fieldByName($idx)->setContent($img);
      }
    }
  }

  function editmasterdetail() {
    $id = $this->request->param('ID');
    if (!$id) {
      return 'error';
    }
    $data = call_user_func(array($this->table_class, 'getByID'), $this->pk, $id);
    if (!$data) {
      return 'error';
    }
    $form = $this->AddForm();
    $form->Fields()->push(new HiddenField($this->pk, $this->pk, $id));
    $form->Fields()->push(new LiteralField('DetailForm', $this->AddDetailForm($data)));
    $form->Actions()->removeByName('action_AddDo');
    $form->Actions()->push(new FormAction('AddDetailDo', 'Save')); // ganti method kalau save    
    $arr_data = $data->attributes();
    $arr_data = $this->convertLowerArray($arr_data);
    //echo '<pre>';var_dump($arr_data);die();
    $form->loadDataFrom($arr_data); // inject data to form    
    $this->formFilePreview($form, $arr_data);
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