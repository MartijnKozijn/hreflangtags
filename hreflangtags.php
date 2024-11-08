<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class HreflangTags extends Module
{
    public function __construct()
    {
        $this->name = 'hreflangtags';
        $this->version = '1.0.1';
        $this->author = 'Jaymian-Lee Reinartz';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Hreflang Tags Module');
        $this->description = $this->l('Voegt hreflang tags toe aan de website.');
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayHeader')
            && $this->installDb();
    }

    public function installDb()
    {
        return Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hreflang_tags` (
                `id_hreflang` int(11) NOT NULL AUTO_INCREMENT,
                `url` varchar(255) NOT NULL,
                `locale` varchar(5) NOT NULL,
                PRIMARY KEY (`id_hreflang`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
        ');
    }

    public function uninstall()
    {
        return parent::uninstall()
            && Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'hreflang_tags`');
    }

    public function getContent()
    {
        $output = '';

        // Verwerken van bewerken of verwijderen acties
        if (Tools::isSubmit('updatehreflang_tags') || Tools::isSubmit('addhreflang_tags')) {
            $output .= $this->renderForm();
        } elseif (Tools::isSubmit('deletehreflang_tags')) {
            $this->processDelete();
            $output .= $this->renderList();
        } elseif (Tools::isSubmit('submitHreflangTags')) {
            $this->processSave();
            $output .= $this->renderList();
        } else {
            $output .= $this->renderList();
            $output .= $this->renderForm();
        }

        return $output;
    }

    protected function processSave()
    {
        $id_hreflang = (int) Tools::getValue('id_hreflang');
        $url = Tools::getValue('HREFLANG_URL');
        $locale = Tools::getValue('HREFLANG_LOCALE');

        if ($id_hreflang) {
            // Bewerken
            Db::getInstance()->update('hreflang_tags', [
                'url' => pSQL($url),
                'locale' => pSQL($locale)
            ], 'id_hreflang = ' . $id_hreflang);
            $this->context->controller->confirmations[] = $this->l('Hreflang tag bijgewerkt.');
        } else {
            // Toevoegen
            Db::getInstance()->insert('hreflang_tags', [
                'url' => pSQL($url),
                'locale' => pSQL($locale)
            ]);
            $this->context->controller->confirmations[] = $this->l('Hreflang tag toegevoegd.');
        }
    }

    protected function processDelete()
    {
        $id_hreflang = (int) Tools::getValue('id_hreflang');
        if ($id_hreflang) {
            Db::getInstance()->delete('hreflang_tags', 'id_hreflang = ' . $id_hreflang);
            $this->context->controller->confirmations[] = $this->l('Hreflang tag verwijderd.');
        }
    }

    protected function renderForm()
    {
        $id_hreflang = (int) Tools::getValue('id_hreflang');
        $hreflang = $id_hreflang ? Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . 'hreflang_tags WHERE id_hreflang = ' . $id_hreflang) : null;

        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $id_hreflang ? $this->l('Hreflang Tag Bewerken') : $this->l('Nieuwe Hreflang Tag'),
                    'icon' => 'icon-cogs'
                ],
                'input' => [
                    [
                        'type' => 'hidden',
                        'name' => 'id_hreflang',
                        'value' => $id_hreflang
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('URL'),
                        'name' => 'HREFLANG_URL',
                        'required' => true,
                        'col' => 6,
                        'value' => $hreflang ? $hreflang['url'] : '',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Taalcode (locale)'),
                        'name' => 'HREFLANG_LOCALE',
                        'required' => true,
                        'col' => 2,
                        'desc' => $this->l('Bijv. nl-NL, fr-FR'),
                        'value' => $hreflang ? $hreflang['locale'] : '',
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Opslaan'),
                    'class' => 'btn btn-default pull-right'
                ]
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = 'hreflang_tags';
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = $this->context->language->id;
        $helper->identifier = 'id_hreflang';
        $helper->submit_action = 'submitHreflangTags';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        if ($id_hreflang) {
            $helper->currentIndex .= '&id_hreflang=' . $id_hreflang;
        }
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        return $helper->generateForm([$fields_form]);
    }

    protected function renderList()
    {
        $hreflangs = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'hreflang_tags');

        $fields_list = [
            'id_hreflang' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'width' => 25
            ],
            'url' => [
                'title' => $this->l('URL'),
                'width' => 'auto'
            ],
            'locale' => [
                'title' => $this->l('Locale'),
                'width' => 50
            ],
        ];

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = true;
        $helper->actions = ['edit', 'delete'];
        $helper->identifier = 'id_hreflang';
        $helper->title = $this->l('Hreflang Tags');
        $helper->table = 'hreflang_tags';
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        return $helper->generateList($hreflangs, $fields_list);
    }

    public function hookDisplayHeader()
    {
        $hreflangs = Db::getInstance()->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'hreflang_tags');
        $this->context->smarty->assign('hreflangs', $hreflangs);
        return $this->display(__FILE__, 'views/templates/hook/hreflangtags.tpl');
    }
}
