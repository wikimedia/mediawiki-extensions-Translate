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
 * Terms query
 *
 * A terms query provides access to the indexed terms in a field and the number of documents that match each term.
 * This can be useful for doing auto-suggest or other things that operate at the term level instead of the search
 * or document level. Retrieving terms in index order is very fast since the implementation directly uses Lucene's
 * TermEnum to iterate over the term dictionary.
 *
 * @package Solarium
 * @subpackage Query
 */
class Solarium_Query_Terms extends Solarium_Query
{

    /**
     * Get type for this query
     *
     * @return string
     */
    public function getType()
    {
        return Solarium_Client::QUERYTYPE_TERMS;
    }

    /**
     * Default options
     *
     * @var array
     */
    protected $_options = array(
        'resultclass' => 'Solarium_Result_Terms',
        'handler' => 'terms',
    );

    /**
     * Set the field name(s) to get the terms from
     *
     * For multiple fields use a comma-separated string
     *
     * @param string $value
     * @return self Provides fluent interface
     */
    public function setFields($value)
    {
        return $this->_setOption('fields', $value);
    }

    /**
     * Get the field name(s) to get the terms from
     *
     * @return string
     */
    public function getFields()
    {
        return $this->getOption('fields');
    }

    /**
     * Set the lowerbound term to start at
     *
     * @param string $value
     * @return self Provides fluent interface
     */
    public function setLowerbound($value)
    {
        return $this->_setOption('lowerbound', $value);
    }

    /**
     * Get the lowerbound term to start at
     *
     * @return string
     */
    public function getLowerbound()
    {
        return $this->getOption('lowerbound');
    }

    /**
     * Set lowerboundinclude
     *
     * @param boolean $value
     * @return self Provides fluent interface
     */
    public function setLowerboundInclude($value)
    {
        return $this->_setOption('lowerboundinclude', $value);
    }

    /**
     * Get lowerboundinclude
     *
     * @return boolean
     */
    public function getLowerboundInclude()
    {
        return $this->getOption('lowerboundinclude');
    }

    /**
     * Set mincount (the minimum doc frequency for terms in order to be included)
     *
     * @param integer $value
     * @return self Provides fluent interface
     */
    public function setMinCount($value)
    {
        return $this->_setOption('mincount', $value);
    }

    /**
     * Get mincount
     *
     * @return integer
     */
    public function getMinCount()
    {
        return $this->getOption('mincount');
    }

    /**
     * Set maxcount (the maximum doc frequency for terms in order to be included)
     *
     * @param integer $value
     * @return self Provides fluent interface
     */
    public function setMaxCount($value)
    {
        return $this->_setOption('maxcount', $value);
    }

    /**
     * Get maxcount
     *
     * @return integer
     */
    public function getMaxCount()
    {
        return $this->getOption('maxcount');
    }

    /**
     * Set prefix for terms
     *
     * @param string $value
     * @return self Provides fluent interface
     */
    public function setPrefix($value)
    {
        return $this->_setOption('prefix', $value);
    }

    /**
     * Get maxcount
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->getOption('prefix');
    }

    /**
     * Set regex to restrict terms
     *
     * @param string $value
     * @return self Provides fluent interface
     */
    public function setRegex($value)
    {
        return $this->_setOption('regex', $value);
    }

    /**
     * Get regex
     *
     * @return string
     */
    public function getRegex()
    {
        return $this->getOption('regex');
    }

    /**
     * Set regex flags
     *
     * Use a comma-separated string for multiple entries
     *
     * @param string $value
     * @return self Provides fluent interface
     */
    public function setRegexFlags($value)
    {
        return $this->_setOption('regexflags', $value);
    }

    /**
     * Get regex flags
     *
     * @return string
     */
    public function getRegexFlags()
    {
        return $this->getOption('regexflags');
    }

    /**
     * Set limit
     *
     * If < 0 all terms are included
     *
     * @param integer $value
     * @return self Provides fluent interface
     */
    public function setLimit($value)
    {
        return $this->_setOption('limit', $value);
    }

    /**
     * Get limit
     *
     * @return integer
     */
    public function getLimit()
    {
        return $this->getOption('limit');
    }

    /**
     * Set the upperbound term to start at
     *
     * @param string $value
     * @return self Provides fluent interface
     */
    public function setUpperbound($value)
    {
        return $this->_setOption('upperbound', $value);
    }

    /**
     * Get the upperbound term to start at
     *
     * @return string
     */
    public function getUpperbound()
    {
        return $this->getOption('upperbound');
    }

    /**
     * Set upperboundinclude
     *
     * @param boolean $value
     * @return self Provides fluent interface
     */
    public function setUpperboundInclude($value)
    {
        return $this->_setOption('upperboundinclude', $value);
    }

    /**
     * Get upperboundinclude
     *
     * @return boolean
     */
    public function getUpperboundInclude()
    {
        return $this->getOption('upperboundinclude');
    }

    /**
     * Set raw option
     *
     * @param boolean $value
     * @return self Provides fluent interface
     */
    public function setRaw($value)
    {
        return $this->_setOption('raw', $value);
    }

    /**
     * Get raw option
     *
     * @return boolean
     */
    public function getRaw()
    {
        return $this->getOption('raw');
    }

    /**
     * Set sort option
     *
     * @param string $value
     * @return self Provides fluent interface
     */
    public function setSort($value)
    {
        return $this->_setOption('sort', $value);
    }

    /**
     * Get sort option
     *
     * @return string
     */
    public function getSort()
    {
        return $this->getOption('sort');
    }

}