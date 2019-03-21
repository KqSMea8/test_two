<?php
namespace Admin\Controller;

use Admin\Builder\AdminConfigBuilder;


/**
 * Class OperationController  运维控制器
 * @package Admin\Controller
 * @author:HQ
 */
class OperationController extends AdminController
{

    public function index()
    {
        $this->redirect('Adv/adv');
    }
}
