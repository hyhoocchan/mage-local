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
class Mage_Adminhtml_Customer_OnlineController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        $this->_title($this->__('Customers'))->_title($this->__('Online Customers'));

        if($this->getRequest()->getParam('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->loadLayout();

        $this->_setActiveMenu('customer/online');

        $this->_addContent($this->getLayout()->createBlock('adminhtml/customer_online', 'customers'));

        $this->_addBreadcrumb(Mage::helper('customer')->__('Customers'), Mage::helper('customer')->__('Customers'));
        $this->_addBreadcrumb(Mage::helper('customer')->__('Online Customers'), Mage::helper('customer')->__('Online Customers'));

        $this->renderLayout();
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/online');
    }
}
