@ECHO off

IF NOT EXIST "c:\progra~1\nsclient++" GOTO NSCLIENT2
	set dir="c:\progra~1\nsclient++\plugins"
	mkdir %dir%
	GOTO COPY
:NSCLIENT2
IF NOT EXIST "c:\progra~2\nsclient++" GOTO NONSCLIENT
	set dir="c:\progra~2\nsclient++\plugins"
	mkdir %dir%
	GOTO COPY
:NONSCLIENT
	set dir="c:\progra~1\check_openmanage"
	mkdir %dir%
	GOTO COPY
echo %dir%

:COPY
copy check_openmanage %dir%
copy check_openmanage %dir%\check_openmanage_alertlog
copy check_openmanage %dir%\check_openmanage_batteries
copy check_openmanage %dir%\check_openmanage_cpu
copy check_openmanage %dir%\check_openmanage_esmlog
copy check_openmanage %dir%\check_openmanage_esmhealth
copy check_openmanage %dir%\check_openmanage_fans
copy check_openmanage %dir%\check_openmanage_intrusion
copy check_openmanage %dir%\check_openmanage_memory
copy check_openmanage %dir%\check_openmanage_power
copy check_openmanage %dir%\check_openmanage_pwrmonitor
copy check_openmanage %dir%\check_openmanage_storage
copy check_openmanage %dir%\check_openmanage_temperature
