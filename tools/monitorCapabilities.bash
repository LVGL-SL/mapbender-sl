#!/bin/bash
. /etc/profile
[ -f /tmp/wmsmonitorlock ] && : || /usr/bin/php /opt/geoportal/mapbender/tools/mod_monitorCapabilities_main.php group:36 > /dev/null
