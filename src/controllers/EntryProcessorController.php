<?php

namespace johnfmorton\craftentryeditorlinks\controllers;

use Craft;
use craft\web\Controller;
use Throwable;
use yii\web\Response;

/**
 * Entry Processor controller
 * The main entry point for the API for the plugin
 */
class EntryProcessorController extends Controller
{
    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = true;// self::ALLOW_ANONYMOUS_NEVER;

//    /**
//     * entry-editor-links/entry-processor action
//     */
//    public function actionIndex(): Response
//    {
//        // TODO: Implement actionIndex() method.
//        $myVar = 'Hello World';
//        // return JSON response
//        return $this->asJson([
//            'success' => true,
//            'message' => $myVar,
//        ]);
//    }

    /**
     * entry-editor-links/entry-processor/cp-link action
     *
     * @throws Throwable
     */
    public function actionCpLink(): Response
    {
        // get the logged-in user
        $user = Craft::$app->getUser()->getIdentity();
        // is the user logged in?
        if(!$user) {
            return $this->asJson([
                'success' => false,
                'message' => 'User not logged in',
            ]);
        }

        if (!Craft::$app->user->checkPermission('accessCp')) {
            return $this->asJson([
                'success' => false,
                'message' => 'User does not have access to the CP',
            ]);
        }

        if(Craft::$app->request->getIsCpRequest()) {
            return $this->asJson([
                'success' => false,
                'message' => 'This is a CP request',
            ]);
        }

        if(Craft::$app->request->getIsLivePreview()) {
            return $this->asJson([
                'success' => false,
                'message' => 'This is Preview request'
            ]);
        }

        // get the posted data
        $request = Craft::$app->getRequest();

        // GET the id of the request
        $id = $request->getQueryParam('id');

        // confirm the id parameter is set
        if (!$id) {
            return $this->asJson([
                'success' => false,
                'message' => 'No ID',
            ]);
        }
        // confirm the id is an integer
        if (!is_numeric($id)) {
            return $this->asJson([
                'success' => false,
                'message' => 'Invalid ID',
            ]);
        }

        // Get the entry for the id
        $entry = Craft::$app->getEntries()->getEntryById($id);

        // Get the CP edit URL for the entry
        if (!$entry) {
            return $this->asJson([
                'success' => false,
                'message' => 'No entry found',
            ]);
        }

        // Show all the user's permissions in a dd() statement
        // dd(Craft::$app->userPermissions->getPermissionsByUserId($user->id));
        // We need to check if the user has permission to edit entries in this section by checking the user's permission of "savepeerentries" for this section
        // or to see if this user is the author of the entry, which means they can edit it

        // Get the section for the entry
        $section = $entry->getSection();

        // Get the section id
        $sectionId = $section->uid;

        // find out if the user is the author of the entry
        $authorId = $entry->authorId;

         if (!Craft::$app->user->checkPermission('savepeerentries:'.$sectionId) && $authorId != $user->id) {
            return $this->asJson([
                'success' => false,
                'message' => 'User does not have permission to edit entries in this section and is not the author of this entry',
            ]);
        }

        $message = $entry->getCpEditUrl();

        return $this->asJson([
            'success' => true,
            'message' => $message,
        ]);
    }
}
