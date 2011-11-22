<?php

class Application_DataGrid_Column_CrudActions extends Application_DataGrid_Column
{

    protected $deleteActionName = 'delete';
    protected $updateActionName = 'edit';
    protected $deleteTitle = 'Delete';
    protected $updateTitle = 'Edit';
    protected $deleteIcon = '/images/icons/16/delete_16.png';
    protected $updateIcon = '/images/icons/16/edit_16.png';
    protected $keyName = 'id';

    /**
     * Can this column be sorted?
     * @var boolean
     */
    protected $allowSorting = false;
    
    public function getDeleteActionName()
    {
        return $this->deleteActionName;
    }

    public function setDeleteActionName($deleteActionName)
    {
        $this->deleteActionName = $deleteActionName;
    }

    public function getUpdateActionName()
    {
        return $this->updateActionName;
    }

    public function setUpdateActionName($updateActionName)
    {
        $this->updateActionName = $updateActionName;
    }

    public function getDeleteTitle()
    {
        return $this->deleteTitle;
    }

    public function setDeleteTitle($deleteTitle)
    {
        $this->deleteTitle = $deleteTitle;
    }

    public function getUpdateTitle()
    {
        return $this->updateTitle;
    }

    public function setUpdateTitle($updateTitle)
    {
        $this->updateTitle = $updateTitle;
    }

    public function getDeleteIcon()
    {
        return $this->deleteIcon;
    }

    public function setDeleteIcon($deleteIcon)
    {
        $this->deleteIcon = $deleteIcon;
    }

    public function getUpdateIcon()
    {
        return $this->updateIcon;
    }

    public function setUpdateIcon($updateIcon)
    {
        $this->updateIcon = $updateIcon;
    }

    public function getKeyName()
    {
        return $this->keyName;
    }

    public function setKeyName($keyName)
    {
        $this->keyName = $keyName;
    }

    public function render($data)
    {
        $retval = '';
        $retval .= $this->renderUpdate($data);
        $retval .= '&nbsp;';
        $retval .= $this->renderDelete($data);
        return $retval;
    }

    protected function renderUpdate($data)
    {
        if (isset($data[$this->getDataField()]))
        {
            return '<a class="twip" title="' . $this->updateTitle . '" href="' . $this->view->url(array('action' => $this->updateActionName, $this->keyName => $data[$this->getDataField()])) . '"><img src="' . $this->view->baseUrl() . $this->updateIcon . '" /></a>';
        }
        return $this->autoFillValue;
    }

    protected function renderDelete($data)
    {
        if (isset($data[$this->getDataField()]))
        {
            return '<a class="twip" title="' . $this->deleteTitle . '" href="' . $this->view->url(array('action' => $this->deleteActionName, $this->keyName => $data[$this->getDataField()])) . '"><img src="' . $this->view->baseUrl() . $this->deleteIcon . '" /></a>';
        }
        return $this->autoFillValue;
    }

}