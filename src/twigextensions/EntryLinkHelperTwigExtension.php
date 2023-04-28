<?php
/* EntryLinkHelper Twig Extension for Craft CMS 4.x
 *
 * EntryLinkHelper Extension
 *
 * @link      https://supergeekery.com
 * @package   TwigExtension
 * @since     1.0.0
 */

namespace johnfmorton\craftentryeditorlinks\twigextensions;

//use johnfmorton\craftentryeditorlinks\EntryEditorLinks;
//use johnfmorton\craftentryeditorlinks\services\EntryLinkService;
use johnfmorton\craftentryeditorlinks\shared\SharedFunctions;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

use Craft;

class EntryLinkHelperTwigExtension extends AbstractExtension
{
    public function getName(): string
    {
        return 'EntryLinkHelper TwigExtension';
    }

    /**
     * @throws \Throwable
     */
    public function isFrontEndPageView(): bool
    {
        return SharedFunctions::isFrontEndPageView();
    }

    /*
     * Create a new tag called 'entryLink' that will return a link to the entry editor
     */

    public function getFunctions(): array
    {
        return [
            new TwigFunction('isFrontEndPageView', [$this, 'isFrontEndPageView']),
        ];
    }
}