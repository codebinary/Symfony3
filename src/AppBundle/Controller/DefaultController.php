<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Validator\Constraints as Assert;

class DefaultController extends Controller
{
    
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..'),
        ]);
    }

    public function loginAction(Request $request){
        $helpers = $this->get("app.helpers");

        //Vamos a recibir un json por post
        $json = $request->get("json", null);

        if ($json != null) {
            //Recogemos los parámetros y decodificamos
            $params = json_decode($json);

            //Obtenemos los valores email y password
            $email = (isset($params->email) ? $params->email : null);
            $password = (isset($params->password) ? $params->password : null);

            //Restricción de email
            $emailContraint = new Assert\Email();
            $emailContraint->message = "This email is not valid !!";

            //Validamos los parámetros
            $validate_email = $this->get("validator")->validate($email, $emailContraint);

            //Si hay datos de error en $validate_email entonces es invalido
            if (count($validate_email) == 0 && $password != null) {
                echo "Data Success !!";
            }else{
                echo "Data incorrent !!";
            }


        }else{
            echo "No hay datos";
            die();
        }
    }


    public function pruebasAction(Request $request)
    {   
        //Creamos nuestra varialble helpers para poder utulizar el méotodo json
        $helpers = $this->get("app.helpers");

        //Cremos un entity manager para poder trabajar con entidades
        $em = $this->getDoctrine()->getManager();
        //Sacamos todos los registros de la entidad usuarios
        $user = $em->getRepository('BackendBundle:User')->findAll();


        return $helpers->json($user);

        
    }


}
