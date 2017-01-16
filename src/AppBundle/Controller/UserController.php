<?php 

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\JsonResponse;
use BackendBundle\Entity\User;

class UserController extends Controller{

	//Crear y registrar usuario
	public function newAction(Request $request){

		//Cargamos el servicio de helpers
		$helpers = $this->get("app.helpers");

		//Recogemos los datos que llegan de post
		$json = $request->get("json", null);
		//Parámetros
		$params = json_decode($json);

		$data = array(
			"status" => "error",
			"code" => 400,
			"msg" => "User not created"
		);

		if ($json != null) {

			$createdAt = new \DateTime("now");
			$image = null;
			$role = "user";

			$email = (isset($params->email)) ? $params->email : null;
			$name = (isset($params->name) && ctype_alpha($params->name)) ? $params->name : null;
			$surname = (isset($params->surname) && ctype_alpha($params->surname)) ? $params->surname : null;
			$password = (isset($params->password)) ? $params->password : null;

			$emailContraint = new Assert\Email();
            $emailContraint->message = "This email is not valid !!";
            $validate_email = $this->get("validator")->validate($email, $emailContraint);

			if ($email != null && count($validate_email) == 0
				&& $password != null && $name != null && $surname != null) {
				
				//Creamos el usuarios
				$user = new User();

				$user->setCreatedAt($createdAt);
				$user->setImage($image);
				$user->setRole($role);
				$user->setEmail($email);
				$user->setName($name);
				$user->setSurname($surname);

				//Cifrar password
				$pwd = hash('sha256', $password);
				$user->setPassword($pwd);

				$em = $this->getDoctrine()->getManager();

				//Buscamos el email para saber que no está y luego guardarlo
				$isset_user = $em->getRepository("BackendBundle:User")->findBy(
						array(
							"email" => $email
						)
					);

				//si no existe ningún email, entonces creamos el usuario
				if (count($isset_user) == 0) {
					$em->persist($user);
					$em->flush();

					$data["status"] = 'success';
					$data["code"] = 200;
					$data["msg"]	= 'New User created!!';
				}else{
					$data = array(
						"status" 	=> "error",
						"code"		=>	400,
						"msg"		=> "User not created, duplicated!!"
					);
				}

			}

		}

		return $helpers->json($data);

	}

}