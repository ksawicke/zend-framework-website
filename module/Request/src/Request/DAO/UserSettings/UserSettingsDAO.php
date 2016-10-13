<?php
namespace Request\DAO\UserSettings;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;

class UserSettingsDAO
{
    protected $serviceLocator;
    protected $databaseAdapter;

    public function __construct(ServiceLocatorInterface $serviceLocator, Adapter $databaseAdapter)
    {
        $this->serviceLocator = $serviceLocator;
        $this->databaseAdapter = $databaseAdapter;
    }

    public function getUserSettings($employeeId)
    {
        $sql = new Sql($this->databaseAdapter);

        $select = $sql->select();

        $select->from('TIMEOFF_REQUEST_SETTINGS');

        $select->columns(['SYSTEM_VALUE']);

        $where = new Where();

        $where->equalTo('SYSTEM_KEY', $this->encodeKey($employeeId));

        $statement = $sql->prepareStatementForSqlObject($select);

        $result = $statement->execute();

        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet();
            $resultSet->initialize($result);
            return $resultSet->toArray()[0]['SYSTEM_VALUE'];
        }

        return [];
    }

    public function setUserSettings($employeeId, $setting)
    {
        $currentSettings = $this->getUserSettings($employeeId);
        $decodedSettings = json_decode($currentSettings);
        foreach ($setting as $key => $value) {
            $decodedSettings->$key = $value;
        }
        $newEncodedSettings = json_encode($decodedSettings);

        $encodedKey = $this->encodeKey($employeeId);

        $sql = new Sql($this->databaseAdapter);

        $rawStatement = "merge into TIMEOFF_REQUEST_SETTINGS using (values('" . $encodedKey . "', '" . $newEncodedSettings .
        "')) insrow(system_key, system_value) on TIMEOFF_REQUEST_SETTINGS.system_key = insrow.system_key when matched then update set system_value = insrow.system_value when not matched then insert (system_key, system_value) values(insrow.system_key, insrow.system_value)";

        $statement = $this->databaseAdapter->createStatement($rawStatement);

        $result = $statement->execute();

    }

    protected function encodeKey($employeeId)
    {
        $encodedKey = json_encode([
            'EN' => $employeeId,
            'ER' => '002'
        ]);

        return $encodedKey;
    }
}

