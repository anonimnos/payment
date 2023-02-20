(function ($, root, undefined) {
    
    $(function () {
        
        'use strict';

        if ( typeof wc_add_to_cart_params === 'undefined' ) {
            return false;
        }
      
        $(document).on('submit', 'form.cart', function(e){
            
            var form = $(this),
                button = form.find('.single_add_to_cart_button');
            
            var formFields = form.find('input:not([name="product_id"]), select, button, textarea');
    
            var formData = [];
            formFields.each(function(i, field){
                var fieldName = field.name;
                var fieldValue = field.value;
    
                if(fieldName && fieldValue) {
                    if(fieldName == 'add-to-cart'){
                        fieldName = 'product_id';
                    }
    
                    formData.push({
                        name: fieldName,
                        value: fieldValue
                    });                
                }
            });
    
            if(!formData.length){
                return;
            }
            
            e.preventDefault();
            
            form.block({ 
                message: null, 
                overlayCSS: {
                    background: "#ffffff",
                    opacity: 0.6 
                }
            });
    
            $(document.body).trigger('adding_to_cart', [button, formData]);
      
            $.ajax({
                type: 'POST',
                url: woocommerce_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart'),
                data: formData,
                success: function(response){
                    if(!response){
                        return;
                    }
                    if(response.error & response.product_url){
                        window.location = response.product_url;
                        return;
                    }
                    
                    $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, button]);
                },
                complete: function(){
                    form.unblock();
                    $('.payment-cf-wrapper').hide();
                    $('.single_add_to_cart_button').hide();
                    $('.payments-order-wrapper').addClass('active');
                }
            });
      
            return false;
      
        });
        
    });
    
})(jQuery, this);