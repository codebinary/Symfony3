<?php 

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\JsonResponse;
use BackendBundle\Entity\User;
use BackendBundle\Entity\Video;
use BackendBundle\Entity\Comment;

class CommentController extends Controller{

	public function newAction(Request $request){

		$helpers = $this->get("app.helpers");

		$hash = $request->get("authorization", null);
		$authCheck = $helpers->authCheck($hash);

		if ($authCheck) {
			$identity = $helpers->authCheck($hash, true);

			$json = $request->get("json", null);

			if ($json != null) {
				$params = json_decode($json);

				$createdAt = new \Datetime('now');
				$user_id = (isset($identity->sub)) ? $identity->sub : null;
				$video_id = (isset($params->video_id)) ? $params->video_id : null;
				$body = (isset($params->body)) ? $params->body : null;

				if ($user_id != null && $video_id != null) {
					
					$em = $this->getDoctrine()->getManager();

					$user = $em->getRepository("BackendBundle:User")->findOneBy(array(
						"id" => $user_id
					));

					$video = $em->getRepository("BackendBundle:Video")->findOneBy(array(
						"id" => $video_id
					));

					$comment = new Comment();

					$comment->setUser($user);
					$comment->setVideo($video);
					$comment->setBody($body);
					$comment->setCreatedAt($createdAt);

					$em->persist($comment);
					$em->flush(); 

					$data = array(
						"status" => "success",
						"code" => 200,
						"msg" => "Comment created success!!"
					);

				}else{
					$data = array(
						"status" => "error",
						"code" => 400,
						"msg" => "Comment not created"
					);
				}

			}else{
				$data = array(
					"status" => "error",
					"code" => 400,
					"msg" => "Params not valid!!"
				);
			}
		}else{
			$data = array(
				"status" => "error",
				"code" => 400,
				"msg" => "Authentication not valid!!"
			);
		}

		return $helpers->json($data);

	}


	/**
	 *
	 * Método para eliminar un comentario
	 *
	 */
	public function deleteAction(Request $request, $id = null){

		$helpers = $this->get("app.helpers");

		$hash = $request->get("authorization", null);
		$authCheck = $helpers->authCheck($hash);

		if ($authCheck == true) {
			$identity = $helpers->authCheck($hash, true);

			$user_id = ($identity->sub != null) ? $identity->sub : null;

			$em = $this->getDoctrine()->getManager();

			$comment = $em->getRepository("BackendBundle:Comment")->findOneBy(array(
				"id" => $id
			));

			if (is_object($comment) && $user_id != null) {
				//Comprobamos si el usuario es el dueño del video o del comentario
				if (isset($identity->sub) && 
					($identity->sub == $comment->getUser()->getId() || 
					$identity->sub == $comment->getVideo()->getuser()->getid())) {
					
					$em->remove($comment);
					$em->flush();

					$data = array(
						"status" => "success",
						"code" => 200,
						"msg" => "Comment deleted success!!"
					);
				}else{
					$data = array(
						"status" => "error",
						"code" => 400,
						"msg" => "Comment not deleted!!"
					);
				}
			}else{
				$data = array(
					"status" => "error",
					"code" => 400,
					"msg" => "Comment not deleted!!"
				);
			}


			
		}else{
			$data = array(
				"status" => "error",
				"code" => 400,
				"msg" => "Authentication not valid!!"
			);
		}

		return $helpers->json($data);
	}
	
	/**
	 *
	 * Método para listar todos los comentarios
	 *
	 */
	public function listAction(Request $request, $id = null){
		$helpers = $this->get("app.helpers");
		$em = $this->getDoctrine()->getManager();
		
		$video = $em->getRepository("BackendBundle:Video")->findOneBy(array(
			"id" => $id
		));
		
		//Obtenemos todos los comentarios relacionado al video
		$comments = $em->getRepository("BackendBundle:Comment")->findBy(array(
			"video" => $video
		), array('id'=>'desc'));
		
		if(count($comments) >= 1){
			$data = array(
				"status" => "success",
				"code"	 => 200,
				"data"	 => $comments
			);
		}else{
			$data = array(
				"status" => "error",
				"code"	 => 400,
				"msg"	 => "Dont exists comments in this video!!"
			);
		}
		
		return $helpers->json($data);
	}
	

}