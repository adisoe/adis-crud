<div class="container">
<div class="row">
<div class="col-md-12">
  <h1>$Title</h1>
  $Content
  $Form  
</div>
</div>
</div>

<script>
(function($){
  function initPlugin(){
    $('.datepicker').datepicker({
      todayBtn: "linked",
      keyboardNavigation: false,
      forceParse: false,
      calendarWeeks: true,
      autoclose: true
    });
  }
  initPlugin();
  
  var row_detail = '$RowDetailForm';  
  //alert(detail_data[0].Price);
  $('#button_add_detail').on('click', function(){
    $('#table_detail').append(row_detail);
    initPlugin();
    return false;
  });
  
  $(document).on('click', '.button_delete_detail', function(){
    //alert('delete');
    $(this).closest('tr').remove();
    return false;
  });    
  
  <% if $DetailData %>
//  var detail_data = $DetailData;
//  for(var i in detail_data){
//    $('#table_detail').append(row_detail);
//  }
  <% end_if %>
})(jQuery);  
</script>  