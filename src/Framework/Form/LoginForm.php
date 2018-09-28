<?php
/*
*MIT License
*
*Copyright (c) 2018 Christophe Brachet
*
*Permission is hereby granted, free of charge, to any person obtaining a copy
*of this software and associated documentation files (the "Software"), to deal
*in the Software without restriction, including without limitation the rights
*to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
*copies of the Software, and to permit persons to whom the Software is
*furnished to do so, subject to the following conditions:
*
*The above copyright notice and this permission notice shall be included in all
*copies or substantial portions of the Software.
*
*THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
*IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
*FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
*AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
*LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
*OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
*SOFTWARE.
*
*/
namespace Aspi\Framework\Form;
use Aspi\Framework\Form\Element\Input;
use Pop\Validator;

class LoginForm extends  \Aspi\Framework\Form\AspiForm
{
    public function build()
    {
        $emailValidator = new Validator\Email();
        $emailValidator->setMessage($this->container['Translator']->trans('miss-formatted-email'));
        $emptyEmailValidator = new Validator\NotEmpty();
        $emptyEmailValidator->setMessage($this->container['Translator']->trans('empty-email'));

        $passwordValidator = new Validator\LengthBetween([15, 20]);
        $passwordValidator->setMessage($this->container['Translator']->trans('password-between'));
        $email = new Input\Text('email');
        $email->setLabel($this->container['Translator']->trans('email'))
        ->addValidator($emptyEmailValidator)
        ->addValidator($emailValidator)
        ->setAttribute('placeholder','Email address' )
        ->setAttribute('class','form-control')
        ->setAttribute('autofocus','autofocus')
        ->setAttribute('id','inputEmail');

        $password = new Input\Password('password');
        $password->setLabel($this->container['Translator']->trans('password'))
        ->addValidator($passwordValidator)
        ->setAttribute('placeholder','Mot de passe' )
        ->setAttribute('class','form-control')
        ->setAttribute('autofocus','autofocus')
        ->setAttribute('id','inputPassword');

        $captcha = new Input\Captcha('captcha');
        $captcha->setLabel('Saisir le captcha : ');
        
        $submit = new Input\Submit('submit',$this->container['Translator']->trans('login'));
        $submit->setAttribute('class','btn btn-primary btn-block');
        $this->addFields([$captcha,$password,$submit,$email]);
    }

}