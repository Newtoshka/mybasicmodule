<?php

/**
 * 2007-2019 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author PrestaShop SA <contact@prestashop.com>
 * @copyright  2007-2019 PrestaShop SA
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

 use PrestaShop\PrestaShop\Adapter\Entity\Configuration;
 use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
 
 if (!defined('_PS_VERSION_')) {
     exit;
 }

class MyBasicModule extends Module implements WidgetInterface
{
    // Constructor
    public function __construct()
    {
        $this->name = 'mybasicmodule';
        $this->tab = 'front_office_features';
        $this->version = '1.0';
        $this->author = 'WebAxe';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.6',
            'max' => _PS_VERSION_,
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l("My first module");
        $this->description = $this->l("This is my first module");
        $this->confirmUninstall = $this->l("Are u crazy?");
    }

    // Install method
    public function install()
    {
        return
            $this->installtab() &&
            $this->sqlInstall() &&
            parent::install() &&
            $this->registerHook('registerGDPRConsent') &&
            $this->registerHook('moduleRoutes') &&
            $this->registerHook('displayNavFullWidth');
    }

    // Uninstall method
    public function uninstall()
    {
        return parent::uninstall() &&
            $this->sqlUninstall() &&
            $this->uninstalltab();
    }

    protected function sqlInstall()
    {
        $sqlCreate = "CREATE TABLE `" . _DB_PREFIX_ . "testcomment` (
             `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
             `user_id` varchar(255) DEFAULT NULL,
             `comment` varchar(255) DEFAULT NULL,
             PRIMARY KEY (`id`)
         ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

        return \Db::getInstance()->execute($sqlCreate);
    }

    protected function sqlUninstall()
    {
        $sql = "DROP TABLE " . _DB_PREFIX_ . "testcomment";
        return Db::getInstance()->execute($sql);
    }

    public function installtab()
    {
        $idTab = (int) \Tab::getIdFromClassName('AdminTest');
        if ($idTab) {
            // Tab already exists
            return true;
        }

        $tab = new \Tab();
        $tab->class_name = 'AdminTest';
        $tab->module = $this->name;
        $tab->id_parent = (int) \Tab::getIdFromClassName("DEFAULT");
        $tab->icon = "settings_applications";

        $languages = \Language::getLanguages();
        foreach ($languages as $lang) {
            $tab->name[$lang['id_lang']] = $this->l("TEST Admin controller");
        }

        try {
            $tab->save();
            return true;
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function uninstalltab()
    {
        $idTab = (int) \Tab::getIdFromClassName('AdminTest');

        if ($idTab) {
            $tab = new \Tab($idTab);
            try {
                $tab->delete();
                return true;
            } catch (Exception $e) {
                echo $e->getMessage();
                return false;
            }
        }
        return true;
    }

    public function renderWidget($hookName, array $configuration)
    {
        echo $this->context->link->getModuleLink($this->name, "test");

        if ($hookName == "displayNavFullWidth") {
            return '<br>hi';
        }

        $this->context->smarty->assign($this->getWidgetVariables($hookName, $configuration));

        return $this->fetch("module:mybasicmodule/views/templates/hook/footer.tpl");
    }

    public function getWidgetVariables($hookName, array $configuration)
    {
        return [
            'mytest' => 'Presta dev',
            'idcart' => $this->context->cart->id
        ];
    }

    public function getContent()
    {
        $output = "";

        if (Tools::isSubmit('submit' . $this->name)) {
            $courserating = Tools::getValue('courserating');

            if ($courserating && !empty($courserating) && Validate::isGenericName($courserating)) {
                Configuration::updateValue('COURSE_RATING', Tools::getValue("courserating"));

                $output .= $this->displayConfirmation($this->l('Form submitted'));
            } else {
                $output .= $this->displayError($this->l('Form not submitted'));
            }
        }

        return $output . $this->displayForm();
    }

    public function displayForm()
    {
        $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');

        $form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Rating seting'),
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('COURSE RATING'),
                        'name' => 'courserating',
                        'size' => 20,
                        'required' => true,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ]
        ];

        $helper = new \HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->table = $this->table;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = \AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->name]);
        $helper->submit_action = 'submit' . $this->name;

        // Default language
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');

        // Load current value into the form
        $helper->fields_value['courserating'] = Configuration::get('COURSE_RATING');

        return $helper->generateForm([$form]);
    }

    public function hookModuleRoutes($params)
    {

        return [
            'test' => [
                'controller' => 'test',
                'rule' => 'fc-test',
                'keywords' => [],
                'params' => [
                    'fc' => 'module',
                    'module' => $this->name,
                    'controller' => 'test',
                ]
            ]
        ];
    }
}
