<?php

namespace PuzzleUserException;

use PuzzleError;

class MissingField extends PuzzleError
{
    public function __construct($reason = null)
    {
        parent::__construct($reason);
    }
}
