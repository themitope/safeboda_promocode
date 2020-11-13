<?php
	define('SECRETE_KEY', 'test123');

	//Datatypes
	define('BOOLEAN', '1');
	define('INTEGER', '2');
	define('STRING', '3');

//	Error codes

	define('INCORRECT_PASSWORD', 100);
	define('REQUEST_CONTENTTYPE_NOT_VALID', 101);
	define('REQUEST_NOT_VALID', 102);
	define('VALIDATE_PARAMETER_REQUIRED', 103);
	define('VALIDATE_PARAMETER_DATATYPE', 104);
	define('INVALID_USER_PASS', 108);
	define('USER_NOT_ACTIVE', 109);
	define('SUCCESS_RESPONSE', 200);
	define('CODE_EXISTS', 105);
	define('PASSWORD_MISMATCH', 106);
	define('DB_ERROR', 107);
	define('NULL', 303);
	define('RECORD_EXISTS', 305);

	//Server Errors
	define('AUTHORIZATION_HEADER_NOT_FOUND',  300);
	define('ACCESS_TOKEN_ERRORS', 301);

	define('JWT_PROCESSING_ERROR', 302);
?>