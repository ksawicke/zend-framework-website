/**
 * Gets full set of time off and employee data based on employee ID.
 *
 * Author:  sawik
 * Created: Mar 28, 2016
 */
create or replace function
    timeoff_get_employee_data
    (
        in_employer_number	   varchar(3),
	in_employee_number	   varchar(9),
	in_include_unapproved	   varchar(1)
    )
    returns table
    (
	employer_number		   char(3),
	employee_number		   char(9),
	employee_name              char(75),
        employee_common_name       char(75),
        employee_last_name         char(75),
	employee_description	   char(100),
	employee_description_alt   char(100),
        email_address		   char(75),
	level_1			   char(5),
	level_2			   char(5),
	level_3			   char(5),
	level_4			   char(5),
	position_code		   char(6),
	position_title		   char(30),
	employee_hire_date	   char(10),
	salary_type		   char(1),

	manager_employee_number    char(9),
        manager_name               char(75),
        manager_common_name	   char(75),
        manager_last_name          char(75),
	manager_description	   char(100),
	manager_description_alt	   char(100),
	manager_email_address      char(75),
        manager_position_code      char(6),
	manager_position_title     char(30),

	pto_earned		   decimal(9,2),
	pto_taken		   decimal(9,2),
	pto_unapproved		   decimal(9,2),
	pto_pending      	   decimal(9,2),
	pto_pending_tmp      	   decimal(9,2),
	pto_pending_total	   decimal(9,2),
	pto_remaining		   decimal(9,2),

	float_earned		   decimal(9,2),
	float_taken		   decimal(9,2),
	float_unapproved	   decimal(9,2),
	float_pending      	   decimal(9,2),
	float_pending_tmp      	   decimal(9,2),
	float_pending_total	   decimal(9,2),
	float_remaining		   decimal(9,2),

	sick_earned		   decimal(9,2),
	sick_taken		   decimal(9,2),
	sick_unapproved		   decimal(9,2),
	sick_pending      	   decimal(9,2),
	sick_pending_tmp      	   decimal(9,2),
	sick_pending_total	   decimal(9,2),
	sick_remaining		   decimal(9,2),

	gf_earned		   decimal(9,2),
	gf_taken		   decimal(9,2),
	gf_unapproved		   decimal(9,2),
	gf_pending      	   decimal(9,2),
	gf_pending_tmp      	   decimal(9,2),
	gf_pending_total	   decimal(9,2),
	gf_remaining		   decimal(9,2),

	unexcused_unapproved	   decimal(9,2),
	unexcused_pending      	   decimal(9,2),
	unexcused_pending_tmp      decimal(9,2),
	unexcused_pending_total	   decimal(9,2),

	bereavement_unapproved	   decimal(9,2),
	bereavement_pending        decimal(9,2),
	bereavement_pending_tmp    decimal(9,2),
	bereavement_pending_total  decimal(9,2),

	civic_duty_unapproved	   decimal(9,2),
	civic_duty_pending         decimal(9,2),
	civic_duty_pending_tmp     decimal(9,2),
	civic_duty_pending_total   decimal(9,2),

	unpaid_unapproved	   decimal(9,2),
	unpaid_pending      	   decimal(9,2),
	unpaid_pending_tmp         decimal(9,2),
	unpaid_pending_total	   decimal(9,2)
    )
    language sql
    specific togtempd --timeoff_get_employee_data
    no external action
    not deterministic
    modifies sql data
    allow parallel
    cardinality 1
    concurrent access resolution use currently committed
    set option
        commit=*NONE,
        output=*PRINT

