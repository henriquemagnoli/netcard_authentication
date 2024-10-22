<?php

namespace Netcard\Helper;

class HelperAuthentication
{
    public static function selectAllUserInfo() : string
    {
        return "SELECT L.Id AS LoginId, U.Id AS UserId, U.Name, U.Password, U.Email, L.Blocked, L.Tries, L.Max_tries, L.Show_user FROM tb_login AS L, tb_users AS U WHERE U.Email = :email;";
    }
    
    public static function updateUserTries(int $tries) : string
    {
        return "UPDATE tb_login SET Tries = Tries + $tries WHERE Id = :loginId;";
    }

    public static function insertUser() : string
    {
        return "INSERT INTO tb_users (Name, 
                                      Password, 
                                      Email,
                                      Cpf, 
                                      Profile_picture, 
                                      Sex, 
                                      Birth_date, 
                                      Street, 
                                      Street_number, 
                                      City_id, 
                                      Street_complement, 
                                      District, 
                                      Zip_code, 
                                      Job_id)
                              VALUES (:name,
                                      :password,
                                      :email,
                                      :cpf,
                                      :profilePicture,
                                      :sex,
                                      :birthDate,
                                      :street,
                                      :streetNumber,
                                      :cityId,
                                      :streetComplement,
                                      :district,
                                      :zipCode,
                                      :jobId);";
    }

    public static function insertLogin() : string
    {
        return "INSERT INTO tb_login (User_id) VALUES (:user_id);";
    }
}

?>