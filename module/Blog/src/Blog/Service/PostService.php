<?php

namespace Blog\Service;

use Blog\Model\Post as Post;

class PostService implements PostServiceInterface
{
    protected $data = array(
        array(
            'ID'    => 1,
            'TITLE' => 'Hello World #1',
            'TEXT'  => 'This is our first blog post!'
        ),
        array(
            'ID'     => 2,
            'TITLE' => 'Hello World #2',
            'TEXT'  => 'This is our second blog post!'
        ),
        array(
            'ID'     => 3,
            'TITLE' => 'Hello World #3',
            'TEXT'  => 'This is our third blog post!'
        ),
        array(
            'ID'     => 4,
            'TITLE' => 'Hello World #4',
            'TEXT'  => 'This is our fourth blog post!'
        ),
        array(
            'ID'     => 5,
            'TITLE' => 'Hello World #5',
            'TEXT'  => 'This is our fifth blog post!'
        )
    );

    /**
     * {@inheritDoc}
     */
    public function findAllPosts()
    {
        $allPosts = array();

        foreach ($this->data as $index => $post) {
            $allPosts[] = $this->findPost($index);
        }

        return $allPosts;
    }

    /**
     * {@inheritDoc}
     */
    public function findPost($ID)
    {
        $postData = $this->data[$ID];

        $model = new Post();
        $model->setId($postData['ID']);
        $model->setTitle($postData['TITLE']);
        $model->setText($postData['TEXT']);

        return $model;
    }
}
