<?php
namespace AppBundle\Services;


class ListManager{
    
    public function __construct($em) {
        $this->em = $em;
    }
    
    public function productList($currentPage,$sortBy,$order) {
        return $this->em->getRepository('AppBundle:Product')->findProducts($currentPage,$sortBy,$order);
    }
}