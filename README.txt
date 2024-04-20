=== Callback Info ===
Contributors: tommcfarlin
Donate link: https://buymeacoffee.com/tommcfarlin
Tags: developer, debug, tools
Requires at least: 6.5
Tested up to: 6.5.2
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

Renders contextual information about every function registered with all WordPress hooks.

== Description ==

This plugin renders contextual information about every function registered with all WordPress hooks.

Specifically, Callback Info looks at each hook  used in the current WordPress instance including the active themes _and_ plugins and renders:

- the name of the hook,
- the type of method (object, static, standard function, or anonymous),
- the file in which it's registered,
- the line on which the function starts,
- the line on which the function ends.

Callback Info was originally developed as a plugin to demonstrate certain concepts outlined in a series of blog posts. As the plugin became more advanced, I found it's functionality useful in my day-to-day work so decided to release it for others to use, too.

This should only be used in development environments.

Pull Requests, Feedback, Questions, Bug Reports, and Feature Requests are welcome to be posted on the [GitHub Issues](https://github.com/tommcfarlin/tm-callback-info/issues) page.

== Installation ==

= Install the Plugin From Within WordPress =

1. Visit the Plugins page from your WordPress dashboard and click "Add New" at the top of the page.
1. Search for "callback-info" using the search bar on the right side.
1. Click "Install Now" to install the plugin.
1. After it's installed, click "Activate" to activate the plugin on your site.
1. Once installed, add `?show-callback-info=true` to your query string to render contextual information.

= Install the Plugin Manually =

1. Download the plugin from WordPress.org or get the latest release from the [Github Releases page](https://github.com/tommcfarlin/tmn-callback-info/releases).
1. Unzip the downloaded archive.
1. Upload the entire `tm-callback-info` folder to your `/wp-content/plugins` directory.
1. Visit the Plugins page from your WordPress dashboard and look for the newly installed plugin.
1. Click "Activate" to activate the plugin on your site.
1. Once installed, add `?show-callback-info=true` to your query string to render contextual information.

== Usage Instructions ==

= Running the Plugin =

Once the plugin is activated, navigate to a page on the front-end of your site (that is, do not try to run this plugin within the administration area of your WordPress installation).

Assuming that your domain is `https://plugin.test`, append the query string `?show-callback-info=true` to your URL such that the address is `https://plugin.test?show-callback-info=true`.

This will render all of the hooks along with each function that fires during that hook. Further, you'll see which file in which the function belongs as well as the starting line number and ending line number for the callback.

= Include an Example Anonymous Function =

Since not all plugins nor themes use anonymous functions, it's not possible to see how this plugin will detect anonymous functions and provide feedback.

To see how this works, you can also append a second query string to your URL: `?use-sample-anonymous-function=true`. The full URL should look like this: `https://plugin.test?show-callback-info=true&use-sample-anonymous-function=true`

This will render a sample element at the bottom of the page (which you can view in the screenshot below) and you can see the anonymous function and its relevant information under the `wp_enqueue_scripts` hook.

**Note:** This plugin is not designed to run in a production environment.

== Screenshots ==

1. The `wp_enqueue_script` hook with its callbacks.

=== Frequently Asked Questions ===

None at this time.

=== Upgrade Notice ===

None at the time.

== Changelog

See the [CHANGELOG](https://github.com/tommcfarlin/tm-callback-info/blob/master/CHANGELOG.md).