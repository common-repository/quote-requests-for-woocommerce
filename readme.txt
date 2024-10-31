=== Quote Requests for WooCommerce ===
Contributors: ahegyes, deepwebsolutions
Tags: woocommerce, quotes, estimates, proposals, bids, requests
Requires at least: 5.6
Tested up to: 6.0
Requires PHP: 7.4
Stable tag: 1.0.4  
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

A WooCommerce extension for allowing customers to submit quote requests to get customized prices before placing their orders.

== Description ==

**Quote Requests is a WooCommerce extension for enabling your customers to submit quote requests asking for customized prices instead of placing orders.**

Not all products have an assigned price. It could be that market volatility makes prices fluctuate so often, you can't keep up with keeping your products updated. Or maybe you offer customized products that require more customer input before you can name a price.

For all of these situations, and more, it makes more sense to accept quote requests from your customers instead of having them place an order. This gives shop owners the opportunity to handle each customer independently, and transform this quote request into an order at a later point in time.

### How do customers submit requests?

First, shop owners must decide which products can be added to quote requests, and which customers are allowed to submit quote requests. This can be done inside the new `Quotes` tab on the WooCommerce settings page.

Valid choices for *who* can submit requests include:

* Any website visitor.
* Only logged-in visitors.
* Only guest (logged-out) visitors.
* **[Premium]** Only logged-in visitors in specific user roles.
* **[Premium]** Only logged-in visitors returned by your custom PHP function.

Valid choices for *what* customer quote requests can include:

* All supported products.
* Only supported products assigned given categories.
* Only supported products assigned given tags.
* **[Premium]** Only supported products assigned given terms from custom taxonomies.
* **[Premium]** Only supported products returned by your custom PHP function.

After this is configured, any valid customer visiting your website and viewing a valid product can then choose to either add it to their cart, or to their quote request list. After assembling their list, they can proceed to submit it thus creating a new quote request.

### What is the quote request list?

The quote request list is the WooCommerce cart itself. The plugin will disable a few features, like coupons and the shipping calculator, and will replace a few words with ones that make more sense, but that's it.

Similarly, when submitting the quote request list, the customers are basically filling out a slightly modified version of the WooCommerce checkout form.

**Premium:** Premium customers can choose to host the quote request list on a dedicated page and have the WC cart as a separate entity on their site. Similarly, the request submission form will then be separate from the checkout form.

### **[Premium]** Advanced customer request features

The premium version of *Quote Requests for WooCommerce* comes with a few more features to customize your customer's quote submission process:

* Hiding valid quote products from invalid customers: when an invalid customer visits the site, they won't be able to see quote request products.
* Disabling the purchase of valid products: force your customers to submit a quote request instead of giving them the choice between that and outright purchasing the product.
* Hiding the prices of valid products: make your prices private and don't reveal them to your customers until you finalize the quote.
* Requests for out-of-stock products: allow customers to submit quote requests regardless of whether the product is in stock or not.

### What happens after the quote request is submitted?

After the quote request is submitted, the customer will receive a confirmation email and shop managers will be able to view the request on the dedicated admin page. On the admin-side, quotes look almost exactly the same as orders, so this should be a familiar interface.

The next step is then to adjust the quote (change prices, substitute products, add shipping fees etc.), and to then set it into the status `Waiting on customer`. That will trigger the customer to receive an email with their finalized quote that will prompt them to either accept or reject the quote.

Customers with an account will have to perform either of these actions in their account area, while guest users will be able to use the quote tracking form created when first activating the plugin.

If the customer then accepts the quote, a new order will be created with the exact same contents, and they will be prompted to pay. If they choose to reject it, you can adjust the contents/prices and resubmit it for approval as many times as necessary.

### **[Premium]** Advanced quote features

The premium version of *Quote Requests for WooCommerce* provides a few more convenience features to facilitate quote acceptance:

* PDF documents: customers will be able to download a PDF version of their personalized quote.
* Rejection reason: customers will need to enter a reason for rejecting the quote so shop managers can act accordingly.
* Fulfillment conditions: before finalizing the quote and requesting customer approval, you can enter conditions in free-form text that the customer must also agree to.
* Automatic expiration: the ability to set an expiration date on the quote and send out automatic reminder emails a few days before the quote expires.

### Premium support and features

