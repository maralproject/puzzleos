<?php

namespace PuzzleUserException;

use PuzzleError;

class FailedToSendOTP extends PuzzleError
{
    public function __construct($reason = null)
    {
        parent::__construct($reason);
    }
}
