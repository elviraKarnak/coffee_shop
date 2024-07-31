jQuery(document).ready( function () {
    var signupCondition;
    var productsSettingLabel;
    var productsSetting;
    var productsSettingDesc;
    productsSettingLabel = document.getElementById('mycred-pref-hooks-affiliate-signup-products-setting-label');
    productsSetting = document.getElementById('mycred-pref-hooks-affiliate-signup-products-setting');
    signupCondition = document.getElementById('mycred-pref-hooks-affiliate-signup-condition');
    productsSettingDesc.style.display = 'none';

    if ( signupCondition.value == 'all_products' )
    {
        productsSettingLabel.style.display = 'none';
        productsSetting.style.display = 'none';
        productsSettingDesc.style.display = 'none';
    }

    if ( signupCondition.value == 'products_except' )
    {
        productsSettingLabel.style.display = 'block';
        productsSettingLabel.innerHTML = 'Product ID\'s';
        productsSetting.style.display = 'block';
        productsSettingDesc.style.display = 'block';

    }

    if ( signupCondition.value == 'selected_products' )
    {
        productsSettingLabel.style.display = 'block';
        productsSettingLabel.innerHTML = 'Product ID\'s';
        productsSetting.style.display = 'block';
        productsSettingDesc.style.display = 'block';

    }

    if ( signupCondition.value == 'order_amount' )
    {
        productsSettingLabel.style.display = 'block';
        productsSettingLabel.innerHTML = 'Orders Amount';
        productsSetting.style.display = 'block';
        productsSettingDesc.style.display = 'block';

    }
})
function orderSignupCondition( select )
{
    var signupCondition;
    var productsSettingLabel;
    var productsSetting;
    var productsSettingDesc;
    productsSettingLabel = document.getElementById('mycred-pref-hooks-affiliate-signup-products-setting-label');
    productsSetting = document.getElementById('mycred-pref-hooks-affiliate-signup-products-setting');
    signupCondition = document.getElementById('mycred-pref-hooks-affiliate-signup-condition');
    productsSettingDesc = document.getElementById('mycred-pref-hooks-affiliate-signup-products-setting-desc');

    if ( signupCondition.value == 'all_products' )
    {
        productsSettingLabel.style.display = 'none';
        productsSetting.style.display = 'none';
        productsSettingDesc.style.display = 'none';
    }

    if ( signupCondition.value == 'products_except' )
    {
        productsSettingLabel.style.display = 'block';
        productsSettingLabel.innerHTML = 'Product ID\'s';
        productsSetting.style.display = 'block';
        productsSettingDesc.style.display = 'block';
    }

    if ( signupCondition.value == 'selected_products' )
    {
        productsSettingLabel.style.display = 'block';
        productsSettingLabel.innerHTML = 'Product ID\'s';
        productsSetting.style.display = 'block';
        productsSettingDesc.style.display = 'block';
    }

    if ( signupCondition.value == 'order_amount' )
    {
        productsSettingLabel.style.display = 'block';
        productsSettingLabel.innerHTML = 'Orders Amount';
        productsSetting.style.display = 'block';
        productsSettingDesc.style.display = 'block';
    }
}