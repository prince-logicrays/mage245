var config = {
	config: {
        mixins: {
            'Magento_Checkout/js/sidebar': {
                'Logicrays_IncreaseQty/js/sidebar': true
            }
        }
    },
    map: {
        '*': {
            'Magento_Checkout/template/minicart/item/default.html': 'Logicrays_IncreaseQty/template/minicart/item/default.html'
        },
        '*': {
            'Magento_Swatches/js/swatch-renderer':'Logicrays_IncreaseQty/js/swatch-renderer'
        }
    }
};

