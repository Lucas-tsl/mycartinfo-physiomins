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
        $this->version = '1.0.0';
        $this->author = 'Troteseil Lucas';
        $this->need_instance = 0;
        $this->bootstrap = true;
	
	// URL GitHub du module
	$this->github = 'https://github.com/ton-compte/mycartinfo';

        parent::__construct();



        $this->displayName = $this->l('Mon Message Panier');
        $this->description = $this->l('Ajoute un champ texte modifiable pour le panier.');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        return parent::install() 
            && $this->registerHook('displayMyCartInfo')
            && Configuration::updateValue('MY_CART_INFO_MSG', '<p>Votre message par défaut ici.</p>', true);
    }

    public function uninstall()
    {
        return Configuration::deleteByName('MY_CART_INFO_MSG') 
            && parent::uninstall();
    }

    // Affiche le message sur la page Panier via un hook personnalisé
    public function hookDisplayMyCartInfo($params)
    {
        $message = Configuration::get('MY_CART_INFO_MSG');
        
        if (empty($message)) {
            return '';
        }

        // On retourne le message encadré dans une col-12 pour respecter la grille (.row)
        return '
        <div class="col-12 mt-3 mb-3">
            <div class="alert alert-info custom-cart-message">' . $message . '</div>
        </div>';
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

    // Génère un bel encadré d'information Bootstrap natif PrestaShop
    protected function renderInfoBlock()
    {
        return '
        <div class="panel">
            <div class="panel-heading"><i class="icon-info"></i> '.$this->l('À propos de ce module').'</div>
            <div class="row">
                <div class="col-md-2 text-center" style="display: flex; align-items: center; justify-content: center;">
                    <img src="'.$this->_path.'logo.png" style="max-width: 100%; height: auto;" alt="Logo Module"/> 
                    <br>
                    <a href="https://openclassrooms.com/fr/" style="padding-left: 30px;">Accédez au repository GitHub</a>
                    
                </div>
                <div class="col-md-10">
                    <h4><strong>'.$this->displayName.'</strong></h4>
                    <p>'.$this->description.'</p>
                    <p><strong>'.$this->l('Comment l\'utiliser ?').'</strong></p>
                    <ol>
                        <li>'.$this->l('Rédigez votre message ci-dessous.').'</li>
                        <li>'.$this->l('Ajoutez la balise  `{hook h=\'displayMyCartInfo\'}` dans votre fichier `cart.tpl` à l\'endroit désiré.').'</li>
                    </ol>
                    <p class="text-muted"><small><em>'.$this->l('Développé avec soin par').' '.$this->author.' - Version '.$this->version.'</em></small></p>
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
                        'type' => 'textarea',
                        'label' => $this->l('Votre message'),
                        'name' => 'MY_CART_INFO_MSG',
                        'autoload_rte' => true, // Active l'éditeur de texte riche
                    ],
                ],
                'submit' => ['title' => $this->l('Enregistrer'), 'class' => 'btn btn-default pull-right']
            ],
        ];

        $helper->fields_value['MY_CART_INFO_MSG'] = Configuration::get('MY_CART_INFO_MSG');
        return $helper->generateForm([$fields_form]);
    }

    protected function postProcess()
    {
        if (Tools::isSubmit('submitMyCartConfig')) {
            Configuration::updateValue('MY_CART_INFO_MSG', Tools::getValue('MY_CART_INFO_MSG'), true);
            return $this->displayConfirmation($this->l('C\'est enregistré !'));
        }
    }
}