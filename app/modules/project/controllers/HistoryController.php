<?php
namespace project\controllers;

use app\components\AuthControl;
use project\controllers\actions\CommitSummaryAction;
use project\controllers\actions\FileViewAction;
use project\controllers\actions\LogAction;
use project\models\Project;
use VcsCommon\exception\CommonException;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * View projects history using simple view or graph view
 */
class HistoryController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'accessControl' => [
                'class' => AuthControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ]
        ]);
    }

    /**
     * Get basic variables as project model and declare standalone actions.
     * If project not found - generate 404.
     *
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function actions()
    {
        $projectId = Yii::$app->request->get('id');
        $project = $this->findModel($projectId);

        $repository = $project->getRepositoryObject();

        return [
            'log' => [
                'class' => LogAction::className(),
                'project' => $project,
                'repository' => $repository,
                'type' => Yii::$app->request->get('type'),
                'path' => Yii::$app->request->get('path'),
            ],
            'commit-summary' => [
                'class' => CommitSummaryAction::className(),
                'project' => $project,
                'repository' => $repository,
                'commitId' => Yii::$app->request->get('commitId'),
            ],
            'file-view' => [
                'class' => FileViewAction::className(),
                'project' => $project,
                'repository' => $repository,
                'commitId' => Yii::$app->request->get('commitId'),
                'filePath' => Yii::$app->request->get('filePath'),
                'mode' => Yii::$app->request->get('mode'),
            ],
        ];
    }

    /**
     * Find project model by identifier.
     *
     * Throws 404 if not found.
     *
     * @param integer $id
     * @return Project
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        $model = is_scalar($id) ? Project::findOne($id) : null;
        if (!$model instanceof Project) {
            throw new NotFoundHttpException();
        }
        return $model;
    }
}
