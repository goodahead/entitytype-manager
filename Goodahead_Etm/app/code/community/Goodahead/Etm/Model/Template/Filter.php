<?php
/**
 * This file is part of Goodahead_Etm extension
 *
 * This extension allows to create and manage custom EAV entity types
 * and EAV entities
 *
 * Copyright (C) 2014 Goodahead Ltd. (http://www.goodahead.com)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * and GNU General Public License along with this program.
 * If not, see <http://www.gnu.org/licenses/>.
 *
 * @category   Goodahead
 * @package    Goodahead_Etm
 * @copyright  Copyright (c) 2014 Goodahead Ltd. (http://www.goodahead.com)
 * @license    http://www.gnu.org/licenses/lgpl-3.0-standalone.html GNU Lesser General Public License
 */

class Goodahead_Etm_Model_Template_Filter
    extends Mage_Core_Model_Email_Template_Filter
{

    /**
     * @var Goodahead_Etm_Model_Entity
     */
    protected $_entity;

    /**
     * @var Goodahead_Etm_Model_Entity_Type
     */
    protected $_entityType;

    /**
     * @var Goodahead_Etm_Model_Resource_Entity_Collection
     */
    protected $_entityCollection;

    /**
     * @var Mage_Page_Block_Html_Pager
     */
    protected $_pagerBlock;

    /**
     * @var string
     */
    protected $_itemTemplate;


    /**
     * Whether to allow SID in store directive: AUTO
     *
     * @var bool
     */
    protected $_useSessionInUrl = null;

    /**
     * Setter whether SID is allowed in store directive
     *
     * @param bool $flag
     * @return Mage_Cms_Model_Template_Filter
     */
    public function setUseSessionInUrl($flag)
    {
        $this->_useSessionInUrl = (bool)$flag;
        return $this;
    }

    /**
     * @param Goodahead_Etm_Model_Entity $entity
     *
     * @return $this
     */
    public function setEntity(Goodahead_Etm_Model_Entity $entity)
    {
        $this->_entity = $entity;
        return $this;
    }

    /**
     * @return Goodahead_Etm_Model_Entity|null
     */
    public function getEntity()
    {
        return $this->_entity;
    }

    /**
     * @param Goodahead_Etm_Model_Entity_Type $entityType
     *
     * @return $this
     */
    public function setEntityType(Goodahead_Etm_Model_Entity_Type $entityType)
    {
        $this->_entityType = $entityType;
        return $this;
    }

    /**
     * @return Goodahead_Etm_Model_Entity_Type|null
     */
    public function getEntityType()
    {
        return $this->_entityType;
    }

    /**
     * @param Goodahead_Etm_Model_Resource_Entity_Collection $entityCollection
     *
     * @return $this
     */
    public function setCollection(Goodahead_Etm_Model_Resource_Entity_Collection $entityCollection)
    {
        $this->_entityCollection = $entityCollection;
        return $this;
    }

    /**
     * @return Goodahead_Etm_Model_Resource_Entity_Collection|null
     */
    public function getCollection()
    {
        return $this->_entityCollection;
    }


    /**
     * @param Mage_Page_Block_Html_Pager $pagerBlock
     *
     * @return $this
     */
    public function setPagerBlock(Mage_Page_Block_Html_Pager $pagerBlock)
    {
        $this->_pagerBlock = $pagerBlock;
        return $this;
    }

    /**
     * @return Mage_Page_Block_Html_Pager|null
     */
    public function getPagerBlock()
    {
        return $this->_pagerBlock;
    }

    /**
     * @param $template
     *
     * @return $this
     */
    public function setItemTemplate($template)
    {
        $this->_itemTemplate = $template;
        return $this;
    }

    /**
     * @return string
     */
    public function getItemTemplate()
    {
        return $this->_itemTemplate;
    }

    public function pagerDirective($construction)
    {
        if ($this->_pagerBlock instanceof Mage_Page_Block_Html_Pager) {
            if (null === $this->_pagerBlock->getCollection()) {
                $params = $this->_getIncludeParameters($construction[2]);
                foreach ($params as $key => $value) {
                    switch ($key) {
                        case 'limits':
                            $limits = array_filter(explode("|", $value), 'is_numeric');
                            $limits = array_combine($limits, $limits);
                            $this->_pagerBlock->setAvailableLimit($limits);
                        break;
                        case 'frame_length':
                            $value = (int)$value;
                            if (0 > $value) {
                                $value = null;
                            }
                        case 'limit_var_name':
                        case 'page_var_name':
                            if (isset($value)) {
                                $method = 'set'.uc_words($key, '');
                                if (is_callable(array($this->_pagerBlock, $method))) {
                                    $this->_pagerBlock->$method($value);
                                }
                            }
                        break;
                    }
                }
                $this->_pagerBlock->setCollection($this->getCollection());
            }
            return $this->_pagerBlock->toHtml();
        }
        return '';
    }




    public function itemsDirective($construction)
    {
        $itemsValue = '';

        if (($collection = $this->getCollection()) instanceof Goodahead_Etm_Model_Resource_Entity_Collection) {
            $templateFilter = Mage::getModel('goodahead_etm/template_filter');
            $templateFilter
                ->setStoreId($this->getStoreId())
                ->setUseAbsoluteLinks(true)
                ->setPlainTemplateMode(false)
                ->setEntityType($this->getEntityType());
            $template = $this->getItemTemplate();

            foreach ($collection->getItems() as $entity) {
                $resource = $entity->getResource();
                $resource->loadAllAttributes($entity);
                $data = $resource->walkAttributes(
                    'frontend/getValue', array($entity));
                $templateFilter->setVariables($data);
                $templateFilter->setEntity($entity);
                $itemsValue .= $templateFilter->filter($template);
            }

        }

        return $itemsValue;
    }


}