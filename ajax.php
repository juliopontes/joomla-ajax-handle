<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Plugin
 * @copyright   Copyright (C) 2013 Company, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('JPATH_BASE') or die;

jimport('joomla.filesystem.file');
jimport('joomla.application.helper');
jimport('legacy.application.helper');

/**
 * Joomla! Ajax Plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  System.ajax
 */
class PlgSystemAjax extends JPlugin
{
	public function onAfterRoute()
	{
		// Reference global application object
		$this->app = JFactory::getApplication();
		// Instantiate the JDispatcher class
		$this->dispatcher = JDispatcher::getInstance();
		// Jinput
		$this->input = $this->app->input;

		// check request mode
		$request_mode = $this->params->get('ajax_mode','request_with');
		switch ($request_mode)
		{
			case 'variable':
				$request_param = $this->params->get('custom_request_variable','ajax');
				$request_param_value = $this->input->getCmd($request_param);
				if (!empty($request_param_value))
					$this->doAjax();
				break;
			case 'option':
				// check option request
				if ($this->input->getCmd('option') == $this->params->get('custom_request_variable','com_ajax')) {
					$this->doAjax();
				}
				break;
			case 'request_with':
				$requestWith = strtolower($this->input->server->get('HTTP_X_REQUESTED_WITH'));
				if(!empty($requestWith) && $requestWith == 'xmlhttprequest') {
					$this->doAjax();
				}
				break;
		}
	}

	/**
	 * Request Ajax
	 */
	private function doAjax()
	{
		JFactory::getLanguage()->load('plg_system_ajax');
		$request_format = $this->input->getCmd('format','raw');
		
		$module = $this->input->getCmd('module');
		$plugin = $this->input->getCmd('plugin');
		$component = $this->input->getCmd('component');

		$ajax_checktoken = $this->params->get('ajax_checktoken');

		$enable_sufix = $this->params->get('ajax_use_prefix_token', false);
		$ajax_prefix = '';
		if ($enable_sufix == true) {
			$ajax_prefix = $this->params->get('ajax_prefix');
		}

		if ($ajax_checktoken) {
			if (!JSession::checkToken($this->params->get('ajax_jform_token_method','post'))) {
				$results = array(JText::_('JINVALID_TOKEN'));
			}	
		}

		$enable_acl = $this->params->get('verify_acl_request');
		if ($enable_acl) {
			$user = JFactory::getUser();

			$authorisedViewLevel = array_intersect($this->params->get('access'),$user->getAuthorisedViewLevels());
			$authorisedGroup = array_intersect($this->params->get('usergroup'),$user->getAuthorisedGroups());

			if (count($authorisedViewLevel) && count($authorisedGroup)) {
				$results = array(JText::_('PLG_AJAX_USER_DONT_HAVE_ACL_PERMISSION'));
			}
		}

		if (empty($results) || !isset($results)) {
			if ($module) {
				$client = $this->input->getCmd('client','site');
				$module = $this->input->getCmd('module');
				$helper = $this->input->getCmd('helper', 'helper');
				$class 	= $this->input->getCmd('class', 'mod' . ucfirst($module) . 'Helper');
				$method = $this->input->getCmd('method', 'getAjax');
				
				$client = JApplicationHelper::getClientInfo($client);
				
				$helper_path = $client->path . '/modules/mod_' . $module . '/' . $helper . '.php';
				if (JFile::exists($helper_path)) {
					require_once $helper_path;
					$results = $class::$method($params);
				} else {
					$results = array();
				}
			} else if ($plugin) {
				JPluginHelper::importPlugin('ajax');
				$plugin = ucfirst($plugin);
				$results = $this->dispatcher->trigger('onAjax' . $plugin);
			} else if ($component) {
				$client = $this->input->getCmd('client','site');
				$client = JApplicationHelper::getClientInfo($client);
				
				JControllerLegacy::getInstance($controller_base,array('base_path' => $client->path.'/components'))->execute();
				$this->app->getSystemMessages();
				
				
			} else {
				$results = array();
			}
		}

		// Return the results from this plugin group in the desired format
		switch ($request_format) {
			case 'jsonp':
				$cb = $this->input->get('callback','callbackResponse');
				$response = $cb.'('.$ajax_prefix.json_encode($results).$ajax_prefix.')';
				break;
			case 'json':
				$response = $ajax_prefix.json_encode($results).$ajax_prefix;
			break;
			case 'raw':
			default:
				$response = $ajax_prefix.implode($results).$ajax_prefix;
			break;
		}
		echo $response;
		$this->app->close();
	}
}