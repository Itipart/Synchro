<?php

namespace Itipart\SynchroBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('ItipartSynchroBundle:Default:index.html.twig');
    }
}