Begin

    return

    with

        a (EMPLOYER_NUMBER, EMPLOYEE_NUMBER, EMPLOYEE_NAME, EMPLOYEE_COMMON_NAME, EMPLOYEE_LAST_NAME,
	   EMPLOYEE_EMAIL_ADDRESS, 
           LEVEL_1, LEVEL_2, LEVEL_3, LEVEL_4, POSITION_CODE, POSITION_TITLE,
           EMPLOYEE_HIRE_DATE, SALARY_TYPE,

	   MANAGER_EMPLOYEE_NUMBER, MANAGER_NAME, MANAGER_COMMON_NAME, MANAGER_LAST_NAME,
           MANAGER_EMAIL_ADDRESS,  
           MANAGER_POSITION_CODE, MANAGER_POSITION_TITLE,

	   PTO_EARNED, PTO_TAKEN, PTO_UNAPPROVED, PTO_PENDING, PTO_PENDING_TMP,
           FLOAT_EARNED, FLOAT_TAKEN, FLOAT_UNAPPROVED, FLOAT_PENDING, FLOAT_PENDING_TMP,
           SICK_EARNED, SICK_TAKEN, SICK_UNAPPROVED, SICK_PENDING, SICK_PENDING_TMP,
           GF_EARNED, GF_TAKEN, GF_UNAPPROVED, GF_PENDING, GF_PENDING_TMP,

	   UNEXCUSED_UNAPPROVED, UNEXCUSED_PENDING, UNEXCUSED_PENDING_TMP,
           BEREAVEMENT_UNAPPROVED, BEREAVEMENT_PENDING, BEREAVEMENT_PENDING_TMP,
	   CIVIC_DUTY_UNAPPROVED, CIVIC_DUTY_PENDING, CIVIC_DUTY_PENDING_TMP,
	   UNPAID_UNAPPROVED, UNPAID_PENDING, UNPAID_PENDING_TMP
        )
        as (
             SELECT employee.PRER, refactor_employee_id(employee.PREN), employee.PRCKNM, employee.PRCOMN, employee.PRLNM,
	     employee.PREML1,
             employee.PRL01, employee.PRL02, employee.PRL03, employee.PRL04, employee.PRPOS, employee.PRTITL,
	     employee.PRDOHE, employee.PRPAY,

	     -- GET MANAGER DATA
	     refactor_employee_id(manager_addons.PREN), manager_addons.PRCKNM, manager_addons.PRCOMN, manager_addons.PRLNM,
	     manager_addons.PREML1,
             manager_addons.PRPOS, manager_addons.PRTITL,
	     
	     -- GET ALL PTO DATA
	     employee.PRVAC, employee.PRVAT,
	     (select * from table(timeoff_get_unapproved_hours(in_employee_number, 'P')) as data),
	     CASE WHEN employee.PRPAY = 'S' THEN
                 (select PENDING FROM table(timeoff_get_salary_pending(in_employer_number, in_employee_number, 'P')) as data)
	     ELSE
                 (select PENDING FROM table(timeoff_get_hourly_pending(in_employer_number, in_employee_number, 'P')) as data)
	     END,

	     CASE WHEN employee.PRPAY = 'S' THEN
                 (select PENDING_TMP FROM table(timeoff_get_salary_pending_tmp(in_employer_number, in_employee_number, 'P')) as data)
	     ELSE
                 (select PENDING_TMP FROM table(timeoff_get_hourly_pending_tmp(in_employer_number, in_employee_number, 'P')) as data)
	     END,

	     -- GET ALL FLOAT DATA
	     employee.PRSHA, employee.PRSHT,
	     (select * from table(timeoff_get_unapproved_hours(in_employee_number, 'K')) as data),
	     CASE WHEN employee.PRPAY = 'S' THEN
                 (select PENDING FROM table(timeoff_get_salary_pending(in_employer_number, in_employee_number, 'K')) as data)
	     ELSE
                 (select PENDING FROM table(timeoff_get_hourly_pending(in_employer_number, in_employee_number, 'K')) as data)
	     END,

	     CASE WHEN employee.PRPAY = 'S' THEN
                 (select PENDING_TMP FROM table(timeoff_get_salary_pending_tmp(in_employer_number, in_employee_number, 'K')) as data)
	     ELSE
                 (select PENDING_TMP FROM table(timeoff_get_hourly_pending_tmp(in_employer_number, in_employee_number, 'K')) as data)
	     END,

	     -- GET ALL SICK DATA
	     employee.PRSDA, employee.PRSDT,
	     (select * from table(timeoff_get_unapproved_hours(in_employee_number, 'S')) as data),
	     CASE WHEN employee.PRPAY = 'S' THEN
                 (select PENDING FROM table(timeoff_get_salary_pending(in_employer_number, in_employee_number, 'S')) as data)
	     ELSE
                 (select PENDING FROM table(timeoff_get_hourly_pending(in_employer_number, in_employee_number, 'S')) as data)
	     END,

	     CASE WHEN employee.PRPAY = 'S' THEN
                 (select PENDING_TMP FROM table(timeoff_get_salary_pending_tmp(in_employer_number, in_employee_number, 'S')) as data)
	     ELSE
                 (select PENDING_TMP FROM table(timeoff_get_hourly_pending_tmp(in_employer_number, in_employee_number, 'S')) as data)
	     END,

	     -- GET ALL GF DATA
	     employee.PRAC5E, employee.PRAC5T,
	     (select * from table(timeoff_get_unapproved_hours(in_employee_number, 'R')) as data),
	     CASE WHEN employee.PRPAY = 'S' THEN
                 (select PENDING FROM table(timeoff_get_salary_pending(in_employer_number, in_employee_number, 'R')) as data)
	     ELSE
                 (select PENDING FROM table(timeoff_get_hourly_pending(in_employer_number, in_employee_number, 'R')) as data)
	     END,

             CASE WHEN employee.PRPAY = 'S' THEN
                 (select PENDING_TMP FROM table(timeoff_get_salary_pending_tmp(in_employer_number, in_employee_number, 'R')) as data)
	     ELSE
                 (select PENDING_TMP FROM table(timeoff_get_hourly_pending_tmp(in_employer_number, in_employee_number, 'R')) as data)
	     END,

	     -- GET ALL UNEXCUSED DATA
	     (select * from table(timeoff_get_unapproved_hours(in_employee_number, 'X')) as data),
             CASE WHEN employee.PRPAY = 'S' THEN
                 (select PENDING FROM table(timeoff_get_salary_pending(in_employer_number, in_employee_number, 'X')) as data)
	     ELSE
                 (select PENDING FROM table(timeoff_get_hourly_pending(in_employer_number, in_employee_number, 'X')) as data)
	     END,

	     CASE WHEN employee.PRPAY = 'S' THEN
                 (select PENDING_TMP FROM table(timeoff_get_salary_pending_tmp(in_employer_number, in_employee_number, 'X')) as data)
	     ELSE
                 (select PENDING_TMP FROM table(timeoff_get_hourly_pending_tmp(in_employer_number, in_employee_number, 'X')) as data)
	     END,


             -- GET ALL BEREAVEMENT DATA
             (select * from table(timeoff_get_unapproved_hours(in_employee_number, 'B')) as data),
             CASE WHEN employee.PRPAY = 'S' THEN
                 (select PENDING FROM table(timeoff_get_salary_pending(in_employer_number, in_employee_number, 'B')) as data)
	     ELSE
                 (select PENDING FROM table(timeoff_get_hourly_pending(in_employer_number, in_employee_number, 'B')) as data)
	     END,

             CASE WHEN employee.PRPAY = 'S' THEN
                 (select PENDING_TMP FROM table(timeoff_get_salary_pending_tmp(in_employer_number, in_employee_number, 'B')) as data)
	     ELSE
                 (select PENDING_TMP FROM table(timeoff_get_hourly_pending_tmp(in_employer_number, in_employee_number, 'B')) as data)
	     END,


             -- GET ALL CIVIC DUTY DATA
             (select * from table(timeoff_get_unapproved_hours(in_employee_number, 'J')) as data),
             CASE WHEN employee.PRPAY = 'S' THEN
                 (select PENDING FROM table(timeoff_get_salary_pending(in_employer_number, in_employee_number, 'J')) as data)
	     ELSE
                 (select PENDING FROM table(timeoff_get_hourly_pending(in_employer_number, in_employee_number, 'J')) as data)
	     END,

	     CASE WHEN employee.PRPAY = 'S' THEN
                 (select PENDING_TMP FROM table(timeoff_get_salary_pending_tmp(in_employer_number, in_employee_number, 'J')) as data)
	     ELSE
                 (select PENDING_TMP FROM table(timeoff_get_hourly_pending_tmp(in_employer_number, in_employee_number, 'J')) as data)
	     END,


	     -- GET ALL UNPAID TIME
             (select * from table(timeoff_get_unapproved_hours(in_employee_number, 'A')) as data),
             CASE WHEN employee.PRPAY = 'S' THEN
                 (select PENDING FROM table(timeoff_get_salary_pending(in_employer_number, in_employee_number, 'A')) as data)
	     ELSE
                 (select PENDING FROM table(timeoff_get_hourly_pending(in_employer_number, in_employee_number, 'A')) as data)
	     END,

             CASE WHEN employee.PRPAY = 'S' THEN
                 (select PENDING_TMP FROM table(timeoff_get_salary_pending_tmp(in_employer_number, in_employee_number, 'A')) as data)
	     ELSE
                 (select PENDING_TMP FROM table(timeoff_get_hourly_pending_tmp(in_employer_number, in_employee_number, 'A')) as data)
	     END

             FROM PRPMS employee
	     LEFT JOIN PRPSP manager ON employee.PREN = manager.SPEN
             LEFT JOIN PRPMS manager_addons ON manager_addons.PREN = manager.SPSPEN

             WHERE employee.PREN = refactor_employee_id(in_employee_number) and
	     employee.PRER = in_employer_number
        )

    select a.EMPLOYER_NUMBER, a.EMPLOYEE_NUMBER, a.EMPLOYEE_NAME, a.EMPLOYEE_COMMON_NAME, a.EMPLOYEE_LAST_NAME,
    TRIM(a.EMPLOYEE_LAST_NAME) CONCAT ', ' CONCAT TRIM(a.EMPLOYEE_COMMON_NAME) CONCAT ' (' CONCAT TRIM(a.EMPLOYEE_NUMBER) CONCAT ')',
    TRIM(a.EMPLOYEE_COMMON_NAME) CONCAT ' ' CONCAT TRIM(a.EMPLOYEE_LAST_NAME) CONCAT ' (' CONCAT TRIM(a.EMPLOYEE_NUMBER) CONCAT ')',
    TRIM(a.EMPLOYEE_EMAIL_ADDRESS),
    a.LEVEL_1, a.LEVEL_2, a.LEVEL_3, a.LEVEL_4, a.POSITION_CODE, TRIM(a.POSITION_TITLE),
    a.EMPLOYEE_HIRE_DATE, a.SALARY_TYPE,

    a.MANAGER_EMPLOYEE_NUMBER, a.MANAGER_NAME, a.MANAGER_COMMON_NAME, a.MANAGER_LAST_NAME,
    TRIM(a.MANAGER_LAST_NAME) CONCAT ', ' CONCAT TRIM(a.MANAGER_COMMON_NAME) CONCAT ' (' CONCAT TRIM(a.MANAGER_EMPLOYEE_NUMBER) CONCAT ')',
    TRIM(a.MANAGER_COMMON_NAME) CONCAT ' ' CONCAT TRIM(a.MANAGER_LAST_NAME) CONCAT ' (' CONCAT TRIM(a.MANAGER_EMPLOYEE_NUMBER) CONCAT ')',
    TRIM(a.MANAGER_EMAIL_ADDRESS),
    a.MANAGER_POSITION_CODE, a.MANAGER_POSITION_TITLE,

    -- PTO VALUES
    a.PTO_EARNED, a.PTO_TAKEN, a.PTO_UNAPPROVED, a.PTO_PENDING, a.PTO_PENDING_TMP,
    CASE in_include_unapproved
        WHEN 'Y'
        THEN COALESCE( (a.PTO_UNAPPROVED + a.PTO_PENDING + a.PTO_PENDING_TMP), 0 )
        ELSE COALESCE( (a.PTO_PENDING + a.PTO_PENDING_TMP), 0 )
    END,
    CASE in_include_unapproved
        WHEN 'Y'
        THEN COALESCE( (a.PTO_EARNED - a.PTO_TAKEN - a.PTO_UNAPPROVED - a.PTO_PENDING - a.PTO_PENDING_TMP), 0 )
        ELSE COALESCE( (a.PTO_EARNED - a.PTO_TAKEN - a.PTO_PENDING - a.PTO_PENDING_TMP), 0 )
    END,
    
    -- FLOAT VALUES
    a.FLOAT_EARNED, a.FLOAT_TAKEN, a.FLOAT_UNAPPROVED, a.FLOAT_PENDING, a.FLOAT_PENDING_TMP,
    CASE in_include_unapproved
        WHEN 'Y'
        THEN COALESCE( (a.FLOAT_UNAPPROVED + a.FLOAT_PENDING + a.FLOAT_PENDING_TMP), 0 )
        ELSE COALESCE( (a.FLOAT_PENDING + a.FLOAT_PENDING_TMP), 0 )
    END,
    CASE in_include_unapproved
        WHEN 'Y'
        THEN COALESCE( (a.FLOAT_EARNED - a.FLOAT_TAKEN - a.FLOAT_UNAPPROVED - a.FLOAT_PENDING - a.FLOAT_PENDING_TMP), 0 )
        ELSE COALESCE( (a.FLOAT_EARNED - a.FLOAT_TAKEN - a.FLOAT_PENDING - a.FLOAT_PENDING_TMP), 0 )
    END,
    
    -- SICK VALUES
    a.SICK_EARNED, a.SICK_TAKEN, a.SICK_UNAPPROVED, a.SICK_PENDING, a.SICK_PENDING_TMP,
    CASE in_include_unapproved
        WHEN 'Y'
        THEN COALESCE( (a.SICK_UNAPPROVED + a.SICK_PENDING + a.SICK_PENDING_TMP), 0 )
        ELSE COALESCE( (a.SICK_PENDING + a.SICK_PENDING_TMP), 0 )
    END,
    CASE in_include_unapproved
        WHEN 'Y'
        THEN COALESCE( (a.SICK_EARNED - a.SICK_TAKEN - a.SICK_UNAPPROVED - a.SICK_PENDING - a.SICK_PENDING_TMP), 0 )
        ELSE COALESCE( (a.SICK_EARNED - a.SICK_TAKEN - a.SICK_PENDING - a.SICK_PENDING_TMP), 0 )
    END,
    
    -- GF VALUES
    a.GF_EARNED, a.GF_TAKEN, a.GF_UNAPPROVED, a.GF_PENDING, a.GF_PENDING_TMP,
    CASE in_include_unapproved
        WHEN 'Y'
        THEN COALESCE( (a.GF_UNAPPROVED + a.GF_PENDING + a.GF_PENDING_TMP), 0 )
        ELSE COALESCE( (a.GF_PENDING + a.GF_PENDING_TMP), 0 )
    END,
    CASE in_include_unapproved
        WHEN 'Y'
        THEN COALESCE( (a.GF_EARNED - a.GF_TAKEN - a.GF_UNAPPROVED - a.GF_PENDING - a.GF_PENDING_TMP), 0 )
        ELSE COALESCE( (a.GF_EARNED - a.GF_TAKEN - a.GF_PENDING - a.GF_PENDING_TMP), 0 )
    END,

    -- UNEXCUSED VALUES
    a.UNEXCUSED_UNAPPROVED, a.UNEXCUSED_PENDING, a.UNEXCUSED_PENDING_TMP,
    CASE in_include_unapproved
        WHEN 'Y'
        THEN COALESCE( (a.UNEXCUSED_UNAPPROVED + a.UNEXCUSED_PENDING + a.UNEXCUSED_PENDING_TMP), 0 )
        ELSE COALESCE( (a.UNEXCUSED_PENDING + a.UNEXCUSED_PENDING_TMP), 0 )
    END,

    -- BEREAVEMENT VALUES
    a.BEREAVEMENT_UNAPPROVED, a.BEREAVEMENT_PENDING, a.BEREAVEMENT_PENDING_TMP,
    CASE in_include_unapproved
        WHEN 'Y'
        THEN COALESCE( (a.BEREAVEMENT_UNAPPROVED + a.BEREAVEMENT_PENDING + a.BEREAVEMENT_PENDING_TMP), 0 )
        ELSE COALESCE( (a.BEREAVEMENT_PENDING + a.BEREAVEMENT_PENDING_TMP), 0 )
    END,

    -- CIVIC DUTY VALUES
    a.CIVIC_DUTY_UNAPPROVED, a.CIVIC_DUTY_PENDING, a.CIVIC_DUTY_PENDING_TMP,
    CASE in_include_unapproved
        WHEN 'Y'
        THEN COALESCE( (a.CIVIC_DUTY_UNAPPROVED + a.CIVIC_DUTY_PENDING + a.CIVIC_DUTY_PENDING_TMP), 0 )
        ELSE COALESCE( (a.CIVIC_DUTY_PENDING + a.CIVIC_DUTY_PENDING_TMP), 0 )
    END,

    -- UNPAID VALUES
    a.UNPAID_UNAPPROVED, a.UNPAID_PENDING, a.UNPAID_PENDING_TMP,
    CASE in_include_unapproved
        WHEN 'Y'
        THEN COALESCE( (a.UNPAID_UNAPPROVED + a.UNPAID_PENDING + a.UNPAID_PENDING_TMP), 0 )
        ELSE COALESCE( (a.UNPAID_PENDING + a.UNPAID_PENDING_TMP), 0 )
    END

    FROM a;

End;;