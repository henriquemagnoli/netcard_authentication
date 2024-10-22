<?php

namespace Netcard\Dao;

use Netcard\Model\ResponseMessage;

interface AuthenticationDao
{
    public function signIn(object $body) : ResponseMessage;
    public function signUp(object $body) : ResponseMessage;
}

?>