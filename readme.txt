=== Easy Plugin Demo Builder ===
Contributors: mikeyhoward1977
Tags: demo, plugin, theme, multisite, wpmu
Requires at least: 5.3
Tested up to: 5.6
Requires PHP: 5.4
Stable tag: 1.3.10
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A WordPress demo builder plugin that fully automates the creation of sandbox sites for you to showcase your plugins, themes and content to customers.

== Description ==

With Easy Plugin Demo (EPD), showcasing your plugins, themes, and content has never been easier!

**EPD requires that you are running a WordPress multisite instance**

EPD enables you to automate the process of creating sandbox demo environments for customers who wish to try your product(s) before they download or buy. With EPD you can **increase** your customers and your sales.

Once you've added the `[epd_register]` shortcode to a page, users simply enter their details into the registration form and a new demo site is created just for them in a matter of seconds. All configured with the options you define. You choose the plugins, themes, posts, pages and settings and Easy Plugin Demo takes care of the rest.

Each demo site is automatically deleted, together with all its custom content and user accounts once the lifetime you set, has passed.

You can add further customizations via the plugin settings page such as:

* Select the name for each new demo site created
* Choose which theme should be activated for the new site by default
* Choose which additional theme(s) can be activated within new demo sites
* Select which plugins should be activated for the new demo site
* Specify the maximum number of sites a single user can register at any given time
* Choose where to direct the user upon successful registration
* Require users to activate their demo's to validate their email address
* Automatically delete a site after a given period
* Define a custom welcome panel message to be displayed on new sites
* Send a customized email message to the user once their registration completes
* Duplicate posts from the primary blog to the newly created demo site (3 posts per post type unless the Premium extension is installed)

