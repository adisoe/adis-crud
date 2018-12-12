<?php

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
      'edit',
      'search',
      'searchajax',
      'delete'
  );

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
            'Type' => 'Text',
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
      if($col['Type'] == 'Text'){
        $fields->push(new TextField($col['Column'], $col['Column']));
      }
      elseif($col['Type'] == 'Number'){
        $fields->push(new NumericField($col['Column'], $col['Column']));
      }
      else{
        $fields->push(new TextField($col['Column'], $col['Column']));
      }
    }
    // custom fields disini
    $fields->removeByName('ID');
    $fields->removeByName('LastEdited');
    $action = new FieldList(
            $button = new FormAction('AddDo', 'Save')
    );
    //$button->addExtraClass('btn-lg');
    $validator = new RequiredFields('Title', 'Price');

    $form = new BootstrapForm($this, 'AddForm', $fields, $action, $validator);
    return $form;
  }

  function AddDo($data, $form) {
    //var_dump($data);
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
    return $this->redirect($this->Link() . 'add');
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
      $temp[] = '<a href="">Edit</a>
        <a href="">Delete</a>
        <a href="">More</a>
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

}

?>