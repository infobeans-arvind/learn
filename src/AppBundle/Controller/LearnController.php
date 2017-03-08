<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Entity\Product;
use Snc\RedisBundle\Doctrine\Cache\RedisCache;
use Predis\Client;

class LearnController extends Controller
{
    /**
     * @Route("/index", name="index")
     * @Route("/index/{id}", name="index_edit")
     * @Template()
     */
    public function indexAction(Request $request,$id=null)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $dataObj = $em->getRepository('AppBundle:Category')->findAll();
        (object)$prodObj = "";
        if($request->isMethod('POST')){
            if(!$id){
                $prodObj = new Product();
            }else{
                $prodObj = $em->getRepository('AppBundle:Product')->findOneById($id);
            }
            
            $prodObj->setName($request->request->get('name'));
            $prodObj->setPrice($request->request->get('price'));
            $prodObj->setQuantity($request->request->get('quantity'));
            $catObj = $em->getRepository('AppBundle:Category')->findOneById($request->request->get('category'));
            $prodObj->setCategory($catObj);
            $prodObj->setCreatedDate(new \DateTime());
            $prodObj->setModifiedBy(new \DateTime());
            $prodObj->setSortOrder(1);
            
            $validator = $this->get('validator');
            $errors = $validator->validate($prodObj);

            if (count($errors) > 0) {
                return array('errors'=>$errors);
            }
            $em->persist($prodObj);
            $em->flush();
            $prodObj1 = $prodObj;
            $predis = new RedisCache();
            $predis->setRedis(new Client());
            $predis->save("[1dc22d7c67ae0cc259dda26c4cb3cd3f][1]", $prodObj1);
            
            return new RedirectResponse($this->get('router')->generate('list'));
        }else{
            if($id){
                $prodObj = $em->getRepository('AppBundle:Product')->findOneById($id);
            }
        }

        return array('categories'=>$dataObj,'product'=>$prodObj);
        
    }
    
    /**
     * @Route("/list/{page}/{sortBy}/{order}", name="list")
     * @Template()
     */
    public function listAction($page = 1,$sortBy='name',$order='ASC') {
        $list = $this->get('list_manager')->productList($page,$sortBy,$order);
        return $this->render('AppBundle:Learn:list.html.twig', compact('list'));
    }
    
    /**
     * @Route("/delete/{id}", name="delete")
     * @Template()
     */
    public function deleteAction($id) {
        $em = $this->getDoctrine()->getEntityManager();
        $prodObj = $em->getRepository('AppBundle:Product')->findOneById($id);
        $em->remove($prodObj);
        $em->flush();
        return new RedirectResponse($this->get('router')->generate('list'));
    }

}