The Easy Plugin Demo [Premium Pack](https://easy-plugin-demo.com/downloads/premium-pack) extension unlocks a host of additional features including;

* Cloning of a master site for all new demo sites
* Demo Templates enable you to create multiple demo configurations which users can select
* Duplication of posts from any post type
* Duplicate an unlimited number of posts
* Include taxonomies and terms as part of post duplication
* Include post attachments (media) as part of the post duplication
* Include post comments as part of the post duplication
* Define author for replicated posts
* Clone database tables, including custom ones
* Choose to automatically add users to new sites and their roles
* Zapier integration to integrate Easy Plugin Demo with other a thousand third party applications
* MailChimp integration
* Schedule notices and provide information to users, or upsell your product(s)

We've also included a number of hooks and filters for further customizations by developers.

**Follow this plugin on [GitHub](https://github.com/mikeyhoward1977/easy-plugin-demo)**

**Check out Easy Plugin Demo for yourself!**
Our demo site provides a number of demos to give you an idea of how powerful Easy Plugin Demo is [https://demos.easy-plugin-demo.com/]('https://demos.easy-plugin-demo.com/').

**Languages**

Would you like to help translate the plugin into more languages? [Join the WP-Translations Community](https://translate.wordpress.org/projects/wp-plugins/easy-plugin-demo).

== Installation ==

**EPD requires that you are running a WordPress multisite instance**

**Automated Installation**

1. Login to your WordPress multisite network admin
1. Head to **Plugins** -> **Add New**
1. Enter Easy Plugin Demo into the search field
1. Click to install the plugin
1. Network Activate the plugin
1. Go to **Settings** -> **Easy Plugin Demo** to set your preferences

**Manual Installation**

Once you have downloaded the plugin zip file, follow these simple instructions to get going;

1. Go to the the Easy Plugin Demo WordPress plugin page at [https://wordpress.org/plugins/easy-plugin-demo/](https://wordpress.org/plugins/easy-plugin-demo/) and click the **Download** button to download the zip file
1. Login to your WordPress multisite network administration screen and select "Plugins" -> "Add New" from the menu
1. Select "Upload Plugin" from the top of the main page
1. Click "Choose File" and select the **easy-plugin-demo.zip** file you downloaded
1. Click "Install Now"
1. Once installation has finished, select **Network Activate**
1. Go to **Settings** -> **Easy Plugin Demo** to set your preferences

== Frequently Asked Questions ==

= How do I create an EPD registration page for my users? =

Create a new page and insert the `[epd_register]` into the content. Once published, visiting this page will display the registration form for new demo sites.

= How do I determine which plugins are enabled on a new demo site =

Any plugins that you Network Activate within your multisite will be automatically enabled for a new demo site. If you install plugins but do not network activate, you may choose which of them are to be activated within the **Settings** -> **Easy Plugin Demo** page.

= What will be the domain/sub-domain of the new demo site? =

EPD uses an alpha-numeric version of the users email address to create the new demo site. i.e. for a user with the email address of *email@domain.com*, the site will be *emaildomaincom*.

If you enable multiple demo sites per user, each additional site will be suffixed with a dash and a numerical representation of the current number of sites the user has registered. i.e. *emaildomaincom-1*.

= Do I need a sub-domain installation or a sub-directory installation of WordPress multisite? =

Either is fine. EPD supports both installation methods.

= Is there a demo of Easy Plugin Demo? =

Yes. Visit [https://demo.kb-support.com/](https://demo.kb-support.com/) to experience the registration process for a user when creating a new demo site.

= Where can I find support? =

Support is provided via the [WordPress.org support forums](https://wordpress.org/support/plugin/easy-plugin-demo). Post your questions and we'll reply as soon as we can!

== Screenshots ==

1. EPD settings screen preview. Customize these options to suit your needs.

2. User registration form. Fresh WordPress installation with default Twenty Seventeen theme.

3. Completed registration with confirmation. Fresh WordPress installation with default Twenty Seventeen theme.

== Changelog ==

= 1.3.10 =

**Tuesday, 8th December 2020**

* **New**: Added option to auto login users when their demo site is registered. For fresh installations, this option is off. For existing installations, it is set to on to maintain current feature

* **Tweak**: Throw an error if the plugin is being activated in a non-multisite environment
* **Tweak**: Incremented WordPress tested up to version

= 1.3.9 =

**Friday, 20th November 2020**

* **Bug**: Super admins should not be able to register for a demo site
* **Tweak**: Added Network plugin header

= 1.3.8 =

**Wednesday, 18th November 2020**

* **New**: Added support for the [Easy Plugin Demo API](https://easy-plugin-demo.com/articles/the-epd-rest-api/) provided within the [EPD Premium Pack](https://easy-plugin-demo.com/downloads/epd-premium-pack/)

* **Tweak**: Seperate the demo registration process from initial data santization
* **Tweak**: Corrected filter name within settings API
* **Tweak**: Added the secret field type to settings
* **Tweak**: Standardized the width of buttons within settings CSS

= 1.3.7 =

**Thursday, 5th November 2020**

* **New**: Added option to hide the WordPress admin bar during demos
* **Tweak**: Better styling for the licensing setting page
* **Tweak**: Incremented WordPress tested up to value
* **Tweak**: Added the network value to the readme file
* **Tweak**: Corrected plugin name within readme description

= 1.3.6 =

**Thursday, 17th September 2020**

* **New**: Added a site upload quota setting. The number entered here will limit the total size of files uploaded to demo sites
* **Bug**: PHP notice generated when activation is required
* **Tweak**: Added the `$blog_id` variable to the `epd_after_registration_redirect_url` filter

= 1.3.5 =

**Tuesday, 8th September 2020**

* **Bug**: Users may not be deleted when a site is deleted
* **Tweak**: Check for, and delete, custom tables when a site is deleted

= 1.3.4 =

**Monday, 24th August 2020**

* **New**: Added **Require Activation** option. Users are required to activate their new demo sites if enabled. [More information](https://easy-plugin-demo.com/articles/requiring-demo-site-activation/)
* **New**: Added the `{demo_site_activation_url}` template tag to support the new **Require Activation** option
* **New**: Added a custom message displayed when a site that has not been activated is accessed
* **New**: Added template file `register-pending.php` which is used if the **Require Activation** feature is enabled

* **Bug**: Confirmation email may be resent during site reset

* **Tweak**: Enabled filtering of front end registration confirmation notices

= 1.3.3 =

**Tuesday, 17th August 2020**

* **Tweak**: Added the `epd_settings_tabs_before_licenses` filter to allow for insertion of the integrations tab added by the Premium Pack
* **Tweak**: Added the `epd_registration` hook after successful registration
* **Tweak**: Added hooks to the output within the Right Now dashboard metabox

= 1.3.2 =

**Wednesday, 12th August 2020**

* **Bug**: Addresses issue whereby randomly the primary user was not reassigned permissions to the site being reset
* **Bug**: Reset link may be missing from menu and admin bar for demo site owner
* **Tweak**: Added the `epd_register_display_firstname` and `epd_register_display_lastname` filters to the registration form template. **Customised templates should be reviewed and updated**. See [this article](https://easy-plugin-demo.com/articles/hiding-registration-form-fields/) for details of these filters.

**Tuesday, 11th August 2020**

= 1.3 =

**Tuesday, 11th August 2020**

* **New**: Demo sites can now be reset to their original state by the site creator, or network admins. Admins can enable this option via the Settings -> Easy Plugin Demo screen
* **Bug**: Occasional odd behaviour when saving the plugin options
* **Tweak**: Correct URL pointing to Premium Pack
* **Tweak**: Added Premium Pack upsell to settings screen

= 1.2.1 =

**Thursday, 31st July 2020**

* **New**: Added **Delete Data on Uninstall** option. Plugin data will only be deleted on uninstall if this option is selected
* **Bug**: Missing function caused PHP error

= 1.2 =

**Thursday, 30th July 2020**

* **Bug**: Allowed themes setting was not being honoured
* **Bug**: Corrected site path for sub directory installations
* **Bug**: Missing brackets from function name
* **Bug**: Incorrect MySQL syntax caused PHP warning on site deletion
* **Bug**: Hide Search Engine Visibility option when Disable Visibility Changes option is enabled. Previously incorrectly hidden when Discourage Search Engines was enabled

* **Tweak**: Added registration page option within settings
* **Tweak**: If a site has expired, remove from sites list for user
* **Tweak**: Added the `epd_before_registration` hook
* **Tweak**: Added the `epd_plugins_to_activate` filter
* **Tweak**: Parse site object with the `epd_set_new_site_defaults` filter
* **Tweak**: Added the `epd_delete_expired_sites_exclusions` filter to allow for sites to be excluded from expiring
* **Tweak**: Added the `epd_hide_default_welcome_panel` and `epd_add_custom_welcome_panel` filters
* **Tweak**: Add some site meta for EPD created sites
* **Tweak**: Enabled filtering of various template tags

= 1.1.5 =

**Tuesday, 10th March 2020**

* **Bug**: Path was not honored for subdomain installs
* **Tweak**: Added `epd_register_shortcode_atts` filter to allow filtering of the `epd_register` shortcode atts
* **Tweak**: Added link to Premium Pack in plugin row

= 1.1.3 =

**Wednesday, 4th March 2020**

* **Bug**: Do not show delete link for primary site when admin is multisite admin is logged in

= 1.1.2 =

**Saturday, 19th October 2019**

* **Bug**: `{demo_site_url}` may not always return the full URL path 

= 1.1.1 =

**Saturday, 19th October 2019**

* **Bug**: `epd_plugins_to_activate()` function may not return an array as expected

= 1.1 =

**Friday, 15th March 2019**

* **New**: You can now duplicate posts and/or pages into new sites (maximum 3 per type)
* **Tweak**: Make select fields searchable on settings page
* **Tweak**: Correct URL to EPD website within welcome panel
* **Tweak**: Added license handler
* **Dev**: Added more hooks and filters

= 1.0.2 =

**Wednesday, 12th September 2018**

* **New**: Added count of created sites to network dashboard Right Now widget
* **Bug**: Missing value may cause fatal error during activation
* **Dev**: Use `epd_welcome_panel_text` hook to output welcome panel if it is hooked

= 1.0.1 =

**Monday, 20th August 2018**

* **New**: Added Google reCaptcha. Insert your site/secret keys within EDP Settings
* **New**: Added option to force Search Engine Visibility setting for new sites
* **New**: Added option to hide the Search Engine Visibility setting
* **New**: Define a personal welcome panel to be displayed on new sites
* **Tweak**: Split settings into tabs and sections
* **Tweak**: Moved plugin files into sub-directories
* **Tweak**: Added `epd_register_form_top` hook

= 1.0 =

**Thursday, 16th August 2018**

The initial release... Enjoy :)

== Upgrade Notice ==
