<?php

namespace Controller;
use Pimple\Container;


class HomeController
{
    private $container = null;
    public function __construct(Container $container)
    {
       
        $this->container = $container;
    }
    public function Login(\Psr\Http\Message\ServerRequestInterface  $request)
    {
        
     
        $form = new  \Aspi\Framework\Form\LoginForm($this->container,$request);
        $form->build();
        if ($form->isSubmitted() && $form->isValid()) {
            $form->clearTokens();
            $form->getField('email')->setAttribute('class','form-control is-valid');
            $form->getField('password')->setAttribute('class','form-control is-valid');

        }
        else
        {
            if($form->getField('email')->hasErrors())
            {
                $form->getField('email')->setAttribute('class','form-control is-invalid');
            }
            if($form->getField('password')->hasErrors())
            {
                $form->getField('password')->setAttribute('class','form-control is-invalid');
            }
        }
        return  $this->container['twig']->render(200,'front/login.twig.html',array('form'=>$form));
    }
    public function Index(\Psr\Http\Message\ServerRequestInterface  $request)
    {

       return  $this->container['twig']->render(200,'front/test.twig.html',array());
    }

}