jQuery(function($) {
  $('body').on('payment_method_selected', function() {
    var html = $("label[for='payplus_kbank'] > p").html();
    $("label[for='payplus_kbank'] > p").replaceWith('<div style="display: inline-block;">'+html+'</div>');
  });
});