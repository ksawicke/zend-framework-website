/**
 * Author:  sawik
 * Created: Mar 28, 2016
 */
create or replace function
    timeoff_get_salary_pending_tmp
    (
        in_employer_number	varchar(3),
	in_employee_number	varchar(9),
	in_category		varchar(1)
    )
    returns table
    (
	pending_tmp		decimal(9,2)
    )
    language sql
    specific togtsalpdt --timeoff_get_salary_pending_tmp
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

        a (PENDING_TMP)
        as(
             SELECT
	         COALESCE(SUM(
	            CASE WHEN AAWK1RC1A = in_category THEN AAWK1HR1A ELSE 0 END +
	            CASE WHEN AAWK1RC1B = in_category THEN AAWK1HR1B ELSE 0 END +
	            CASE WHEN AAWK1RC2A = in_category THEN AAWK1HR2A ELSE 0 END +
	            CASE WHEN AAWK1RC2B = in_category THEN AAWK1HR2B ELSE 0 END +
	            CASE WHEN AAWK1RC3A = in_category THEN AAWK1HR3A ELSE 0 END +
	            CASE WHEN AAWK1RC3B = in_category THEN AAWK1HR3B ELSE 0 END +
	            CASE WHEN AAWK1RC4A = in_category THEN AAWK1HR4A ELSE 0 END +
	            CASE WHEN AAWK1RC4B = in_category THEN AAWK1HR4B ELSE 0 END +
	            CASE WHEN AAWK1RC5A = in_category THEN AAWK1HR5A ELSE 0 END +
	            CASE WHEN AAWK1RC5B = in_category THEN AAWK1HR5B ELSE 0 END +
	            CASE WHEN AAWK1RC6A = in_category THEN AAWK1HR6A ELSE 0 END +
	            CASE WHEN AAWK1RC6B = in_category THEN AAWK1HR6B ELSE 0 END +
	            CASE WHEN AAWK1RC7A = in_category THEN AAWK1HR7A ELSE 0 END +
	            CASE WHEN AAWK1RC7B = in_category THEN AAWK1HR7B ELSE 0 END +
	     
	            CASE WHEN AAWK2RC1A = in_category THEN AAWK2HR1A ELSE 0 END +
	            CASE WHEN AAWK2RC1B = in_category THEN AAWK2HR1B ELSE 0 END +
	            CASE WHEN AAWK2RC2A = in_category THEN AAWK2HR2A ELSE 0 END +
	            CASE WHEN AAWK2RC2B = in_category THEN AAWK2HR2B ELSE 0 END +
	            CASE WHEN AAWK2RC3A = in_category THEN AAWK2HR3A ELSE 0 END +
	            CASE WHEN AAWK2RC3B = in_category THEN AAWK2HR3B ELSE 0 END +
	            CASE WHEN AAWK2RC4A = in_category THEN AAWK2HR4A ELSE 0 END +
	            CASE WHEN AAWK2RC4B = in_category THEN AAWK2HR4B ELSE 0 END +
	            CASE WHEN AAWK2RC5A = in_category THEN AAWK2HR5A ELSE 0 END +
	            CASE WHEN AAWK2RC5B = in_category THEN AAWK2HR5B ELSE 0 END +
	            CASE WHEN AAWK2RC6A = in_category THEN AAWK2HR6A ELSE 0 END +
	            CASE WHEN AAWK2RC6B = in_category THEN AAWK2HR6B ELSE 0 END +
	            CASE WHEN AAWK2RC7A = in_category THEN AAWK2HR7A ELSE 0 END +
	            CASE WHEN AAWK2RC7B = in_category THEN AAWK1HR7B ELSE 0 END
	         ),0)
		  
	    FROM PAPAATMP
	    WHERE AACLK# = refactor_employee_id(in_employee_number)
        )

    select a.PENDING_TMP
    FROM a;

End;;