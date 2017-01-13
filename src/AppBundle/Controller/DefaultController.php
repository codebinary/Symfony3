<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..'),
        ]);
    }


    public function pruebasAction(Request $request)
    {   
        //Cremos un entity manager para poder trabajar con entidades
        $em = $this->getDoctrine()->getManager();
        //Sacamos todos los registros de la entidad usuarios
        $user = $em->getRepository('BackendBundle:User')->findAll();

        var_dump($user);

        die();
    }
}
