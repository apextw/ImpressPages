<?php
/**
 * @package ImpressPages
 *
 *
 */

namespace Ip\Module\Content;





class Zone extends \Ip\Frontend\Zone {
    var $db;
    function __construct($properties) {
        $this->db = new DbFrontend();
        parent::__construct($properties);
    }



    function getPages($languageId = null, $parentElementId = null, $startFrom = 0, $limit = null, $includeHidden = false, $reverseOrder = false) {


        if($languageId == null)
        $languageId = ipGetCurrentLanguage()->getId();

        $urlVars = array();

        if($parentElementId != null) {  //if parent specified
            $parentElements = $this->getRoadToPage($parentElementId);
            foreach($parentElements as $key => $element)
            $urlVars[] = $element->getUrl();
        }

        $breadCrumb = $this->getBreadCrumb();
        $depth = sizeof($urlVars)+1;
        if(isset($breadCrumb[$depth-1])) {
            $selectedId = $breadCrumb[$depth-1]->getId();
        }else
        $selectedId = null;

        if($reverseOrder)
        $dbElements = $this->db->getElements($this->getName(), $parentElementId, $languageId, $this->currentPage?$this->currentPage->getId():null, $selectedId, 'desc', $startFrom, $limit, $includeHidden);
        else
        $dbElements = $this->db->getElements($this->getName(), $parentElementId, $languageId, $this->currentPage?$this->currentPage->getId():null, $selectedId, 'asc', $startFrom, $limit, $includeHidden);
        $elements = array();
        foreach($dbElements as $key => $dbElement) {
            $newElement = $this->makeElementFromDb($dbElement, sizeof($urlVars) == 1);

            if($selectedId == $dbElement['id'])
            $newElement->setSelected(1);
            else
            $newElement->setSelected(0);

            if($this->currentPage && $this->currentPage->getId() == $dbElement['id'])
            $newElement->setCurrent(1);
            else
            $newElement->setCurrent(0);
            $elements[] = $newElement;
        }

        foreach($elements as $key => $element) { //link generation optimization.
            if($elements[$key]->getType() == 'default')
            $elements[$key]->setLink(\Ip\Internal\Deprecated\Url::generate($languageId, $this->getName(), array_merge($urlVars, array($element->getUrl())), null));
        }

        return $elements;

    }



    function getPage($pageId) {
        $dbElement = $this->db->getElement($pageId);
        if($dbElement) {
            $dbParentElement = $this->db->getElement($dbElement['parent']);
            $element = $this->makeElementFromDb($dbElement, $dbParentElement['parent'] == null);
            return $element;
        } else {
            return false;
        }
    }



    function getFirstElement($parentId, $level) {

        $elements = $this->db->getElements($this->getName(), $parentId, ipGetCurrentLanguage()->getId(), null, null, 'asc', 0, null);
        foreach($elements as $key => $element) {
            switch($element['type']) {
                case 'inactive':
                case 'subpage':
                case 'redirect':
                    $subElement = $this->getFirstElement($element['id'], $level+1);
                    if($subElement) {
                        return $subElement;
                    }
                    break;
                case 'default':
                default:
                    return $this->makeElementFromDb($element, $level == 1);
                    break;
            }

        }
        return false;
    }

    function findPage($urlVars, $getVars) {
        $currentEl = null;

        $elId = $this->db->getRootElementId($this->getName(), ipGetCurrentLanguage()->getId());
        if ($elId) {
            if (sizeof($urlVars) == 0) {
                return $this->getFirstElement($elId, 1);
            } else {
                foreach ($urlVars as $value) {
                    $tmp = $this->db->getElementByUrl($value, $elId);
                    if ($tmp) {
                        $currentEl = $tmp;
                        $elId = $currentEl['id'];
                    } else {
                        return null;
                    }
                }
                return $this->makeElementFromDb($currentEl, sizeof($urlVars) == 0);
            }
        } else {
            return false;
            //trigger_error("Can't find menu element");
        }
    }



    private function makeElementFromDb($dbElement, $firstLevel) {
        $newElement = new \Ip\Page($dbElement['id'], $this->getName());
        $newElement->setButtonTitle($dbElement['button_title']);
        $newElement->setPageTitle($dbElement['page_title']);
        $newElement->setKeywords($dbElement['keywords']);
        $newElement->setDescription($dbElement['description']);
        $newElement->setUrl($dbElement['url']);
        $newElement->setText($dbElement['cached_text']);
        $newElement->setLastModified($dbElement['last_modified']);
        $newElement->setCreatedOn($dbElement['created_on']);
        $newElement->setModifyFrequency($dbElement['modify_frequency']);
        $newElement->setRss($dbElement['rss']);
        $newElement->setVisible($dbElement['visible']);
        if($firstLevel)
        $newElement->setParentId(null);
        else
        $newElement->setParentId($dbElement['parent']);
        $newElement->setHtml($dbElement['html']);
        $newElement->setType($dbElement['type']);
        $newElement->setRedirectUrl($dbElement['redirect_url']);
        return $newElement;
    }



}
