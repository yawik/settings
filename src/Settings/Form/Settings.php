<?php

namespace Settings\Form;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Core\Entity\Hydrator\EntityHydrator;
use Zend\Form\Form;
use Zend\Form\Element;
use Zend\Stdlib\Hydrator\ClassMethods;
use Settings\Entity\Settings as SettingsEntity;
use Zend\Stdlib\Hydrator\ArraySerializable;

class Settings extends Form implements ServiceLocatorAwareInterface {
	
	protected $forms;

	public function __construct($name = null) {
		parent::__construct('settings');
		$this->setAttribute('method', 'post');
	
		// 'action' => $this->url('lang/settings', array(), true),
	}
	
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		$this->forms = $serviceLocator;
		return $this;
	}
	
	public function getServiceLocator()
	{
		return $this->forms;
	}
    
    public function getHydrator() {
        if (!$this->hydrator) {
            $hydrator = new ArraySerializable();
            $this->setHydrator($hydrator);
        }
        return $this->hydrator;
    }
    
    protected function getPlugin($name) {
        $plugin = Null;
        $factory = $this->getFormFactory();
        $formElementManager = $factory->getFormElementManager();
        if (isset($formElementManager)) {
            $serviceLocator = $formElementManager->getServiceLocator();
            $viewhelpermanager = $serviceLocator->get('viewhelpermanager');
            if (isset($viewhelpermanager)) {
                $plugin = $viewhelpermanager->get($name);
            }
        }
        return $plugin;
    }

    public function init() {

        $this->setName('setting-core');
        
        $plugin = $this->getPlugin('url');
        $url = call_user_func_array($plugin, array(null, array('lang' => 'de')));
        $this->setAttribute('action', $url);
        
        //->setHydrator(new ModelHydrator())
        //->setObject(new SettingsEntity());
        
        $this->add(
        		$this->forms->get('settings-core-fieldset')
        		->setUseAsBaseFieldset(true)
        );
                
        $this->add($this->forms->get('DefaultButtonsFieldset'));

    }

}
