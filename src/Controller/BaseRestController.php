<?php

namespace StarterKit\StartBundle\Controller;

use StarterKit\StartBundle\Model\Page\PageModel;
use StarterKit\StartBundle\Model\Response\ResponseFormErrorModel;
use StarterKit\StartBundle\Model\Response\ResponseModel;
use StarterKit\StartBundle\Model\Response\ResponsePageModel;
use StarterKit\StartBundle\Model\Response\ResponseTypeInterface;
use StarterKit\StartBundle\Service\FormSerializer;
use StarterKit\StartBundle\Service\FormSerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BaseRestController extends Controller
{
    use ControllerTrait;

    /**
     * @var FormSerializerInterface
     */
    protected $formSerializer;

    public function __construct(FormSerializerInterface $formSerializer)
    {
        $this->formSerializer = $formSerializer;
    }

    /**
     * Returns a serialized error response
     *
     * @param FormInterface $form
     * @return JsonResponse
     */
    public function serializeFormError(FormInterface $form)
    {
        $errors = $this->formSerializer->createFormErrorArray($form);

        $responseModel = new ResponseFormErrorModel($errors);

        return new JsonResponse($responseModel->getBody(), Response::HTTP_BAD_REQUEST);
    }
}