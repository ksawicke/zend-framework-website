/**
 * Author:  sawik
 * Created: Mar 28, 2016
 */
cl: call sawik/ll;
cl: CHGCURLIB CURLIB(SAWIK) ;

create table TIMEOFF_REQUEST_CODES for system name TOREQCODES (
	REQUEST_CODE			for column REQCODE 		varchar(1) not null default,
	DESCRIPTION			for column REQCDDESC 		varchar(50) not null default
)
rcdfmt TOREQCODE1;
cl: crtjrnrcv TORQCDRCV;;
cl: crtjrn JRN(SAWIK/TORQCDJRN) JRNRCV(SAWIK/TORQCDRCV);;
cl: STRJRNPF FILE(TOREQCODES) JRN(TORQCDJRN) IMAGES(*BOTH);;
cl: grtobjaut obj(TOREQCODES) objtype(*file) refobj(hrdbfa/prpms) refobjtype(*file);;      
cl: chgobjown obj(TOREQCODES) objtype(*file) newown(s2kobjownr);;