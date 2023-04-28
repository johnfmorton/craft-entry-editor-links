<?php

namespace johnfmorton\craftentryeditorlinks\shared;

use Craft;
use Throwable;

class SharedFunctions
{
    /**
     * Check this page view is on the front end
     * and is not in the CP or Live Preview
     *
     * This is useful because we want to render the
     * entry editor link on the front end, but not
     * when viewed in the CP or Live Preview or when
     * the page is loaded with a preview token.
     *
     * @return bool
     * @throws Throwable
     */
    public static function isFrontEndPageView(): bool
    {
        // is this a CP request?
        if(Craft::$app->request->getIsCpRequest()) {
            return false;
        }

        // is this the live preview?
        if(Craft::$app->request->getIsLivePreview()) {
            return false;
        }

        // get the posted data
        $request = Craft::$app->getRequest();

        // get request header 'sec-fetch-dest' and test if it is 'iframe'
        $secFetchDest = $request->getHeaders()->get('sec-fetch-dest');
        if($secFetchDest == 'iframe') {
            return false;
        }

        // does the request have the param 'x-craft-preview'?
        $xCraftPreview = $request->getQueryParam('x-craft-preview');
        if($xCraftPreview) {
            return false;
        }

        return true;
    }
}