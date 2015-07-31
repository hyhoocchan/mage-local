<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition End User License Agreement
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magento.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Api2
 * @copyright Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license http://www.magento.com/license/enterprise-edition
 */

/**
 * Request content interpreter query adapter
 *
 * @category    Mage
 * @package     Mage_Api2
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api2_Model_Request_Interpreter_Query implements Mage_Api2_Model_Request_Interpreter_Interface
{
    /**
     * URI validate pattern
     */
    const URI_VALIDATE_PATTERN = "/^(?:%[[:xdigit:]]{2}|[A-Za-z0-9-_.!~*'()\[\];\/?:@&=+$,])*$/";

    /**
     * Parse request body into array of params
     *
     * @param string $body  Posted content from request
     * @return array        Return always array
     * @throws Exception|Mage_Api2_Exception
     */
    public function interpret($body)
    {
        if (!is_string($body)) {
            throw new Exception(sprintf('Invalid data type "%s". String expected.', gettype($body)));
        }

        if (!$this->_validateQuery($body)) {
            throw new Mage_Api2_Exception(
                'Invalid data type. Check Content-Type.',
                Mage_Api2_Model_Server::HTTP_BAD_REQUEST
            );
        }

        $data = array();
        parse_str($body, $data);
        return $data;
    }

    /**
     * Returns true if and only if the query string passes validation.
     *
     * @param  string $query The query to validate
     * @return boolean
     * @link   http://www.faqs.org/rfcs/rfc2396.html
     */
    protected function _validateQuery($query)
    {
        return preg_match(self::URI_VALIDATE_PATTERN, $query);
    }
}
