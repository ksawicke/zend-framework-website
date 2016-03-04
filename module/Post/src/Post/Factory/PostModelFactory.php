<?php
namespace Post\Factory;

use Post\Model\PostModel;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PostModelFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $services)
	{
		$adapter   = $services->get('Zend\Db\Adapter\Adapter');

        try {
		    $model = new PostModel($adapter);
		} catch (\Exception $e) {
		    $model = new \stdClass();
		}
		return $model;
	}
}
