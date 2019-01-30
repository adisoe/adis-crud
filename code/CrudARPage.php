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
  static $page_class = 'CrudARPage';

  function requireDefaultRecords() {
    $class = self::$page_class;
    if (!DataObject::get_one($class)) {
      $page = new $classs();
      $page->Title = $class;
      $page->URLSegment = strtolower($class);
      $page->Status = 'Published';
      $page->write();
      $page->publish('Stage', 'Live');
      $page->flushCache();
      DB::alteration_message($class.' created on page tree', 'created');
    }
    parent::requireDefaultRecords();
  }
}

class CrudARPage_Controller extends Page_Controller {
  static $table_class = 'CrudARModel';
  static $table_detail_class = 'CrudDetailARModel';
  static $table = 'mgaptbeli';
  static $table_detail = 'mgaptbelid';
  static $pk = 'IdTBeli';
  static $pk_detail = 'IdTBeliD';
  static $foreign_key = 'IdTBeli';
  static $columns = array(
      array(
          'Column' => 'IdTBeli',
          'Type' => 'Number'
      ),
      array(
          'Column' => 'TglTBeli',
          'Type' => 'Date',
          'Required' => false,
          'HideTable' => false
      ),
      array(
          'Column' => 'BuktiTBeli',
          'Type' => 'Varchar',
          'Required' => false
      ),
      array(
          'Column' => 'Bruto',
          'Type' => 'Number',
          'Required' => false
      )
  );
  static $detail_columns = array(
      array(
          'Column' => 'IdTBeli',
          'Type' => 'Hidden',
          'Required' => true
      ),
      array(
          'Column' => 'Qty1',
          'Type' => 'Number',
          'Required' => true
      ),
      array(
          'Column' => 'Qty2',
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
  static $search_field = array(
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

  // SETTING INI
  function getCustomColumns() {
    //$config = 'Customer';
    $columns = self::$columns;
    return $columns;
  }

  // SETTING INI
  function getCustomDetailColumns() {
    //$config = 'Customer';
    $columns = self::$detail_columns;
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
      $fields->push(self::generateFieldsByType($col['Type'], $col['Column'], $col['Column']));
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
    $product = null;
    if (isset($data[self::$pk]) && $data[self::$pk]) {
      // update mode
      //echo 'xxx';
      $product = call_user_func(array(self::$table_class, 'getByID'), self::$pk, $data[self::$pk]);

      // convert data
      foreach ($data as $idx => $row) {
        if ($this->getColumnType($idx) == 'Number') {
          $data[$idx] = CT::convertCurrencyToFloat($data[$idx]);
        }
      }

      //$product = CrudData::get()->byID($data[self::$pk]);
      //if($product)
      //unset($data[self::$pk]);
    }
    //echo '<pre>'; var_dump($data);die();
    //var_dump($product);die();
    if (!$product) {
      // new mode
      $class = self::$table_class;
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
    $data = call_user_func(array(self::$table_class, 'getByID'), self::$pk, $id);
    if (!$data) {
      return 'error';
    }

    $form = $this->AddForm(); // edit juga ttp pakai add form
    $form->Fields()->push(new HiddenField(self::$pk, self::$pk, $id));
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
    $data = call_user_func(array(self::$table_class, 'getByID'), self::$pk, $id);
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

  function generateSearchWhere($query) {
    $sql = '';
    foreach (self::$search_field as $row) {
      if ($sql) {
        $sql.= " OR ";
      }
      $sql .= " $row LIKE '%" . $query . "%' ";
    }
    return $sql;
  }

  function searchajax() {
    // ============ filter bawaan datatable
    $start = (isset($_REQUEST['start'])) ? $_REQUEST['start'] : 0;
    $length = (isset($_REQUEST['length'])) ? $_REQUEST['length'] : 10;
    $search = (isset($_REQUEST['search']['value'])) ? $_REQUEST['search']['value'] : '';
    $columnsort = (isset($_REQUEST['order'][0]['column'])) ? $_REQUEST['order'][0]['column'] : 1;
    $typesort = (isset($_REQUEST['order'][0]['dir'])) ? $_REQUEST['order'][0]['dir'] : 'DESC';
    //$status = (isset($_REQUEST['status'])) ? $_REQUEST['status'] : '';
    $fieldsort = (isset($_REQUEST['columns'][$columnsort]['data']) && $_REQUEST['columns'][$columnsort]['data']) ? $_REQUEST['columns'][$columnsort]['data'] : 'TglTBeli';
    // ============ end filter
    // SETTING INI
    $where = " " . self::$pk . " != '' ";
    if ($search) {
      $where .= " AND (" . $this->generateSearchWhere($search) . ") ";
    }
    //echo $where;die();
//    $result = CrudARModel::find('all', array(
//                'conditions' => $where,
//                'order' => $fieldsort.' '.$typesort,
//                'limit' => $length,
//                'offset' => $start
//    ));
    $result = call_user_func(array(self::$table_class, 'find'), 'all', array(
        'conditions' => $where,
        'order' => $fieldsort . ' ' . $typesort,
        'limit' => $length,
        'offset' => $start
    ));
    $sql = "select count(" . self::$pk . ") as total
      from " . self::$table . "
      where $where";
    //$result_count = CrudARModel::find_by_sql($sql);
    $result_count = call_user_func(array(self::$table_class, 'find_by_sql'), $sql);
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
        } else {
          $temp[] = $row->$temp_field;
        }
      }
      $temp_field_pk = strtolower(self::$pk);
      $edit_link = $this->Link() . 'edit/' . $row->$temp_field_pk;
      //if (CrudARModel::countChild($row->$temp_field_pk)) {
      //var_dump(call_user_func(array(self::$table_class, 'countChild'), $row->$temp_field_pk));
      if (call_user_func(array(self::$table_class, 'countChild'), $row->$temp_field_pk)) {
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
        $fields->push(self::generateFieldsByType($col['Type'], 'DataDetail[' . $col['Column'] . '][]', $col['Column'], $data->$temp_field, $col['Source'], $col['Required']));
        //var_dump($data->$col['Column']);
      } else {
        $fields->push(self::generateFieldsByType($col['Type'], 'DataDetail[' . $col['Column'] . '][]', $col['Column'], '', $col['Source'], $col['Required']));
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
        $html_head .= '<th>' . $col['Column'] . '</th>';
      }
      $html_head .= '<th>Action</th>';

      // body
      $html_body = '';
      if ($data) {
        //echo '<pre>';var_dump($data);
        // jika ada data, loop semua data dlm bentuk table
        $temp_field_pk = strtolower(self::$pk);
        if (call_user_func(array(self::$table_class, 'countChild'), $data->$temp_field_pk)) {
          $childs = call_user_func(array(self::$table_class, 'getChild'), $data->$temp_field_pk);
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
    $product = null;
    if (isset($data[self::$pk]) && $data[self::$pk]) {
      // update mode
      //echo 'xxx';
      $product = call_user_func(array(self::$table_class, 'getByID'), self::$pk, $data[self::$pk]);
      //$product = CrudData::get()->byID($data[self::$pk]);
      //if($product)
      //unset($data[self::$pk]);
    }
    //var_dump($product);die();
    if (!$product) {
      // new mode
      $class = self::$table_class;
      $product = new $class();
    }
    $data_save = $data;
    unset($data_save['DataDetail']);
    $product->set_attributes($data_save);
    $product->save();

    // SAVE DETAIL

    if (isset($data['DataDetail']) && count($data['DataDetail'])) {
      // hapus all detail
      call_user_func(array(self::$table_class, 'deleteChild'), self::$pk, $data[self::$pk]);

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
        $class = self::$table_detail_class;
        $foreign_key = strtolower(self::$foreign_key);
        $pk_detail = strtolower(self::$pk_detail);
        $detail_obj = new $class();
        //unset($arr_temp['ID']);
        //var_dump($arr_temp);die();
        $detail_obj->set_attributes($arr_temp);
        $detail_obj->$pk_detail = rand(); // TODO: generate id detail
        $detail_obj->$foreign_key = $data[self::$pk];
        $detail_obj->save();
      }
      //echo '<pre>'; var_dump($arr_detail);die();
    }

    // good / info / bad
    $form->sessionMessage('success', 'good');
    $this->redirectBack();
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
          continue;
        }
      }
    }
    return $arr;
  }

  function editmasterdetail() {
    $id = $this->request->param('ID');
    if (!$id) {
      return 'error';
    }
    $data = call_user_func(array(self::$table_class, 'getByID'), self::$pk, $id);
    if (!$data) {
      return 'error';
    }
    $form = $this->AddForm();
    $form->Fields()->push(new HiddenField(self::$pk, self::$pk, $id));
    $form->Fields()->push(new LiteralField('DetailForm', $this->AddDetailForm($data)));
    $form->Actions()->removeByName('action_AddDo');
    $form->Actions()->push(new FormAction('AddDetailDo', 'Save')); // ganti method kalau save    
    $arr_data = $data->attributes();
    //echo '<pre>'; var_dump($arr_data);
    $arr_data = $this->convertLowerArray($arr_data);
    $form->loadDataFrom($arr_data); // inject data to form
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