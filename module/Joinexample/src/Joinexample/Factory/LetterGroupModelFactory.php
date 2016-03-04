<?php
namespace Joinexample\Factory;

use Joinexample\Model\LetterGroupModel;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class LetterGroupModelFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $services)
	{
		// see zf2_cust-example/config/autoload/db.local.php
		$adapter   = $services->get('db-adapter');
		try {
		    $model = new LetterGroupModel('POPDTALIB.LTRGRP', $adapter);
		} catch (\Exception $e) {
		    $model = new \stdClass();
		}
		return $model;
	}
}
