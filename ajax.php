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
				if ($this->input->getCmd('component') == $this->params->get('custom_request_variable','com_ajax')) {
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
		
		if (JFactory::getSession()->get('ajax', false)) {
			$this->doAjax();
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
		$component = $this->input->getCmd('option');

		$ajax_checktoken = $this->params->get('ajax_checktoken');

		$enable_sufix = $this->params->get('ajax_use_prefix_token', false);
		$ajax_prefix = '';
		if ($enable_sufix == true) {
			$ajax_prefix = $this->params->get('ajax_prefix');
		}
		
		if (JFactory::getSession()->get('ajax', false)) {
			$messages = $this->app->getMessageQueue();
			// Build the sorted message list
			if (is_array($messages) && !empty($messages))
			{
				foreach ($messages as $msg)
				{
					if (isset($msg['type']) && isset($msg['message']))
					{
						$results[] = $msg['message'];
					}
				}
			} else {
				$results = array();
			}
			
			JFactory::getSession()->set('ajax', false);
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
				JFactory::getSession()->set('ajax', true);
				$this->app->dispatch();
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