Some of the features mentioned above are only bundled with the premium version of our plugin available [here](https://www.deep-web-solutions.com/plugins/quote-requests-for-woocommerce/). It is perfectly possible, however, to use the free version and extend it via filters and actions with your own version of these features.

Premium customers are also entitled to prioritized help and support through [our support forum](https://www.deep-web-solutions.com/support/).

== Installation ==

This plugin requires WooCommerce 5.0+ to run. If you're running a lower version, please update first. After you made sure that you're running a supported version of WooCommerce, you may install `Quote Requests for WooCommerce` either manually or through your site's plugins page.

### INSTALL FROM WITHIN WORDPRESS

1. Visit the plugins page withing your dashboard and select `Add New`.
1. Search for `Quote Requests for WooCommerce` and click the `Install Now` button.

1. Activate the plugin from within your `Plugins` page.


### INSTALL MANUALLY

1. Download the plugin from https://wordpress.org/plugins/quote-requests-for-woocommerce and unzip the archive.
1. Upload the `quote-requests-for-woocommerce` folder to the `/wp-content/plugins/` directory.

1. Activate the plugin through the `Plugins` menu in WordPress.


### AFTER ACTIVATION

If the minimum required version of WooCommerce is present, you will find a new tab called `Quotes` on the WooCommerce `Settings` page. There you will be able to:

1. Configure the customer tracking page.
1. Configure which products can be added to customer quote requests.
1. Configure which customers can submit quote requests.


== Frequently Asked Questions ==

= Is this compatible with [insert plugin name] =

Probably yes. Since the default WooCommerce cart is used as a quote request list, and the checkout is also handled by the native WooCommerce process, quote requests should have complete parity with orders (for example, all product add-on plugins should work fine).

But if you have any issues with a 3rd-party plugin, please raise a support question!

= Is this compatible with [insert theme name] =

Probably yes. However, if your theme overrides the cart or checkout templates of WooCommerce and/or uses its own language domain when doing so, you might need to include a few lines of code to make things run smoothly. If that happens, please submit a support request.

= Can customer quote requests be disabled? =

Yes. If you want, you can disable customer quote requests and just keep the admin interface. That works best for physical stores or POS locations.

= What product types are supported for customer quote requests? =

At the moment, only simple and variable products can be added to quote requests by customers. The modular design of the plugin should allow anyone to add support for other types of products as well though. These restrictions do not apply to the admin area.

= How can I get help if I'm stuck? =

If you're using the free version, you can find more examples in [our knowledge base](https://docs.deep-web-solutions.com/article-categories/quote-requests-for-woocommerce/) and you can open a community support ticket here at [wordpress.org](https://wordpress.org/support/plugin/quote-requests-for-woocommerce/). Our staff regularly goes through the community forum to try and help.

If you've purchased the premium version of the plugin [on our website](https://www.deep-web-solutions.com/plugins/quote-requests-for-woocommerce/), you are entitled to a year of premium updates and access to [our prioritized support forum](https://www.deep-web-solutions.com/support/). You can use that to contact our support team directly if you have any questions.


= I have a question that is not listed here =

There is a chance that your question might be answered [in our knowledge base](https://docs.deep-web-solutions.com/article-categories/quote-requests-for-woocommerce/). Otherwise, feel free to reach out via our [contact form](https://www.deep-web-solutions.com/contact/).

== Screenshots ==

1. New quotes submenu.
2. Example of a new quote request inside the admin area.
3. Example of how the quote editing screen looks like.
4. New customer my-account endpoint.
5. Example of a finalized quote inside the customer account area.
6. Tracking form for guest users.
7. Quote status page.
8. Request quote button on product page.
9. Cart acting as a request list.
10. Checkout acting as a request form.

== Changelog ==

= 1.0.4 (June 8th, 2022) =

* Fixed: object cache invalidation issues.
* Updated dev packages and tested compatibility with latest WP and WC versions.

= 1.0.3 (March 30th, 2022) =
* Tested up to WooCommerce 6.3.
* Security: updated the Freemius SDK.

= 1.0.2 (February 11th, 2022) =
* Tested up to WooCommerce 6.2.


= 1.0.1 (February 8th, 2022) =
* Tested up to WordPress 5.9.
* Dev: updated DWS framework.

= 1.0.0 (January 23rd, 2022) =
* First official release.
