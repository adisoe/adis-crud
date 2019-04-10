<link rel="stylesheet" href="adis-crud/css/bootstrap-datetimepicker.css">
<script src="adis-crud/js/moment.min.js"></script>
<script src="adis-crud/js/bootstrap-datetimepicker.js"></script>
<script src="adis-crud/js/autoNumeric.js"></script>

<div class="container">
<div class="row">
<div class="col-md-12">
  <h1>$Title</h1>
  $Content
  $Form  
</div>
</div>
</div>

<table id="row_detail" style="display:none;">$RowDetailForm</table>

<script>
(function($){
  function initPlugin(){
    $('.datepicker').datetimepicker({        
      format: 'YYYY-MM-DD'
    });

    $('.autonumeric').autoNumeric('init',{mDec:0});  //autoNumeric with defaults
  }
  initPlugin();

  //alert(detail_data[0].Price);
  $('#button_add_detail').on('click', function(){
    //console.log($('#row_detail').text());
    $('#table_detail').append($('#row_detail').html());
    initPlugin();
    return false;
  });
  
  $(document).on('click', '.button_delete_detail', function(){
    //alert('delete');
    $(this).closest('tr').remove();
    return false;
  });        
  
  $('.text-browse').on('click', function(){      
    var module = $(this).attr('browse-module');
    textBrowse = $(this);
    // variable win ada di paling luar / atas javascript
    win = window.open('browse/window/'+module, 'MyWindow', "menubar=0,toolbar=0,width=600,height=400");    
  });
  
  <% if $DetailData %>
//  var detail_data = $DetailData;
//  for(var i in detail_data){
//    $('#table_detail').append(row_detail);
//  }
  <% end_if %>
})(jQuery);  
</script>  