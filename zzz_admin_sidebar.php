<style>
    .zzz_app_admin_sidebar {
      
    }

    .zzz_app_admin_sidebar .more_plugins_list li a {
        background: url("<?php echo $webweb_wp_digishop_obj->get('plugin_url')?>/zzz_media/star.png") no-repeat scroll 0 0 transparent;
        padding: 0 0 3px 20px;
    }
</style>

<div class="zzz_app_admin_sidebar">
    <p style="border:2px dashed red; padding:3px;"><a href="http://orbisius.com/go/intro2site?s=<?php echo $webweb_wp_digishop_obj->get('plugin_id_str'); ?>"
                                                target="_blank">Free e-book: How to Build a Website Using WordPress: Beginners Guide</a>
    </p>
        <?php echo $webweb_wp_digishop_obj->generate_newsletter_box(array('form_only' => 1, 'src2' => 'admin_sidebar')); ?>
        <br class="clear_both" />
       
		<div>
            <iframe src="//www.facebook.com/plugins/likebox.php?href=www.facebook.com%2Fpages%2FWebWebca%2F172278676154985&amp;width=250&amp;height=558&amp;colorscheme=light&amp;show_faces=true&amp;border_color&amp;stream=true&amp;header=false&amp;appId=291949997486374" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:250px; height:558px;" allowTransparency="true"></iframe>
		</div>
</div>