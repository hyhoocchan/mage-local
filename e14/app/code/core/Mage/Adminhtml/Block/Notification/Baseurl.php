<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

class Mage_Adminhtml_Block_Notification_Baseurl extends Mage_Adminhtml_Block_Template
{
    /**
     * Get url for config settings where base url option can be changed
     *
     * @return string | false
     */
    public function getConfigUrl()
    {
        $defaultUnsecure= (string) Mage::getConfig()->getNode('default/'.Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_URL);
        $defaultSecure  = (string) Mage::getConfig()->getNode('default/'.Mage_Core_Model_Store::XML_PATH_SECURE_BASE_URL);

        if ($defaultSecure == '{{base_url}}' || $defaultUnsecure == '{{base_url}}') {
            return $this->getUrl('adminhtml/system_config/edit', array('section'=>'web'));
        }

        $configData = Mage::getModel('core/config_data');
        $dataCollection = $configData->getCollection()
            ->addValueFilter('{{base_url}}');

        $url = false;
        foreach ($dataCollection as $data) {
            if ($data->getScope() == 'stores') {
                $code = Mage::app()->getStore($data->getScopeId())->getCode();
                $url = $this->getUrl('adminhtml/system_config/edit', array('section'=>'web', 'store'=>$code));
            }
            if ($data->getScope() == 'websites') {
                $code = Mage::app()->getWebsite($data->getScopeId())->getCode();
                $url = $this->getUrl('adminhtml/system_config/edit', array('section'=>'web', 'website'=>$code));
            }

            if ($url) {
                return $url;
            }
        }
        return $url;
    }
}
