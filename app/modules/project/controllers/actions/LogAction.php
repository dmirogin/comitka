<?php
namespace project\controllers\actions;

use project\models\Project;
use VcsCommon\BaseBranch;
use VcsCommon\BaseCommit;
use VcsCommon\BaseRepository;
use Yii;
use yii\base\Action;
use yii\base\InvalidParamException;
use yii\data\Pagination;
use yii\helpers\FileHelper;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * View repository log using project model
 */
class LogAction extends Action
{
    /**
     * Commits per page
     */
    const PAGE_LIMIT = 100;

    /**
     * Graph history type
     */
    const TYPE_GRAPH = 'graph';

    /**
     * Simple history type
     */
    const TYPE_SIMPLE = 'simple';

    /**
     * @var Project project model
     */
    public $project;

    /**
     * @var BaseRepository repository model
     */
    public $repository;

    /**
     * @var string history type (see TYPE_* constants)
     */
    public $type;

    /**
     * @var string Relative project path to view log (null if root history)
     */
    public $path = null;

    /**
     * Validate input vars before run action
     *
     * @throws InvalidParamException
     */
    public function init()
    {
        parent::init();
        if (!$this->project instanceof Project) {
            throw new InvalidParamException('Repository property must be an instance of \project\models\Project');
        }
        if (!$this->repository instanceof BaseRepository) {
            throw new InvalidParamException('Repository property must be an instance of \VcsCommon\BaseRepository');
        }
        if (!is_string($this->type)) {
            throw new InvalidParamException('History type must be a string');
        }
        if (!is_null($this->path) && $this->type != self::TYPE_SIMPLE) {
            throw new NotFoundHttpException('Graph history is not supported for relative path');
        }
        // normalize relative path
        if (!is_null($this->path)) {
            $this->path = trim(FileHelper::normalizePath($this->path), DIRECTORY_SEPARATOR);
        }
    }

    /**
     * Render repository history using set type (graph or simple).
     * If type not found - generate 404.
     *
     * @return string
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function run()
    {
        $page = is_scalar(Yii::$app->request->get('page', 1)) ? (int) Yii::$app->request->get('page', 1) : 1;

        // calculate skipped commits amount
        $skip = $this->calculateSkip($page);

        // commits list
        /* @var $history BaseCommit[] */
        $history = [];

        // view simple log
        if ($this->type == self::TYPE_SIMPLE) {
            $history = $this->repository->getHistory(self::PAGE_LIMIT, $skip, $this->path);
        }
        // view graph log
        else if ($this->type == self::TYPE_GRAPH) {
            $graph = $this->repository->getGraphHistory(self::PAGE_LIMIT, $skip);
            $history = $graph->getCommits();
        }
        // if else - generate 404
        else {
            throw new NotFoundHttpException();
        }

        // branches list with head commits
        /* @var $branches BaseBranch[] */
        $branches = $this->repository->getBranches();

        // list pages
        $pagination = new Pagination([
            'pageSize' => self::PAGE_LIMIT,
            'totalCount' => count($history) < self::PAGE_LIMIT ?
                $skip + count($history) :
                $skip + self::PAGE_LIMIT + 1,
            'defaultPageSize' => self::PAGE_LIMIT,
        ]);

        return $this->controller->render('log/' . $this->type, [
            'project' => $this->project,
            'repository' => $this->repository,
            'pagination' => $pagination,
            'history' => $history,
            'branches' => $branches,
            'path' => $this->path,
        ]);
    }

    /**
     * Calculate amount of skipped commits using page num.
     *
     * @param integer $page page number
     * @return integer skipped commits
     */
    protected function calculateSkip($page)
    {
        $page = is_scalar($page) ? max(1, (int) $page) : 1;
        $skipPages = max(0, $page - 1);
        return $skipPages * self::PAGE_LIMIT;
    }
}
