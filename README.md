joomla-ajax-handle
==================

A slim, plugin to act as an entry point for Ajax functionality in Joomla. It is designed to execute plugins following the onAjax[foo] naming convention, where [foo] is the name of the Ajax plugin group to execute. For example, the [Ajax Session Plugin](https://github.com/betweenbrain/Ajax-Session-Plugin) is executed by the component calling  onAjaxSession as the plugin extends `JPlugin` with `plgAjaxSession`.

AJAX Handle
==============
You have 3 ways to handle ajax using this plugin

* Custom variable e.g.: ajax=1 (default value)
* Fake component call. e.g.: option=com_ajax (default value)
* Checking Request Header e.g.: X-Requested-With: XMLHttpRequest (this probably will catch all ajax requests, but remember that requester can change value from X-Requested-With to other value and will not be catch by plugin)

Contributing
====================
Your contributions are more than welcome!