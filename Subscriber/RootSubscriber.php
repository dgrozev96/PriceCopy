<?php
namespace PriceCopy\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\Components\Theme\LessDefinition;
use Doctrine\Common\Collections\ArrayCollection;
use Enlight_Template_Manager;

class RootSubscriber implements SubscriberInterface
{
    private $pluginName;
    private $pluginDirectory;
    protected $request;

    /**
     * @var Enlight_Template_Manager
     */
    private $template;

    public function __construct($pluginName, $pluginDirectory, Enlight_Template_Manager $template)
    {
        $this->pluginName = $pluginName;
        $this->pluginDirectory = $pluginDirectory;
        $this->template = $template;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_PriceCopyModulePlainHtml' => 'onGetBackendController'
        ];
    }

    /**
     * @return string
     */
    public function onGetBackendController()
    {
        return __DIR__ . '/../Controllers/Backend/PriceCopyModulePlainHtml.php';
    }

    private function getConfig()
    {
        return Shopware()->Container()->get('shopware.plugin.config_reader')->getByPluginName($this->pluginName, Shopware()->Shop());
    }
}