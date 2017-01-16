<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Component\HttpFoundation\JsonResponse;

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
        //Servicio de respuesta en Json
        $helpers = $this->get("app.helpers");
        //Llamamos al servicio de validación
        $jwt_auth = $this->get("app.jwt_auth");


        //Vamos a recibir un json por post
        $json = $request->get("json", null);

        if ($json != null) {
            //Recogemos los parámetros y decodificamos
            $params = json_decode($json);   

            //Obtenemos los valores email y password
            $email = (isset($params->email) ? $params->email : null);
            $password = (isset($params->password) ? $params->password : null);
            $gethash = (isset($params->gethash) ? $params->gethash : null);

            //Restricción de email
            $emailContraint = new Assert\Email();
            $emailContraint->message = "This email is not valid !!";

            //Validamos los parámetros
            $validate_email = $this->get("validator")->validate($email, $emailContraint);

            //Ciframos la contraseña
            $pwd = hash('sha256', $password);

            //Si hay datos de error en $validate_email entonces es invalido
            if (count($validate_email) == 0 && $password != null) {

                if ($gethash == null) {
                    $signup = $jwt_auth->signup($email, $pwd);
                }else{
                    $signup = $jwt_auth->signup($email, $pwd, "hash");
                }

                return new JsonResponse($signup);

            }else{
                return $helpers->json(
                            array(
                                "status"    => "error",
                                "data"      => "Login not valid !"
                            )
                        );
            }


        }else{
            return $helpers->json(
                    array(
                            "status"    => "error",
                            "data"      => "Login not valid!!"
                        )
                    );
        }
    }


    public function pruebasAction(Request $request)
    {   
        //Servicio de respuesta en Json
        $helpers = $this->get("app.helpers");
        //Llamamos al servicio de validación

        $hash = $request->get("authorization", null);
        $check = $helpers->authCheck($hash, true);

        var_dump($check);
        die();
        //Cremos un entity manager para poder trabajar con entidades
        /*$em = $this->getDoctrine()->getManager();
        //Sacamos todos los registros de la entidad usuarios
        $user = $em->getRepository('BackendBundle:User')->findAll();*/


        return $helpers->json($user);

        
    }


}
