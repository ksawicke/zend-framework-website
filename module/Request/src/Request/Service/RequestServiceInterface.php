<?php

namespace Request\Service;

use Request\Model\RequestInterface;

interface RequestServiceInterface
{
    public function findTimeOffBalances($employeeId);
}
