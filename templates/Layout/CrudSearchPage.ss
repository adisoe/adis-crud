<link rel="stylesheet" href="$ThemeDir/css/meanmenu/jquery.dataTables.min.css">
<script src="$ThemeDir/js/data-table/jquery.dataTables.min.js"></script>

<div class="container">
<div class="row">
<div class="col-md-12">
  <h1>$Title</h1>
  $Form
  <a href="{$Link}add" class="btn btn-success"><i class="fa fa-plus"></i> Tambah</a>
  <a href="{$Link}addmasterdetail" class="btn btn-success"><i class="fa fa-plus"></i> Tambah Master Detail</a>
  <a href="#" class="btn btn-primary" id="btn_detail_search"><i class="fa fa-search"></i> Detail Search</a>
  $SearchForm
  <table id="datatable1" class="table">
    <thead>
      <tr>
        <% loop Columns %>
          <% if not HideTable %>
          <th data-order="$Column">$Label</th>
          <% end_if %>
        <% end_loop %>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      
    </tbody>
  </table>
</div>
</div>
</div>

<script>
(function($){
  var table;
  $(document).ready(function(){
    table = $('#datatable1').DataTable({
      'processing': true,
      'serverSide': true,
      'ajax': {
        'url' : '{$Link}searchajax',
        "data": function(d){
          $SearchDataTableAdditional
        }
      }
    });
    
    $(document).on('click', '.btn_delete', function(){
      //alert('yakin untuk hapus?');
      var r = confirm("Yakin untuk hapus?");
      if (r == true) {
        
      } else {
        return false;
      }
    });
    
    $('form.form_search').on('submit', function(e){
      e.preventDefault();
      table.draw();
    });
    
    $('form.form_search').toggle();
    $('#btn_detail_search').on('click', function(){
      $('form.form_search').toggle();
      return false;
    });
  });  
})(jQuery);  
</script>  