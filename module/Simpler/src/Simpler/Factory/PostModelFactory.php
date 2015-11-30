<?php
namespace Simpler\Factory;

use Simpler\Model\PostModel;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PostModelFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $services)
	{
		// see zf2_cust-example/config/autoload/db.local.php
		$adapter   = $services->get('Zend\Db\Adapter\Adapter');
//        echo '<pre>';
//        print_r($adapter);
//        echo '</pre>';

//        $model = new PostModel();
//
//        exit();

        try {
		    $model = new PostModel($adapter); // 'POPDTALIB.LTRGRP',
		} catch (\Exception $e) {
		    $model = new \stdClass();
		}
		return $model;
	}
}
