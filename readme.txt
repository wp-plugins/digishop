=== DigiShop ===
Contributors: lordspace
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7APYDVPBCSY9A
Tags: store,ecommerce,estore,online shop, shopping cart, wordperss e-commerce, wordperss ecommerce, sell digital products, sell ebook, ebook, sell ebook,digishop,digi shop
Requires at least: 2.0.2
Tested up to: 3.5.1
Stable tag: 1.1.4
License: GPLv2 or later

DigiShop plugin allows you to start selling your digital products such as e-books, reports in minutes.

== Description ==

= Support =
> Support is handled on our site: <a href="http://club.orbisius.com/support/" target="_blank" title="[new window]">http://club.orbisius.com/support/</a>
> Please do NOT use the WordPress forums or other places to seek support.
> New: We have launched our <a href="http://club.orbisius.com/products/" target="_blank" title="[new window]">membership site</a>
> which gives you access to all of our premium plugins at an affordable price.

DigiShop is a WordPress plugin which allows you to setup your e-store and start selling your digital products such as e-books, reports in minutes.
It adds a simple buy now button which sends your customer to PayPal to complete the payment and after that he/she is returned to your site.

= Demo =

http://www.youtube.com/watch?v=6EKNMYjzwlM

= Benefits / Features =

* Easy to use
* Downloads links are served from the main domain e.g. yourdomain.com/?digishop_dl=f47c137
* When download link is clicked the download dialog is shown i.e. the file does not show within the browser (forced download)
* Handles PayPal Live and Sanbox
* Functionality to enable/disable products (when a product is disabled the buy now link will not be shown and the file can't be downloaded even with the download link)
* Customize the text for the successful and unsuccessful transaction
* In case of a failed transaction the email is sent to the admin so he can handle the failed transaction manually
* There is a dollar sign button in edit page/post that allows you to choose a product and this will insert the correct shortcode which will be later replaced by Buy Now button
* Protection against multiple calls made from PayPal IPN which paypal makes to notify the site owner for the transaction.
* Download expires after 48 hours
* Download limits: maximum 3 per order
* Allows functionality the form submission to go in a new window
* Optional shipping address requirement (both available as a global setting and per individual product)
* Supports a secure HOP URL. The main idea of the Secure HOP URL is to redirect to another URL. The script must redirect to an address passed by the "r" parameter.
Having this kind of redirect is very useful because when your visitors are about to return to your site PayPal checks and if the returning URL is a non-ssl link then it puts a warning with
makes the user experience less optimal. DigiShop includes a sample redirect script that you can install on your secure site.

<a href="http://orbisius.com/go/intro2site?digishop"
    target="_blank">Free e-book: How to Build a Website Using WordPress: Beginners Guide</a>

= Author =

Svetoslav Marinov (Slavi) | <a href="http://orbisius.com" title="Custom Web Programming, Web Design, e-commerce, e-store, Wordpress Plugin Development, Facebook and Mobile App Development in Niagara Falls, St. Catharines, Ontario, Canada" target="_blank">Custom Web and Mobile Programming by Orbisius.com</a>

== Installation ==

= Automatic Install =
Please go to Wordpress Admin &gt; Plugins &gt; Add New Plugin &gt; Search for: DigiShop and then press install

= Manual Installation =
1. Upload digishop.zip into to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Is DIGIshop Purchase Required? =
DigiShop WordPress plugin has nothing to do with the DigiSHOP from sumeffect

DigiShop WordPress plugin is free to use for personal or commercial one.

= issues/questions/suggestions = 
If you have run into issues or have questions/suggestions please register on our support forum and post your suggestion or question there.

> Support is handled on our site: <a href="http://club.orbisius.com/support/" target="_blank" title="[new window]">http://club.orbisius.com/support/</a>
> Please do NOT use the WordPress forums or other places to seek support.


== Screenshots ==
1. Plugin icon when editing post/page
2. Dashboard
3. Products
4. Add Product
5. Settings
6. FAQ
7. Help
8. Contact
9. About
10. Buy Now Button
11. Buy Now Button after the transaction with the success message.

== Upgrade Notice ==
n/a

== Changelog ==

= 1.1.4 =
= Fixed when updating a product the status message was showing as error that happened only when replacing the filename
= Misc Fixes.

= 1.1.3 =
* Added info about the changed support
* Corrected links to the donation email
* Tested the plugin with WP 3.5.1

= 1.1.2 =
= added a secure hop URL field in the settings.

= 1.1.1 =
= fixed a hard to find bug when validating the txn

= 1.1.0 =
= fixed the error about get instance ... the plugin was crashing when options were saved
= added shipping address checkbox if it should be required or not (both a global setting and per individual products)
= made the settings page more compact by hiding the advanced options by default and show them of from another show/hide button
= added please wait

= 1.0.8 =
= added checkbox to select if the form submission should be done in a new window
= fixed a call that was breaking because was referencing a different plugin of mine
= added a link to my free e-book

= 1.0.7 =
* put css classes on the buy now button in case people want to apply styles to it.

= 1.0.6 =
* Added a check for invalid input. The plugin stops working if it receives invalid input e.g. text instead of a number (applicable for IDs)
* Implemented file sanitization method ... leaves only nicely formatted filenames. If the filename is totally cleaned up then it'll use a default name
* Turned autocomplete input box for Price in Add Product
* Fix: If the file exists when adding a product append a number (timestamp) before the extension
* Fix: some people reported that they or their clients got lots of emails. The plugin will not process it a transaction if PayPal calls the plugin for 2nd or more times
* Added download expiration within 48 hours for new orders.
* Showing the annoying messages when sandbox mode is turned on to appear only on the plugins page(s)
* Added a limit to the downloaded files 3 per hash

= 1.0.5 =
* fixed: passing an extra variable which caused PayPal transactions not to validate
* Address Settings > sandbox IP which if supplied with enabled sandbox will enable sandbox mode only for that specific IP address.

= 1.0.4 =
* made the payment form to submit to the blog and then the WP site will redirect to PayPal
* added files to be supplied as external URL
* functionality to call another URL after a transaction
* added option to customize the submit button's image
* added info about what to backup in FAQ.
* added Products link in the Plugins section
* fixed the IPN part
* added trailing slash to the blog ...
* showing transaction status message (positive/negative) at the top in addition to the old message.
* added uninstall script to clean stuff up after plugin removal
* added sanbox paypal email in the settings (useful when testing with sandbox)
* added .htaccess in data/ folder
* added aggressive logging. the log file is made up hash and date for harder guessing. It should not be accessible because of htaccess
* rearranged settings menu screen
* showing the max upload size (hosting dependant)
* fixes and tweaks

= 1.0.3 =
* Added some fixes with the downloads.
* Chrome users were getting download interrupted.
* Added a link to the files (e.g. when the admin has to manually send the download link)
* Added a newsletter box in the settings

= 1.0.2 =
* Fixed: Notification was not sent to the payer

= 1.0.1 =
* Newsletter and donation boxes.
* Show product status and icons if a file has been attached

= 1.0.0 =
* Initial Release
