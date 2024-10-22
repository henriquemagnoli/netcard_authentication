<?php

namespace Netcard\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Netcard\Model\ResponseMessage;
use Netcard\Dao\Impl\AuthenticationImpl;
use PDOException;
use Exception;

class AuthenticationController
{
    public function signIn(Request $request, Response $response) : Response
    {
        try
        {
            $signIn = new AuthenticationImpl();

            $response_message = $signIn->signIn($request->getBody());

            $response->getBody()->write(json_encode($response_message->send()));
            return $response;
        }
        catch(PDOException $ex)
        {
            $response_message = new ResponseMessage();
            $response_message->setSuccess(false);
            $response_message->setHttpStatusCode(500);
            $response_message->setMessages("Ocorreu um erro na conexão com o servidor.");

            $response->getBody()->write(json_encode($response_message->send()));
            return $response;
        }
        catch(Exception $ex)
        {
            $response_message = new ResponseMessage();
            $response_message->setSuccess(false);
            $response_message->setHttpStatusCode(empty($ex->getCode()) ? 500 : $ex->getCode());
            $response_message->setMessages(empty($ex->getMessage()) ? "Ocorreu um erro ao realizar o login." : $ex->getMessage());

            $response->getBody()->write(json_encode($response_message->send()));
            return $response;
            
        }
       
    }
}

?>