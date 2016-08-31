BEGIN

RETURN

WITH

A ( PENDING )
AS (
SELECT
	COALESCE ( SUM (
	CASE WHEN AAWK1RC1A = IN_CATEGORY THEN AAWK1HR1A ELSE 0 END +
	CASE WHEN AAWK1RC1B = IN_CATEGORY THEN AAWK1HR1B ELSE 0 END +
	CASE WHEN AAWK1RC2A = IN_CATEGORY THEN AAWK1HR2A ELSE 0 END +
	CASE WHEN AAWK1RC2B = IN_CATEGORY THEN AAWK1HR2B ELSE 0 END +
	CASE WHEN AAWK1RC3A = IN_CATEGORY THEN AAWK1HR3A ELSE 0 END +
	CASE WHEN AAWK1RC3B = IN_CATEGORY THEN AAWK1HR3B ELSE 0 END +
	CASE WHEN AAWK1RC4A = IN_CATEGORY THEN AAWK1HR4A ELSE 0 END +
	CASE WHEN AAWK1RC4B = IN_CATEGORY THEN AAWK1HR4B ELSE 0 END +
	CASE WHEN AAWK1RC5A = IN_CATEGORY THEN AAWK1HR5A ELSE 0 END +
	CASE WHEN AAWK1RC5B = IN_CATEGORY THEN AAWK1HR5B ELSE 0 END +
	CASE WHEN AAWK1RC6A = IN_CATEGORY THEN AAWK1HR6A ELSE 0 END +
	CASE WHEN AAWK1RC6B = IN_CATEGORY THEN AAWK1HR6B ELSE 0 END +
	CASE WHEN AAWK1RC7A = IN_CATEGORY THEN AAWK1HR7A ELSE 0 END +
	CASE WHEN AAWK1RC7B = IN_CATEGORY THEN AAWK1HR7B ELSE 0 END +
	
	CASE WHEN AAWK2RC1A = IN_CATEGORY THEN AAWK2HR1A ELSE 0 END +
	CASE WHEN AAWK2RC1B = IN_CATEGORY THEN AAWK2HR1B ELSE 0 END +
	CASE WHEN AAWK2RC2A = IN_CATEGORY THEN AAWK2HR2A ELSE 0 END +
	CASE WHEN AAWK2RC2B = IN_CATEGORY THEN AAWK2HR2B ELSE 0 END +
	CASE WHEN AAWK2RC3A = IN_CATEGORY THEN AAWK2HR3A ELSE 0 END +
	CASE WHEN AAWK2RC3B = IN_CATEGORY THEN AAWK2HR3B ELSE 0 END +
	CASE WHEN AAWK2RC4A = IN_CATEGORY THEN AAWK2HR4A ELSE 0 END +
	CASE WHEN AAWK2RC4B = IN_CATEGORY THEN AAWK2HR4B ELSE 0 END +
	CASE WHEN AAWK2RC5A = IN_CATEGORY THEN AAWK2HR5A ELSE 0 END +
	CASE WHEN AAWK2RC5B = IN_CATEGORY THEN AAWK2HR5B ELSE 0 END +
	CASE WHEN AAWK2RC6A = IN_CATEGORY THEN AAWK2HR6A ELSE 0 END +
	CASE WHEN AAWK2RC6B = IN_CATEGORY THEN AAWK2HR6B ELSE 0 END +
	CASE WHEN AAWK2RC7A = IN_CATEGORY THEN AAWK2HR7A ELSE 0 END +
	CASE WHEN AAWK2RC7B = IN_CATEGORY THEN AAWK1HR7B ELSE 0 END
	) , 0 )
		
	FROM HRLYPAPAA
	WHERE AACLK# = REFACTOR_EMPLOYEE_ID ( IN_EMPLOYEE_NUMBER )
)

SELECT A . PENDING
FROM A ;;

END ;;