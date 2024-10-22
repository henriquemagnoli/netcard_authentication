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
            $response_message->buildMessage(500, false, ['Ocorreu um erro na conexão com o servidor.'], null);
            $response->getBody()->write(json_encode($response_message->send()));
            return $response;
        }
        catch(Exception $ex)
        {
            $response_message = new ResponseMessage();
            $response_message->buildMessage((empty($ex->getCode()) ? 500 : $ex->getCode()), false, empty($ex->getMessage()) ? ["Ocorreu um erro ao realizar o login."] : [$ex->getMessage()], null);
            $response->getBody()->write(json_encode($response_message->send()));
            return $response;
        }
    }

    public function signUp(Request $request, Response $response) : Response
    {
        try
        {
            $signUp = new AuthenticationImpl();

            $response_message = $signUp->signUp($request->getBody());

            $response->getBody()->write(json_encode($response_message->send()));
            return $response;
        }
        catch(PDOException $ex)
        {
            $response_message = new ResponseMessage();
            $response_message->buildMessage(500, false, ['Ocorreu um erro na conexão com o servidor.'], null);
            $response->getBody()->write(json_encode($response_message->send()));
            return $response;
        }
        catch(Exception $ex)
        {
            $response_message = new ResponseMessage();
            $response_message->buildMessage((empty($ex->getCode()) ? 500 : $ex->getCode()), false, empty($ex->getMessage()) ? ["Ocorreu um erro ao cadatrar o usuário."] : [$ex->getMessage()], null);

            $response->getBody()->write(json_encode($response_message->send()));
            return $response;
        }
    }
}

?>