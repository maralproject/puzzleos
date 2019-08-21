<?php

namespace PuzzleUserException;

use PuzzleError;

class UserNotFound extends PuzzleError
{
    public function __construct($reason = null)
    {
        parent::__construct($reason);
    }
}
