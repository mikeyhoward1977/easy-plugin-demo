=== Easy Plugin Demo ===
Contributors: mikeyhoward1977
Tags: demo, plugin, theme, multisite, wpmu
Requires at least: 4.1
Tested up to: 5.4
Requires PHP: 5.4
Stable tag: 1.1.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automate the provisioning and management of a plugin or theme demo environment to showcase your WordPress products.

== Description ==

With Easy Plugin Demo (EPD), showcasing your plugin or themes has never been easier!

**EPD requires that you are running a WordPress multisite instance**

EPD enables you to easily provide an individual WordPress instance for users who would like to demo your plugin or theme without making changes or adding content that is visible to other users wishing to access the demo.

Simply adding the `[epd_register]` shortcode to your page is sufficient to get started once the plugin is installed and activated. Once a user enters their details, a new site is created and configured per the options you define within the settings page.

Once the defined lifetime period of the site has passed, it will be automatically deleted together with the associated demo user account.

You can add further customizations via the plugin settings page such as:

* Select the name for each new demo site created
* Choose which theme should be activated for the new site by default
* Specify the maximum number of sites a single user can register at any given time
* Choose where to direct the user upon successful registration
* Automatically delete a site after a given period
* Define a custom welcome panel message to be displayed on new sites
* Send a customized email message to the user once their registration completes
* Select which plugins should be activated for the new demo site
* Define which theme(s) can be used within new demo sites
* Duplicate posts from the primary blog to the newly created demo site (3 posts per post type unless the Premium extension is installed)

The Easy Plugin Demo [Premium Pack](https://easy-plugin-demo.com/downloads/premium-pack) extension unlocks a host of additional features including;

* Cloning of a master site for all new demo sites
* Duplication of posts from any post type
* Duplicate an unlimited number of posts
* Include taxonomies and terms as part of post duplication
* Include post attachments (media) as part of the post duplication
* Include post comments as part of the post duplication
* Define author for replicated posts
* Clone database tables, including custom ones
* Choose to automatically add users to new sites and their roles

We've also included a number of hooks and filters for further customizations by developers.

**Follow this plugin on [GitHub](https://github.com/mikeyhoward1977/easy-plugin-demo)**

**See this plugin in action**
We have a real world instance of this plugin running at [https://testdrive.kb-support.net/]('https://testdrive.kb-support.net/'). Alternatively check out the brief video below which shows the user registration process.

https://youtu.be/W7yVotr-FIE

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

1. Go to the the Synchronized Post Publisher WordPress plugin page at [https://wordpress.org/plugins/easy-plugin-demo/](https://wordpress.org/plugins/easy-plugin-demo/) and click the **Download** button to download the zip file
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

Yes. Visit [https://testdrive.kb-support.net/register/](https://testdrive.kb-support.net/register/) to experience the registration process for a user when creating a new demo site.

= Where can I find support? =

Support is provided via the [WordPress.org support forums](https://wordpress.org/support/plugin/easy-plugin-demo). Post your questions and we'll reply as soon as we can!

== Screenshots ==

1. EPD settings screen preview. Customize these options to suit your needs.

2. User registration form. Fresh WordPress installation with default Twenty Seventeen theme.

3. Completed registration with confirmation. Fresh WordPress installation with default Twenty Seventeen theme.

== Changelog ==

= 1.1.6 =

**DATE**

* **Bug**: Corrected site path for sub directory installations
* **Bug**: Missing brackets from function name
* **Bug**: Incorrect MySQL syntax caused PHP warning on site deletion
* **Bug**: Hide Search Engine Visibility option when Disable Visibility Changes option is enabled. Previously incorrectly hidden when Discourage Search Engines was enabled

* **Tweak**: Added the `epd_plugins_to_activate` filter
* **Tweak**: Parse site object with the `epd_set_new_site_defaults` filter
* **Tweak**: Added the `epd_delete_expired_sites_exclusions` filter to allow for sites to be excluded from expiring
* **Tweak**: Added the `epd_hide_default_welcome_panel` and `epd_add_custom_welcome_panel` filters

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
