<?php
/**
 * Copyright 2011 Bas de Nooijer. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this listof conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDER AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * The views and conclusions contained in the software and documentation are
 * those of the authors and should not be interpreted as representing official
 * policies, either expressed or implied, of the copyright holder.
 *
 * @copyright Copyright 2011 Bas de Nooijer <solarium@raspberry.nl>
 * @license http://github.com/basdenooijer/solarium/raw/master/COPYING
 * @link http://www.solarium-project.org/
 *
 * @package Solarium
 * @subpackage Query
 */

/**
 * Base class for all query types, not intended for direct usage
 *
 * @package Solarium
 * @subpackage Query
 */
abstract class Solarium_Query extends Solarium_Configurable
{

    /**
     * Helper instance
     *
     * @var Solarium_Query_Helper
     */
    protected $_helper;

    /**
     * Extra query params (e.g. dereferenced params)
     *
     * @var array
     */
    protected $_params = array();

    /**
     * Get type for this query
     *
     * @return string
     */
    abstract public function getType();

    /**
     * Set handler option
     *
     * @param string $handler
     * @return Solarium_Query Provides fluent interface
     */
    public function setHandler($handler)
    {
        return $this->_setOption('handler', $handler);
    }

    /**
     * Get handler option
     *
     * @return string
     */
    public function getHandler()
    {
        return $this->getOption('handler');
    }

    /**
     * Set resultclass option
     *
     * If you set a custom result class it must be available through autoloading
     * or a manual require before calling this method. This is your
     * responsibility.
     *
     * Also you need to make sure it extends the orginal result class of the
     * query or has an identical API.
     *
     * @param string $classname
     * @return Solarium_Query Provides fluent interface
     */
    public function setResultClass($classname)
    {
        return $this->_setOption('resultclass', $classname);
    }

    /**
     * Get resultclass option
     *
     * @return string
     */
    public function getResultClass()
    {
        return $this->getOption('resultclass');
    }

    /**
     * Get a helper instance
     *
     * Uses lazy loading: the helper is instantiated on first use
     *
     * @return Solarium_Query_Helper
     */
    public function getHelper()
    {
        if (null === $this->_helper) {
            $this->_helper = new Solarium_Query_Helper($this);
        }

        return $this->_helper;
    }

    /**
     * Add extra params to the request
     *
     * Only intended for internal use, for instance with dereferenced params.
     * Therefore the params are limited in functionality. Only add and get
     *
     * @param string $name
     * @param string $value
     * @return self Provides fluent interface
     */
    public function addParam($name, $value)
    {
        $this->_params[$name] = $value;
        return $this;
    }

    /**
     * Get extra params
     *
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

}