joomla-ajax-handle
==================

A slim, plugin to act as an entry point for Ajax functionality in Joomla. It is designed to execute plugins following the onAjax[foo] naming convention, where [foo] is the name of the Ajax plugin group to execute. For example, the [Ajax Session Plugin](https://github.com/betweenbrain/Ajax-Session-Plugin) is executed by the component calling  onAjaxSession as the plugin extends `JPlugin` with `plgAjaxSession`.

URL Convention
==============
Choose how you want to handle ajax requests

* Custom variable handle
  * Ajax events are triggered by submitting a request to `index.php?ajax=1`
  * Variable 'ajax' is customisable
* Fake component call
  * Ajax events are triggered by submitting a request to `index.php?option=com_ajax`
  * Component 'com_ajax' is customisable 
* Checking Request Header
  *  X-Requested-With: XMLHttpRequest (this probably will catch all ajax requests, but remember that requester can change value from X-Requested-With to other value and will not be catch by plugin)

Aditional Options
==============
* JForm token check
  * Configure if will be check by POST or GET
  * Add <?php echo JHtml::_('form.token'); ?> to your ajax call
* Joomla ACL check
  * Configure access level
  * Configure usergroup
* Beginning and end tokens
  * Configure token for use as Beginning and end tokens
  * E.g.: ###{response here}###

Changelog
==============
3.1.1
* ACL access level and usergroup
* JForm token check
* Ajax prefix/sufix response
* Component call
* Module hellper call
* Ajax plugin support

Contributing
====================
Your contributions are more than welcome!
