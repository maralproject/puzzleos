<?php

namespace PuzzleUserException;

use PuzzleError;

class InvalidField extends PuzzleError
{
    public function __construct($reason = null)
    {
        parent::__construct($reason);
    }
}
