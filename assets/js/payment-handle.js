(function ($, root, undefined) {
    
    $(function () {
        
        'use strict';

        var entityMap = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
            '/': '&#x2F;',
            '`': '&#x60;',
            '=': '&#x3D;'
        };
          
        function escapeHtml(string) {
            return String(string).replace(/[&<>"'`=\/]/g, function (s) {
              return entityMap[s];
            });
        }

        $('#init-payment').on('submit', function(e) {
            var form = $(this);
            var formFields = form.find('input');
            var formData = [];

            $('.payments-order-wrapper').addClass('loading');

            formFields.each(function(i, field){
                var fieldName = field.name;
                var fieldValue = field.value;
    
                if(fieldName && fieldValue) {
                    formData.push({
                        name: fieldName,
                        value: escapeHtml(fieldValue)
                    });                
                }
            });

            $.ajax({
                type: 'POST',
                url: Order.url,
                data: formData,
                dataType: 'JSON',
                success: function(response) {
                    if(response) {
                        window.location.href = response;
                    }
                },
                error: function(){
                    $('.payments-order-wrapper').removeClass('loading');
                }
            });

            e.preventDefault();
        });
        
    });
    
})(jQuery, this);