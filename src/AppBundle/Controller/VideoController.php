<?php 

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\JsonResponse;
use BackendBundle\Entity\User;
use BackendBundle\Entity\Video;

class VideoController extends Controller{

	/**
	 *
	 * Método para crear video
	 *
	 */
	
	public function newAction(Request $request){
		
		$helpers = $this->get("app.helpers");

		$hash = $request->get("authorization", null);
		$authCheck = $helpers->authCheck($hash);

		if ($authCheck == true) {
			//Decadificamos el token para trabajar con los datos y buscar el usuario en la base de datos
			$identity = $helpers->authCheck($hash, true);

			//Recibimos todos los datos por post
			$json = $request->get("json", null);

			if ($json != null) {

				$params = json_decode($json);

				$createdAt = new \Datetime('now');
				$updatedAt = new \Datetime('now');
				$image = null;
				$video_path = null;

				$user_id = ($identity->sub != null) ? $identity->sub : null;
				$title = (isset($params->title)) ? $params->title : null;
				$description = (isset($params->description)) ? $params->description : null;
				$status = (isset($params->status)) ? $params->status : null;

				if ($user_id != null && $title != null) {
					//Cargamos el entity manager
					$em = $this->getDoctrine()->getManager();

					$user = $em->getRepository("BackendBundle:User")->findOneBy(
						array(
							"id" => $user_id
						));

					//Setemos los datos del video obteniendo el objeto del usuario quien creo el video
					$video = new Video();
					$video->setUser($user);
					$video->setTitle($title);
					$video->setDescription($description);
					$video->setStatus($status);
					$video->setCreatedAt($createdAt);
					$video->setUpdatedAt($updatedAt);

					//Persistimos los datos en doctrine
					$em->persist($video);
					//Guardamos los datos en la BD
					$em->flush();

					$video = $em->getRepository("BackendBundle:Video")->findOneBy(
							array(
								"user" 		=> $user,
								"title" 	=> $title,
								"status" 	=> $status,
								"createdAt" => $createdAt
							));

					$data = array(
						"status" => "success", 
						"code" => 200, 
						"data" => $video
					);

				}else{
					$data = array(
						"status" => "error", 
						"code" => 400, 
						"msg" => "Video not created"
					);
				}

			}else{
				$data = array(
					"status" => "error", 
					"code" => 400, 
					"msg" => "Video not created, params failed"
				);
			}
			




		}else{
			$data = array(
				"status" => "error", 
				"code" => 400, 
				"msg" => "Authorization not valid"
			);
		}

		return $helpers->json($data);

	}


	/**
	 *
	 * Método para editar video
	 *
	 */
	
	public function editAction(Request $request, $id = null){
		
		$helpers = $this->get("app.helpers");

		$hash = $request->get("authorization", null);
		$authCheck = $helpers->authCheck($hash);

		if ($authCheck == true) {
			//Decadificamos el token para trabajar con los datos y buscar el usuario en la base de datos
			$identity = $helpers->authCheck($hash, true);

			//Recibimos todos los datos por post
			$json = $request->get("json", null);

			if ($json != null) {

				$params = json_decode($json);

				$video_id = $id;

				$createdAt = new \Datetime('now');
				$updatedAt = new \Datetime('now');
				$image = null;
				$video_path = null;

				$user_id = ($identity->sub != null) ? $identity->sub : null;
				$title = (isset($params->title)) ? $params->title : null;
				$description = (isset($params->description)) ? $params->description : null;
				$status = (isset($params->status)) ? $params->status : null;

				if ($user_id != null && $title != null) {
					//Cargamos el entity manager
					$em = $this->getDoctrine()->getManager();

					//Buscamos el video para editar
					$video = $em->getRepository("BackendBundle:Video")->findOneBy(
						array(
							"id" => $video_id
						));
					
					if (isset($identity->sub) && $identity->sub == $video->getUser()->getId()) {	
						$video->setTitle($title);
						$video->setDescription($description);
						$video->setStatus($status);
						$video->setUpdatedAt($updatedAt);

						//Persistimos los datos en doctrine
						$em->persist($video);
						//Guardamos los datos en la BD
						$em->flush();


					
						$data = array(
							"status" => "success", 
							"code" => 200, 
							"data" => "Video updated 	success!! " 
						);
					}else{
						$data = array(
							"status" => "error", 
							"code" => 400, 
							"data" => "Video updated  error, you not owner!!" 
						);
					}
				}else{
					$data = array(
						"status" => "error", 
						"code" => 400, 
						"msg" => "Video not updated"
					);
				}

			}else{
				$data = array(
					"status" => "error", 
					"code" => 400, 
					"msg" => "Video not updated, params failed"
				);
			}
			




		}else{
			$data = array(
				"status" => "error", 
				"code" => 400, 
				"msg" => "Authorization not valid"
			);
		}

		return $helpers->json($data);

	}


	/**
	 *
	 * Método para subir una imagen y un video
	 *
	 */
	public function uploadAction(Request $request, $id) {
		$helpers = $this->get("app.helpers");

		$hash = $request->get("authorization", null);
		$authCheck = $helpers->authCheck($hash);
		echo $hash;
		die();
		if ($authCheck == true) {
			$identity = $helpers->authCheck($hash, true);

			$video_id = $id;

			$em = $this->getDoctrine()->getManager();
			$video = $em->getRepository("BackendBundle:Video")->findOneBy(array(
				"id" => $video_id
			));
			

			if ($video_id != null && isset($identity->sub) && $identity->sub == $video->getUser()->getId()) {

				$file = $request->files->get('image', null);
				$file_video = $request->files->get('video', null);

				if ($file != null && !empty($file)) {
					$ext = $file->guessExtension();

					if ($ext == "jpeg" || $ext == "jpg" || $ext == "png") {
						$file_name = time() . "." . $ext;
						$path_of_file = "uploads/video_images/video_" . $video_id;
						$file->move($path_of_file, $file_name);

						$video->setImage($file_name);
						$em->persist($video);
						$em->flush();

						$data = array(
							"status" => "success",
							"code" => 200,
							"msg" => "Image file for video uploaded!!"
						);
					} else {
						$data = array(
							"status" => "error",
							"code" => 400,
							"msg" => "Format for image not valid!!"
						);
					}
				} else {
					if ($file_video != null && !empty($file_video)) {
						$ext = $file_video->guessExtension();

						if ($ext == "mp4" || $ext == "avi") {
							$file_name = time() . "." . $ext;
							$path_of_file = "uploads/video_files/video_" . $video_id;
							$file_video->move($path_of_file, $file_name);

							$video->setVideoPath($file_name);

							$em->persist($video);
							$em->flush();

							$data = array(
								"status" => "success",
								"code" => 200,
								"msg" => "Video file uploaded!!"
							);
						} else {
							$data = array(
								"status" => "error",
								"code" => 400,
								"msg" => "Format for video not valid!!"
							);
						}
					}
				}
			} else {
				$data = array(
					"status" => "error",
					"code" => 400,
					"msg" => "Video updated error, you not owner!!"
				);
			}
		} else {
			$data = array(
				"status" => "error",
				"code" => 400,
				"msg" => "Authorization not valid"
			);
		}
		return $helpers->json($data);
	}
	


}