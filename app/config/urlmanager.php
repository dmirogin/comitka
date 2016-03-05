<?php
/**
 * UrlManager configuration
 */

return [
    'class' => 'yii\web\UrlManager',
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        '/' => 'main/index',
        '/sign-in' => '/user/auth/sign-in',
        '/sign-out' => '/user/auth/sign-out',
        '/forgot-password' => '/user/auth/forgot-password',
        '/change-password/<hash:([\w]+)>' => '/user/auth/change-password',
        '/profile' => '/user/profile/index',
        '/users' => '/user/user-manager/index',
        '/users/create' => '/user/user-manager/create',
        '/users/<action:(update|lock|activate)>/<id:(\d+)>' => '/user/user-manager/<action>',
        '/roles' => '/user/role-manager/index',
        '/roles/create' => '/user/role-manager/create',
        '/roles/<action:(update|delete)>/<id:(\w+)>' => '/user/role-manager/<action>',
        '/projects' => '/project/project/index',
        '/projects/<id:(\d+)>/<action:(update|delete)>' => '/project/project/<action>',
        '/projects/create' => '/project/project/create',
        '/projects/<id:(\d+)>/<type:(graph|simple)>' => '/project/history/log',
        '/projects/<id:(\d+)>/rev/<commitId:([a-f0-9]+)>/summary' => '/project/history/commit-summary',
        '/projects/<id:(\d+)>/rev/<commitId:([a-f0-9]+)>/details' => '/project/history/file-view',
    ],
];
