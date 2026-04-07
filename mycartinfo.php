<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class MyCartInfo extends Module
{
    public function __construct()
    {
        $this->name = 'mycartinfo'; 
        $this->tab = 'front_office_features';
        $this->version = '1.1.0';
        $this->author = 'Troteseil Lucas';
        $this->need_instance = 0;
        $this->bootstrap = true;
	
	// URL GitHub du module
	$this->github = 'https://github.com/ton-compte/mycartinfo';

        parent::__construct();



        $this->displayName = $this->l('Mon Message Panier');
    $this->description = $this->l('Ajoute un message personnalisable dans le panier avec switch d\'activation et recommandations produits.');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        return parent::install() 
            && $this->registerHook('displayMyCartInfo')
            && Configuration::updateValue('MY_CART_INFO_ACTIVE', 1)
            && Configuration::updateValue('MY_CART_INFO_MSG', '<p>Votre message par défaut ici.</p>', true)
            && Configuration::updateValue('MY_CART_INFO_PROD_ACTIVE', 0)
            && Configuration::updateValue('MY_CART_INFO_PROD_IDS', '');
    }

    public function uninstall()
    {
        return Configuration::deleteByName('MY_CART_INFO_ACTIVE')
            && Configuration::deleteByName('MY_CART_INFO_MSG') 
            && Configuration::deleteByName('MY_CART_INFO_PROD_ACTIVE')
            && Configuration::deleteByName('MY_CART_INFO_PROD_IDS')
            && parent::uninstall();
    }

    // Affiche le message sur la page Panier via un hook personnalisé
    public function hookDisplayMyCartInfo($params)
    {
        $html = '';

        // 1. Affichage du message personnalisé
        if (Configuration::get('MY_CART_INFO_ACTIVE')) {
            $message = Configuration::get('MY_CART_INFO_MSG');
            if (!empty($message)) {
                $html .= '
                <div class="col-12 mt-3 mb-3">
                    <div class="alert alert-info custom-cart-message">' . $message . '</div>
                </div>';
            }
        }

        // 2. Affichage des produits suggérés
        if (Configuration::get('MY_CART_INFO_PROD_ACTIVE')) {
            $ids_string = Configuration::get('MY_CART_INFO_PROD_IDS');
            if (!empty($ids_string)) {
                $ids = array_filter(array_map('intval', explode(',', $ids_string)));
                $id_lang = $this->context->language->id;
                
                if (!empty($ids)) {
                    $html .= '<div class="col-12 mt-4 mb-3">';
                    $html .= '  <h4 style="font-weight: 600; margin-bottom: 20px;">'.$this->l('Vous aimerez aussi :').'</h4>';
                    $html .= '  <div class="row">';
                    
                    foreach ($ids as $id_product) {
                        $product = new Product((int)$id_product, false, $id_lang);
                        
                        if (Validate::isLoadedObject($product) && $product->active) {
                            // Gestion sécurisée de l'image
                            $cover = Product::getCover($product->id);
                            $id_image = (is_array($cover) && isset($cover['id_image'])) ? $cover['id_image'] : 0;
                            $image_params = $id_image ? $product->id . '-' . $id_image : $this->context->language->iso_code . '-default';
                            
                            // Gérer le cas où link_rewrite est un tableau (parfois le cas selon la config)
                            $link_rewrite = is_array($product->link_rewrite) ? $product->link_rewrite[$id_lang] : $product->link_rewrite;
                            if (empty($link_rewrite)) {
                                $link_rewrite = 'produit';
                            }

                            $img_url = $this->context->link->getImageLink($link_rewrite, $image_params, 'home_default');
                            $link = $this->context->link->getProductLink($product);
                            $price = Tools::displayPrice($product->getPrice(true)); // Afficher TTC
                            $token = Tools::getToken(false);
                            
                            $html .= '
                            <div class="col-md-4 col-sm-6 mb-3">
                                <div class="card h-100 text-center" style="border-radius:0px; overflow:hidden; border: 1px solid #eef0f3;">
                                    <a href="'.$link.'" style="display:block; background:#fff; padding:15px;">
                                        <img src="'.$img_url.'" class="card-img-top" alt="'.htmlspecialchars(is_array($product->name) ? $product->name[$id_lang] : $product->name).'" style="max-height:160px; object-fit:contain;">
                                    </a>
                                    <div class="card-body d-flex flex-column" style="padding: 15px; background: #fff;">
                                        <h5 class="card-title" style="font-size:14px; margin-bottom:10px; font-weight:600; text-wrap: balance;">
                                            <a href="'.$link.'" style="color:#363a41; text-decoration:none;">'.(is_array($product->name) ? $product->name[$id_lang] : $product->name).'</a>
                                        </h5>
                                        <p class="card-text fw-bold" style="color:#212529; font-size:16px; margin-bottom: 15px;">'.$price.'</p>
                                        <form action="'.$this->context->link->getPageLink('cart').'" method="post" class="mt-auto">
                                            <input type="hidden" name="token" value="'.$token.'">
                                            <input type="hidden" name="id_product" value="'.$product->id.'">
                                            <input type="hidden" name="id_customization" value="0">
                                            <input type="hidden" name="add" value="1">
                                            <input type="hidden" name="action" value="update">
                                            <button type="submit" class="btn btn-primary btn-sm w-100" data-button-action="add-to-cart" style="border-radius:0px; display:flex; align-items:center; justify-content:center; gap:5px; width: 100%;">
                                                '.$this->l('Ajouter').'
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>';
                        }
                    }
                    
                    $html .= '  </div>';
                    $html .= '</div>';
                }
            }
        }

        return $html;
    }

    // Cette fonction permet d'afficher le bouton "Configurer"
    
    public function getContent()
    {
        $output = '';
        
        // Traitement de l'enregistrement du formulaire
        $output .= $this->postProcess();

        // Ajout du bloc d'information personnalisé au-dessus du formulaire
        $output .= $this->renderInfoBlock();

        // Ajout du formulaire
        $output .= $this->renderForm();

        return $output;
    }

    // Génère un bel encadré d'information avec un style Bento moderne
    protected function renderInfoBlock()
    {
        return '
        <style>
            .bento-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 20px;
                margin-bottom: 20px;
            }
            .bento-card {
                background: #ffffff;
                border-radius: 20px;
                padding: 24px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
                border: 1px solid #eef0f3;
                transition: transform 0.2s ease, box-shadow 0.2s ease;
                display: flex;
                flex-direction: column;
            }
            .bento-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            }
            .bento-icon-wrapper {
                width: 48px;
                height: 48px;
                background: #f4f6fe;
                color: #25b9d7;
                border-radius: 14px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                margin-bottom: 16px;
            }
            .bento-card h4 {
                font-size: 16px;
                font-weight: 600;
                color: #363a41;
                margin: 0 0 10px 0;
            }
            .bento-card p {
                color: #6c868e;
                font-size: 13px;
                line-height: 1.5;
                margin-bottom: 15px;
            }
            .bento-card ul.bento-instructions {
                padding-left: 20px;
                color: #6c868e;
                font-size: 13px;
                margin-bottom: 10px;
            }
            .bento-code {
                background: #f8f9fa;
                border-radius: 8px;
                padding: 10px;
                font-family: monospace;
                color: #e83e8c;
                border: 1px solid #e9ecef;
                display: block;
                margin-top: auto;
            }
            
            /* Styliser le panel PrestaShop existant (celui du HelperForm) pour coller au style Bento */
            #configuration_form {
                border-radius: 20px !important;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04) !important;
                border: 1px solid #eef0f3 !important;
                padding: 20px !important;
            }
            #configuration_form .panel-heading {
                background: transparent !important;
                border-bottom: 1px solid #eef0f3 !important;
                padding-bottom: 15px !important;
                margin-bottom: 20px !important;
                font-weight: 600 !important;
                color: #363a41 !important;
                border-radius: 0 !important;
                height: auto !important;
            }
        </style>

        <div class="bento-grid">
            <!-- Carte 1 : Informations générales -->
            <div class="bento-card">
                <div class="bento-icon-wrapper">
                    <i class="icon-info"></i>
                </div>
                <h4>'.$this->displayName.'</h4>
                <p>'.$this->description.'</p>
                <div style="margin-top: auto; display: flex; flex-direction: column; gap: 10px;">
                    <div style="display: flex; align-items: center;">
                        <img src="'.$this->_path.'logo.png" style="width: 40px; height: 40px; border-radius: 10px; margin-right: 15px; object-fit: contain; border: 1px solid #eef0f3; padding: 3px;" alt="Logo"/>
                        <a href="https://github.com/Lucas-tsl/mycartinfo" target="_blank" class="btn btn-default" style="border-radius: 8px; font-weight: 600; font-size: 11px; padding: 4px 8px;">
                            <i class="icon-github"></i> Repository
                        </a>
                        <a href="https://few-volleyball-409.notion.site/Guide-Utilisateur-Modifier-le-message-du-Panier-32829fb0a29c807ca492fecc1e508c7c?pvs=74" target="_blank" class="btn btn-default" style="border-radius: 8px; font-weight: 600; font-size: 11px; padding: 4px 8px; margin-left: 5px; color: #000; background-color: #f7f7f7;">
                            <i class="icon-book"></i> Notice
                        </a>
                    </div>
                </div>
            </div>

            <!-- Carte 2 : Instruction d\'intégration -->
            <div class="bento-card">
                <div class="bento-icon-wrapper" style="background: #fff4eb; color: #ff9800;">
                    <i class="icon-code"></i>
                </div>
                <h4>'.$this->l('Comment l\'utiliser ?').'</h4>
                <p>Modifiez le message dans le formulaire en bas puis ajoutez la balise dans votre thème :</p>
                <ul class="bento-instructions">
                    <li>'.$this->l('Ouvrez le fichier `cart.tpl` ou similaire.').'</li>
                    <li>'.$this->l('Placez ce code à l\'endroit désiré :').'</li>
                </ul>
                <code class="bento-code">{hook h=\'displayMyCartInfo\'}</code>
            </div>

            <!-- Carte 3 : Informations développeur -->
            <div class="bento-card" style="background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);">
                <div class="bento-icon-wrapper" style="background: white; color: #00c853;">
                    <i class="icon-user"></i>
                </div>
                <h4>'.$this->l('Développeur').'</h4>
                <p>Ce module a été développé avec soin pour vous aider à mieux communiquer avec vos clients.</p>
                <div style="margin-top: auto; padding-top: 15px; border-top: 1px dashed rgba(0,0,0,0.1);">
                    <p style="margin: 0; font-size: 14px; color: #363a41;"><strong>Créateur :</strong> '.$this->author.'</p>
                    <p style="margin: 5px 0 0 0; font-size: 12px; color: #6c868e;">Version '.$this->version.'</p>
                </div>
            </div>
        </div>';
    }

    protected function renderForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->submit_action = 'submitMyCartConfig';

        $fields_form = [
            'form' => [
                'legend' => ['title' => $this->l('Configuration'), 'icon' => 'icon-cogs'],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Activer le message'),
                        'name' => 'MY_CART_INFO_ACTIVE',
                        'is_bool' => true,
                        'desc' => $this->l('Afficher ou masquer l\'encart promotionnel dans le panier'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Oui')
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Non')
                            ]
                        ]
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Votre message'),
                        'name' => 'MY_CART_INFO_MSG',
                        'autoload_rte' => true, // Active l'éditeur de texte riche
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Activer les recommandations de produits'),
                        'name' => 'MY_CART_INFO_PROD_ACTIVE',
                        'is_bool' => true,
                        'desc' => $this->l('Affiche une sélection de produits en dessous du message dans le panier.'),
                        'values' => [
                            [
                                'id' => 'prod_active_on',
                                'value' => 1,
                                'label' => $this->l('Oui')
                            ],
                            [
                                'id' => 'prod_active_off',
                                'value' => 0,
                                'label' => $this->l('Non')
                            ]
                        ]
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('ID des produits à afficher'),
                        'name' => 'MY_CART_INFO_PROD_IDS',
                        'desc' => $this->l('Saisissez les ID des produits séparés par une virgule (ex: 1,5,8). Idéalement 3 produits pour un affichage optimal.'),
                    ],
                ],
                'submit' => ['title' => $this->l('Enregistrer'), 'class' => 'btn btn-default pull-right']
            ],
        ];

        $helper->fields_value['MY_CART_INFO_ACTIVE'] = Configuration::get('MY_CART_INFO_ACTIVE');
        $helper->fields_value['MY_CART_INFO_MSG'] = Configuration::get('MY_CART_INFO_MSG');
        $helper->fields_value['MY_CART_INFO_PROD_ACTIVE'] = Configuration::get('MY_CART_INFO_PROD_ACTIVE');
        $helper->fields_value['MY_CART_INFO_PROD_IDS'] = Configuration::get('MY_CART_INFO_PROD_IDS');
        return $helper->generateForm([$fields_form]);
    }

    protected function postProcess()
    {
        if (Tools::isSubmit('submitMyCartConfig')) {
            Configuration::updateValue('MY_CART_INFO_ACTIVE', Tools::getValue('MY_CART_INFO_ACTIVE'));
            Configuration::updateValue('MY_CART_INFO_PROD_ACTIVE', Tools::getValue('MY_CART_INFO_PROD_ACTIVE'));
            Configuration::updateValue('MY_CART_INFO_PROD_IDS', Tools::getValue('MY_CART_INFO_PROD_IDS'));
            Configuration::updateValue('MY_CART_INFO_MSG', Tools::getValue('MY_CART_INFO_MSG'), true);
            return $this->displayConfirmation($this->l('C\'est enregistré !'));
        }
    }
}