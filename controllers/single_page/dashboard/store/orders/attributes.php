<?php
namespace Concrete\Package\CommunityStore\Controller\SinglePage\Dashboard\Store\Orders;

use Concrete\Core\Attribute\Key\Category;
use Concrete\Core\Attribute\Type;
use Concrete\Core\Page\Controller\DashboardAttributesPageController;

class Attributes extends DashboardAttributesPageController
{
    protected function getCategoryObject()
    {
        return Category::getByHandle('store_order');
    }

    public function view()
    {
        $this->renderList();
    }

    public function edit($akID = null)
    {
        $key = $this->getCategoryObject()->getController()->getByID($akID);
        $this->renderEdit($key,
            \URL::to('/dashboard/store/orders/attributes', 'view')
        );
    }

    public function update($akID = null)
    {
        $this->edit($akID);
        $key = $this->getCategoryObject()->getController()->getByID($akID);
        $this->executeUpdate($key,
            \URL::to('/dashboard/store/orders/attributes', 'view')
        );
    }

    public function select_type($type = null)
    {
        $type = Type::getByID($type);
        $this->renderAdd($type,
            \URL::to('/dashboard/store/orders/attributes', 'view')
        );
    }

    public function add($type = null)
    {
        $this->select_type($type);
        $type = Type::getByID($type);
        $this->executeAdd($type, \URL::to('/dashboard/store/orders/attributes', 'view'));
    }

    public function delete($akID = null)
    {
        $key = $this->getCategoryObject()->getController()->getByID($akID);
        $this->executeDelete($key,
            \URL::to('/dashboard/store/orders/attributes', 'view')
        );
    }
}