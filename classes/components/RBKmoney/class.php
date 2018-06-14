<?php

class RBKmoney extends def_module
{

    public function __construct()
    {
        parent::__construct();

        // В зависимости от режима работы системы
        if (cmsController::getInstance()->getCurrentMode() === 'admin') {
            $this->initTabs();
            $this->includeAdminClasses();
        }

        $this->includeCommonClasses();

        include 'src/settings.php';

        if (getRequest('action') === RBK_MONEY_CALLBACK_ACTION) {
            include 'RBKmoneyCallback.php';
        }

        if (getRequest('action') === RBK_MONEY_RECURRENT_ACTION) {
            include 'recurrentCron.php';
        }
    }

    /**
     * Создает вкладки административной панели модуля
     */
    protected function initTabs()
    {
        $configTabs = $this->getConfigTabs();

        if ($configTabs instanceof iAdminModuleTabs) {
            $configTabs->add('config');
        }

        $commonTabs = $this->getCommonTabs();

        if ($commonTabs instanceof iAdminModuleTabs) {
            $commonTabs->add('settings');
            $commonTabs->add('page_transactions');
            $commonTabs->add('page_recurrent');
            $commonTabs->add('recurrent_items');
            $commonTabs->add('logs');
        }
    }

    /**
     * Подключает классы функционала административной панели
     */
    protected function includeAdminClasses()
    {
        $this->__loadLib('admin.php');
        $this->__implement('RBKmoneyAdmin');

        $this->loadAdminExtension();
    }

    /**
     * Подключает общие классы функционала
     */
    protected function includeCommonClasses()
    {
        $this->loadCommonExtension();
        $this->loadTemplateCustoms();
    }
}
