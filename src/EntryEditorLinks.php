<?php

namespace johnfmorton\craftentryeditorlinks;

use Craft;
use craft\base\Event;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use johnfmorton\craftentryeditorlinks\models\Settings;
use johnfmorton\craftentryeditorlinks\services\EntryLinkService;
use johnfmorton\craftentryeditorlinks\twigextensions\EntryLinkHelperTwigExtension;

/**
 * entry-editor-links plugin
 *
 * @method static EntryEditorLinks getInstance()
 * @method Settings getSettings()
 * @author John F Morton <morton@jmx2.com>
 * @copyright John F Morton
 * @license https://craftcms.github.io/license/ Craft License
 * @property-read EntryLinkService $entryLinkService
 */
class EntryEditorLinks extends Plugin
{
    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * EntryEditorLinks::$plugin
     * @var EntryEditorLinks
     */
    public static EntryEditorLinks $plugin;

    /** @var string The plugin’s schema version number */
    public string $schemaVersion = '1.0.0';

    /** @var bool Whether the plugin has a settings page in the control panel */
    public bool $hasCpSettings = false;

    /**
     * Returns the base config that the plugin should be instantiated with.
     *
     * It is recommended that plugins define their internal components from here:
     *
     * ```php
     * public static function config(): array
     * {
     *     return [
     *         'components' => [
     *             'myComponent' => ['class' => MyComponent::class],
     *             // ...
     *         ],
     *     ];
     * }
     * ```
     *
     * Doing that enables projects to customize the components as needed, by
     * overriding `\craft\services\Plugins::$pluginConfigs` in `config/app.php`:
     *
     * ```php
     * return [
     *     'components' => [
     *         'plugins' => [
     *             'pluginConfigs' => [
     *                 'my-plugin' => [
     *                     'components' => [
     *                         'myComponent' => [
     *                             'myProperty' => 'foo',
     *                             // ...
     *                         ],
     *                     ],
     *                 ],
     *             ],
     *         ],
     *     ],
     * ];
     * ```
     *
     * The resulting config will be passed to `\Craft::createObject()` to instantiate the plugin.
     *
     * @return array
     */
    public static function config(): array
    {
        return [
            'components' => ['entryLinkService' => EntryLinkService::class],
        ];
    }

    /**
     * Initializes the module.
     *
     * This method is called after the module is created and initialized with property values
     * given in configuration. The default implementation will initialize [[controllerNamespace]]
     * if it is not set.
     *
     * If you override this method, please make sure you call the parent implementation.
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        // Defer most setup tasks until Craft is fully initialized
        Craft::$app->onInit(function() {
            $this->attachEventHandlers();
            $this->registerTwigExtensions();
        });
//        Craft::$app->view->registerTwigExtension(new EntryLinkHelper());
    }

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return Model|null
     */
    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content block on the settings page.
     *
     * @return string|null The rendered settings HTML
     */
    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate('entry-editor-links/_settings.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
        ]);
    }

    private function attachEventHandlers(): void
    {
        // Register event handlers here ...
        // (see https://craftcms.com/docs/4.x/extend/events.html to get started)

        // add the API end-point
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            static function (RegisterUrlRulesEvent $event) {
                $event->rules['entry-editor-links/entry-processor'] = 'entry-editor-links/entry-processor';
            }
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            static function (RegisterUrlRulesEvent $event) {
                $event->rules['entry-editor-links/entry-processor/cp-link'] = 'entry-editor-links/entry-processor/cp-link';
            }
        );
    }

    private function registerTwigExtensions(): void
    {
        $extensions =[
            EntryLinkHelperTwigExtension::class,
        ];

        foreach ($extensions as $extension) {
            Craft::$app->view->registerTwigExtension(new $extension());
        }
    }
}
