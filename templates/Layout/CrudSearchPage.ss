<link rel="stylesheet" href="$ThemeDir/css/meanmenu/jquery.dataTables.min.css">
<script src="$ThemeDir/js/data-table/jquery.dataTables.min.js"></script>

<div class="container">
<div class="row">
<div class="col-md-12">
  <h1>$Title</h1>
  $Form
  <a href="$Link/add" class="btn btn-success"><i class="fa fa-plus"></i> Tambah</a>
  <a href="$Link/addmasterdetail" class="btn btn-success"><i class="fa fa-plus"></i> Tambah Master Detail</a>
  <table id="datatable1" class="table">
    <thead>
      <tr>
        <% loop Columns %>
        <th>$Column</th>
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
        'url' : 'crud/searchajax'
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
  });  
})(jQuery);  
</script>  