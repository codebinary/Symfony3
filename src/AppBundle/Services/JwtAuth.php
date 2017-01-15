<?php 

namespace AppBundle\Services;

use Firebase\JWT\JWT;

class JwtAuth{

	public $manager;

	public function __construct($manager){
		$this->manager = $manager;
	}

	//Método para la validación
	public function signup($email, $password, $getHash = NULL){

		//Clave secrete
		$key = "clave-secreta";

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
			return array("status" => "success", "data" => "Login success !!");
		}else{
			return array("status" => "error", "data" => "Login failed !!");
		}
	}




}