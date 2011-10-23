<div class="webweb_wp_plugin">
    <div class="wrap">
        <h2>Help</h2>
		
        <h3>How to use the plugin</h3>
        <p>
            First, add enter product name, price and upload a file (can be anything)<br/>
            Second, go to Products to copy the short code <br/>
            Third, paste the short code in the article/page where you'd like the buy button to appear. <br/>
            
            Enter your paypal details in the Settings page and enable the plugin. <br/>
        </p>

        <h3>What are the steps for the buyer</h3>
        <p>
            The user goes to the page dedicated to the e-product then clicks Buy Now link and gets redirected to paypal. <br/>
            After the payment the user is returned to the same page he/she has ordered the product from. <br/>
            Depending on the payment status the user will see the success or the error message. <br/>
            In case of an error the admin will receive an email with the information that has been sent from PayPal.<br/>
            If the payment was successful the user will receive a download link. <br/>
            The admin will also receive it because he/she is added to the BCC list in the email. <br/>
        </p>

        <h3>Demo</h3>
		
		<p>
            TODO
			<!--Link: <a href="http://www.youtube.com/watch?v=gFatlDwc8uU&hd=1" target="_blank" title="[opens in a new and bigger tab/window]">http://www.youtube.com/watch?v=gFatlDwc8uU&hd=1</a>
			<p>
				<iframe width="425" height="349" src="http://www.youtube.com/embed/gFatlDwc8uU?hl=en&fs=1" frameborder="0" allowfullscreen></iframe>
			</p>
			-->

			<?php if (0) : ?>
			<?php
		$app_link = 'http://www.youtube.com/embed/gFatlDwc8uU?hl=en&fs=1';
		$app_title = $webweb_wp_digishop_obj->get('app_title');
		$app_descr = $webweb_wp_digishop_obj->get('plugin_description');
		?>
		<p>Share this video:
			<!-- AddThis Button BEGIN -->
			<div class="addthis_toolbox addthis_default_style addthis_16x16_style">
			<a class="addthis_button_facebook" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
			<a class="addthis_button_twitter" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
			<a class="addthis_button_google_plusone" g:plusone:count="false" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
			<a class="addthis_button_linkedin" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
			<a class="addthis_button_email" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
			<a class="addthis_button_myspace" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
			<a class="addthis_button_google" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
			<a class="addthis_button_digg" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
			<a class="addthis_button_delicious" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
			<a class="addthis_button_stumbleupon" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
			<a class="addthis_button_googlebuzz" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
			<a class="addthis_button_tumblr" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
			<a class="addthis_button_favorites" addthis:url="<?php echo $app_link?>" addthis:title="<?php echo $app_title?>" addthis:description="<?php echo $app_descr?>"></a>
			<a class="addthis_button_compact"></a>
			</div>
			<!-- The JS code is in the footer -->
		</p>
			
		<script type="text/javascript">
		var addthis_config = {"data_track_clickback":true};
		var addthis_share = {
		  templates: { twitter: 'Check out {{title}} @ {{lurl}} (from @webwebsoft)' }
		}
		</script>
		<!-- AddThis Button START part2 -->
		<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=lordspace"></script>
		<!-- AddThis Button END part2 -->
		</p>		

        <?php endif; ?>
        
        <h3>Still unclear ?</h3>
        <p>Please send questions, bug reports, feature requests either using to 
            <a href="mailto:<?php echo $webweb_wp_digishop_obj->get('plugin_support_email');?>?subject=<?php echo urlencode($webweb_wp_digishop_obj->get('plugin_name'));?>">
            <?php echo $webweb_wp_digishop_obj->get('plugin_support_email');?></a>
            or using the form below.
        </p>
        <p>Plugin's home page: <a href="<?php echo $webweb_wp_digishop_obj->get('plugin_home_page');?>"
                                  target="_blank"><?php echo $webweb_wp_digishop_obj->get('plugin_home_page');?></a></p>

        <h2>Suggest a feature or report a bug</h2>

        <p>
            <iframe src="<?php echo $webweb_wp_digishop_obj->get('plugin_support_link');?>" style="overflow: hidden; width: 100%; height: 910px;border: none;" marginheight="0" align="top" scrolling="No" frameborder="0" hspace="0" vspace="0">Cannot load frame. Please send us an email instead. The email is: info@use-the-same-domain</iframe>
        </p>
    </div>
</div>
