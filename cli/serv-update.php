<?php

passthru('echo "Updating the System..."');
passthru("sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 4M/' /etc/php5/cli/php.ini");

passthru('echo "Updates finished."');