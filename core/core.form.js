(function($){
    
    var form = (function() {
      var init = function() {
        $('[data-provider="datepicker"]').datepicker({
            format: "yyyy/mm/dd"
        });
      };
      
      return {
        init: init
      };
    })();

    $.extend(true, window, {
      core: {
        form: form
      }
    });

    $(function() {
        core.form.init();
    });

}(jQuery));