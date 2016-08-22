/**
 * Author:  sawik
 * Created: Mar 28, 2016
 */
create or replace function
    timeoff_get_unapproved_hours
    (
	in_employee_number	varchar(9),
	in_category		varchar(1)
    )
    returns table
    (
	unapproved		decimal(9,2)
    )
    language sql
    specific togtuhrs --timeoff_get_unapproved_hours
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

        a (UNAPPROVED)
        as(
	     SELECT COALESCE(SUM(E.REQUESTED_HOURS),0)
	     FROM TIMEOFF_REQUEST_ENTRIES E
	     JOIN TIMEOFF_REQUESTS R ON E.REQUEST_ID = R.REQUEST_ID
	     WHERE R.EMPLOYEE_NUMBER = refactor_employee_id(in_employee_number) AND
	     E.REQUEST_CODE = in_category AND
	     R.REQUEST_STATUS = 'P' AND
	     E.IS_DELETED = '0'
        )

    select a.UNAPPROVED
    FROM a;

End;;