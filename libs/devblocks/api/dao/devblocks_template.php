<?php
/***********************************************************************
 | Cerb(tm) developed by Webgroup Media, LLC.
 |-----------------------------------------------------------------------
 | All source code & content (c) Copyright 2002-2015, Webgroup Media LLC
 |   unless specifically noted otherwise.
 |
 | This source code is released under the Devblocks Public License.
 | The latest version of this license can be found here:
 | http://cerberusweb.com/license
 |
 | By using this software, you acknowledge having read this license
 | and agree to be bound thereby.
 | ______________________________________________________________________
 |	http://www.cerbweb.com	    http://www.webgroupmedia.com/
 ***********************************************************************/
/*
 * IMPORTANT LICENSING NOTE from your friends on the Cerb Development Team
 *
 * Sure, it would be so easy to just cheat and edit this file to use the
 * software without paying for it.  But we trust you anyway.  In fact, we're
 * writing this software for you!
 *
 * Quality software backed by a dedicated team takes money to develop.  We
 * don't want to be out of the office bagging groceries when you call up
 * needing a helping hand.  We'd rather spend our free time coding your
 * feature requests than mowing the neighbors' lawns for rent money.
 *
 * We've never believed in hiding our source code out of paranoia over not
 * getting paid.  We want you to have the full source code and be able to
 * make the tweaks your organization requires to get more done -- despite
 * having less of everything than you might need (time, people, money,
 * energy).  We shouldn't be your bottleneck.
 *
 * We've been building our expertise with this project since January 2002.  We
 * promise spending a couple bucks [Euro, Yuan, Rupees, Galactic Credits] to
 * let us take over your shared e-mail headache is a worthwhile investment.
 * It will give you a sense of control over your inbox that you probably
 * haven't had since spammers found you in a game of 'E-mail Battleship'.
 * Miss. Miss. You sunk my inbox!
 *
 * A legitimate license entitles you to support from the developers,
 * and the warm fuzzy feeling of feeding a couple of obsessed developers
 * who want to help you get more done.
 *
 \* - Jeff Standen, Darren Sugita, Dan Hildebrandt
 *	 Webgroup Media LLC - Developers of Cerb
 */

if(class_exists('C4_AbstractView')):
class View_DevblocksTemplate extends C4_AbstractView implements IAbstractView_QuickSearch {
	const DEFAULT_ID = 'templates';

	function __construct() {
		$this->id = self::DEFAULT_ID;
		$this->name = 'Templates';
		$this->renderLimit = 25;
		$this->renderSortBy = SearchFields_DevblocksTemplate::PATH;
		$this->renderSortAsc = true;

		$this->view_columns = array(
			SearchFields_DevblocksTemplate::PLUGIN_ID,
//			SearchFields_DevblocksTemplate::TAG,
			SearchFields_DevblocksTemplate::LAST_UPDATED,
		);
		
		$this->doResetCriteria();
	}

	function getData() {
		$objects = DAO_DevblocksTemplate::search(
			$this->view_columns,
			$this->getParams(),
			$this->renderLimit,
			$this->renderPage,
			$this->renderSortBy,
			$this->renderSortAsc,
			$this->renderTotal
		);
		
		$this->_lazyLoadCustomFieldsIntoObjects($objects, 'SearchFields_DevblocksTemplate');
		
		return $objects;
	}
	
	function getQuickSearchFields() {
		$search_fields = SearchFields_DevblocksTemplate::getFields();
		
		$fields = array(
			'_fulltext' => 
				array(
					'type' => DevblocksSearchCriteria::TYPE_TEXT,
					'options' => array('param_key' => SearchFields_DevblocksTemplate::PATH, 'match' => DevblocksSearchCriteria::OPTION_TEXT_PARTIAL),
				),
			'id' => 
				array(
					'type' => DevblocksSearchCriteria::TYPE_NUMBER,
					'options' => array('param_key' => SearchFields_DevblocksTemplate::ID),
				),
			'path' => 
				array(
					'type' => DevblocksSearchCriteria::TYPE_TEXT,
					'options' => array('param_key' => SearchFields_DevblocksTemplate::PATH, 'match' => DevblocksSearchCriteria::OPTION_TEXT_PARTIAL),
				),
			'plugin' => 
				array(
					'type' => DevblocksSearchCriteria::TYPE_TEXT,
					'options' => array('param_key' => SearchFields_DevblocksTemplate::PLUGIN_ID, 'match' => DevblocksSearchCriteria::OPTION_TEXT_PARTIAL),
				),
		);
		
		// Add is_sortable
		
		$fields = self::_setSortableQuickSearchFields($fields, $search_fields);
		
		// Sort by keys
		
		ksort($fields);
		
		return $fields;
	}
	
