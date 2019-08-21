<?php

namespace PuzzleUserException;

use PuzzleError;

class GroupNotFound extends PuzzleError
{
    public function __construct($reason = null)
    {
        parent::__construct($reason);
    }
}
