=== Easy Plugin Demo ===
Contributors: mikeyhoward1977
Tags: demo, plugin, theme, multisite, wpmu
Requires at least: 4.1
Tested up to: 4.9.8
Requires PHP: 5.4
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automate the provisioning and management of a plugin or theme demo environment to showcase your WordPress products.

== Description ==

With Easy Plugin Demo (EPD), showcasing your plugin or themes has never been easier!

EPD enables you to easily provide an individual WordPress instance for users who would like to demo your plugin or theme without making changes or adding content that is visible to other users wishing to access the demo.

Simply adding the `[epd_register]` shortcode to your page is sufficient to get started once the plugin is installed and activated. Once a user enters their details, a new site is created and configured per the options you define within the settings page.

Once the defined lifetime period of the site has passed, it will be automatically deleted together with the associated demo user account.

You can add further customizations via the plugin settings page such as:

* Select the name for each new demo site created
* Choose which theme should be activated for the new site by default
* Specify the maximum number of sites a single user can register at any given time
* Choose where to direct the user upon successful registration
* Delete a site after a given period
* Send a customized email message to the user once their registration completes
* Select which plugins should be activated for the new demo site

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

**Monday, 20th August 2018**

* **New**: Added Google reCaptcha. Insert your site/secret keys within EDP Settings
* **New**: Added option to force Search Engine Visibility setting for new sites
* **New**: Added option to hide the Search Engine Visibility setting
* **New**: Define a personal welcome panel to be displayed on new sites
* **Tweak**: Split settings into tabs and sections
* **Tweak**: Moved plugin files into sub-directories
* **Tweak**: Added `epd_register_form_top` hook

**Thursday, 16th August 2018**

The initial release... Enjoy :)

== Upgrade Notice ==
