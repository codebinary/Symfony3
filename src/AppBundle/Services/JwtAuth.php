<?php 

namespace AppBundle\Services;

use Firebase\JWT\JWT;

class JwtAuth{

	public $manager;
	public $key;

	public function __construct($manager){
		$this->manager = $manager;
		$this->key = "clave-secreta";
	}

	//Método para la validación de login
	public function signup($email, $password, $getHash = NULL){

		//Clave secrete
		$key = $this->key;

		//Consulta para comprobar los datos
		//Esto es como decir SELECT * FROM USER WHERE EMAIL = $EMAIL AND PASSWORD = $PASSWORD
		$user = $this->manager->getRepository('BackendBundle:User')->findOneBy(
				array(
					"email" => $email,
					"password" => $password
				)
			);

		$signup = false;

		//si devuelve un objeto
		if (is_object($user)) {
			$signup = true;
		}

		if ($signup == true) {

			$token = array(
				"sub" 		=> $user->getId(),
				"email" 	=> $user->getEmail(),
				"name" 		=> $user->getName(),
				"surname" 	=> $user->getSurname(),
				"password" 	=> $user->getPassword(),
				"image" 	=> $user->getImage(),
				"iat" 		=> time(),//Cuando se ha creado el token
				"exp" 		=> time() + (7 * 24 * 60 * 60)//Fecha de expiración
			);	

			//codificamos los datos
			$jwt = JWT::encode($token, $key, "HS256");

			$decode = JWT::decode($jwt, $key, array('HS256'));	

			//Datos en limpio
			if ($getHash != null) {
				return $jwt;
			}else{
				return $decode;
			}

			//return array("status" => "success", "data" => "Login success !!");
		}else{
			return array("status" => "error", "data" => "Login failed !!");
		}
	}


	//Método que recibe el token y lo validará
	public function checkToken($jwt, $getIdentity = false){

		$key = $this->key; 
		$auth = false;

		try{

			$decoded = JWT::decode($jwt, $key, array('HS256'));

		}catch(\UnexpectedValueException $e){

			$auth = false;

		}catch(\DomainException $e){

			$auth = false;

		}

		//Si existe la propiedad entonces el token es correcto
		//(Sub es el id del usuario) pero puede ser cualquier datos del user
		if (isset($decoded->sub)) {
			$auth = true;
		}else{
			$auth = false;
		}

		if ($getIdentity == true) {
			return $decoded;
		}else{
			return $auth;
		}


	}




}