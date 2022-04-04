<?php
namespace modules;

use Craft;
use craft\base\Element;
use craft\elements\User;
use craft\events\ModelEvent;
use craft\helpers\ElementHelper;
use yii\base\Event;

/**
 * Custom module class.
 *
 * This class will be available throughout the system via:
 * `Craft::$app->getModule('my-module')`.
 *
 * You can change its module ID ("my-module") to something else from
 * config/app.php.
 *
 * If you want the module to get loaded on every request, uncomment this line
 * in config/app.php:
 *
 *     'bootstrap' => ['my-module']
 *
 * Learn more about Yii module development in Yii's documentation:
 * http://www.yiiframework.com/doc-2.0/guide-structure-modules.html
 */
class Module extends \yii\base\Module
{
    /**
     * Initializes the module.
     */
    public function init()
    {
        // Set a @modules alias pointed to the modules/ directory
        Craft::setAlias('@modules', __DIR__);

        // Set the controllerNamespace based on whether this is a console or web request
        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            $this->controllerNamespace = 'modules\\console\\controllers';
        } else {
            $this->controllerNamespace = 'modules\\controllers';
        }

        parent::init();

        // Custom initialization code goes here...
        Event::on(
          User::class,
          Element::EVENT_BEFORE_SAVE,
          function(ModelEvent $event) {
            /* @var User $entry */
            $entry = $event->sender;

            if (ElementHelper::isDraftOrRevision($entry)) {
              // don’t do anything with drafts or revisions
              return;
            }

            if(Craft::$app->getRequest()->getIsSiteRequest())
            {
              $password = Craft::$app->getRequest()->getBodyParam('password');
              $passwordConfirm = Craft::$app->getRequest()->getBodyParam('passwordConfirm');

              if (isset($passwordConfirm) && strcmp($password, $passwordConfirm) !== 0) {
                $event->sender->addErrors([
                  'password' => 'Passwords do not match.'
                ]);
                $event->isValid = false;
              }
            }
          }
        );
    }
}
