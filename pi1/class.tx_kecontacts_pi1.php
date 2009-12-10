<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 www.kennziffer.com GmbH <info@kennziffer.com>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'Kontaktverwaltung' for the 'ke_contacts' extension.
 *
 * @author	www.kennziffer.com GmbH <info@kennziffer.com>
 * @package	TYPO3
 * @subpackage	tx_kecontacts
 */
class tx_kecontacts_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_kecontacts_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_kecontacts_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'ke_contacts';	// The extension key.
	var $pi_checkCHash = true;
	var $templateCode = '';
	var $templateFile = '';
	var $flexConf = array();
	var $tmpl = '';
	var $numberOfPages = 0;
	var $formError = array();
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content, $conf) {
		//configuration settings and general settings
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->createFlexFormArray();
		
		//apply default css styles
		$this->addCssToPage();
		//$GLOBALS['TSFE']->pSetup['bodyTagAdd'] = 'onload="javascript: changeType('.$this->piVars['tx_kecontacts_type'].'); return false;"';
		
		//switch to required plugin mode
		switch(t3lib_div::removeXSS($this->piVars['mode'])) {
			case 'edit':
				//edit contact
				$GLOBALS['TSFE']->setJS($this->prefixId.'_js',$this->addJavascript());
				if($this->piVars['submit'] && $this->validateFields()) {
					$content = $this->updateRecord();
				} else
					$content = $this->renderCreateView(true);
			break;
			case 'create':
				//create contact
				$GLOBALS['TSFE']->setJS($this->prefixId.'_js',$this->addJavascript());
				if($this->piVars['submit'] && $this->validateFields()) {
					$content = $this->saveRecord();
				} else
					$content = $this->renderCreateView();
			break;
			case 'single':
				//view single contact
				if(isset($this->piVars['commentButton']) && strlen($this->piVars['comment'])) {
					$this->addComment();
				}
				$content = $this->renderSingleView();
			break;
			case 'delete':
				//delete contact
				if(isset($this->piVars['deleteButton'])) {
					$content = $this->deleteContact();
				} else {
					$content = $this->renderDeleteView();
				}
			break;
			default:
				//default view: list of contacts
				$content = $this->renderListView();
			break;
		}
		
		//render content
		return $this->pi_wrapInBaseClass($content);
	}
	
	function renderDeleteView() {
		$content = '';
		$contactId = intval($this->piVars['id']);
		
		//try to get user details needed for deletion
		$whereClause .= 'tt_address.pid = '.$this->flexConf['storage_pid'];
		$whereClause .= ' AND tt_address.uid ='.$contactId;
		$whereClause .= ' AND tx_kecontacts_type != 0';
		$whereClause .= $this->cObj->enableFields('tt_address');
		
		$resUserDetails = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tt_address',$whereClause);
		
		//user does not exist, exit with error
		if(!$GLOBALS['TYPO3_DB']->sql_num_rows($resUserDetails)) {
			$markerArray = array('ERRORTEXT' => $this->pi_getLL('single_not_found'));
			$content = $this->substituteMarkers('###GENERAL_ERROR###',$markerArray);
			
			return $content;
		}
		
		$userDetails = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resUserDetails);
		
		//fill markers
		$markerArray = array(
			'LABEL_DELETE' => $this->pi_getLL('delete_label_delete','L&ouml;schen von '),
			'FIRST_NAME' => $userDetails['first_name'],
			'LAST_NAME' => $userDetails['last_name'],
			'HINT' => $this->pi_getLL('delete_hint'),
			'DELETE_BUTTON_SUBMITVALUE' => $this->pi_getLL('delete_button_submitvalue'),
		);
		
		//render deletion form
		$subpart = 'DELETEVIEW';
		$content = $this->substituteMarkers($subpart,$markerArray);
		
		return $content;
	}
	
	function deleteContact() {
		$content = '';
		$contactId = intval($this->piVars['id']);
		
		//try to get user details needed for deletion
		$whereClause .= 'tt_address.pid = '.$this->flexConf['storage_pid'];
		$whereClause .= ' AND tt_address.uid ='.$contactId;
		$whereClause .= ' AND tx_kecontacts_type != 0';
		$whereClause .= $this->cObj->enableFields('tt_address');
		
		$resUserDetails = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tt_address',$whereClause);
		
		//user does not exist, exit with error
		if(!$GLOBALS['TYPO3_DB']->sql_num_rows($resUserDetails)) {
			$markerArray = array('ERRORTEXT' => $this->pi_getLL('single_not_found'));
			$content = $this->substituteMarkers('###GENERAL_ERROR###',$markerArray);
			
			return $content;
		}
		
		$userDetails = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resUserDetails);
		$contactType = $userDetails['tx_kecontacts_type'];
		
		switch($contactType) {
			case 1:
				//person: disable, comments stay
				$resDeletePerson = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_address',$whereClause,array('deleted' => 1));
				if(!$GLOBALS['TYPO3_DB']->sql_affected_rows()) {
					$markerArray = array('ERRORTEXT' => $this->pi_getLL('error_deleting_person'));
					$content = $this->substituteMarkers('###GENERAL_ERROR###',$markerArray);
			
					return $content;
				}
				
				$whereClause = 'uid_foreign ='.$contactId;
				$resDeletePersonsRelations = $GLOBALS['TYPO3_DB']->exec_DELETEquery('tt_address_tx_kecontacts_members_mm',$whereClause);
			break;
			case 2:
				//organization: disable, do not remove related persons, comments stay
				$resDeleteOrg = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_address',$whereClause,array('deleted' => 1));
				
				if(!$GLOBALS['TYPO3_DB']->sql_affected_rows()) {
					$markerArray = array('ERRORTEXT' => $this->pi_getLL('error_deleting_org'));
					$content = $this->substituteMarkers('###GENERAL_ERROR###',$markerArray);
			
					return $content;
				}
				
				//check if there are existing relations else skip this step
				$resCheckRelationCount = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid_local','tt_address_tx_kecontacts_members_mm','uid_local='.$contactId);
				
				if($GLOBALS['TYPO3_DB']->sql_num_rows($resCheckRelationCount)) {
					$whereClause = 'uid_local ='.$contactId;
					$resDeletePersonsRelations = $GLOBALS['TYPO3_DB']->exec_DELETEquery('tt_address_tx_kecontacts_members_mm',$whereClause);
					
					if(!$GLOBALS['TYPO3_DB']->sql_affected_rows()) {
						$markerArray = array('ERRORTEXT' => $this->pi_getLL('error_deleting_org_rel'));
						$content = $this->substituteMarkers('###GENERAL_ERROR###',$markerArray);
			
						return $content;
					}
				}
			break;
		}
		
		//success
		$markerArray = array('ERRORTEXT' => $this->pi_getLL('success_delete'));
		$content = $this->substituteMarkers('###GENERAL_ERROR###',$markerArray);
		
		return $content;
	}
	
	function validateFields() {
		//prepare validation  check
		$this->formError = array();
		$fieldConf = t3lib_div::removeDotsFromTs($this->conf['formFields.']);
		$formFields = $this->piVars;
		$mode = intval($this->piVars['tx_kecontacts_type']);
		
		//filter user input
		foreach($formFields as $fieldName => $fieldValue) {
			$formFields[$fieldName] = t3lib_div::removeXSS($fieldValue);
		}
		
		//if we check an organisation, we have to unset some validations for a single person
		if($mode == 2) {
			$unsetByType = array('tx_kecontacts_birthday','tx_kecontacts_organization','gender','title','first_name','tx_kecontacts_function','mobile');
			foreach($unsetByType as $ffield)
				unset($fieldConf[$ffield]);
		}
		
		//validate
		foreach($fieldConf as $fieldName => $fieldValidation) {
			$fieldValidationMethod = $fieldValidation['validate'];
			$fieldRequired = $fieldValidation['required'];
			
			//check by using validation method
			if(($fieldValidationMethod != '' && ($fieldRequired || strlen($formFields[$fieldName]))) || (!$fieldRequired && strlen($formFields[$fieldName]))) {
				switch($fieldValidationMethod) {
					case 'int':
						//valid integer required
						if(intval($formFields[$fieldName]) == 0) $this->formError[$fieldName] = 1;
					break;
					case 'email':
						//valid email required
						if(!t3lib_div::validEmail($formFields[$fieldName])) $this->formError[$fieldName] = 1;
					break;
					case 'date':
						//valid date required
						$valDate = @explode('.',$formFields[$fieldName]);
						if(strlen($formFields[$fieldName]) != 10 || !is_array($valDate) || count($valDate) != 3 || !checkdate(intval($valDate[1]),intval($valDate[0]),intval($valDate[2])))
							$this->formError[$fieldName] = 1;
					break;
					case 'gender':
						//gender must be set
						if($formFields[$fieldName] != 'f' && $formFields[$fieldName] != 'm') $this->formError[$fieldName] = 1;
					break;
					case 'nocountry':
						//country has to be selected
						if($formFields[$fieldName] == '') $this->formError[$fieldName] = 1;
					break;
					default:
					break;
				}
			}
			
			//check for required fields
			if($fieldRequired && !strlen($formFields[$fieldName]))
				$this->formError[$fieldName] = 1;
		}
		
		return (count($this->formError)?false:true);
	}
	
	function saveRecord() {
		//prepare values for update
		$content = '';
		$formFields = $this->piVars;
		$orgId = intval($formFields['tx_kecontacts_organization']);
		$addressId = intval($this->piVars['id']);
		$formFields['tstamp'] = time();
		$formFields['pid'] = $this->flexConf['storage_pid'];
		
		//filter user input
		foreach($formFields as $fieldName => $fieldValue) {
			$formFields[$fieldName] = t3lib_div::removeXSS($fieldValue);
		}
		
		//unset fields which are not needed for update query
		$unsetFields = array('id','submit','mode','tx_kecontacts_organization','function');
		foreach($unsetFields as $unsetField)
			unset($formFields[$unsetField]);
		
		//transform birthday to timestamp
		$bday = explode('.',$this->piVars['birthday']);
		$formFields['birthday'] = (strlen($formFields['birthday']))?mktime(0,0,0,$bday[1],$bday[0],$bday[2]):0;
		
		//check for duplicate contacts
		$duplicateWhere = ' first_name = "'.$formFields['first_name'].'" AND last_name = "'.$formFields['last_name'].'" AND email = "'.$formFields['email'].'" AND pid='.$this->flexConf['storage_pid'].' '.$this->cObj->enableFields('tt_address');
		$resDuplicateCheck = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tt_address',$duplicateWhere);
		
		if($GLOBALS['TYPO3_DB']->sql_num_rows($resDuplicateCheck)) {
			$markerArray = array('ERRORTEXT' => $this->pi_getLL('error_already_exists'));
			$content = $this->substituteMarkers('###GENERAL_ERROR###',$markerArray);
			
			return $content;
		}
		
		//insert new contact
		$GLOBALS['TYPO3_DB']->debugOutput = true;
		$resUpdateData = $GLOBALS['TYPO3_DB']->exec_INSERTquery('tt_address',$formFields);
		$contactId = $GLOBALS['TYPO3_DB']->sql_insert_id();
		
		if(!$contactId) {
			$markerArray = array('ERRORTEXT' => $this->pi_getLL('error_create'));
			$content = $this->substituteMarkers('###GENERAL_ERROR###',$markerArray);
			
			return $content;
		}
		
		//connect contact to organization
		if($orgId != 0) {
			$mmFields = array(
							'uid_local' => $orgId,
							'uid_foreign' => $contactId,
							'sorting' => 1,
						);
			
			$resUpdateData = $GLOBALS['TYPO3_DB']->exec_INSERTquery('tt_address_tx_kecontacts_members_mm',$mmFields);
			//if(!$GLOBALS['TYPO3_DB']->sql_insert_id()) return "ERROR_CREATE_NEW_MM";
		}
		
		//success
		$markerArray = array('ERRORTEXT' => $this->pi_getLL('success_create'));
		$content = $this->substituteMarkers('###GENERAL_ERROR###',$markerArray);
		
		return $content;
	}
	
	function updateRecord() {
		//prepare values for update
		$content = '';
		$formFields = $this->piVars;
		$orgId = intval($formFields['tx_kecontacts_organization']);
		$addressId = intval($this->piVars['id']);
		$formFields['tstamp'] = time();
		
		//filter user input
		foreach($formFields as $fieldName => $fieldValue) {
			$formFields[$fieldName] = t3lib_div::removeXSS($fieldValue);
		}
		
		//check uid of old organization to update relations later on
		$resOldOrgId = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid_local','tt_address_tx_kecontacts_members_mm','uid_foreign='.$addressId);
		if($GLOBALS['TYPO3_DB']->sql_num_rows($resOldOrgId)) {
			$oldOrgId = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resOldOrgId);
			$oldOrgId = ($orgId == $oldOrgId['uid_local'])?-2:$oldOrgId['uid_local'];
		} else {
			$oldOrgId = -1;
		}
		
		//unset fields which are not needed for update query
		$unsetFields = array('id','submit','mode','tx_kecontacts_organization','function');
		foreach($unsetFields as $unsetField)
			unset($formFields[$unsetField]);
		
		//transform birthday to timestamp
		$bday = explode('.',$this->piVars['birthday']);
		$formFields['birthday'] = (strlen($formFields['birthday']))?mktime(0,0,0,$bday[1],$bday[0],$bday[2]):0;

		
		//update and reset timestamp
		$GLOBALS['TYPO3_DB']->debugOutput = true;
		$resUpdateData = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_address','uid='.$addressId,$formFields);
		
		if(!$GLOBALS['TYPO3_DB']->sql_affected_rows()) {
			$markerArray = array('ERRORTEXT' => $this->pi_getLL('error_update'));
			$content = $this->substituteMarkers('###GENERAL_ERROR###',$markerArray);
			
			return $content;
		}
		
		t3lib_div::debug($oldOrgId.' '.$orgId);
		
		//update relation to organization - if changed
		if($oldOrgId != -2 && $oldOrgId != -1) {
			if($orgId == 0) {
				//special case: organization of contact changes to "no organization" - delete relation
				$resDeleteData = $GLOBALS['TYPO3_DB']->exec_DELETEquery('tt_address_tx_kecontacts_members_mm','uid_foreign='.$addressId.' AND uid_local ='.$oldOrgId);
			} else {
				//set new organization for contact - if changed
				$updateOrg = array('uid_local' => $orgId);
				$GLOBALS['TYPO3_DB']->debugOutput = true;
				$resUpdateData = $GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_address_tx_kecontacts_members_mm','uid_foreign='.$addressId.' AND uid_local ='.$oldOrgId,$updateOrg);
			}
			
			//mysql error occurred
			if(!$GLOBALS['TYPO3_DB']->sql_affected_rows()) {
				$markerArray = array('ERRORTEXT' => $this->pi_getLL('error_update'));
				$content = $this->substituteMarkers('###GENERAL_ERROR###',$markerArray);
			
				return $content;
			}
		} elseif($oldOrgId == -1) {
			if($orgId == 0) t3lib_div::debug('NIX');
			//person was not connected to any organization until now, so create a new relation
			$insertOrgRel = array('uid_foreign' => $addressId, 'uid_local' => $orgId);
			$resInsertData = $GLOBALS['TYPO3_DB']->exec_INSERTquery('tt_address_tx_kecontacts_members_mm',$insertOrgRel);
			if(!$GLOBALS['TYPO3_DB']->sql_affected_rows()) {
				$markerArray = array('ERRORTEXT' => $this->pi_getLL('error_update'));
				$content = $this->substituteMarkers('###GENERAL_ERROR###',$markerArray);
			
				return $content;
			}
		}
		
		//return
		$markerArray = array('ERRORTEXT' => $this->pi_getLL('success_update'));
		$content = $this->substituteMarkers('###GENERAL_ERROR###',$markerArray);
		
		return $content;
	}
	
	function renderCreateView($edit = false) {
		$content = '';
		$formFieldsConfig = t3lib_div::removeDotsFromTS($this->conf['formFields.']);
		
		//get template
		$subpart = '###MAIN_CREATE###';
		$this->loadTemplate();
		$content = $this->cObj->getSubpart($this->tmpl, $subpart);
		
		//prefill formfields with db data if mode = "edit"
		if($edit) {
			$whereClause .= '1=1 '.$this->cObj->enableFields('tt_address');
			$whereClause .= ' AND tt_address.pid = '.$this->flexConf['storage_pid'];
			$whereClause .= ' AND tt_address.uid ='.intval($this->piVars['id']);
	
			$resAddressData = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tt_address',$whereClause);
			$editData = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resAddressData);
			
			$resOrgId = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid_local','tt_address_tx_kecontacts_members_mm','uid_foreign='.intval($this->piVars['id']));
			$orgId = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resOrgId);
		} else {
			$editData = $this->piVars;
			$orgId['uid_local'] = $this->piVars['tx_kecontacts_organization'];
		}
	
		foreach($formFieldsConfig as $formFieldName => $formFieldConfig) {
			$type = $formFieldConfig['type'];
			$required = $formFieldConfig['required'];
			
			//generate form fields by type
			switch($type) {
				case 'input':
					$markerArray_field = array(
						'NAME' => $this->prefixId.'['.$formFieldName.']',
						'VALUE' => $editData[$formFieldName],
					);
					
					$content_field = $this->substituteMarkers('###SUB_INPUT_TEXT###',$markerArray_field);
				break;
				case 'contacttype':					
					for($i = 0;$i <= 2;$i++) {
						$markerArrayOptions = array(
								'VALUE' => $i,
								'SELECTED' => ($editData['tx_kecontacts_type'] == $i)?'selected':'',
								'DISABLED' => ($edit && $editData['tx_kecontacts_type'] != $i)?'disabled':'',
								'LABEL' => $this->pi_getLL('edit_contacts_type_'.$i),
							);	
					
						$content_options .= $this->substituteMarkers('###SUB_SELECT_OPTION###',$markerArrayOptions);
					}
					
					$content_select = $this->cObj->getSubpart($this->tmpl,'###SUB_SELECT_JS###');
					$content_select = $this->cObj->substituteMarkerArray($content_select,array('###NAME###' => $this->prefixId.'['.$formFieldName.']','###DISABLED###' => ($edit)?'disabled':''));
					$content_field = $this->cObj->substituteSubpart($content_select,'###SUB_SELECT_OPTION###',$content_options);
				break;
				case 'organization':
					$whereClause = ' 1=1 '.$this->cObj->enableFields('tt_address');
					$whereClause .= ' AND tt_address.pid IN ('.$this->flexConf['storage_pid'].')';
					$whereClause .= ' AND tx_kecontacts_type = 2';
					
					$resOrg = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,last_name','tt_address',$whereClause,'','last_name ASC','');
					
					if($GLOBALS['TYPO3_DB']->sql_num_rows($resOrg)) {
						$content_options = '';
						
						//empty option
						$markerArrayOptions = array(
								'VALUE' => 0,
								'SELECTED' => ($orgId['uid_local'] == $company['uid'])?'selected':'',
								'DISABLED' => '',
								'LABEL' => $this->pi_getLL(edit_please_select),
							);
							
						$content_options .= $this->substituteMarkers('###SUB_SELECT_OPTION###',$markerArrayOptions);						
						
						while($company = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resOrg)) {
							$markerArrayOptions = array(
								'VALUE' => $company['uid'],
								'SELECTED' => ($orgId['uid_local'] == $company['uid'])?'selected':'',
								'DISABLED' => '',
								'LABEL' => $company['last_name'],
							);
							
							$content_options .= $this->substituteMarkers('###SUB_SELECT_OPTION###',$markerArrayOptions);
						}
					} else {
						$markerArrayOptions = array(
								'VALUE' => 0,
								'SELECTED' => 'selected',
								'DISABLED' => 'disabled',
								'LABEL' => $this->pi_getLL('edit_no_company'),
						);
							
						$content_options = $this->substituteMarkers('###SUB_SELECT_OPTION###',$markerArrayOptions);
					}
					
					$content_select = $this->cObj->getSubpart($this->tmpl,'###SUB_SELECT###');
					$content_select = $this->cObj->substituteMarkerArray($content_select,array('###NAME###' => $this->prefixId.'['.$formFieldName.']'));
					$content_field = $this->cObj->substituteSubpart($content_select,'###SUB_SELECT_OPTION###',$content_options);
				break;
				case 'country':
					if(!t3lib_extMgm::isLoaded('static_info_tables_de')) {
						$content_field = $this->pi_getLL('edit_not_loaded');
					} else {
						$content_options = '';
						$resCountry = $GLOBALS['TYPO3_DB']->exec_SELECTquery('cn_short_de','static_countries','1=1 '.$this->cObj->enableFields('static_countries'),'','cn_short_de ASC');
						
						//empty option
						$markerArrayOptions = array(
								'VALUE' => 0,
								'SELECTED' => ($orgId['uid_local'] == $company['uid'])?'selected':'',
								'DISABLED' => '',
								'LABEL' => $this->pi_getLL(edit_please_select),
							);
							
						$content_options .= $this->substituteMarkers('###SUB_SELECT_OPTION###',$markerArrayOptions);						
												
						while($country = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resCountry)) {
							$markerArrayOptions = array(
								'VALUE' => $country['cn_short_de'],
								'SELECTED' => ($editData[$formFieldName] == $country['cn_short_de'])?'selected':'',
								'DISABLED' => '',
								'LABEL' => $country['cn_short_de'],
							);
							
							$content_options .= $this->substituteMarkers('###SUB_SELECT_OPTION###',$markerArrayOptions);
						}
						
						$content_select = $this->cObj->getSubpart($this->tmpl,'###SUB_SELECT###');
						$content_select = $this->cObj->substituteMarkerArray($content_select,array('###NAME###' => $this->prefixId.'['.$formFieldName.']'));
						$content_field = $this->cObj->substituteSubpart($content_select,'###SUB_SELECT_OPTION###',$content_options);
					}
				break;
				case 'gender':
					$markerArrayOptions = array(
						'NAME' => $this->prefixId.'['.$formFieldName.']',
						'VALUE' => 'm',
						'LABEL' => $this->pi_getLL('gender_male'),
						'CHECKED' => ($editData[$formFieldName] == 'm')?'checked':'',
					);
					
					$content_field = $this->substituteMarkers('###SUB_RADIO_ROW###',$markerArrayOptions);
					
					$markerArrayOptions = array(
						'NAME' => $this->prefixId.'['.$formFieldName.']',
						'VALUE' => 'f',
						'LABEL' => $this->pi_getLL('gender_female'),
						'CHECKED' => ($editData[$formFieldName] == 'f')?'checked':'',
					);
					
					$content_field .= $this->substituteMarkers('###SUB_RADIO_ROW###',$markerArrayOptions);
				break;
				case 'birthday':
					$markerArray_field = array(
						'NAME' => $this->prefixId.'['.$formFieldName.']',
						'VALUE' => ($edit && $editData[$formFieldName] != 0)?date('d.m.Y',$editData[$formFieldName]):'',
					);
					
					$content_field = $this->substituteMarkers('###SUB_INPUT_TEXT###',$markerArray_field);
				break;
				default:
					$content_field = $this->substituteMarkers('###SUB_INPUT_TEXT###',array('1' => '1'));
				break;
			}
						
			$markerArray['LABEL_'.strtoupper($formFieldName)] = $this->pi_getLL('edit_label_'.$formFieldName,'');
			$markerArray['FORMFIELD_'.strtoupper($formFieldName)] = $content_field;
			$markerArray['ERROR_'.strtoupper($formFieldName)] = ($this->formError[$formFieldName] == 1)?$this->pi_getLL('error_'.$formFieldName):'';
		}
		
		//set label for submit button
		$markerArray['SUBMITACTION'] = ($edit)?$this->pi_getLL('edit_submitaction_edit'):$this->pi_getLL('edit_submitaction_new');
		
		//apply type of contact to hide fields automatically after form was rendered
		$markerArray['JS'] = 'javascript: changeType('.$editData['tx_kecontacts_type'].');';
		
		//create form
		$content = $this->cObj->substituteMarkerArray($content, $markerArray,$wrap='###|###',$uppercase=1);
		
		return $content;
	}
	
	function getComments($type = 1) {
		$content = '';
		$userId = intval($this->piVars['id']);
		
		$whereClause .= ' '.$this->cObj->enableFields('tt_address').' '.$this->cObj->enableFields('tx_kecontacts_comments');
		$whereClause .= ' AND tt_address.pid = '.$this->flexConf['storage_pid'];
		
		//query comment belongs to (company) or not
		if($type == 2) {
			$whereClause .= ' AND (tt_address_tx_kecontacts_comments_mm.uid_local = '.$userId.' OR tx_kecontacts_comments.organization = '.$userId.')';
		} else {
			$whereClause .= ' AND tt_address_tx_kecontacts_comments_mm.uid_local = '.$userId;
		}
		
		$resComments = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query('tt_address.name,tx_kecontacts_comments.*','tt_address','tt_address_tx_kecontacts_comments_mm','tx_kecontacts_comments',$whereClause,'','tx_kecontacts_comments.crdate DESC','');
		
		if($GLOBALS['TYPO3_DB']->sql_num_rows($resComments)) {
			while($comment = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resComments)) {				
				$markerArray = array(
					'DATE' => date('d.m.Y',$comment['crdate']),
					'TIME' => date('H:i:s',$comment['crdate']),
					'COMMENTEDBY' => $comment['fe_user'],
					'COMMENTTEXT' => nl2br($comment['comment']),
					'BELONGSTO' => ($type == 2 && $comment['organization'] != 0)?$this->pi_getLL('single_label_belongsto2').' '.$comment['name']:'',
				);
				
				$content .= $this->substituteMarkers('###COMMENT###',$markerArray);
			}
		} else {
			$content = 'Keine Kommentare';
		}
		
		return $content;
	}
	
	function addComment() {
		//set and cleanup neccessary values
		$comment = t3lib_div::removeXSS($this->piVars['comment']);
		$userId = intval($this->piVars['id']);
		$feUserId = $GLOBALS['TSFE']->fe_user->user['username'];
		
		//this part is because of selecting all entries for a company - give persons company id directly, companies get id "0"
		$companyId = 0;
		$resCompany = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid_local','tt_address_tx_kecontacts_members_mm','uid_foreign = '.$userId);
		
		if($GLOBALS['TYPO3_DB']->sql_num_rows($resCompany)) {
			$companyId = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resCompany);
		}
		
		//find pid of current userId and store related comment there
		$resUserPid = $GLOBALS['TYPO3_DB']->exec_SELECTquery('pid','tt_address','uid = '.$userId.' '.$this->cObj->enableFields('tt_address'));
		if($GLOBALS['TYPO3_DB']->sql_num_rows($resUserPid))
			$userPid = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resUserPid);
		else
			$userPid['pid'] = 0;
		
		//prepare fields for comment table
		$commentFieldValues = array(
			'comment' => $comment,
			'fe_user' => $feUserId,
			'crdate' => time(),
			'organization' => ($companyId['uid_local'])?$companyId['uid_local']:0,
			'pid' => $userPid['pid'],
		);
		
		//insert into comment table
		$resComment = $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_kecontacts_comments',$commentFieldValues);
		$commentId = $GLOBALS['TYPO3_DB']->sql_insert_id();
		
		//prepare fields for mm table
		$mmFieldValues = array(
			'uid_local' => $userId,
			'uid_foreign' => $commentId,
		);
		
		//insert into mm table
		$resMM = $GLOBALS['TYPO3_DB']->exec_INSERTquery('tt_address_tx_kecontacts_comments_mm',$mmFieldValues);
		$mmId = $GLOBALS['TYPO3_DB']->sql_insert_id();
	}
	
	function renderSingleView() {
		$content = '';
		
		//set tt_address uid to query and get record for uid
		$userId = intval($this->piVars['id']);
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tt_address','uid = '.$userId.' AND pid = '.$this->flexConf['storage_pid'].' '.$this->cObj->enableFields('tt_address'),'','','');
		
		//check for existence of address
		if(!$GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
			$markerArray = array('ERRORTEXT' => $this->pi_getLL('single_not_found'));
			$content = $this->substituteMarkers('###ERROR###',$markerArray);
			
			return $content;
		}
		
		$addressData = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		
		//connect persons to organization and display as single links
		$persons = '';
		if($addressData['tx_kecontacts_type'] == 2) {
			$whereClause .= ' '.$this->cObj->enableFields('tt_address');
			$whereClause .= ' AND tt_address.pid = '.$this->flexConf['storage_pid'];
			$whereClause .= ' AND tta2.tx_kecontacts_type = 1';
			$whereClause .= ' AND tt_address_tx_kecontacts_members_mm.uid_local = '.$userId;
			
			//querying same table not possible with typo3 db core functions, using the "dirty way" here
			$resPersons = $GLOBALS['TYPO3_DB']->sql_query('SELECT tta2.* FROM tt_address,tt_address_tx_kecontacts_members_mm,tt_address AS tta2 WHERE tt_address.uid = tt_address_tx_kecontacts_members_mm.uid_local AND tt_address_tx_kecontacts_members_mm.uid_foreign = tta2.uid '.$whereClause.' ORDER BY tta2.last_name ASC');
			
			if($GLOBALS['TYPO3_DB']->sql_num_rows($resPersons)) {
				while($person = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resPersons)) {
					$detailLinkConf = array(
						'parameter' => $GLOBALS['TSFE']->id,
						'additionalParams' => '&'.$this->prefixId.'[mode]=single&'.$this->prefixId.'[id]='.$person['uid'],
					);
					
					$persons .= $this->cObj->typoLink($person['title'].' '.$person['first_name'].' '.$person['last_name'],$detailLinkConf).'<br />';
				}
			} else {
				$persons = $this->pi_getLL('single_no_persons_found');
			}
		}
		
		//get company person belongs to
		$belongsTo = '';
		if($addressData['tx_kecontacts_type'] == 1) {
			$whereClause .= ' '.$this->cObj->enableFields('tt_address');
			$whereClause .= ' AND tt_address.pid = '.$this->flexConf['storage_pid'];
			$whereClause .= ' AND tt_address.tx_kecontacts_type = 2';
			
			//$GLOBALS['TYPO3_DB']->debugOutput = true;
			$resBelongsTo = $GLOBALS['TYPO3_DB']->sql_query('SELECT tt_address.last_name,tt_address.uid FROM tt_address,tt_address_tx_kecontacts_members_mm AS b WHERE tt_address.uid = b.uid_local AND b.uid_foreign = '.$userId.' '.$whereClause);

			if($GLOBALS['TYPO3_DB']->sql_num_rows($resBelongsTo)) {
				
				$belongsToRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resBelongsTo);
				
				$detailLinkConf = array(
					'parameter' => $GLOBALS['TSFE']->id,
					'additionalParams' => '&'.$this->prefixId.'[mode]=single&'.$this->prefixId.'[id]='.$belongsToRow['uid'],
				);
				
				$belongsTo = $this->cObj->typoLink($belongsToRow['last_name'],$detailLinkConf);
			} else {
				$belongsTo = $this->pi_getLL('single_no_organization_found');
			}
		}
		
		//configuration delete link
		$linkConfDelete = array(
			'parameter' => $GLOBALS['TSFE']->id,
			'additionalParams' => '&'.$this->prefixId.'[mode]=delete&'.$this->prefixId.'[id]='.intval($this->piVars['id']),
			'title' => $this->pi_getLL('single_delete_link'),
			'ATagParams' => 'class="deleteSingleView"',
			//'useCacheHash' => 'false',
		);
		
		//configuration edit link
		$linkConfEdit = array(
			'parameter' => $GLOBALS['TSFE']->id,
			'additionalParams' => '&'.$this->prefixId.'[mode]=edit&'.$this->prefixId.'[id]='.intval($this->piVars['id']),
			'title' => $this->pi_getLL('single_edit_link'),
			'ATagParams' => 'class="editSingleView"',
			//'useCacheHash' => 'false',
		);
		
		//fill markers
		$gender = ($addressData['gender'] == 'm')?'Herr':'Frau';
		
		$markerArray = array(
			'DELETELINK' => $this->cObj->typoLink($this->pi_getLL('single_delete_link'),$linkConfDelete),
			'EDITLINK' => $this->cObj->typoLink($this->pi_getLL('single_edit_link'),$linkConfEdit),
			'GENDER' => ($addressData['tx_kecontacts_type'] == 1)?$gender:'',
			'TITLE' => $addressData['title'],
			'FIRST_NAME' => $addressData['first_name'],
			'LAST_NAME' => $addressData['last_name'],
			'LABEL_BELONGSTO' => $this->pi_getLL('single_label_belongsto'),
			'ORGANIZATION' => $belongsTo,
			'ADDRESS' => $addressData['address'],
			'ZIP' => $addressData['zip'],
			'CITY' => $addressData['city'],
			'COUNTRY' => $addressData['country'],
			'LABEL_PHONE' => $this->pi_getLL('single_label_phone'),
			'PHONE' => $addressData['phone'],
			'LABEL_FAX' => $this->pi_getLL('single_label_fax'),
			'FAX' => $addressData['fax'],
			'LABEL_MOBILE' => $this->pi_getLL('single_label_mobile'),
			'MOBILE' => $addressData['mobile'],
			'LABEL_EMAIL' => $this->pi_getLL('single_label_email'),
			'E-MAIL' => $this->cObj->typoLink($addressData['email'],array('parameter' => $addressData['email'])),
			'LABEL_BIRTHDAY' => $this->pi_getLL('single_label_birthday'),
			'BIRTHDAY' => ($addressData['birthday'] != 0)?date('d.m.Y',$addressData['birthday']):'',
			'LABEL_PERSONS' => $this->pi_getLL('single_label_persons'),
			'PERSONS' => $persons,
			'FORMURL' => $this->pi_linkTP_keepPIvars_url(array(),0,0,$GLOBALS['TSFE']->id),
			'COMMENTS' => $this->getComments($addressData['tx_kecontacts_type']),
			'SINGLE_BUTTON_DELETE' => $this->pi_getLL('single_button_delete'),
			'SINGLE_BUTTON_UPDATE' => $this->pi_getLL('single_button_update'),
		);
		
		//create content
		$subpart = ($addressData['tx_kecontacts_type'] == 1)?'MAIN_PERSON':'MAIN_ORG';
		$content = $this->substituteMarkers($subpart,$markerArray);
	
		return $content;
	}
	
	function renderListView() {
		$content = '';
		
		//link configuration for "new" link
		$linkConfNew = array(
			'parameter' => $GLOBALS['TSFE']->id,
			'additionalParams' => '&'.$this->prefixId.'[mode]=create',
			'title' => $this->pi_getLL('list_create_new'),
			'ATagParams' => 'class="linkTextHeader"',
			//'useCacheHash' => 'false',
		);
		
		//generate filter options
		$filterOneSelected = t3lib_div::removeXSS($this->piVars['headerDropDown']);
		$filterOneOptions = '';
				
		for($i = 1; $i <= 3; $i++) {
			//generate each option for filter and check if selected
			$filterOneOptions .= '<option value="'.$i.'" '.(($filterOneSelected == $i)?'selected':'').'>'.$this->pi_getLL('list_filter1_'.$i).'</option>';
		}
	
		//set  default value for searchbox
		$searchWord = (strlen($this->piVars['sword']))?(t3lib_div::removeXSS($this->piVars['sword'])):$this->pi_getLL('search_phrase');
		
		//get items for listview
		$listItems = $this->getData();
		
		//generate filter options
		$pageSelected = (isset($this->piVars['pointer']))?(t3lib_div::removeXSS($this->piVars['pointer'])):0;
		$pageOptions = '';
				
		for($i = 0; $i <= $this->numberOfPages-1; $i++) {
			//generate each option for filter and check if selected
			$pageOptions .= '<option value="'.$i.'" '.(($pageSelected == $i)?'selected':'').'>'.($i+1).'</option>';
		}
		
		$pageCount = str_replace('%1',($this->piVars['pointer']+1),$this->pi_getLL('list_page_count'));
		$pageCount = str_replace('%2',$this->numberOfPages,$pageCount);
		
		//fill markers
		$markerArray = array(
							'LIST_HEADER1' => $this->pi_getLL('list_header1'),
							'LIST_HEADER2' => $this->pi_getLL('list_header2'),
							'LIST_HEADER3' => $this->pi_getLL('list_header3'),
							'GOTOPAGE' => $this->pi_getLL('list_goto_page'),
							'PAGECOUNT' => $pageCount,
							'FORMURL' => $this->pi_linkTP_keepPIvars_url(array(),1,0,$GLOBALS['TSFE']->id),
							'CREATENEWCONTACT' => $this->cObj->typoLink($this->pi_getLL('list_create_new'),$linkConfNew),
							'FILTER1_OPTIONS' => $filterOneOptions,
							'PAGES_OPTIONS' => $pageOptions,
							'SEARCHPHRASE' => $searchWord,
							'ITEMS' => $listItems,
						);
		
		$content = $this->substituteMarkers('###LISTVIEW###',$markerArray);
		return $content;
	}
	
	function getData() {
		//get filter from piVars
		$typeFilter = intval($this->piVars['headerDropDown']);
		$searchWord = t3lib_div::removeXSS($this->piVars['sword']);
		$content = '';
		$searchDbFields = 'first_name,last_name,address,city,zip,email,phone,fax,mobile';
		
		//generate where clause
		$whereClause = (!strlen($searchWord) || $searchWord == $this->pi_getLL('search_phrase'))?'1=1':'1=1 '.$this->cObj->searchWhere($searchWord,$searchDbFields,'tt_address');
		$whereClause .= ' '.$this->cObj->enableFields('tt_address');
		$whereClause .= ' AND tt_address.pid = '.$this->flexConf['storage_pid'];
		
		if(intval($this->piVars['headerDropDown']) == 1)
			$whereClause .= ' AND tt_address.tx_kecontacts_type IN (1,2)';
		elseif(intval($this->piVars['headerDropDown']) == 2)
			$whereClause .= ' AND tt_address.tx_kecontacts_type = 1';
		elseif(intval($this->piVars['headerDropDown']) == 3)
			$whereClause .= ' AND tt_address.tx_kecontacts_type = 2';
		
		//collect data for page browser
		$resCount = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tt_address',$whereClause,'','tt_address.last_name ASC','');	
		$numberOfResultsToDisplay = ($this->flexConf['contacts_per_page'] != 25)?$this->flexConf['contacts_per_page']:25;
		$this->numberOfPages = ceil($GLOBALS['TYPO3_DB']->sql_num_rows($resCount) / $numberOfResultsToDisplay);
		
		$limit[] = $this->piVars['pointer'] * $numberOfResultsToDisplay;
		$limit[] = $numberOfResultsToDisplay;
		//execute query
		
		$GLOBALS['TYPO3_DB']->debugOutput = true;
		//$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query('*','tt_address','tt_address_tx_kecontacts_members_mm','tt_address',$whereClause,'','tt_address.last_name ASC','');
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tt_address',$whereClause,'','tt_address.last_name ASC',join(',',$limit));
		
		//also select contacts connected to an organization - if condition matches
		if((!isset($this->piVars['headerDropDown']) || $this->piVars['headerDropDown'] == 3 || $this->piVars['headerDropDown'] == 1) && $searchWord != $this->pi_getLL('search_phrase') && strlen($searchWord)) {
			$searchWhereTta = $this->cObj->searchWhere($searchWord,$searchDbFields,'tt_address');
			$searchWhereTta2 = "OR (tta2.first_name LIKE '".$searchWord."' OR tta2.last_name LIKE '".$searchWord."' OR tta2.address LIKE '".$searchWord."' OR tta2.city LIKE '".$searchWord."' OR tta2.zip LIKE '".$searchWord."' OR tta2.email LIKE '".$searchWord."' OR tta2.phone LIKE '".$searchWord."' OR tta2.fax LIKE '".$searchWord."' OR tta2.mobile LIKE '".$searchWord."')";

			//TODO: ABFRAGE BEI ORG/ALLE und allen Kontakten ausgliedern!
			$sql_query = 'SELECT * FROM tt_address LEFT JOIN tt_address_tx_kecontacts_members_mm AS mm1 ON tt_address.uid = mm1.uid_local LEFT JOIN tt_address AS tta2 ON mm1.uid_foreign = tta2.uid WHERE tt_address.pid = '.$this->flexConf['storage_pid'].' AND tt_address.tx_kecontacts_type IN (1,2) '.$searchWhereTta.' '.$searchWhereTta2.' '.$this->cObj->enableFields('tt_address');
			$res = $GLOBALS['TYPO3_DB']->sql_query($sql_query);
			t3lib_div::debug($sql_query);
		}
		
		if($GLOBALS['TYPO3_DB']->sql_num_rows($res)) {
			$rowCount = 0;
			
			while($addressRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				
				$rowCount++;
				
				//link configuration for "new" link
				$linkConfEdit = array(
					'parameter' => $GLOBALS['TSFE']->id,
					'additionalParams' => '&'.$this->prefixId.'[mode]=single&'.$this->prefixId.'[id]='.$addressRow['uid'],
					'title' => $this->pi_getLL('list_single'),
				);
				
				$markerArray = array(
				
								'FIRST_NAME' => $addressRow['first_name'],
								'LAST_NAME' => (strlen($addressRow['first_name']))?$addressRow['last_name'].', ':$addressRow['last_name'],
								'TITLE' => $addressRow['title'],
								'ORGANISATION' => $addressRow['company'],
								'ADDRESS' => $addressRow['address'],
								'ZIP' => $addressRow['zip'],
								'CITY' => $addressRow['city'],
								'TELEPHONE' => $addressRow['phone'],
								'EMAIL' => $this->cObj->typoLink($addressRow['email'],array('parameter' => $addressRow['email'])),
								'WWW' => $this->cObj->typoLink($addressRow['www'],array('parameter' => $addressRow['www'],'extTarget' => '_blank')),
								'EDIT_ICON' => $this->cObj->typoLink($this->cObj->fileResource(t3lib_extMgm::siteRelPath($this->extKey).'res/img/Edit.png'),$linkConfEdit),
								'HIGHLIGHTROW' => (($rowCount % 2) == 0)?'firstRowBodyList':'secondRowBodyList',
							);
				$content .= $this->substituteMarkers('###ITEM###',$markerArray);
				
			}
		} else {
			$content = '<strong>Keine Ergebnisse</strong>';
		}
		
		return $content;
	}
	
	function loadTemplate() {
		//harden neccessary piVars
		$viewMode = t3lib_div::removeXSS($this->piVars['mode']);

		//set html template path
		switch($viewMode) {
			case 'edit':
			case 'create':
				$templateFile = (!strlen($this->flexConf['edit_template_file']))?(t3lib_extMgm::siteRelPath($this->extKey).'res/templates/edit.html'):'uploads/'.$this->flexConf['edit_template_file'];
			break;
			case 'single':
				$templateFile = (!strlen($this->flexConf['single_template_file']))?(t3lib_extMgm::siteRelPath($this->extKey).'res/templates/singleview.html'):'uploads/'.$this->flexConf['single_template_file'];
			break;
			case 'delete':
				$templateFile = (!strlen($this->flexConf['delete_template_file']))?(t3lib_extMgm::siteRelPath($this->extKey).'res/templates/delete.html'):'uploads/'.$this->flexConf['delete_template_file'];
			break;
			default:
				$templateFile = (!strlen($this->flexConf['list_template_file']))?(t3lib_extMgm::siteRelPath($this->extKey).'res/templates/listview.html'):'uploads/'.$this->flexConf['list_template_file'];
			break;
		}
			
		//place content of html template in member var
		$this->tmpl = $this->cObj->fileResource($templateFile);
	}
	
	function addCssToPage() {
		// include css
		$cssfile = t3lib_extMgm::siteRelPath($this->extKey).'res/css/'.$this->extKey.'.css';
		$GLOBALS['TSFE']->additionalHeaderData[$this->prefixId] .= '<link rel="stylesheet" type="text/css" href="'.$cssfile.'" />';
	}
	
	function addJavascript() {
		$content = '
					function changeType(type) {
						fields = new Array(\'kecontacts_birthday\',\'kecontacts_organization\',\'kecontacts_gender\',\'kecontacts_title\',\'kecontacts_firstname\',\'kecontacts_function\',\'kecontacts_mobilephone\');
						for(i=0; i<fields.length; i++ ) {
							index = fields[i];
							el = document.getElementById(index);
							if (type == 2) el.style.display = \'none\';
							else el.style.display = \'inline\';
						}
					}
		';
		
		return $content;
	}
	
	function substituteMarkers($subpart = '', $markerArray = array()) {
		$content = '';
		if(!strlen($subpart) || !count($markerArray)) return $content;

		$this->loadTemplate();
		$template = $this->cObj->getSubpart($this->tmpl, $subpart);

		foreach($markerArray as $marker => $markerContent)
			$markers[$marker] = $markerContent;

		$content = $this->cObj->substituteMarkerArray($template, $markerArray,$wrap='###|###',$uppercase=1);

		return $content;
	}

	function createFlexFormArray() {
		$this->pi_initPIflexForm();
		$this->flexConf = array();
		$piFlexForm = $this->cObj->data['pi_flexform'];

		//convert "in-depth" keys and values to faster configuration structure
		foreach ( $piFlexForm['data'] as $sheet => $data )
			foreach ( $data as $lang => $value )
				foreach ( $value as $key => $val )
					$this->flexConf[$key] = $this->pi_getFFvalue($piFlexForm, $key, $sheet);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_contacts/pi1/class.tx_kecontacts_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_contacts/pi1/class.tx_kecontacts_pi1.php']);
}

?>