	function getParamsFromQuickSearchFields($fields) {
		$params = array();

		if(is_array($fields))
		foreach($fields as $k => $v) {
			
			switch($k) {
				// Texts (fuzzy)
				
				case '_fulltext':
				case 'path':
				case 'plugin':
				case 'tag':
					$field_keys = array(
						'_fulltext' => SearchFields_DevblocksTemplate::PATH,
						'path' => SearchFields_DevblocksTemplate::PATH,
						'plugin' => SearchFields_DevblocksTemplate::PLUGIN_ID,
						'tag' => SearchFields_DevblocksTemplate::TAG,
					);
					
					@$field_key = $field_keys[$k];
					
					if($field_key && false != ($param = DevblocksSearchCriteria::getTextParamFromQuery($field_key, $v, DevblocksSearchCriteria::OPTION_TEXT_PARTIAL)))
						$params[$field_key] = $param;
					break;
					
				// Dates
				
				case 'updated':
					$field_keys = array(
						'updated' => SearchFields_DevblocksTemplate::LAST_UPDATED,
					);
					
					@$field_key = $field_keys[$k];
					
					if($field_key && false != ($param = DevblocksSearchCriteria::getDateParamFromQuery($field_key, $v)))
						$params[$field_key] = $param;
					break;
					
				// Numbers
				
				case 'id':
					$field_keys = array(
						'id' => SearchFields_DevblocksTemplate::ID,
					);
					
					@$field_key = $field_keys[$k];
					
					if($field_key && false != ($param = DevblocksSearchCriteria::getNumberParamFromQuery($field_key, $v)))
						$params[$field_key] = $param;
					break;
			}
		}
		
		return $params;
	}
	
	function render() {
		$this->_sanitize();
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);
		$tpl->assign('view', $this);

//		$custom_fields = DAO_CustomField::getByContext(CerberusContexts::CONTEXT_WORKER);
//		$tpl->assign('custom_fields', $custom_fields);

		$tpl->display('devblocks:cerberusweb.core::configuration/section/portal/tabs/templates/view.tpl');
	}

	function renderCriteria($field) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);

		switch($field) {
			case SearchFields_DevblocksTemplate::PATH:
			case SearchFields_DevblocksTemplate::PLUGIN_ID:
			case SearchFields_DevblocksTemplate::TAG:
				$tpl->display('devblocks:cerberusweb.core::internal/views/criteria/__string.tpl');
				break;
			case SearchFields_DevblocksTemplate::LAST_UPDATED:
				$tpl->display('devblocks:cerberusweb.core::internal/views/criteria/__date.tpl');
				break;
			case SearchFields_DevblocksTemplate::ID:
				$tpl->display('devblocks:cerberusweb.core::internal/views/criteria/__number.tpl');
				break;
		}
	}

	function renderCriteriaParam($param) {
		$field = $param->field;
		$values = !is_array($param->value) ? array($param->value) : $param->value;

		switch($field) {
			default:
				parent::renderCriteriaParam($param);
				break;
		}
	}

	function getFields() {
		return SearchFields_DevblocksTemplate::getFields();
	}

	function doSetCriteria($field, $oper, $value) {
		$criteria = null;

		switch($field) {
			// String
			case SearchFields_DevblocksTemplate::PATH:
			case SearchFields_DevblocksTemplate::PLUGIN_ID:
			case SearchFields_DevblocksTemplate::TAG:
				$criteria = $this->_doSetCriteriaString($field, $oper, $value);
				break;
				
			// Date
			case SearchFields_DevblocksTemplate::LAST_UPDATED:
				$criteria = $this->_doSetCriteriaDate($field, $oper);
				break;
				
			// Number
			case SearchFields_DevblocksTemplate::ID:
				$criteria = new DevblocksSearchCriteria($field,$oper,$value);
				break;
		}

		if(!empty($criteria)) {
			$this->addParam($criteria);
			$this->renderPage = 0;
		}
	}

	function doBulkUpdate($filter, $do, $ids=array()) {
		@set_time_limit(600); // 10m
		
		$change_fields = array();
		$deleted = false;
		$custom_fields = array();

		if(empty($do))
			return;

		// Make sure we have checked items if we want a checked list
		if(0 == strcasecmp($filter,"checks") && empty($ids))
			return;

		if(is_array($do))
		foreach($do as $k => $v) {
			switch($k) {
				case 'deleted':
					$deleted = true;
					break;
				default:
					// Custom fields
					if(substr($k,0,3)=="cf_") {
						$custom_fields[substr($k,3)] = $v;
					}
					break;

			}
		}

		$pg = 0;

		if(empty($ids))
		do {
			list($objects,$null) = DAO_DevblocksTemplate::search(
				array(),
				$this->getParams(),
				100,
				$pg++,
				DAO_DevblocksTemplate::ID,
				true,
				false
			);
			 
			$ids = array_merge($ids, array_keys($objects));
			 
		} while(!empty($objects));

		$batch_total = count($ids);
		for($x=0;$x<=$batch_total;$x+=100) {
			$batch_ids = array_slice($ids,$x,100);
			
			if(!$deleted)
				DAO_DevblocksTemplate::update($batch_ids, $change_fields);
			else
				DAO_DevblocksTemplate::delete($batch_ids);
			
			// Custom Fields
//			self::_doBulkSetCustomFields(CerberusContexts::CONTEXT_WORKER, $custom_fields, $batch_ids);
			
			unset($batch_ids);
		}
		
		unset($ids);
	}
};
endif;
