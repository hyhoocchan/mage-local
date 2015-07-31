<?php
/**
 * Created by PhpStorm.
 * User: henry
 * Date: 4/22/15
 * Time: 12:33
 */




class Forix_SystemNotification_Model_Observer {
	protected $_oldObjects = array();
	protected $_object = array();


	public function getToEmail() {
		return Mage::getStoreConfig('notification_to_email', Mage::app()->getStore());
	}
	public function getToName() {
		return Mage::getStoreConfig('notification_to_name', Mage::app()->getStore());
	}
	public function getFromEmail() {
		return Mage::getStoreConfig('trans_email/ident_general/email', Mage::app()->getStore());
	}
	public function getFromName() {
		return Mage::getStoreConfig('trans_email/ident_general/name', Mage::app()->getStore());
	}
	public function getEmailSubject() {
		return Mage::getStoreConfig('notification_subject', Mage::app()->getStore());
	}
	public function getEmailTemplate() {
		return Mage::getStoreConfig('notification_template', Mage::app()->getStore());
	}

	public function predispatch(Varien_Event_Observer $observer) {
		/*
		$controller = $observer->getEvent()->getData('controller_action');
		$_session = Mage::getModel('admin/session');
		$userName = $_session->getUser()->getUsername();
		$userId = $_session->getUser()->getId();
		$userEmail = $_session->getUser()->getEmail();
		$remoteAddr = Mage::helper('core/http')->getRemoteAddr();
		// printing visitor information data
		echo "<pre>"; var_dump($remoteAddr, $userId, $userName, $userEmail, $_session->getUser()->debug() ); echo "</pre>"; exit;
		*/
	}
	/**
	 * After Save Config Object Key
	 *
	 * @param Varien_Event_Observer $observer
	 * @return  Forix_SystemNotification_Model_Observer
	 */
	public function saveConfigSuccess(Varien_Event_Observer $observer) {
		$user_ip = $_SERVER['REMOTE_ADDR'];
		$result = filter_var( $user_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE |  FILTER_FLAG_NO_RES_RANGE );
		if( !$result ) {
			return $this;
		}
		$website = $observer->getEvent()->getData('website');
		$store = $observer->getEvent()->getData('store');
		$section = $observer->getEvent()->getData('section');

		$_session = Mage::getModel('admin/session');
		$userName = $_session->getUser()->getUsername();
		$userId = $_session->getUser()->getId();
		$userEmail = $_session->getUser()->getEmail();

		$object = $this->_object;
		$scope_code = Mage::app()->getStore($store)->getCode();
		$website_code = Mage::app()->getStore($store)->getWebsite()->getCode();
		$results = array();
		foreach($this->_oldObjects as $obj) {
			if( !isset($obj['new_value']) && !isset($obj['type']) ) {
				continue;
			}
			$results[] = sprintf("<p>&nbsp;&nbsp;&nbsp;&nbsp;Configuration Path: <strong>%s</strong> | Old Value: <strong>%s</strong> | New Value: <strong>%s</strong></p>", $obj['path'], $obj['old_value'], $obj['new_value']);
		}
		$remoteAddr = Mage::helper('core/http')->getRemoteAddr( );
		$results = implode("\n", $results);
		if( !empty($results) ) {
			$results = sprintf( $this->getEmailTemplate(), date('Y-m-d H:i:s', time()), $remoteAddr, $userName, $userEmail, $website_code, $scope_code, $results);
			$url = str_replace('/index.php', '', Mage::getBaseUrl() );
			# $to_email = sprintf("%s <%s>", $this->getToName(), $this->getToEmail() );
			$subject = sprintf( $this->getEmailSubject(), $url);

			$mail = Mage::getModel('core/email');
			$mail->setToName( $this->getToName() );
			$mail->setToEmail( $this->getToEmail() );
			$mail->setBody( $results );
			$mail->setSubject( $subject );
			$mail->setFromEmail( $this->getFromEmail() );
			$mail->setFromName( $this->getFromName() );
			$mail->setType('html');  // You can use 'html' or 'text'
			#$mail->setType('text'); // You can use 'html' or 'text'

			try {
				$mail->send();
				# Mage::getSingleton('core/session')->addSuccess('Notification from your request has been sent');
			} catch (Exception $e) {
				Mage::log('Notification from your request unable to send.<br>Error: ' . $e->getMessage(), Zend_Log::INFO, 'system-notification.log');
				# Mage::getSingleton('core/session')->addError('Notification from your request unable to send.<br>Error: ' . $e->getMessage() . "<br><pre>" . $e->getTraceAsString() . "</pre>");
			}
		} else {
			# Mage::getSingleton('core/session')->addSuccess('Notification from your request: no change');
		}

		return $this;
	}

