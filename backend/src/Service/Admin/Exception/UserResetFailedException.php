<?php

namespace Fileknight\Service\Admin\Exception;

class UserResetFailedException extends \Exception
{
    public function __construct(string $username, string $reason = null)
    {
        $r = $reason ? "Reason: $reason" : '';
        parent::__construct("Failed to reset user $username's token. $r");
    }
}
