<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use Firebase\JWT\JWT;

class AuthController extends ResourceController
{
    public function __construct()
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(ROOTPATH);
        $dotenv->load();
    }

    public function login()
    {
        $usernameOrEmail = $this->request->getVar('username_or_email'); // Can be `email_admin` or `username`
        $password = $this->request->getVar('password');
        $userType = $this->request->getVar('user_type'); // 'admin' or 'member'

        $key = getenv('JWT_SECRET');
        if (!$key) {
            return $this->fail('JWT Secret not set');
        }

        if ($userType === 'admin') {
            $model = new \App\Models\AdminModel();
            $user = $model->where('email_admin', $usernameOrEmail)->first();
            $userIdField = 'id_admin';
            $nameField = 'nama_admin';
            $emailField = 'email_admin';
        } elseif ($userType === 'member') {
            $model = new \App\Models\MemberModel();
            $user = $model->where('username', $usernameOrEmail)->first();
            $userIdField = 'id_anggota';
            $nameField = 'nama_anggota';
            $usernameField = 'username';
        } else {
            return $this->fail('Invalid user type');
        }

        if (!$user) {
            return $this->failUnauthorized('User not found');
        }

        $storedPassword = trim($user['password']); 
        $inputPassword = trim($this->request->getVar('password'));

        if (!password_verify($inputPassword, $storedPassword)) {
            return $this->failUnauthorized(
                'Password does not match. Debug Info: ' . json_encode([
                    'stored_password' => $storedPassword,
                    'input_password' => $inputPassword,
                    'password_verify_result' => password_verify($inputPassword, $storedPassword) ? 'true' : 'false'
                ])
            );
        }

        // Generate JWT Token
        $payload = [
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24), // Token valid for 1 day
            'sub' => $user[$userIdField],
            'role' => $userType
        ];

        $token = JWT::encode($payload, $key, 'HS256');

        $responseUser = [
            'id' => $user[$userIdField],
            'name' => $user[$nameField],
            'role' => $userType
        ];

        if ($userType === 'admin') {
            $responseUser['email'] = $user[$emailField];
        } elseif ($userType === 'member') {
            $responseUser['username'] = $user[$usernameField];
        }

        return $this->respond([
            'token' => $token,
            'type' => 'Bearer',
            'user' => $responseUser
        ]);
    }
}