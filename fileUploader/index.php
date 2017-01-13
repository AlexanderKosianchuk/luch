<?php
/*
 * jQuery File Upload Plugin PHP Example 5.14
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

error_reporting(E_ALL | E_STRICT);
set_time_limit (0);
ini_set('post_max_size', '128M');
ini_set('upload_max_filesize', '128M');

require('UploadHandler.php');
$upload_handler = new UploadHandler();

?>
