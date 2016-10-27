<?php
namespace Request\Model;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;

class TimeOffEmailReminder extends BaseDB
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Retrieve all records thar are overdue
     */
    public function getAllUnsendRecordData()
    {
        $sql = new Sql($this->adapter);

        $select = $sql->select();

        $select->from('TIMEOFF_REQUEST_EMAIL_REMINDER');
        $select->columns(
            [
                'IDENTITY_ID',
                'EMAIL_SEND',
                'REQUEST_ID',
                'EMPLOYEE_NAME' => new Expression("GET_EMPLOYEE_COMMON_NAME('002', REFACTOR_EMPLOYEE_ID(EMPLOYEE_NUMBER))"),
                'SUPERVISOR_NAME' => new Expression("GET_EMPLOYEE_COMMON_NAME('002', REFACTOR_EMPLOYEE_ID(SPSPEN))")
            ]
        );

        $select->join('TIMEOFF_REQUESTS', 'TIMEOFF_REQUEST_EMAIL_REMINDER.REQUEST_ID = TIMEOFF_REQUESTS.REQUEST_ID', ['EMPLOYEE_NUMBER', 'TO_REQUEST_ID' => 'REQUEST_ID']);
        $select->join('PRPSP', "SPER = '002' AND SPEN = EMPLOYEE_NUMBER", ['SPSPEN']);

        $where = new Where();

        $where->notEqualTo('EMAIL_SEND', 'Y');

        $select->where($where);

        $select->order(['SPSPEN', 'EMPLOYEE_NUMBER']);

        $statement = $sql->prepareStatementForSqlObject($select);

        $result = $statement->execute();

        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet();
            $resultSet->initialize($result);
            return $resultSet->toArray();
        }

        return [];

    }

    /**
     * Mark email records as send
     *
     * @param unknown $identityId
     */
    public function markRecordAsSend($identityId)
    {
        $sql = new Sql($this->adapter);

        $update = $sql->update();

        $update->table('TIMEOFF_REQUEST_EMAIL_REMINDER');

        $update->set(['EMAIL_SEND' => 'Y']);

        $update->where(['IDENTITY_ID' => $identityId]);

        $statement = $sql->prepareStatementForSqlObject($update);

        $result = $statement->execute();
    }

    /**
     * Bulk insert of new records
     *
     * @param unknown $reminderRecords
     * @return boolean
     */
    public function insertReminderRecords( $reminderRecords = null )
    {
        if ($reminderRecords == null ) {
            return false;
        }

        foreach ($reminderRecords as $reminder) {
            $this->insertReminderRecord( $reminder );
        }
    }

    /**
     * insert individual record
     *
     * @param unknown $reminderRecord
     * @return boolean|mixed[]|NULL[]
     */
    public function insertReminderRecord( $reminderRecord = null )
    {
        if ($reminderRecord == null) {
            return false;
        }

        $sql = new Sql($this->adapter);

        $merge = "merge into TIMEOFF_REQUEST_EMAIL_REMINDER using (values(" . $reminderRecord['REQUEST_ID'] .")) " .
                  "insrow(request_id) on TIMEOFF_REQUEST_EMAIL_REMINDER.REQUEST_ID = insrow.REQUEST_ID AND (TIMEOFF_REQUEST_EMAIL_REMINDER.EMAIL_SEND = 'N' " .
                  " OR (TIMEOFF_REQUEST_EMAIL_REMINDER.EMAIL_SEND = 'Y' AND date(TIMEOFF_REQUEST_EMAIL_REMINDER.EMAIL_SEND_ON) <= current_date)) " .
                  "when not matched then insert(request_id) values(insrow.request_id)";

        $statement = $this->adapter->getDriver()->createStatement($merge);

        $statement->prepare();

        $result = $statement->execute();

        /* success ? */
        if ($result) {
            /* retrieve guest record identity id */
            $generatedValue = $result->getGeneratedValue();

            /* return identity-id */
            return ['generatedValue' => $generatedValue];
        }

    }
}