	/**
	 * After Save Config Object Key
	 *
	 * @param Varien_Event_Observer $observer
	 * @return  Forix_SystemNotification_Model_Observer
	 */
	public function modelBeforeSave(Varien_Event_Observer $observer) {
		$object = $observer->getEvent()->getData('object');
		/**
		 * var $object Mage_Adminhtml_System_ConfigController
		 */
		$section = $object->getSection();
		$website = $object->getWebsite();
		$store   = $object->getStore();
		$groups  = $object->getGroups();
		$scope   = $object->getScope();
		$scopeId = $object->getScopeId();
		$this->_object = $object;
		if (empty($groups)) {
			return $this;
		}

		$sections = Mage::getModel('adminhtml/config')->getSections();
		/* @var $sections Mage_Core_Model_Config_Element */

		$oldConfig = $this->_getConfig($object, true);

		foreach ($groups as $group => $groupData) {
			/**
			 * Map field names if they were cloned
			 */
			$groupConfig = $sections->descend($section.'/groups/'.$group);

			if ($clonedFields = !empty($groupConfig->clone_fields)) {
				if ($groupConfig->clone_model) {
					$cloneModel = Mage::getModel((string)$groupConfig->clone_model);
				} else {
					Mage::throwException('Config form fieldset clone model required to be able to clone fields');
				}
				$mappedFields = array();
				$fieldsConfig = $sections->descend($section.'/groups/'.$group.'/fields');

				if ($fieldsConfig->hasChildren()) {
					foreach ($fieldsConfig->children() as $field => $node) {
						foreach ($cloneModel->getPrefixes() as $prefix) {
							$mappedFields[$prefix['field'].(string)$field] = (string)$field;
						}
					}
				}
			}
			// set value for group field entry by fieldname
			// use extra memory
			$fieldsetData = array();
			foreach ($groupData['fields'] as $field => $fieldData) {
				$fieldsetData[$field] = (is_array($fieldData) && isset($fieldData['value']))
					? $fieldData['value'] : null;
			}

			foreach ($groupData['fields'] as $field => $fieldData) {
				$fieldConfig = $sections->descend($section . '/groups/' . $group . '/fields/' . $field);
				if (!$fieldConfig && $clonedFields && isset($mappedFields[$field])) {
					$fieldConfig = $sections->descend($section . '/groups/' . $group . '/fields/'
					                                  . $mappedFields[$field]);
				}
				if (!$fieldConfig) {
					$node = $sections->xpath($section .'//' . $group . '[@type="group"]/fields/' . $field);
					if ($node) {
						$fieldConfig = $node[0];
					}
				}

				/**
				 * Get field backend model
				 */
				$backendClass = (isset($fieldConfig->backend_model))? $fieldConfig->backend_model : false;
				if (!$backendClass) {
					$backendClass = 'core/config_data';
				}

				/** @var $dataObject Mage_Core_Model_Config_Data */
				$dataObject = Mage::getModel($backendClass);
				if (!$dataObject instanceof Mage_Core_Model_Config_Data) {
					Mage::throwException('Invalid config field backend model: '.$backendClass);
				}

				if (!isset($fieldData['value'])) {
					$fieldData['value'] = null;
				}

				$path = $section . '/' . $group . '/' . $field;

				/**
				 * Look for custom defined field path
				 */
				if (is_object($fieldConfig)) {
					$configPath = (string)$fieldConfig->config_path;
					if (!empty($configPath) && strrpos($configPath, '/') > 0) {
						// Extend old data with specified section group
						$groupPath = substr($configPath, 0, strrpos($configPath, '/'));
						if (!isset($oldConfigAdditionalGroups[$groupPath])) {
							$oldConfig = $object->extendConfig($groupPath, true, $oldConfig);
							$oldConfigAdditionalGroups[$groupPath] = true;
						}
						$path = $configPath;
					}
				}

				$inherit = !empty($fieldData['inherit']);

				if( (is_array($fieldData['value']) || is_object($fieldData['value']) ) ) {
					$new_val =  implode(",", $fieldData['value']);
					# $new_val = serialize($fieldData['value']);
				} else {
					$new_val = $fieldData['value'];
				}

				if (isset($oldConfig[$path])) {
					if (isset($oldConfig[$path]['value']) && (is_array($oldConfig[$path]['value']) || is_object($oldConfig[$path]['value']) ) ) {
						$old_val = implode(",", $oldConfig[$path]['value']);
						# $old_val = serialize($oldConfig[$path]['value']);
					} else {
						$old_val = isset($oldConfig[$path]['value']) ? $oldConfig[$path]['value'] : '';
					}

					if( $old_val != $new_val || $inherit ) {
						$oldConfig[$path]['type'] = !$inherit ? 'Save' : 'Delete';
						$oldConfig[$path]['path'] = $path;
						$oldConfig[$path]['old_value'] = $old_val;
						$oldConfig[$path]['new_value_1'] = $fieldData['value'];
						$oldConfig[$path]['new_value'] = $new_val;
					}
				} elseif (!$inherit) {
					$oldConfig[$path]['type'] = 'Save';
					$oldConfig[$path]['path'] = $path;

					$oldConfig[$path]['old_value'] = 'NULL';
					$oldConfig[$path]['new_value_1'] = $fieldData['value'];
					$oldConfig[$path]['new_value'] = $new_val;
				}
			}
		}
		$this->_oldObjects = $oldConfig;
		return $this;
	}

	/**
	 * Return formatted config data for current section
	 *
	 * @param bool $full Simple config structure or not
	 * @return array
	 */
	protected function _getConfig($object, $full = true)
	{
		return $this->_getPathConfig($object, $object->getSection(), $full);
	}

	/**
	 * Return formatted config data for specified path prefix
	 *
	 * @param string $path Config path prefix
	 * @param bool $full Simple config structure or not
	 * @return array
	 */
	protected function _getPathConfig($object, $path, $full = true)
	{
		$configDataCollection = Mage::getModel('core/config_data')
		                            ->getCollection()
		                            ->addScopeFilter($object->getScope(), $object->getScopeId(), $path);

		$config = array();
		foreach ($configDataCollection as $data) {
			if ($full) {
				$config[$data->getPath()] = array(
					'path'      => $data->getPath(),
					'value'     => $data->getValue(),
					'config_id' => $data->getConfigId()
				);
			}
			else {
				$config[$data->getPath()] = $data->getValue();
			}
		}
		return $config;
	}

}
