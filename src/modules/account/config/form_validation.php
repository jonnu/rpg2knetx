<?php

$config = array(

    'account-register' => array(

        array(
            'field' => 'user_email',
            'label' => 'Email Address',
            'rules' => 'required'
        )
    ),

    'account-login' => array(

        array(
            'field' => 'user_email',
            'label' => 'Email Address',
            'rules' => 'required|valid_email'
        ),
        array(
            'field' => 'user_password',
            'label' => 'Password',
            'rules' => 'required'
        ),
        array(
            'field' => 'user_persistent',
            'label' => 'Remember Me',
            'rules' => ''
        )
    ),

    'account-lost' => array(

        array(
            'field' => 'user_email',
            'label' => 'Email Address',
            'rules' => 'required|valid_email'
        )
    ),

    'account-reset' => array(

        array(
            'field' => 'user_password',
            'label' => 'Password',
            'rules' => 'required'
        ),
        array(
            'field' => 'user_password_confirm',
            'label' => 'Confirm Password',
            'rules' => 'required|matches[user_password]'
        )
    ),

    'account-register' => array(

        array(
            'field' => 'user_name',
            'label' => 'User Name',
            'rules' => 'required|module[user_model,valid_username]'
        ),
        array(
            'field' => 'user_email',
            'label' => 'Email Address',
            'rules' => 'required|valid_email|is_unique[user.user_email]'
        ),
        array(
            'field' => 'user_password',
            'label' => 'Password',
            'rules' => 'required'
        ),
        array(
            'field' => 'user_password_confirm',
            'label' => 'Confirm Password',
            'rules' => 'required|matches[user_password]'
        )
    ),

    'account-temp' => array(

        array(
            'field' => 'file',
            'label' => 'File',
            'rules' => 'trim'
        ),

        array(
            'field' => 'file_nonce',
            'label' => 'file_nonce',
            'rules' => 'required'
        )

    )

);