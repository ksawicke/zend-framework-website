<?php
namespace Request\DAO\UserSettings;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Request\Model\EmployeeId;
use Request\Model\UserSetting;

class UserSettingsDAO
{
    protected $serviceLocator;
    protected $databaseAdapter;

    public function __construct(ServiceLocatorInterface $serviceLocator, Adapter $databaseAdapter)
    {
        $this->serviceLocator = $serviceLocator;
        $this->databaseAdapter = $databaseAdapter;
    }

    public function getUserSettings(EmployeeId $employeeId)
    {
        $sql = new Sql($this->databaseAdapter);

        $select = $sql->select();

        $select->from('TIMEOFF_REQUEST_SETTINGS');

        $select->columns(['SYSTEM_VALUE']);

        $where = new Where();

        $where->equalTo('SYSTEM_KEY', $this->encodeKey($employeeId));

        $select->where($where);

        $statement = $sql->prepareStatementForSqlObject($select);

        $result = $statement->execute();

        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet();
            $resultSet->initialize($result);
            return $resultSet->toArray();
        }

        return [];
    }

    public function setUserSettings(EmployeeId $employeeId, UserSetting $setting)
    {

        $currentSettings = $this->getUserSettings($employeeId);

        $decodedSettings = json_decode($currentSettings);

        foreach ($setting->getUserSetting() as $key => $value) {
            $decodedSettings->$key = $value;
        }
        $newEncodedSettings = json_encode($decodedSettings);

        $encodedKey = $this->encodeKey($employeeId);

        $sql = new Sql($this->databaseAdapter);

        $rawStatement = "merge into TIMEOFF_REQUEST_SETTINGS using (values('" . $encodedKey . "', '" . $newEncodedSettings .
        "')) insrow(system_key, system_value) on TIMEOFF_REQUEST_SETTINGS.system_key = insrow.system_key when matched then update set system_value = insrow.system_value when not matched then insert (system_key, system_value) values(insrow.system_key, insrow.system_value)";
// var_dump($rawStatement); die();
        $statement = $this->databaseAdapter->createStatement($rawStatement);

        $result = $statement->execute();

    }

    protected function encodeKey(EmployeeId $employeeId)
    {
        $encodedKey = json_encode([
            'ER' => $employeeId->getEmployerId(),
            'EN' => $employeeId->getEmployeeId()
        ]);

        return $encodedKey;
    }
}

