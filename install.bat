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
copy check_openmanage.exe %dir%
