<?php

// ------------------------------------------------
//	LDAP SERVER ACCESS
// ------------------------------------------------

// Remote LDAP server hostname.
define('UL_LDAP_DEFAULT_HOST', 'ldap.your_domain.tld');

// Remote LDAP server port.
// Usually 389 or 636.
define('UL_LDAP_DEFAULT_PORT', 636);

// Encryption type for LDAP communication.
// 'SSL', 'TLS' or 'None'
define('UL_LDAP_DEFAULT_ENCRYPTION', 'SSL');

// Template that is used to build the DN from a username.
// The substring '[username]' will be automatically replaced by
// the current username.
define('UL_LDAP_DN_TEMPLATE', 'uid=[username],ou=users,dc=luch,dc=tld');

// LDAP attribute that contains the user-friendly username.
define('UL_LDAP_NICK_ATTRIB', 'uid');

// LDAP attribute that contains the user's password.
define('UL_LDAP_PWD_ATTRIB', 'userPassword');

// Hash type that is used to store passwords.
// Valid options are: '{SSHA}', '{SHA}', '{SMD5}', '{MD5}', '{CRYPT}'
define('UL_LDAP_PWD_HASH', '{SSHA}');

// LDAP DN and password with read-only access to the directory.
define('UL_LDAP_SEARCH_DN', 'user');
define('UL_LDAP_SEARCH_PWD', 'userPassword');

// LDAP DN and password with creation, deletion, modification
// and optionally password setting rights for anyone else.
define('UL_LDAP_PRIVILEGED_DN', 'user');
define('UL_LDAP_PRIVILEGED_PWD', 'userPassword');
?>