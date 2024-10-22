<?php

namespace Netcard\Model;

class ResponseMessage
{
    private $_success;
    private $_httpStatusCode;
    private $_messages;
    private $_data;
    private $_responseData = array();

    // Set functions
    public function setSuccess($success){
        $this->_success = $success;
    }

    public function setHttpStatusCode($httpStatusCode){
        $this->_httpStatusCode = $httpStatusCode;
    }

    public function setMessages($message){
        $this->_messages = $message;
    }

    public function setData($data){
        $this->_data = $data;
    }

    // Get functions
    public function getSuccess(){
        return $this->_success;
    }

    public function getData(){
        return $this->_data;
    }

    // Send function will create the JSON response
    public function send()
    {
        http_response_code($this->_httpStatusCode);

        $this->_responseData['statusCode'] = $this->_httpStatusCode;
        $this->_responseData['success'] = $this->_success;
        $this->_responseData['messages'] = $this->_messages;
        $this->_responseData['data'] = $this->_data;

        return $this->_responseData;
    }

    public function buildMessage(int $status_code, bool $success, ?array $messages, mixed $data)
    {   
        $this->setHttpStatusCode($status_code);
        $this->setSuccess($success);
        $this->setMessages($messages);
        $this->setData($data);
    }
}

?>