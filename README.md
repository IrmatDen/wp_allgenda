wp_allgenda
===========

WordPress plugin to display an Allgenda widget

Installation
------------

Simply put all 4 _wp-allgenda*.php_ files in a subfolder of your WordPress plugin directory.
Example: _/wp-content/plugins/wp-allgenda_

**Note**: curl **must** be enabled to use this plugin

Setup
-----

Now, open your dashboard, enable _Allgenda widget_ in your Plugins list. Once activated, settings will be available by accessing _Settings_ > _Allgenda widget_.

You should now see the following options:

* Group ID: the group ID as you can found on Allgenda group settings' page 
* Number of events: quantity of upcoming events you want to appear in the widget list
* Widget caption: Widget title in your sidebar
* Timezone: the timezone you want to display events start & end time

Lastly, go to your widgets setup (_Appearance_ > _Widgets_) and place _Allgenda_ in the sidebar you want it in :)

Dev info
--------

wp-allgenda will register the following options:
- wp_allgenda_gid: Allgenda groupid
- wp_allgenda_noe: The number of upcoming events to retrieve from allgenda (default: 5)
- wp_allgenda_timezone: timezone to interpret Allgenda times (default: WordPress timezone)
- wp_allgenda_widget_caption: Caption for sidebar widget (default: Allgenda)
- wp_allgenda_offline_since: "hidden" option; used to tag Allgenda as offline and not
                             generate any new requests for the next 10 minutes (avoid
                             WordPress visitors having to wait curl timeout, 3s currently).

A transient is used to cache allgenda query results for 3 minutes (see get_json_allgenda_info).
The cached request is proper to each group ID and number of events.
NB: caching includes Allgenda error state!

i18n: displayed strings should be found in the wp_allgenda_trdom domain

Dependancy: curl [mandatory]