<?php

namespace Controller;

use Exception\UnauthorizedException;
use Exception\BadRequestException;
use Exception\NotFoundException;
use Exception\ForbiddenException;

class FlightCommentController extends BaseController
{
    public function getComment($flightId)
    {
        return json_encode(1);
    }

    public function setComment(
        $flightId,
        $comment,
        $commanderAdmitted,
        $aircraftAllowed,
        $generalAdmission
    ) {
        return json_encode(1);
    }
}
