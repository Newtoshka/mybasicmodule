<?php

require_once(_PS_MODULE_DIR_ . 'mybasicmodule/classes/comment.class.php');

use PrestaShop\PrestaShop\Adapter\Entity\ModuleAdminController;

class AdminTestController extends ModuleAdminController
{

    public function initContent()
    {
        parent::initContent();
        $content = $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'mybasicmodule/views/templates/admin/configuration.tpl'
        );

        $this->context->smarty->assign(
            array(
                'content' => $this->content . $content,
            )
        );
    }

    public function __construct()
    {
        $this->table = 'testcomment';
        $this->className = 'CommentTest';
        $this->identifier = CommentTest::$definition['primary'];
        $this->bootstrap = true;

        $this->fields_list = [
            'id' => [
                'title' => 'The id',
                'align' => 'left'

            ],
            'user_id' => [
                'title' => 'The user id',
                'align' => 'left'

            ],
            'comment' => [
                'title' => 'The comment',
                'align' => 'left'

            ]
        ];

        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->addRowAction('view');

        parent::__construct();
    }

    public function renderForm()
    {
        $this->fields_form = [
            'legend' => [
                'title' => 'New comment',
                'icon' => 'icon-cog'
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => 'The user',
                    'name' => 'user_id',
                    'class' => 'input fixed-with-sm',
                    'required' => true,
                    'empty_message'=> 'Please fill the input'
                ],
                [
                    'type' => 'text',
                    'label' => 'The comment',
                    'name' => 'comment',
                    'class' => 'input fixed-with-sm',
                    'required' => true,
                    'empty_message'=> 'Please fill the input'
                ]
            ],
            'submit' => [
                'title' => 'Submit'
            ]  
        ];

        return parent::renderForm();
    }

    public function renderView()
    {
        $tplFile = dirname(__FILE__) . '/../../views/templates/admin/view.tpl';
        $tpl = $this->context->smarty->createTemplate($tplFile);

        $sql = new DbQuery();
        $sql->select('*')
            ->from($this->table)
            ->where('id', Tools::getValue('id'));

        $data = Db::getInstance()->executeS($sql);

        $tpl->assign([
            'data' => $data
        ]);

        return $tpl->fetch();
    }
}
