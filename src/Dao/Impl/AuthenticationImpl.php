<?php

namespace Netcard\Dao\Impl;

use Exception;
use Netcard\Database\Connection;
use Netcard\Dao\AuthenticationDao;
use Netcard\Model\ResponseMessage;
use Netcard\Helper\HelperAuthentication;
use PDOException;
use PDO;
use Firebase\JWT\JWT;

class AuthenticationImpl implements AuthenticationDao
{
    public function signIn(object $body): ResponseMessage
    {
        try
        {
            $response_message = new ResponseMessage;

            if(!$json_data = json_decode(strval($body)))
                throw new Exception('Corpo da requisição não é um JSON válido.', 400);

            if(!isset($json_data->email) || empty($json_data->email))
            {
                (!isset($json_data->email) ? throw new Exception('E-mail não foi inserido no corpo da requisição.', 400) : false);
                (empty($json_data->email) ? throw new Exception('E-mail deve ser preenchido.') : false);
            }

            if(!isset($json_data->password) || empty($json_data->password))
            {
                (!isset($json_data->password) ? throw new Exception('Senha não foi inserida no corpo da requisição.', 400) : false);
                (empty($json_data->password) ? throw new Exception('Senha deve ser preenchida.') : false);
            }

            $connection = Connection::openConnection();

            $command = $connection->prepare(HelperAuthentication::selectAllUserInfo());
            $command->bindParam(':email', $json_data->email);
            $command->execute();

            $row_count = $command->rowCount();

            if($row_count === 0)
                throw new Exception('E-mail ou senha inválidos.', 401);

            $user_data = $command->fetch(PDO::FETCH_ASSOC);

            if($user_data['Blocked'] === 1)
                throw new Exception('Sua conta está bloqueada. Contate o suporte.', 403);

            if($user_data['Tries'] >= $user_data['Max_tries'])
                throw new Exception('Sua conta foi bloqueada devido ao número de tentativas ao realizar o login. Contate o suporte.', 403);

            if(!password_verify($json_data->password, $user_data['Password']))
            {
                $connection->beginTransaction();

                $command = $connection->prepare(HelperAuthentication::updateUserTries(1));
                $command->bindParam(':loginId', $user_data['LoginId']);
                $command->execute();

                $connection->commit();

                throw new Exception('E-mail ou senha inválidos.', 401);
            }

            $payload = [
                'id' => $user_data['LoginId'],
                'name' => $user_data['Name'],
                'email' => $user_data['Email'],
                'iat' => time(),
                'exp' => time() + 259200
            ];

            $jwt = JWT::encode($payload, $_ENV['SECRET_KEY'], 'HS256');

            $connection->beginTransaction();

            $command = $connection->prepare(HelperAuthentication::updateUserTries(0));
            $command->bindParam(':loginId', $user_data['LoginId']);
            $command->execute();

            $connection->commit();

            $returned_data = array();
            $returned_data['ID'] = $user_data['LoginId'];
            $returned_data['Name'] = $user_data['Name'];
            $returned_data['Email'] = $user_data['Email'];
            $returned_data['Token'] = $jwt;

            $response_message->setSuccess(true);
            $response_message->setHttpStatusCode(200);
            $response_message->setMessages('Login efetuado.');
            $response_message->setData($returned_data);

            return $response_message;
        }
        catch(PDOException $ex)
        {
            throw $ex;
        }
        catch(Exception $ex)
        {
            throw $ex;
        }
    }

