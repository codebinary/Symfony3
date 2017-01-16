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




	//Método para editar un usuario
	public function editAction(Request $request){

		//Cargamos el servicio de helpers
		$helpers = $this->get("app.helpers");

		//Comprabamos que el token que llega sea el correcto
		$hash = $request->get("authorization", null);
		$authCheck = $helpers->authCheck($hash);

		//Si el token es correcto entonces realizamos todo el proceso
		if ($authCheck == true) {
			
			//Decodificamos los datos que lleguen en el token
			$identity = $helpers->authCheck($hash, true);

			$em = $this->getDoctrine()->getManager();
			//Igualamos el valor del token llamando sub con el id si coinciden
			$user = $em->getRepository("BackendBundle:User")->findOneBy(
					array(
							"id" => $identity->sub
						)
				);

			//Recogemos los datos que llegan de post
			$json = $request->get("json", null);
			//Parámetros
			$params = json_decode($json);

			$data = array(
				"status" => "error",
				"code" => 400,
				"msg" => "User not updated"
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

				if ($email != null && count($validate_email) == 0 && $name != null && $surname != null) {
					
					//Creamos el usuarios
					//$user = new User();

					$user->setCreatedAt($createdAt);
					$user->setImage($image);
					$user->setRole($role);
					$user->setEmail($email);
					$user->setName($name);
					$user->setSurname($surname);

					if ($password != null) {
						//Cifrar password
						$pwd = hash('sha256', $password);
						$user->setPassword($pwd);
					}

					$em = $this->getDoctrine()->getManager();

					//Buscamos el email para saber que no está y luego guardarlo
					$isset_user = $em->getRepository("BackendBundle:User")->findBy(
							array(
								"email" => $email
							)
						);

					//confirmamos si el email es igual al que recibimos por post
					if (count($isset_user) == 0 || $identity->email == $email) {
						$em->persist($user); //Persistimos en la clase los datos
						$em->flush(); //Guardamos en la base de datos

						$data["status"] = 'success';
						$data["code"] = 200;
						$data["msg"]	= 'User updated created!!';
					}else{
						$data = array(
							"status" 	=> "error",
							"code"		=>	400,
							"msg"		=> "User not updated, duplicated!!"
						);
					}
				}
			}
		}else{
			$data = array(
					"status" 	=> "error",
					"code"		=> 	400,
					"msg"		=> 	"Authorization not valid"
				);
		}

	return $helpers->json($data);
	}


	public function uploadImageAction(Request $request){

		$helpers = $this->get("app.helpers");

		$hash = $request->get("authorization", null);
		$authCheck = $helpers->authCheck($hash);

		if ($authCheck == true) {
			//Decodificamos el token
			$identity = $helpers->authCheck($hash, true);

			$em = $this->getDoctrine()->getManager();
			$user = $em->getRepository("BackendBundle:User")->findOneBy(array(
					"id" => $identity->sub
				));

			//Obtenemos el fichero que llego por post 
			$file = $request->files->get("image");

			if (!empty($file) && $file != null) {
				$ext = $file->guessExtension(); //Obtenemos la extensión del archivo

				//Comprobamos que el fichero sea una imagen
				if ($ext == "jpg" || $ext == "jpeg" || $ext == "png" || $ext == "gif") {
					$file_name = time().".".$ext;
					$file->move("uploads/users", $file_name);

					$user->setImage($file_name);
					$em->persist($user);
					$em->flush();

					$data = array(
						"status" => "success",
						"code"	 => 200,
						"msg"	 => "Image for user uploaded success !!"
					);
				}else{
					$data = array(
						"status" => "error",
						"code"	 => 200,
						"msg"	 => "File not valid!!"
					);
				}
			}else{
				$data = array(
					"status" => "error",
					"code"	 => 400,
					"msg"	 => "Image not uploaded"
				);
			}

		}else{
			$data = array(
				"status" => "error",
				"code"	 => 400,
				"msg"	 => "Authorization not valid"
			);
		}

		return $helpers->json($data);

	}

}