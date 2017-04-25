<?php
namespace Hug\Group\Services\Request;

use Paf\Lsf\Core\App;

// @deprecated
class TrackID
{
    // @deprecated
    public function get()
    {
        return app('request.client')->getTrackID();
    }

    // @deprecated
    public function set($sTrackID)
    {
        return app('request.client')->setTrackID($sTrackID);
    }
}
