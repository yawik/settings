<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

/** Settings controller */
namespace Settings\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\EventManager\Event;

/**
 * Main Action Controller for Settings module
 *
 */
class IndexController extends AbstractActionController
{
    /**
     * attaches further Listeners for generating / processing the output
     *
     * @return $this
     */
    public function attachDefaultListeners()
    {
        parent::attachDefaultListeners();
        $serviceLocator  = $this->serviceLocator;
        $defaultServices = $serviceLocator->get('DefaultListeners');
        $events          = $this->getEventManager();
        $events->attach($defaultServices);
        return $this;
    }

    public function indexAction()
    {
        $services = $this->serviceLocator;
        $translator = $services->get('translator');
        $moduleName = $this->params('module', 'Core');
        
        $settings = $this->settings($moduleName);
        $jsonFormat = 'json' == $this->params()->fromQuery('format');
        if (!$this->getRequest()->isPost() && $jsonFormat) {
            return $settings->toArray();
        }
        
        $mvcEvent = $this->getEvent();
        $mvcEvent->setParam('__settings_active_module', $moduleName);
        
        $formManager = $this->serviceLocator->get('FormElementManager');
        $formName = $moduleName . '/SettingsForm';
        if (!$formManager->has($formName)) {
            $formName = "Settings/Form";
        }
        
        // Fetching an distinct Settings
        
        
        // Write-Access is per default only granted to the own module - change that
        $settings->enableWriteAccess();
        
        $form = $formManager->get($formName);
        
        // Binding the Entity to the Formular
        $form->bind($settings);
        $data = $this->getRequest()->getPost();
        if (0 < count($data)) {
            $form->setData($data);
            
            $valid   = $form->isValid();
            $partial = $services->get('viewhelpermanager')->get('partial');
            $text    = $valid
                     ?  /*@translate*/'Changes successfully saved'
                     :  /*@translate*/'Changes could not be saved';

            $vars = array();
            $this->notification()->success($translator->translate($text));

            if ($valid) {
                $event = new Event(
                    'SETTINGS_CHANGED',
                    $this,
                    array('settings' => $settings)
                );
                $this->getEventManager()->trigger($event);
            } else {
                $vars['error'] = $form->getMessages();
            }
            
            return new JsonModel($vars);
        }

        $vars['form']=$form;
        return $vars;
    }
}
