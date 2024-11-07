<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class HreflangTags extends Module
{
    public function __construct()
    {
        $this->name = 'hreflangtags';
        $this->version = '1.0.0';
        $this->author = 'Jaymian-Lee Reinartz';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Hreflang Tags Module');
        $this->description = $this->l('Voegt hreflang tags toe aan de website.');
    }

    public function install()
    {
        return parent::install() && $this->registerHook('displayHeader') && $this->installDb();
    }

    public function installDb()
    {
        return Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'hreflang_tags` (
                `id_hreflang` int(11) NOT NULL AUTO_INCREMENT,
                `url` varchar(255) NOT NULL,
                `locale` varchar(5) NOT NULL,
                PRIMARY KEY (`id_hreflang`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;
        ');
    }

    public function uninstall()
    {
        return parent::uninstall() && Db::getInstance()->execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'hreflang_tags`');
    }

    public function getContent()
    {
        $output = '';
        
        // Opslaan van nieuwe of bewerkte hreflang tag
        if (Tools::isSubmit('submitHreflangTags')) {
            $url = Tools::getValue('HREFLANG_URL');
            $locale = Tools::getValue('HREFLANG_LOCALE');
            
            if (Tools::getValue('id_hreflang')) {
                // Bewerken
                Db::getInstance()->update('hreflang_tags', [
                    'url' => pSQL($url),
                    'locale' => pSQL($locale)
                ], 'id_hreflang = ' . (int)Tools::getValue('id_hreflang'));
                $output .= $this->displayConfirmation($this->l('Hreflang tag bijgewerkt.'));
            } else {
                // Toevoegen
                Db::getInstance()->insert('hreflang_tags', [
                    'url' => pSQL($url),
                    'locale' => pSQL($locale)
                ]);
                $output .= $this->displayConfirmation($this->l('Hreflang tag toegevoegd.'));
            }
        }
        
        // Verwijderen van een hreflang tag
        if (Tools::isSubmit('deleteHreflang')) {
            Db::getInstance()->delete('hreflang_tags', 'id_hreflang = ' . (int)Tools::getValue('id_hreflang'));
            $output .= $this->displayConfirmation($this->l('Hreflang tag verwijderd.'));
        }
        
        // Render de form en overzichtslijst
        $output .= $this->renderForm();
        $output .= $this->renderList();
        
        return $output;
    }

    protected function renderForm()
    {
        $id_hreflang = (int)Tools::getValue('id_hreflang');
        $hreflang = $id_hreflang ? Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'hreflang_tags WHERE id_hreflang = '.$id_hreflang) : null;
        
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Hreflang Tag Instellingen'),
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
                        'value' => $hreflang ? $hreflang['url'] : '',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Taalcode (locale)'),
                        'name' => 'HREFLANG_LOCALE',
                        'required' => true,
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
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitHreflangTags';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        return $helper->generateForm([$fields_form]);
    }

    protected function renderList()
    {
        $hreflangs = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'hreflang_tags');
        
        $fields_list = [
            'id_hreflang' => [
                'title' => $this->l('ID'),
                'type' => 'text',
            ],
            'url' => [
                'title' => $this->l('URL'),
                'type' => 'text',
            ],
            'locale' => [
                'title' => $this->l('Locale'),
                'type' => 'text',
            ],
        ];
        
        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = true;
        $helper->actions = ['edit', 'delete'];
        $helper->identifier = 'id_hreflang';
        $helper->title = $this->l('Hreflang Tags');
        $helper->table = 'hreflang_tags';
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        
        return $helper->generateList($hreflangs, $fields_list);
    }

    public function hookDisplayHeader()
    {
        $hreflangs = Db::getInstance()->executeS('SELECT * FROM '._DB_PREFIX_.'hreflang_tags');
        $this->context->smarty->assign('hreflangs', $hreflangs);
        return $this->display(__FILE__, 'views/templates/hook/hreflangtags.tpl');
    }
}
