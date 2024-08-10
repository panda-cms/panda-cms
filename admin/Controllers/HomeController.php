<?php

namespace Panda\Admin\Controllers;

use MongoDB\Exception\Exception;

class HomeController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        global $pandadb;
        $postCount = $pandadb->selectCollection("posts")->countDocuments();
        $commentCount = $pandadb->selectCollection("posts")->aggregate(
            [
                [
                    '$project' => [
                        'commentCount' => [
                            '$cond' => [
                                'if' => ['$isArray' => '$comments'],
                                'then' => ['$size' => '$comments'],
                                'else' => 0
                            ]
                        ]
                    ]
                ],
                [
                    '$group' => [
                        '_id' => null,
                        'totalComments' => ['$sum' => '$commentCount']
                    ]
                ]
            ]
        );

        $commentCount = $commentCount->toArray()[0]->totalComments;

        return $this->template_engine->render("$this->views/index.latte", [
            "postCount" => $postCount, 
            "commentCount" => $commentCount
        ]);
    }
}