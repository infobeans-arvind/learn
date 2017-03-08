<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Entity\Task;
use Symfony\Component\HttpFoundation\Request;

class TaskController extends Controller
{
    /**
     * @Route("task", name="task")
     * @Template()
     */
    public function indexAction(Request $request)
    {
 // create a task and give it some dummy data for this example
        $task = new Task();
        $form = $this->createFormBuilder($task)
            ->setAction($this->generateUrl('task'))
            ->setMethod('POST')
            ->add('task', 'text')
            ->add('dueDate', 'date')
            ->add('points', 'integer')
            ->add('department', 'entity', array(
                'class' => 'AppBundle:Department',
                'property' => 'name',
                'data' => $this->getDoctrine()->getManager()->getReference("AppBundle:Department", 2)
            ))
            ->add('save', 'submit', array('label' => 'Create Post'))
            ->getForm();
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            // but, the original `$task` variable has also been updated
            $task = $form->getData();

            // ... perform some action, such as saving the task to the database
            // for example, if Task is a Doctrine entity, save it!
             $em = $this->getDoctrine()->getManager();
             $em->persist($task);
             $em->flush();

            return $this->redirectToRoute('task_success');
        }
        
        return array(
            'form' => $form->createView(),
        );
        
    }
    
    /**
     * @Route("/task_success", name="task_success")
     * @Template()
     */
    public function listAction() {
        $em = $this->getDoctrine()->getManager();
        
        $list = $em->getRepository('AppBundle:Task')->findAll();
        return compact('list');
    }

}
