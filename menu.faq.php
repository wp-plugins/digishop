<div class="webweb_wp_plugin">
    <div class="wrap">
        <h2>Frequently Asked Questions</h2>

        <p>
            <h3>Test Transactions Always Fail. Why?</h3>
			For some reason PayPal doesn't return VERIFIED for the test transactions.
        </p>
        <p>
            <h3>I need to backup the products table. How can I do that?</h3> 

            Please backup (using phpMyAdmin or similar tool) table: 
			<strong><?php echo $webweb_wp_digishop_obj->get('plugin_db_prefix') . 'products' ; ?></strong>

            <br /> and copy the contents of <strong><?php echo $webweb_wp_digishop_obj->get('plugin_uploads_dir'); ?></strong>
        </p>
    </div>
</div>
