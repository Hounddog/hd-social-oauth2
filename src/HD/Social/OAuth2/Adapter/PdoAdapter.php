<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace HD\Social\OAuth2\Adapter;

use ZF\OAuth2\Adapter\PdoAdapter as ZFOAuthPdoAdapter;

/**
 * Extension of OAuth2\Storage\PDO that provides Bcrypt client_secret/password
 * encryption
 */
class PdoAdapter extends ZFOAuthPdoAdapter
{
    
}
