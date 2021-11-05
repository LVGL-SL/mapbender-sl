<?php

declare(strict_types=1);

namespace Mapbender\WMS\Search;

require_once '/opt/geoportal/mapbender/core/globalSettings.php';


class WMSSearchPermission
{
    public static function getAvailableWMSByMbUserId(int $userId): array
    {
        $mapbender_admin = new \administration();
        $user_guis = $mapbender_admin->getGuisByPermission($userId, true);
        return $mapbender_admin->getWmsByOwnGuis($user_guis);
    }
}
