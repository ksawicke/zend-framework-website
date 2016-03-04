<?php
namespace Joinexample\Factory;

use Joinexample\Model\LetterMonitorModel;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class LetterMonitorModelFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $services)
	{
		// see zf2_cust-example/config/autoload/db.local.php
		$adapter   = $services->get('db-adapter');
		try {
		    $model = new LetterMonitorModel('CHOICE.LTRMON', $adapter);
		} catch (\Exception $e) {
		    $model = new \stdClass();
		}
		return $model;
	}
}
