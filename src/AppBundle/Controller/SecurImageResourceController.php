<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class SecurImageResourceController extends Controller {

  /**
   * Passes a pre-defined SecurImage resource to the client
   *
   * @Route("/securimage/{resource}", name="securimage", requirements={"resource"=".+"})
   */
  public function pageAction(Request $request, $resource) {
    if (preg_match('#^(images/.+|securimage(_play\.swf|\.js|\.css))$#', $resource, $matches)) {
      $path = realpath(sprintf('%s/../vendor/dapphp/securimage/%s', $this->get('kernel')->getRootDir(), $matches[0]));
      if (!empty($path)) {
        return $this->file($path, null, ResponseHeaderBag::DISPOSITION_INLINE);
      }
    }
    throw $this->createNotFoundException('Resource does not exist');
  }

}

// EOF
