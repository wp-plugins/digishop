=== DigiShop ===
Contributors: lordspace
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7APYDVPBCSY9A
Tags: store,ecommerce,estore,online shop, shopping cart, wordperss e-commerce, wordperss ecommerce, sell digital products, sell ebook, ebook, sell ebook,digishop,digi shop
Requires at least: 2.0.2
Tested up to: 3.4
Stable tag: 1.0.5

DigiShop plugin allows you to start selling your digital products such as e-books, reports in minutes.

== Description ==

DigiShop is a WordPress plugin which allows you to setup your e-store and start selling your digital products such as e-books, reports in minutes.

= Demo =

http://www.youtube.com/watch?v=6EKNMYjzwlM

= Benefits / Features =

* Easy to use
* Downloads links are served from the main domain e.g. yourdomain.com/?digishop_dl=f47c137alaskfjlasflajsfkasjfklaklfaksfajsflkasf
* When download link is clicked the download dialog is shown i.e. the file does not show within the browser (forced download)
* Handles PayPal Live and Sanbox
* Functionality to enable/disable products (when a product is disabled the buy now link will not be shown and the file can't be downloaded even with the download link)
* Customize the text for the successful and unsuccessful transaction
* In case of a failed transaction the email is sent to the admin so he can handle the failed transaction manually
* Short codes are just ready to copied and pasted into your pages
* Update:There is a button in edit page/post that allows you to choose a product to insert

= Author =

Svetoslav Marinov (Slavi) | <a href="http://webweb.ca" title="Custom Web Programming, Web Design, e-commerce, e-store, Wordpress Plugin Development, Facebook and Mobile App Development in Niagara Falls, St. Catharines, Ontario, Canada" target="_blank">Custom Web and Mobile Programming by WebWeb.ca</a>

== Installation ==

= Automatic Install =
Please go to Wordpress Admin &gt; Plugins &gt; Add New Plugin &gt; Search for: DigiShop and then press install

= Manual Installation =
1. Upload digishop.zip into to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

n/a

Run into issues or have questions/suggestions? Send them to help@WebWeb.ca

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

= 1.0.5 =
* fixed: passing an extra variable which caused PayPal transactions not to validate
* Address Settings > sandbox IP which if supplied with enabled sandbox will enable sandbox mode only for that specific IP address.
* 

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
