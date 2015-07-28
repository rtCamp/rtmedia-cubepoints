## How to add this module in Product

**NOTE:** This lib is only for **EDD Products**.

This lib will check plugin update notice as well as edd licensing.

Use this lib as a subtree in the product.

To use this lib, first add this repo as a remote.

	git remote add rtm-edd-license-subtree git@git.rtcamp.com:rtmedia/rt-edd-license.git

Run following command to add remote repo as subtree

	git subtree add --prefix=lib/rt-edd-license rtm-edd-license-subtree master

To pull changes subtree, run following command

	git subtree pull --prefix=lib/rt-edd-license rtm-edd-license-subtree master

## How to Use

**NOTE:** Please use below code in plugin's main file to check update.

* First create **Product Details Array**.

    For example 
    
    ```php
    $rtmedia_product_details = array(
	    'rt_product_id' 				 => 'rtmedia_product',
	    'rt_product_name' 				 => 'rtMedia Product',
	    'rt_product_href' 				 => 'rtmedia-product',
	    'rt_license_key' 				 => 'edd_rtmedia_product_license_key',
	    'rt_license_status' 			 => 'edd_rtmedia_product_license_status',
	    'rt_nonce_field_name' 			 => 'edd_rtmedia_product_nonce',
	    'rt_license_activate_btn_name'   => 'edd_rtmedia_product_license_activate',
	    'rt_license_deactivate_btn_name' => 'edd_rtmedia_product_license_deactivate',
	    'rt_product_path'                => RTMEDIA_PRODUCT_PATH,
	    'rt_product_store_url' 			 => EDD_RTMEDIA_PRODUCT_STORE_URL,
	    'rt_product_base_name' 			 => RTMEDIA_PRODUCT_BASE_NAME,
	    'rt_product_version' 			 => RTMEDIA_PRODUCT_VERSION,
	    'rt_item_name' 					 => EDD_RTMEDIA_PRODUCT_ITEM_NAME,
	    'rt_license_hook' 				 => 'rtmedia_license_tabs'
	    'rt_product_text_domain' 		 => 'rt_product_text_domain'
    );
    ```
    
* Create an object of **`RTEDDLicense`** class

    Use below code to create an object:
    ```php
    new RTEDDLicense( $rtmedia_product_details );
    ```
    


## Resources for subtree:

[subtree explained on wpveda](http://wpveda.com/git/subtree.html)

[tutorial on medium](https://medium.com/@v/git-subtrees-a-tutorial-6ff568381844)