    public function signUp(object $body): ResponseMessage
    {
        try
        {
            $response_message = new ResponseMessage();

            if(!$json_data = json_decode(strval($body)))
            {
                $response_message->buildMessage(400, false, ['Corpo da requisição não é um JSON válido.'], null);
                return $response_message;
            }

            // Test body
            if(!isset($json_data->name))
            {
                $response_message->buildMessage(400, false, ['Nome deve ser preenchido.'], null);
                return $response_message;
            }
    
            if(!isset($json_data->password))
            {
                $response_message->buildMessage(400, false, ['Senha deve ser preenchida.'], null);
                return $response_message;
            }

            if(!isset($json_data->email))
            {
                $response_message->buildMessage(400, false, ['E-mail deve ser preenchido.'], null);
                return $response_message;
            }

            if(!isset($json_data->cpf))
            {
                $response_message->buildMessage(400, false, ['Cpf deve ser preenchido.'], null);
                return $response_message;
            }

            if(!isset($json_data->profilePicture))
            {
                $response_message->buildMessage(400, false, ['Foto deve ser inserida.'], null);
                return $response_message;
            }

            if(!isset($json_data->sex))
            {
                $response_message->buildMessage(400, false, ['Sexo ser preenchido.'], null);
                return $response_message;
            }

            if(!isset($json_data->birthDate))
            {
                $response_message->buildMessage(400, false, ['Data de nascimento deve ser preenchido.'], null);
                return $response_message;
            }

            if(!isset($json_data->address))
            {   
                $response_message->buildMessage(400, false, ['Endereço deve ser preenchido.'], null);
                return $response_message;
            }
            else
            {
                if(!isset($json_data->address->street))
                {
                    $response_message->buildMessage(400, false, ['Rua deve ser preenchida.'], null);
                    return $response_message;
                }

                if(!isset($json_data->address->streetNumber))
                {
                    $response_message->buildMessage(400, false, ['Número da residência deve ser preenchida.'], null);
                    return $response_message;
                }

                if(!isset($json_data->address->cityId))
                {
                    $response_message->buildMessage(400, false, ['Cidade deve ser preenchida.'], null);
                    return $response_message;
                }

                if(!isset($json_data->address->district))
                {
                    $response_message->buildMessage(400, false, ['Bairro deve ser preenchido.'], null);
                    return $response_message;
                }

                if(!isset($json_data->address->zipCode))
                {
                    $response_message->buildMessage(400, false, ['CEP deve ser preenchido.'], null);
                    return $response_message;
                }
            }
            
            if(!isset($json_data->jobId))
            {
                $response_message->buildMessage(400, false, ['Profissão deve ser preenchida.'], null);
                return $response_message;
            }
           
            $connection = Connection::openConnection();

            $connection->beginTransaction();

            $password = password_hash($json_data->password, PASSWORD_DEFAULT);
            $streetComplement = (!isset($json_data->address->streetComplement) ? NULL : $json_data->address->streetComplement);

            $command = $connection->prepare(HelperAuthentication::insertUser());
            $command->bindParam(':name', $json_data->name);
            $command->bindParam(':password', $password);
            $command->bindParam(':email', $json_data->email);
            $command->bindParam(':cpf', $json_data->cpf);
            $command->bindParam(':profilePicture', $json_data->profilePicture);
            $command->bindParam(':sex', $json_data->sex);
            $command->bindParam(':birthDate', $json_data->birthDate);
            $command->bindParam(':street', $json_data->address->street);
            $command->bindParam(':streetNumber', $json_data->address->streetNumber);
            $command->bindParam(':cityId', $json_data->address->cityId);
            $command->bindParam(':streetComplement', $streetComplement);
            $command->bindParam(':district', $json_data->address->district);
            $command->bindParam(':zipCode',$json_data->address->zipCode);
            $command->bindParam(':jobId', $json_data->jobId);
            $command->execute();

            $user_id = $connection->lastInsertId();

            $command = $connection->prepare(HelperAuthentication::insertLogin());
            $command->bindParam(':user_id', $user_id);
            $command->execute();    

            $connection->commit();

            $response_message->buildMessage(200, true, ['Conta cadastrada com sucesso.'], null);
            return $response_message;
        }
        catch(PDOException $ex)
        {
            throw $ex;
        }
        catch(Exception $ex)
        {
            throw $ex;
        }
    }
}

?>